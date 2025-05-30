<?php
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit;
}

// Debug sesión
if (!isset($_SESSION['cedula'])) {
    echo json_encode(['success' => false, 'error' => 'No autenticado. Sesión inválida.', 'debug' => '$_SESSION no contiene cedula']);
    exit;
}

$cedula = $_SESSION['cedula'];

// Debug file upload
if (!isset($_FILES['foto_perfil'])) {
    echo json_encode(['success' => false, 'error' => 'No se envió imagen', 'debug' => '$_FILES no contiene foto_perfil']);
    exit;
}

$foto = $_FILES['foto_perfil'];

// Debug tipo y tamaño
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
if (!in_array($foto['type'], $allowedTypes)) {
    echo json_encode([
        'success' => false,
        'error' => 'Formato de imagen no permitido',
        'debug' => 'Tipo recibido: ' . $foto['type']
    ]);
    exit;
}

$maxSize = 5 * 1024 * 1024; // 5MB
if ($foto['size'] > $maxSize) {
    echo json_encode([
        'success' => false,
        'error' => 'Imagen demasiado grande',
        'debug' => 'Tamaño recibido: ' . $foto['size']
    ]);
    exit;
}

$uploadDir = 'uploads/';
if (!is_dir($uploadDir)) {
    if (!mkdir($uploadDir, 0755, true)) {
        echo json_encode([
            'success' => false,
            'error' => 'No se pudo crear carpeta uploads',
        ]);
        exit;
    }
}

$extension = pathinfo($foto['name'], PATHINFO_EXTENSION);
$nombreArchivo = 'perfil_' . $cedula . '_' . time() . '.' . $extension;
$rutaArchivo = $uploadDir . $nombreArchivo;

// Debug move_uploaded_file
if (!move_uploaded_file($foto['tmp_name'], $rutaArchivo)) {
    echo json_encode([
        'success' => false,
        'error' => 'Error al guardar la imagen',
        'debug' => 'move_uploaded_file falló'
    ]);
    exit;
}

// Conexión a la base de datos
$conn = new mysqli('localhost', 'root', '', 'vendetors');

if ($conn->connect_error) {
    echo json_encode([
        'success' => false,
        'error' => 'Error de conexión a la base de datos: ' . $conn->connect_error,
    ]);
    exit;
}

$rutaFotoParaDB = $conn->real_escape_string($rutaArchivo);
$cedula = $conn->real_escape_string($cedula);

// Debug existencia de cédula
$check = $conn->query("SELECT * FROM usuregistro WHERE cedula = '$cedula'");

if (!$check) {
    echo json_encode([
        'success' => false,
        'error' => 'Error al consultar la base de datos',
        'debug' => $conn->error,
    ]);
    $conn->close();
    exit;
}

if ($check->num_rows == 0) {
    echo json_encode([
        'success' => false,
        'error' => 'La cédula no existe en la base de datos',
    ]);
    $conn->close();
    exit;
}

// Actualizar ruta foto en BD
$sql = "UPDATE usuregistro SET foto_perfil = '$rutaFotoParaDB' WHERE cedula = '$cedula'";

if ($conn->query($sql) === TRUE) {
    echo json_encode([
        'success' => true,
        'urlFoto' => $rutaArchivo,
        'debug' => 'Actualización correcta'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Error al actualizar base de datos: ' . $conn->error,
        'debug' => $sql
    ]);
}

$conn->close();
