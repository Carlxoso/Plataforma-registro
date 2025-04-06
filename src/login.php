<?php
// Conexión a la base de datos
$host = "localhost";
$usuario = "root";        // Cambia esto si tu usuario es diferente
$contrasena = "";         // Cambia esto si tienes contraseña
$baseDeDatos = "vendetors"; // Asegúrate de usar guiones bajos en lugar de espacios

$conexion = new mysqli($host, $usuario, $contrasena, $baseDeDatos);

// Verifica la conexión
if ($conexion->connect_error) {
    die("Conexión fallida: " . $conexion->connect_error);
}

// Obtener los datos del formulario
$username = $_POST['username'];
$password = $_POST['password'];
$role = $_POST['role'];

// Consulta para verificar usuario
$sql = "SELECT * FROM usuarios WHERE username = '$username' AND password = '$password' AND role = '$role'";
$resultado = $conexion->query($sql);

if ($resultado->num_rows > 0) {
    // Usuario válido
    echo "Inicio de sesión exitoso como $role";
    // Aquí puedes redirigir a otra página, por ejemplo:
    // header("Location: dashboard.php");
} else {
    // Usuario no válido
    echo "Usuario o contraseña incorrectos";
}

$conexion->close();
?>
