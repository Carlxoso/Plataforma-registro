<?php
session_start();
include 'conexion.php';

if (!isset($_SESSION['cedula'])) {
    echo "<p>No autorizado.</p>";
    exit;
}

$cedula = $_SESSION['cedula'];

$stmt = $conn->prepare("SELECT cedula, nombre_completo, correo, fecha_registro, role FROM usuregistro WHERE cedula = ?");
$stmt->bind_param("s", $cedula);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<p>Perfil no encontrado.</p>";
    exit;
}

$admin = $result->fetch_assoc();
?>

<h3>Perfil del Administrador</h3>
<p><strong>Nombre completo:</strong> <?= htmlspecialchars($admin['nombre_completo']) ?></p>
<p><strong>Cédula:</strong> <?= htmlspecialchars($admin['cedula']) ?></p>
<p><strong>Correo:</strong> <?= htmlspecialchars($admin['correo']) ?></p>
<p><strong>Fecha de registro:</strong> <?= htmlspecialchars($admin['fecha_registro']) ?></p>
<p><strong>Rol:</strong> <?= htmlspecialchars($admin['role']) ?></p>
