<?php
// Protección del panel privado: mismo login que Cifra
require_once __DIR__ . '/../cifra/config/auth_check.php';
require_auth_or_redirect();
