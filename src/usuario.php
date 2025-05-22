<?php
session_start();
include 'conexion.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'user') {
    header('Location: index.php');
    exit();
}

$username = $_SESSION['username'];

// Procesar el formulario si se envió
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_perfil'])) {
    $nombre = trim($_POST['nombre']);
    $correo = trim($_POST['correo']);
    $telefono = trim($_POST['telefono']);

    // Validar datos básicos
    if (empty($nombre) || empty($correo)) {
        $error = "Nombre y correo son obligatorios.";
    } else {
        // Verificar si ya existe el usuario en userinfo
        $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM userinfo WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        if ($row['count'] > 0) {
            // Actualizar
            $stmt = $conn->prepare("UPDATE userinfo SET nombre = ?, correo = ?, telefono = ? WHERE username = ?");
            $stmt->bind_param("ssss", $nombre, $correo, $telefono, $username);
        } else {
            // Insertar
            $stmt = $conn->prepare("INSERT INTO userinfo (username, nombre, correo, telefono) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $username, $nombre, $correo, $telefono);
        }

        if ($stmt->execute()) {
            $mensaje = "Perfil actualizado correctamente.";
        } else {
            $error = "Error al actualizar perfil. Intenta de nuevo.";
        }

        $stmt->close();
    }
}

// Obtener datos actuales para mostrar (si hubo cambios, usar los recién guardados)
$stmt = $conn->prepare("SELECT nombre, correo, telefono FROM userinfo WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $userData = ['nombre' => '', 'correo' => '', 'telefono' => ''];
} else {
    $userData = $result->fetch_assoc();
}
$stmt->close();
$conn->close();

// Control para mostrar formulario editable o solo vista
$modoEdicion = isset($_POST['editar']) || ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_perfil']));

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Panel de Usuario</title>
    <link rel="stylesheet" href="css/usuario.css" />
</head>
<body>
    <div class="user-container">
        <h1>Bienvenido, <?php echo htmlspecialchars($username); ?></h1>

        <?php if (!empty($mensaje)) : ?>
            <div class="mensaje-exito"><?php echo $mensaje; ?></div>
        <?php endif; ?>
        <?php if (!empty($error)) : ?>
            <div class="mensaje-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <h2>Mi Perfil</h2>

        <?php if ($modoEdicion) : ?>
            <form method="POST" action="usuario.php" class="edit-profile-form">
                <label for="nombre">Nombre Completo:</label>
                <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($userData['nombre']); ?>" required>

                <label for="correo">Correo Electrónico:</label>
                <input type="email" id="correo" name="correo" value="<?php echo htmlspecialchars($userData['correo']); ?>" required>

                <label for="telefono">Teléfono:</label>
                <input type="tel" id="telefono" name="telefono" value="<?php echo htmlspecialchars($userData['telefono']); ?>">

                <button type="submit" name="guardar_perfil" class="btn-primary">Guardar Cambios</button>
            </form>
            <form method="POST" action="usuario.php" style="margin-top: 1rem;">
                <button type="submit" name="cancelar" class="btn-secondary">Cancelar</button>
            </form>
        <?php else: ?>
            <div class="profile-info">
                <p><strong>Nombre:</strong> <?php echo htmlspecialchars($userData['nombre']); ?></p>
                <p><strong>Correo:</strong> <?php echo htmlspecialchars($userData['correo']); ?></p>
                <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($userData['telefono']); ?></p>
            </div>

            <form method="POST" action="usuario.php" style="margin-top: 1rem;">
                <button type="submit" name="editar" class="btn-primary">Editar Perfil</button>
            </form>
        <?php endif; ?>

        <a href="logout.php" class="logout-link">Cerrar sesión</a>
    </div>
</body>
</html>
