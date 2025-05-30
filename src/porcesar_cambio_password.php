<?php
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    require 'conexion.php';

    $cedula = $_SESSION['username'];
    $password_actual = $_POST['password_actual'];
    $nueva_password = $_POST['nueva_password'];

    // Obtener contraseña actual almacenada (asumiendo que está hasheada con password_hash)
    $stmt = $conn->prepare("SELECT password FROM usuregistro WHERE cedula = ?");
    $stmt->bind_param("s", $cedula);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $hash_actual = $row['password'];

        if (password_verify($password_actual, $hash_actual)) {
            // Contraseña actual correcta, actualizar a la nueva
            $nueva_hash = password_hash($nueva_password, PASSWORD_DEFAULT);
            $stmt_update = $conn->prepare("UPDATE usuregistro SET password = ? WHERE cedula = ?");
            $stmt_update->bind_param("ss", $nueva_hash, $cedula);
            if ($stmt_update->execute()) {
                echo "<script>alert('Contraseña cambiada correctamente'); window.location.href='usuario.php';</script>";
            } else {
                echo "Error al actualizar la contraseña.";
            }
            $stmt_update->close();
        } else {
            echo "<script>alert('Contraseña actual incorrecta'); window.history.back();</script>";
        }
    } else {
        echo "<script>alert('Usuario no encontrado'); window.history.back();</script>";
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: usuario.php");
    exit();
}
?>
