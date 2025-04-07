<?php
// conexión.php - Establece la conexión a la base de datos MySQL

$host = "localhost";
$usuario = "root";
$contrasena = "";
$baseDeDatos = "vendetors";

// Crear conexión
$conn = new mysqli($host, $usuario, $contrasena, $baseDeDatos);

// Verificar la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Opcional: puedes quitar este echo después de probar
// echo "Conexión exitosa a la base de datos.";
?>
