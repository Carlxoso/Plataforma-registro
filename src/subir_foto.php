<?php
session_start();

if (!isset($_SESSION['cedula'])) {
    echo "No autorizado.";
    exit;
}

$cedula = $_SESSION['cedula'];

// Verificamos que haya archivo subido
if (!isset($_FILES['nueva_foto']) || $_FILES['nueva_foto']['error'] !== UPLOAD_ERR_OK) {
    echo "Error al subir el archivo.";
    exit;
}

// Configuración
$carpeta_subidas = __DIR__ . '/uploads/';
if (!is_dir($carpeta_subidas)) {
    mkdir($carpeta_subidas, 0755, true);
}

$nombre_original = $_FILES['nueva_foto']['name'];
$tipo_archivo = strtolower(pathinfo($nombre_original, PATHINFO_EXTENSION));

// Validar tipo de archivo (solo imágenes)
$ext_permitidas = ['jpg', 'jpeg', 'png', 'gif'];
if (!in_array($tipo_archivo, $ext_permitidas)) {
    echo "Tipo de archivo no permitido. Solo JPG, PNG y GIF.";
    exit;
}

// Generar nombre único para evitar sobreescritura
$nombre_nuevo = uniqid('foto_') . '.' . $tipo_archivo;

// Mover archivo a carpeta uploads
$ruta_destino = $carpeta_subidas . $nombre_nuevo;
if (!move_uploaded_file($_FILES['nueva_foto']['tmp_name'], $ruta_destino)) {
    echo "Error al mover el archivo subido.";
    exit;
}

// Conexión a la base de datos
$mysqli = new mysqli("localhost", "root", "", "vendetors");
if ($mysqli->connect_errno) {
    echo "Error al conectar a la base de datos.";
    exit;
}

$cedula_safe = $mysqli->real_escape_string($cedula);
$nombre_nuevo_safe = $mysqli->real_escape_string($nombre_nuevo);

// Actualizar el campo foto_perfil en la tabla
$query = "UPDATE usuregistro SET foto_perfil = '$nombre_nuevo_safe' WHERE cedula = '$cedula_safe' LIMIT 1";

if ($mysqli->query($query)) {
    // Redirigir de nuevo o mostrar éxito
    header("Location: administrador.php");  // O la página donde esté el modal, o recarga la info
    exit;
} else {
    echo "Error al actualizar la base de datos: " . $mysqli->error;
}

$mysqli->close();
?>
