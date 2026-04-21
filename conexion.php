<?php
$host = "monorail.proxy.rlwy.net";
$port = 54392;
$user = "root";
$password = "VuWMrBHouQPJVpfqwzbRNhmJfVICjVgM";
$database = "railway";

$conn = mysqli_connect($host, $user, $password, $database, $port);

if (!$conn) {
    die("Error de conexión: " . mysqli_connect_error());
}
$conexion = $conn;
?>