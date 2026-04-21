<?php
$host     = getenv('DB_HOST') ?: getenv('MYSQLHOST') ?: 'localhost';
$usuario  = getenv('DB_USER') ?: getenv('MYSQLUSER') ?: 'root';
$password = getenv('DB_PASS') ?: getenv('MYSQLPASSWORD') ?: '';
$base     = getenv('DB_NAME') ?: getenv('MYSQLDATABASE') ?: 'railway';
$port     = getenv('DB_PORT') ?: getenv('MYSQLPORT') ?: 3306;

$conn = new mysqli($host, $usuario, $password, $base, $port);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}
$conexion = $conn;
?>