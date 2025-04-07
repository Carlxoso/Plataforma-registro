<?php
session_start();
include 'conexion.php';

// Verificar si el usuario es administrador
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Agregar vendedor
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['agregar'])) {
    $nombre = $_POST['nombre'];
    $dia = $_POST['dia'];
    $entrada = $_POST['entrada'];
    $salida = $_POST['salida'];
    $producto = $_POST['producto'];

    $stmt = $conn->prepare("INSERT INTO vendedores (nombre, dia, entrada, salida, producto) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $nombre, $dia, $entrada, $salida, $producto);
    $stmt->execute();
    $stmt->close();
    echo "<script>alert('Vendedor agregado correctamente'); window.location.href='administrador.php';</script>";
}

// Editar vendedor
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['editar'])) {
    $id = $_POST['id'];
    $nombre = $_POST['nombre'];
    $dia = $_POST['dia'];
    $entrada = $_POST['entrada'];
    $salida = $_POST['salida'];
    $producto = $_POST['producto'];

    $stmt = $conn->prepare("UPDATE vendedores SET nombre=?, dia=?, entrada=?, salida=?, producto=? WHERE id=?");
    $stmt->bind_param("sssssi", $nombre, $dia, $entrada, $salida, $producto, $id);
    $stmt->execute();
    $stmt->close();
    echo "<script>alert('Vendedor actualizado correctamente'); window.location.href='administrador.php';</script>";
}

// Borrar vendedor
if (isset($_GET['borrar'])) {
    $id = $_GET['borrar'];
    $stmt = $conn->prepare("DELETE FROM vendedores WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    echo "<script>alert('Vendedor eliminado correctamente'); window.location.href='administrador.php';</script>";
}

// Obtener todos los registros
$resultado = $conn->query("SELECT * FROM vendedores");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de registros</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
        }

        /* Menú lateral */
        .sidebar {
            width: 250px;
            height: 100vh;
            background-color: #333;
            position: fixed;
            left: 0;
            top: 0;
            padding-top: 30px;
            color: white;
            transition: all 0.3s;
        }

        .sidebar .logo {
            display: flex;
            align-items: center;
            padding: 20px;
            font-size: 24px;
            font-weight: bold;
        }

        .sidebar .logo img {
            width: 40px;
            height: 40px;
            margin-right: 10px;
        }

        .sidebar ul {
            padding: 0;
            list-style: none;
        }

        .sidebar ul li {
            padding: 15px 20px;
            text-align: left;
        }

        .sidebar ul li a {
            color: white;
            text-decoration: none;
            display: block;
        }

        .sidebar ul li a:hover {
            background-color: #444;
        }

        .main-content {
            margin-left: 250px;
            padding: 20px;
            width: 100%;
            background-color: #f4f4f9;
        }

        .logout-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            background: #e74c3c;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        form, table {
            margin: auto;
            width: 90%;
            max-width: 1200px;
        }

        input[type="text"], input[type="time"] {
            padding: 8px;
            margin: 5px 0;
            width: 100%;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        table {
            margin-top: 30px;
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.27);
        }

        th, td {
            padding: 12px 15px;
            border-bottom: 1px solid #ddd;
            text-align: center;
        }

        th {
            background-color: #3498db;
            color: white;
        }

        tr:hover {
            background-color:rgb(255, 255, 255);
        }

        button {
            padding: 6px 12px;
            margin: 2px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .btn-agregar {
            background-color: #2ecc71;
            color: white;
        }

        .btn-editar {
            background-color: #f39c12;
            color: white;
        }

        .btn-borrar {
            background-color: #e74c3c;
            color: white;
        }

        .form-group {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 10px;
        }

        /* Menu de hamburguesa */
        .menu-toggle {
            display: none;
        }

        @media (max-width: 768px) {
            .sidebar {
                left: -250px;
            }

            .sidebar.active {
                left: 0;
            }

            .menu-toggle {
                display: block;
                position: absolute;
                top: 20px;
                left: 20px;
                font-size: 30px;
                cursor: pointer;
            }

            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo">
            <img src="assets/img/userlogo.png" alt="Admin Logo"> Administrador
        </div>
        <ul>
            <li><a href="administrador.php">Ver registros actuales</a></li>
            <li><a href="administrador.php?agregar_registro=true">Agregar un nuevo registro</a></li> <!-- Enlace actualizado -->
        </ul>
    </div>

    <!-- Menu Toggle Button -->
    <div class="menu-toggle" onclick="toggleMenu()">
        <i class="fas fa-bars"></i>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <h1>Panel de información general.</h1>

        <?php if (isset($_GET['agregar_registro']) && $_GET['agregar_registro'] == 'true') { ?>
            <!-- Agregar registro -->
            <form method="POST">
                <div class="form-group">
                    <input type="text" name="nombre" placeholder="Nombre" required>
                    <input type="text" name="dia" placeholder="Día" required>
                    <input type="time" name="entrada" required>
                    <input type="time" name="salida" required>
                    <input type="text" name="producto" placeholder="Producto" required>
                </div>
                <button type="submit" name="agregar" class="btn-agregar">Agregar</button>
            </form>
        <?php } else { ?>
            <!-- Tabla de registros -->
            <table>
                <tr>
                    <th>Nombre</th>
                    <th>Día</th>
                    <th>Entrada</th>
                    <th>Salida</th>
                    <th>Producto</th>
                    <th>Acciones</th>
                </tr>
                <?php while ($row = $resultado->fetch_assoc()) { ?>
                    <tr>
                        <form method="POST">
                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                            <td><input type="text" name="nombre" value="<?= $row['nombre'] ?>"></td>
                            <td><input type="text" name="dia" value="<?= $row['dia'] ?>"></td>
                            <td><input type="time" name="entrada" value="<?= $row['entrada'] ?>"></td>
                            <td><input type="time" name="salida" value="<?= $row['salida'] ?>"></td>
                            <td><input type="text" name="producto" value="<?= $row['producto'] ?>"></td>
                            <td>
                                <button type="submit" name="editar" class="btn-editar">Editar</button>
                                <a href="administrador.php?borrar=<?= $row['id'] ?>" onclick="return confirm('¿Estás seguro de borrar este registro?');">
                                    <button type="button" class="btn-borrar">Borrar</button>
                                </a>
                            </td>
                        </form>
                    </tr>
                <?php } ?>
            </table>
        <?php } ?>

        <form method="POST" action="logout.php">
            <button class="logout-btn" type="submit">Cerrar sesión</button>
        </form>
    </div>

    <script>
        // Función para mostrar y ocultar el menú en dispositivos móviles
        function toggleMenu() {
            const sidebar = document.querySelector('.sidebar');
            sidebar.classList.toggle('active');
        }
    </script>
</body>
</html>
