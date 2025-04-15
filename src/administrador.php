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

// Obtener orden de filtro si se ha seleccionado
$orden = isset($_GET['orden']) ? $_GET['orden'] : '';

// Construir la consulta con el orden correspondiente
$consulta = "SELECT * FROM vendedores";
switch ($orden) {
    case 'nombre':
        $consulta .= " ORDER BY nombre ASC";
        break;
    case 'entrada':
        $consulta .= " ORDER BY entrada ASC";
        break;
    case 'salida':
        $consulta .= " ORDER BY salida ASC";
        break;
}
$resultado = $conn->query($consulta);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de registros</title>
    <link rel="stylesheet" href="css/styleadmin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    
</head>
<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo">
            <img src="assets/img/userlogo.png" alt="Admin Logo"> Administrador
        </div>
        <ul>
            <li><a href="administrador.php">Ver registros actuales</a></li>
            <li><a href="administrador.php?agregar_registro=true">Agregar un nuevo registro</a></li>
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
                    <th colspan="2" style="position: relative; text-align: center;">
    Acciones
    <div style="display: inline-block; position: relative; margin-left: 21px;">
        <button type="button" onclick="toggleFiltro()" style="background: white; border: 1px solid #ccc; padding: 6px 10px; border-radius: 6px; cursor: pointer; font-size: 16px;">
            <i class="fas fa-filter" style="color: #333;"></i>
        </button>
        <div id="filtro-menu" style="display: none; position: absolute; top: 42px; right: 0; background: white; border: 1px solid #ccc; border-radius: 6px; box-shadow: 0px 4px 10px rgba(0,0,0,0.2); z-index: 999;">
            <form method="GET" style="margin: 0; padding: 8px;">
                <button type="submit" name="orden" value="nombre" style="display: block; background: none; border: none; padding: 8px 12px; width: 100%; text-align: left; cursor: pointer;">Nombre (A-Z)</button>
                <button type="submit" name="orden" value="entrada" style="display: block; background: none; border: none; padding: 8px 12px; width: 100%; text-align: left; cursor: pointer;">Entrada</button>
                <button type="submit" name="orden" value="salida" style="display: block; background: none; border: none; padding: 8px 12px; width: 100%; text-align: left; cursor: pointer;">Salida</button>
            </form>
        </div>
    </div>
</th>


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
                            </td>
                            <td>
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
    function toggleFiltro() {
        const menu = document.getElementById("filtro-menu");
        menu.style.display = menu.style.display === "block" ? "none" : "block";
    }

    // Cierra el menú si se hace clic fuera
    document.addEventListener('click', function(event) {
        const filtro = document.getElementById("filtro-menu");
        const button = event.target.closest("button");
        if (!filtro.contains(event.target) && (!button || !button.innerHTML.includes("filter"))) {
            filtro.style.display = "none";
        }
    });
</script>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                      
</body>
</html>
