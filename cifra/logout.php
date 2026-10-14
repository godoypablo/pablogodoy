<?php
require_once 'config/auth_check.php';
cifra_start_session();
$_SESSION = [];
session_destroy();
cifra_clear_remember_cookie();
// Desde la app de Cifra vuelve a su login (queda dentro de la PWA); desde el panel, a la portada
header('Location: ' . (($_GET['volver'] ?? '') === 'cifra' ? '/cifra/login.php' : '/'));
exit;
