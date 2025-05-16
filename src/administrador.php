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

    // Verificar si el nombre ya existe
    $stmt = $conn->prepare("SELECT id FROM vendedores WHERE nombre = ?");
    $stmt->bind_param("s", $nombre);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Ya existe un vendedor con ese nombre
        $stmt->close();
        echo "<script>alert('Error: Ya existe un vendedor con ese nombre'); window.location.href='administrador.php?agregar_registro=true';</script>";
    } else {
        $stmt->close();
        // Insertar nuevo registro
        $stmt = $conn->prepare("INSERT INTO vendedores (nombre, dia, entrada, salida, producto) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $nombre, $dia, $entrada, $salida, $producto);
        $stmt->execute();
        $stmt->close();
        echo "<script>alert('Vendedor agregado correctamente'); window.location.href='administrador.php';</script>";
    }
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

</head>
<body>

    <div class="menu-toggle" onclick="document.querySelector('.sidebar').classList.toggle('active')">
    <i class="fas fa-bars"></i>
</div>

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
    <div class="form-container">
        <h2>Agregar un Nuevo Registro</h2>
        <div class="form-group">
            <div class="field-wrapper">
                <label for="nombre">Nombre:</label>
                <input type="text" name="nombre" id="nombre" placeholder="Nombre" required>
            </div>
            <div class="field-wrapper">
                <label for="dia">Día:</label>
                <input type="text" name="dia" id="dia" placeholder="Día" required>
            </div>
            <div class="field-wrapper">
                <label for="entrada">Hora de Entrada:</label>
                <input type="time" name="entrada" id="entrada" required>
            </div>
            <div class="field-wrapper">
                <label for="salida">Hora de Salida:</label>
                <input type="time" name="salida" id="salida" required>
            </div>
            <div class="field-wrapper">
                <label for="producto">Producto:</label>
                <input type="text" name="producto" id="producto" placeholder="Producto" required>
            </div>
        </div>
        <button type="submit" name="agregar" class="btn-agregar">Agregar</button>
    </div>
</form>


        <?php } else { ?>

            <!-- Tabla de registros -->
            <table style="width: 100%; border-collapse: collapse;">

                <tr style="background-color: #3498db; color: white;">
                    <th>Nombre</th>
                    <th>Día</th>
                    <th>Entrada</th>
                    <th>Salida</th>
                    <th>Producto</th>
                    <th>QR</th>
                    <th>Eliminar</th>
                    <th>Editar</th>
                    <th>
                        <div style="display: flex; align-items: center; justify-content: center;">
                            
                            <div style="position: relative; margin-left: 8px;">
                                <button type="button" onclick="toggleFiltro()" style="background: white; border: 1px solid #ccc; padding: 6px 10px; border-radius: 6px; cursor: pointer;">
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
                        </div>
                    </th>
                </tr>

                <?php $ngrok_url = "https://448a-157-100-140-76.ngrok-free.app"; ?>
                <?php while ($row = $resultado->fetch_assoc()) { ?>
                    <tr>
                        <form method="POST">
                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                            <td><?= htmlspecialchars($row['nombre']) ?></td>
                            <td><?= htmlspecialchars($row['dia']) ?></td>
                            <td><?= htmlspecialchars($row['entrada']) ?></td>
                            <td><?= htmlspecialchars($row['salida']) ?></td>
                            <td><?= htmlspecialchars($row['producto']) ?></td>

                            <td style="min-width: 120px;">
                                <button class="btn-qr" onclick="mostrarQR(event, '<?= $ngrok_url ?>/descargar_pdf.php?id=<?= $row['id'] ?>')" style="padding: 5px 10px; font-size: 13px;">Ver QR</button>

                            </td>

                            <td style="min-width: 120px;">
                                <a href="administrador.php?borrar=<?= $row['id'] ?>" onclick="return confirm('¿Estás seguro de borrar este registro?');">
                                    <button type="button" class="btn-borrar">Borrar</button>
                                </a>
                            </td>

                            <td style="min-width: 120px;">
                                <button type="button" class="btn-editar" onclick="abrirEdicion(<?= $row['id'] ?>, '<?= htmlspecialchars($row['nombre']) ?>', '<?= htmlspecialchars($row['dia']) ?>', '<?= htmlspecialchars($row['entrada']) ?>', '<?= htmlspecialchars($row['salida']) ?>', '<?= htmlspecialchars($row['producto']) ?>')">Editar</button>
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

    <!-- Modal para mostrar QR -->
    <div id="modalQR" class="modalQR">
        <div class="modal-content">
            <span class="close-btn" onclick="cerrarQR()">&times;</span>
            <h2>Código QR</h2>
            <div id="qrcode"></div>
        </div>
    </div>

    <!-- Modal para editar -->
    <div id="modalEditar" class="modalEditar">
        <div class="modal-content">
            <span class="close-btn" onclick="cerrarEdicion()">&times;</span>
            <h2>Editar Vendedor</h2>
            <form id="formEditar" method="POST">
                <input type="hidden" name="id" id="edit-id">
                <input type="text" name="nombre" id="edit-nombre" required>
                <input type="text" name="dia" id="edit-dia" required>
                <input type="time" name="entrada" id="edit-entrada" required>
                <input type="time" name="salida" id="edit-salida" required>
                <input type="text" name="producto" id="edit-producto" required>
                <button type="submit" name="editar" class="btn-editar">Actualizar</button>
            </form>
        </div>
    </div>

    <script>
    function mostrarQR(event, data) {
        event.preventDefault();  
        const modal = document.getElementById('modalQR');
        modal.style.display = 'flex'; 

        document.getElementById('qrcode').innerHTML = '';
        new QRCode(document.getElementById("qrcode"), {
            text: data,
            width: 150,
            height: 150
        });
    }

    function cerrarQR() {
        const modal = document.getElementById('modalQR');
        modal.style.display = 'none'; 
    }

    function abrirEdicion(id, nombre, dia, entrada, salida, producto) {
        const modal = document.getElementById('modalEditar');
        document.getElementById('edit-id').value = id;
        document.getElementById('edit-nombre').value = nombre;
        document.getElementById('edit-dia').value = dia;
        document.getElementById('edit-entrada').value = entrada;
        document.getElementById('edit-salida').value = salida;
        document.getElementById('edit-producto').value = producto;
        modal.style.display = 'flex'; // Modal flotante centrado
    }

    function cerrarEdicion() {
        const modal = document.getElementById('modalEditar');
        modal.style.display = 'none'; // Cerrar modal flotante
    }
    </script>

    <script>
    document.addEventListener("DOMContentLoaded", function () {
        const menuToggle = document.querySelector(".menu-toggle");
        const sidebar = document.querySelector(".sidebar");

        // Verifica si los elementos existen
        if (menuToggle && sidebar) {
            menuToggle.addEventListener("click", function () {
                sidebar.classList.toggle("active");
            });
        } else {
            console.log("No se encontraron los elementos necesarios para el menú.");
        }
    });
</script>



    
</body>
</html>
