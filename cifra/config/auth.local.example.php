<?php
// Copiar este archivo como auth.local.php (en la misma carpeta) y completar.
// auth.local.php está en .gitignore: NUNCA lo subas a GitHub.
//
// Hash de la contraseña:
//   php -r "echo password_hash('TU_PASSWORD', PASSWORD_BCRYPT), PHP_EOL;"
// Secreto para la cookie "recordarme" (64 caracteres hex):
//   php -r "echo bin2hex(random_bytes(32)), PHP_EOL;"

define('AUTH_USER',      'pablo');
define('AUTH_PASS_HASH', 'PEGAR_HASH_AQUI');
define('AUTH_SECRET',    'PEGAR_SECRETO_AQUI');
