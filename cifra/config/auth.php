<?php
// ── Credenciales de acceso ───────────────────────────────────────
// Las credenciales reales viven en auth.local.php (NO se sube a Git).
// Para crearlo: copiá auth.local.example.php → auth.local.php y completalo.

$__auth_local = __DIR__ . '/auth.local.php';
if (!file_exists($__auth_local)) {
    http_response_code(500);
    error_log('[cifra] Falta config/auth.local.php');
    exit('Configuración de acceso incompleta.');
}
require_once $__auth_local;
unset($__auth_local);

if (!defined('REMEMBER_DAYS'))     define('REMEMBER_DAYS', 30);
if (!defined('LOGIN_MAX_INTENTOS')) define('LOGIN_MAX_INTENTOS', 5);
if (!defined('LOGIN_BLOQUEO_SEG'))  define('LOGIN_BLOQUEO_SEG', 15 * 60);
