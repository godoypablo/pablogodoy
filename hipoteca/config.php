<?php
// Configuración de conexión a MariaDB
// Base de datos: gastos_personales

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');  // Cambiar si tienes contraseña
define('DB_NAME', 'gastos_personales');
define('DB_PORT', 3306);

// Conectar a MariaDB
$conexion = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

// Verificar conexión
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Establecer charset UTF-8
$conexion->set_charset("utf8mb4");
