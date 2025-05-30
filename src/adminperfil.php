<?php
session_start();

if (!isset($_SESSION['cedula'])) {
    echo "No autorizado.";
    exit;
}

$cedula = $_SESSION['cedula'];

// Conexión a la base de datos
$mysqli = new mysqli("localhost", "root", "", "vendetors");

if ($mysqli->connect_errno) {
    echo "Error al conectar a la base de datos.";
    exit;
}

$cedula = $mysqli->real_escape_string($cedula);

$query = "SELECT * FROM usuregistro WHERE cedula = '$cedula' LIMIT 1";
$result = $mysqli->query($query);

if ($result && $result->num_rows > 0) {
    $admin = $result->fetch_assoc();
    $foto = !empty($admin['foto_perfil']) ? 'uploads/' . htmlspecialchars($admin['foto_perfil']) : 'assets/img/default.png';

    echo '
    <style>
        .perfil-container {
            display: flex;
            flex-direction: row;
            align-items: flex-start;
            gap: 25px;
            width: 100%;
            flex-wrap: nowrap;
        }

        .perfil-foto {
            width: 150px;
            text-align: center;
            background-color: #f5f5f5;
            padding: 15px;
            border-radius: 12px;
            border: 2px solid #ddd;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
            flex-shrink: 0;
        }

        .perfil-foto img {
            width: 100%;
            border-radius: 8px;
            border: 3px solid #007bff;
        }

        .perfil-datos {
            flex: 1;
            min-width: 100px;
            text-align: left;
        }

        .perfil-datos p {
            margin: 8px 0;
            word-wrap: break-word;
        }

        .perfil-foto form input[type="file"] {
            margin-top: 10px;
            width: 100%;
            padding: 4px;
            font-size: 14px;
        }

        .perfil-foto form input[type="submit"] {
            margin-top: 10px;
            padding: 6px 14px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s;
        }

        .perfil-foto form input[type="submit"]:hover {
            background-color: #0056b3;
        }

        @media (max-width: 600px) {
            .perfil-container {
                flex-direction: column;
                align-items: center;
            }

            .perfil-datos {
                text-align: center;
            }
        }
    </style>

    <div class="perfil-container">
        <div class="perfil-foto">
            <img src="' . $foto . '" alt="Foto de perfil"><br>
            <form action="subir_foto.php" method="POST" enctype="multipart/form-data">
                <input type="file" name="nueva_foto" accept="image/*" required><br>
                <input type="submit" value="Actualizar foto">
            </form>
        </div>

        <div class="perfil-datos">
            <h2>Perfil del Administrador</h2>
            <br>
            <hr>
            <p><strong>Nombre completo:</strong> ' . htmlspecialchars($admin['nombre_completo']) . '</p>
            <p><strong>Cédula:</strong> ' . htmlspecialchars($admin['cedula']) . '</p>
            <p><strong>Correo:</strong> ' . htmlspecialchars($admin['correo']) . '</p>
            <p><strong>Fecha de registro:</strong> ' . htmlspecialchars($admin['fecha_registro']) . '</p>
            <p><strong>Rol:</strong> ' . htmlspecialchars($admin['role']) . '</p>
        </div>
    </div>';
} else {
    echo "No se encontró información del administrador.";
}

$mysqli->close();
?>
