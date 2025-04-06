<?php
// Conexión a la base de datos
$host = "localhost";
$usuario = "root";
$contrasena = "";
$baseDeDatos = "vendetors";

$conexion = new mysqli($host, $usuario, $contrasena, $baseDeDatos);

if ($conexion->connect_error) {
    die("Conexión fallida: " . $conexion->connect_error);
}

// Recibir datos del formulario
$username = $_POST['username'];
$password = $_POST['password'];
$role = $_POST['role'];

// Consulta
$sql = "SELECT * FROM usuarios WHERE username = '$username' AND password = '$password' AND role = '$role'";
$resultado = $conexion->query($sql);

if ($resultado->num_rows > 0) {
    // Si el usuario es admin, redirige
    if ($role === 'admin') {
        header("Location: admin.html");
        exit();
    } else {
        // También puedes redirigir al usuario normal si lo deseas
        header("Location: usuario.html");
        exit();
    }
} else {
    // Si no coincide, vuelve al login con mensaje de error
    echo "<script>
        alert('Usuario o contraseña incorrectos');
        window.history.back();
    </script>";
}

$conexion->close();
?>
