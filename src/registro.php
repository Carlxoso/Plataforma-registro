<?php
$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $host = "localhost";
    $usuario = "root";
    $contrasena = "";
    $baseDeDatos = "vendetors";

    $conn = new mysqli($host, $usuario, $contrasena, $baseDeDatos);

    if ($conn->connect_error) {
        die("Conexión fallida: " . $conn->connect_error);
    }

    $nombre = trim($_POST['nombre_completo']);
$cedula = trim($_POST['cedula']);
$correo = trim($_POST['correo']);
$password = trim($_POST['password']);

if (empty($nombre) || empty($cedula) || empty($correo) || empty($password)) {
    $mensaje = "Por favor completa todos los campos.";
} elseif (!preg_match('/^\d{1,10}$/', $cedula)) {
    $mensaje = "La cédula debe contener solo números y tener un máximo de 10 dígitos.";
} elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    $mensaje = "Correo electrónico no válido.";
} else {


        // Verificar si cédula o correo ya existen
        $stmtCheck = $conn->prepare("SELECT * FROM usuregistro WHERE cedula = ? OR correo = ?");
        $stmtCheck->bind_param("ss", $cedula, $correo);
        $stmtCheck->execute();
        $resultadoCheck = $stmtCheck->get_result();

        if ($resultadoCheck->num_rows > 0) {
            $mensaje = "La cédula o correo ya están registrados.";
        } else {
            $fecha_registro = date("Y-m-d H:i:s");

            // Hashear contraseña
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("INSERT INTO usuregistro (nombre_completo, cedula, correo, password, fecha_registro) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $nombre, $cedula, $correo, $passwordHash, $fecha_registro);

            if ($stmt->execute()) {
                echo "<script>
                    alert('✅ Registro exitoso. Ahora puedes iniciar sesión.');
                    window.location.href = 'index.php';
                </script>";
                exit();
            } else {
                $mensaje = "❌ Error al registrar: " . $stmt->error;
            }

            $stmt->close();
        }

        $stmtCheck->close();
    }

    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Registro de Usuario</title>
    <link rel="stylesheet" href="css/style.css" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
</head>
<body>
<div class="login-container">
    <div style="text-align: center; margin-bottom: 20px;">
        <img src="../src/assets/img/escudoutlvte.png" alt="Logo" style="max-width: 150px;" />
    </div>

    <h2>Registro de Usuario</h2>

    <?php if (!empty($mensaje)) : ?>
        <p style="color:<?= str_starts_with($mensaje, '✅') ? 'green' : 'red' ?>;">
            <?= htmlspecialchars($mensaje) ?>
        </p>
    <?php endif; ?>

    <form method="POST" action="registro.php">
    <div class="form-group">
        <label for="nombre_completo">Nombre completo:</label>
        <input type="text" id="nombre_completo" name="nombre_completo" required />
    </div>

    <div class="form-group">
    <label for="cedula">Cédula:</label>
    <input type="text" id="cedula" name="cedula" required
           pattern="\d{1,10}" maxlength="10" inputmode="numeric"
           title="La cédula debe contener solo números y tener un máximo de 10 dígitos" />
</div>


    <div class="form-group">
        <label for="correo">Correo electrónico:</label>
        <input type="email" id="correo" name="correo" required />
    </div>

    <div class="form-group">
        <label for="password">Contraseña:</label>
        <input type="password" id="password" name="password" required />
    </div>

    <button type="submit" class="btn">Registrar</button>
</form>


    <div style="margin-top: 15px;">
        <a href="index.php" class="btn" style="display: block; text-align: center; width: 100%; box-sizing: border-box; text-decoration: none;">
            Volver al inicio
        </a>
    </div>
</div>

<script>
document.getElementById('cedula').addEventListener('input', function (e) {
    // Elimina cualquier caracter que no sea un dígito
    this.value = this.value.replace(/\D/g, '');
});
</script>


</body>
</html>
