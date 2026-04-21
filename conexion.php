<?php
$host     = getenv('DB_HOST');
$usuario  = getenv('DB_USER');
$password = getenv('DB_PASS');
$base     = getenv('DB_NAME');

$conn = new mysqli($host, $usuario, $password, $base);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}
?>