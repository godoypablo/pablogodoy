<?php
require_once __DIR__ . '/auth.php';

function cifra_es_https() {
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
}

function cifra_start_session() {
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'secure'   => cifra_es_https(),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();
    }
}

function cifra_validate_remember_cookie() {
    if (empty($_COOKIE['cifra_remember'])) return false;
    $parts = explode('|', $_COOKIE['cifra_remember']);
    if (count($parts) !== 3) return false;
    [$user, $expiry, $mac] = $parts;
    if ((int)$expiry < time()) return false;
    $expected = hash_hmac('sha256', $user . '|' . $expiry, AUTH_SECRET);
    if (!hash_equals($expected, $mac)) return false;
    return $user === AUTH_USER;
}

function cifra_set_remember_cookie() {
    $user   = AUTH_USER;
    $expiry = time() + REMEMBER_DAYS * 86400;
    $mac    = hash_hmac('sha256', $user . '|' . $expiry, AUTH_SECRET);
    setcookie('cifra_remember', $user . '|' . $expiry . '|' . $mac, [
        'expires'  => $expiry,
        'path'     => '/',
        'secure'   => cifra_es_https(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

function cifra_clear_remember_cookie() {
    setcookie('cifra_remember', '', [
        'expires'  => time() - 3600,
        'path'     => '/',
        'secure'   => cifra_es_https(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

// Marca la sesión como autenticada (nuevo ID de sesión: evita fijación de sesión)
function cifra_marcar_logueado() {
    session_regenerate_id(true);
    $_SESSION['logged_in'] = true;
}

// ── Límite de intentos de login (por IP) ─────────────────────────
function cifra_intentos_archivo() {
    $dir = sys_get_temp_dir() . '/cifra_login';
    if (!is_dir($dir)) @mkdir($dir, 0700, true);
    return $dir . '/' . hash('sha256', $_SERVER['REMOTE_ADDR'] ?? 'cli');
}

function cifra_intentos_leer() {
    $f = cifra_intentos_archivo();
    $data = is_file($f) ? json_decode((string)@file_get_contents($f), true) : null;
    if (!is_array($data) || ($data['desde'] ?? 0) < time() - LOGIN_BLOQUEO_SEG) {
        return ['desde' => time(), 'fallos' => 0];
    }
    return $data;
}

// Segundos que faltan para poder reintentar (0 = no bloqueado)
function cifra_login_bloqueado() {
    $d = cifra_intentos_leer();
    if ($d['fallos'] < LOGIN_MAX_INTENTOS) return 0;
    return max(0, $d['desde'] + LOGIN_BLOQUEO_SEG - time());
}

function cifra_login_fallido() {
    $d = cifra_intentos_leer();
    $d['fallos']++;
    @file_put_contents(cifra_intentos_archivo(), json_encode($d), LOCK_EX);
}

function cifra_login_reset() {
    @unlink(cifra_intentos_archivo());
}

// Solo permite redirigir a rutas internas del sitio
function cifra_destino_seguro($next) {
    if (is_string($next) && preg_match('#^/(?![/\\\\])#', $next)) return $next;
    return '/cifra/index.php';
}

// Errores para el cliente: los de BD se ocultan (van al log), el resto se muestra
function cifra_error_publico(Throwable $e) {
    error_log('[cifra] ' . get_class($e) . ': ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
    if ($e instanceof PDOException || $e instanceof mysqli_sql_exception) {
        return 'Error interno de base de datos';
    }
    return 'Error: ' . $e->getMessage();
}

// ── Acceso al viaje con clave compartida del grupo ───────────────
// Cookie propia (no da acceso a Cifra ni al panel). Si estás logueado, entrás directo.
function viaje_cookie_valida() {
    if (empty($_COOKIE['viaje_acceso'])) return false;
    $parts = explode('|', $_COOKIE['viaje_acceso']);
    if (count($parts) !== 2) return false;
    [$expiry, $mac] = $parts;
    if ((int)$expiry < time()) return false;
    return hash_equals(hash_hmac('sha256', 'viaje|' . $expiry, AUTH_SECRET), $mac);
}

// Muestra el formulario de clave y corta la ejecución si no hay acceso
function viaje_require_acceso() {
    cifra_start_session();
    if (!empty($_SESSION['logged_in']) || viaje_cookie_valida()) return;
    if (cifra_validate_remember_cookie()) {
        cifra_marcar_logueado();
        return;
    }

    $error = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['viaje_clave'])) {
        if ($espera = cifra_login_bloqueado()) {
            $error = 'Demasiados intentos. Probá de nuevo en ' . ceil($espera / 60) . ' minutos.';
        } elseif (defined('VIAJE_PASS_HASH') && password_verify($_POST['viaje_clave'], VIAJE_PASS_HASH)) {
            cifra_login_reset();
            $expiry = time() + VIAJE_RECORDAR_DIAS * 86400;
            setcookie('viaje_acceso', $expiry . '|' . hash_hmac('sha256', 'viaje|' . $expiry, AUTH_SECRET), [
                'expires'  => $expiry,
                'path'     => '/',
                'secure'   => cifra_es_https(),
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
            exit;
        } else {
            cifra_login_fallido();
            $error = 'Clave incorrecta.';
        }
    }

    require __DIR__ . '/viaje_acceso.php';
    exit;
}

// Para páginas PHP: redirige a login si no está autenticado
function require_auth_or_redirect() {
    cifra_start_session();
    if (!empty($_SESSION['logged_in'])) return;
    if (cifra_validate_remember_cookie()) {
        cifra_marcar_logueado();
        return;
    }
    header('Location: /cifra/login.php?next=' . urlencode($_SERVER['REQUEST_URI'] ?? '/cifra/'));
    exit;
}

// Para APIs: devuelve 401 JSON si no está autenticado
function require_auth_or_401() {
    cifra_start_session();
    if (!empty($_SESSION['logged_in'])) return;
    if (cifra_validate_remember_cookie()) {
        cifra_marcar_logueado();
        return;
    }
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}
