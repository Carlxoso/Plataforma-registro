<?php
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    require 'conexion.php';

    $cedula = $_POST['cedula'];
    $nombre_completo = $_POST['nombre_completo'];
    $correo = $_POST['correo'];

    if ($conn) {
        $sql = "UPDATE usuregistro SET nombre_completo=?, correo=? WHERE cedula=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $nombre_completo, $correo, $cedula);

        if ($stmt->execute()) {
            $_SESSION['nombre'] = $nombre_completo; // Actualizar sesión con nuevo nombre
            $stmt->close();
            $conn->close();
            echo "<script>alert('Perfil actualizado correctamente'); window.location.href='usuario.php';</script>";
            exit();
        } else {
            echo "Error al actualizar perfil: " . $conn->error;
        }
    }
}
?>
