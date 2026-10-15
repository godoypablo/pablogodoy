<?php
// ── Formulario de contacto de la landing ─────────────────────────
// Recibe POST desde index.php (fetch → JSON; sin JS → redirige con ?enviado=1 / ?error=1)

const CONTACTO_DESTINO   = 'godoypablo@gmail.com';
const CONTACTO_REMITENTE = 'web@pablogodoy.com.ar';   // debe ser del dominio para no caer en spam
const CONTACTO_MAX_HORA  = 3;                          // envíos por IP por hora

$esAjax = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'fetch';

function responder($ok, $mensaje, $esAjax) {
    if ($esAjax) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($ok ? 200 : 422);
        echo json_encode(['ok' => $ok, 'mensaje' => $mensaje], JSON_UNESCAPED_UNICODE);
    } else {
        header('Location: /?' . ($ok ? 'enviado=1' : 'error=1') . '#contacto');
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /#contacto');
    exit;
}

// Anti-spam 1: campo trampa invisible (los bots lo completan)
if (!empty($_POST['sitio_web'])) {
    responder(true, '¡Gracias! Te respondo a la brevedad.', $esAjax);
}

// Anti-spam 2: el formulario no puede enviarse en menos de 3 segundos
$t = (int)($_POST['t'] ?? 0);
if ($t <= 0 || time() - $t < 3) {
    responder(false, 'Esperá unos segundos y volvé a enviar.', $esAjax);
}

// Anti-spam 3: límite por IP
$dir = sys_get_temp_dir() . '/pg_contacto';
if (!is_dir($dir)) @mkdir($dir, 0700, true);
$f = $dir . '/' . hash('sha256', $_SERVER['REMOTE_ADDR'] ?? '');
$envios = is_file($f) ? array_filter((array)json_decode((string)@file_get_contents($f), true), fn($ts) => $ts > time() - 3600) : [];
if (count($envios) >= CONTACTO_MAX_HORA) {
    responder(false, 'Recibí varios mensajes tuyos. Si es urgente, escribime por WhatsApp.', $esAjax);
}

// Validación
$limpiar = fn($v, $max) => mb_substr(trim(str_replace(["\r", "\n"], ' ', (string)$v)), 0, $max);
$nombre   = $limpiar($_POST['nombre'] ?? '', 100);
$email    = $limpiar($_POST['email'] ?? '', 150);
$telefono = $limpiar($_POST['telefono'] ?? '', 40);
$servicio = $limpiar($_POST['servicio'] ?? '', 60);
$mensaje  = mb_substr(trim((string)($_POST['mensaje'] ?? '')), 0, 4000);

if ($nombre === '' || $mensaje === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    responder(false, 'Completá nombre, un email válido y tu consulta.', $esAjax);
}

$servicios = ['Sitio web', 'App Android / iPhone', 'Software a medida', 'Consultoría', 'Otro'];
if (!in_array($servicio, $servicios, true)) $servicio = 'Otro';

// Envío
$asunto = '=?UTF-8?B?' . base64_encode("Consulta web: $servicio — $nombre") . '?=';
$cuerpo = "Nueva consulta desde pablogodoy.com.ar\n\n"
        . "Nombre:   $nombre\n"
        . "Email:    $email\n"
        . "Teléfono: " . ($telefono ?: '-') . "\n"
        . "Servicio: $servicio\n\n"
        . "Mensaje:\n$mensaje\n\n"
        . "--\nIP: " . ($_SERVER['REMOTE_ADDR'] ?? '-') . ' · ' . date('d/m/Y H:i');
$headers = implode("\r\n", [
    'From: Web Pablo Godoy <' . CONTACTO_REMITENTE . '>',
    'Reply-To: ' . $email,
    'MIME-Version: 1.0',
    'Content-Type: text/plain; charset=UTF-8',
    'Content-Transfer-Encoding: 8bit',
]);

if (!@mail(CONTACTO_DESTINO, $asunto, $cuerpo, $headers, '-f' . CONTACTO_REMITENTE)) {
    error_log('[contacto] mail() falló para ' . $email);
    responder(false, 'No se pudo enviar. Escribime por WhatsApp, por favor.', $esAjax);
}

$envios[] = time();
@file_put_contents($f, json_encode(array_values($envios)), LOCK_EX);
responder(true, '¡Gracias, ' . $nombre . '! Te respondo a la brevedad.', $esAjax);
