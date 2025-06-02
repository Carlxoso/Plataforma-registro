<?php
session_start();

// Tiempo máximo de inactividad en segundos (15 minutos)
$tiempoMaxInactividad = 15 * 60; 

// Verificar tiempo de inactividad
if (isset($_SESSION['last_activity'])) {
    $tiempoInactividad = time() - $_SESSION['last_activity'];
    if ($tiempoInactividad > $tiempoMaxInactividad) {
        session_unset();
        session_destroy();
        header("Location: login.php?mensaje=sesion_expirada");
        exit();
    }
}
$_SESSION['last_activity'] = time();

// Seguridad: solo admins
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include 'conexion.php';


$fotoAdmin = "assets/img/userlogo.png"; // Valor por defecto
$nombreAdmin = "Administrador"; // Valor por defecto

if (isset($_SESSION['cedula'])) {
    $cedulaAdmin = $_SESSION['cedula'];

    $stmt = $conn->prepare("SELECT nombre_completo, foto_perfil FROM usuregistro WHERE cedula = ?");
    if ($stmt) {
        $stmt->bind_param("s", $cedulaAdmin);
        $stmt->execute();
        $stmt->bind_result($nombreCompleto, $fotoPerfil);
        if ($stmt->fetch()) {
            if (!empty($fotoPerfil) && file_exists("uploads/$fotoPerfil")) {
                $fotoAdmin = "uploads/$fotoPerfil";
            }
            if (!empty($nombreCompleto)) {
                $nombreAdmin = $nombreCompleto;
            }
        }
        $stmt->close();
    } else {
        die("Error al preparar la consulta: " . $conn->error);
    }
}

// Agregar vendedor
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['agregar'])) {
    $nombre = $_POST['nombre'];
    $cedula = $_POST['cedula'];
    $dia = $_POST['dia'];
    $entrada = $_POST['entrada'];
    $salida = $_POST['salida'];
    $producto = $_POST['producto'];
    $zona = $_POST['zona'];

    if (!preg_match('/^\d{10}$/', $cedula)) {
        echo "<script>alert('La cédula debe contener exactamente 10 dígitos numéricos'); window.history.back();</script>";
        exit;
    }

    $stmt = $conn->prepare("SELECT cedula FROM vendedores WHERE cedula = ?");
    $stmt->bind_param("s", $cedula);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->close();
        echo "<script>alert('Error: Ya existe un vendedor con esa cédula'); window.location.href='administrador.php?agregar_registro=true';</script>";
    } else {
        $stmt->close();
        $stmt = $conn->prepare("INSERT INTO vendedores (nombre, cedula, dia, entrada, salida, producto, zona, fecha_registro, activo) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), 0)");
        $stmt->bind_param("sssssss", $nombre, $cedula, $dia, $entrada, $salida, $producto, $zona);

        $stmt->execute();
        $stmt->close();
        echo "<script>alert('Vendedor agregado correctamente'); window.location.href='administrador.php';</script>";
    }
}

// Editar vendedor
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['editar'])) {
    $cedula = $_POST['cedula'];
    $nombre = $_POST['nombre'];
    $dia = $_POST['dia'];
    $entrada = $_POST['entrada'];
    $salida = $_POST['salida'];
    $producto = $_POST['producto'];
    $zona = $_POST['zona'];

    if (!preg_match('/^\d{10}$/', $cedula)) {
        echo "<script>alert('La cédula debe contener exactamente 10 dígitos numéricos'); window.history.back();</script>";
        exit;
    }

    $stmt = $conn->prepare("UPDATE vendedores SET nombre = ?, dia = ?, entrada = ?, salida = ?, producto = ?, zona = ? WHERE cedula = ?");
    $stmt->bind_param("sssssss", $nombre, $dia, $entrada, $salida, $producto, $zona, $cedula);
    $stmt->execute();
    $stmt->close();
    echo "<script>alert('Vendedor actualizado correctamente'); window.location.href='administrador.php';</script>";
}

// Borrar vendedor
if (isset($_GET['borrar'])) {
    $cedula = $_GET['borrar'];
    $stmt = $conn->prepare("DELETE FROM vendedores WHERE cedula = ?");
    $stmt->bind_param("s", $cedula);
    $stmt->execute();
    $stmt->close();
    echo "<script>alert('Vendedor eliminado correctamente'); window.location.href='administrador.php';</script>";
}

// Desactivar vendedor
if (isset($_GET['inactivar'])) {
    $id = $_GET['inactivar'];
    $stmt = $conn->prepare("UPDATE vendedores SET activo = 0 WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    echo "<script>alert('Vendedor inactivado'); window.location.href='administrador.php';</script>";
    exit;
}

// Activar vendedor
if (isset($_GET['activar'])) {
    $id = $_GET['activar'];
    $stmt = $conn->prepare("UPDATE vendedores SET activo = 1 WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    echo "<script>alert('Vendedor activado'); window.location.href='administrador.php';</script>";
    exit;
}

// Filtro de orden
$orden = isset($_GET['orden']) ? $_GET['orden'] : '';
$consulta = "SELECT * FROM vendedores WHERE activo = 1"; // Solo vendedores activos
switch ($orden) {
    case 'nombre': $consulta .= " ORDER BY nombre ASC"; break;
    case 'entrada': $consulta .= " ORDER BY entrada ASC"; break;
    case 'salida': $consulta .= " ORDER BY salida ASC"; break;
}
$resultado = $conn->query($consulta);

// Consulta de vendedores inactivos
$consulta_inactivos = "SELECT * FROM vendedores WHERE activo = 0";
$resultado_inactivos = $conn->query($consulta_inactivos);

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
    <div class="logo" style="display: flex; align-items: center; gap: 10px; padding: 10px;">
        <img src="<?php echo $fotoAdmin; ?>" alt="Admin Foto" style="width: 40px; height: 40px; border-radius: 50%;">
        <span style="font-weight: bold;"><?php echo htmlspecialchars($nombreAdmin); ?></span>
    </div>
<br><br>
    <ul>
        <li><a href="#" id="verPerfilBtn"><b>Ver perfil</b></a></li>
        <li><a href="administrador.php">Ver registros actuales</a></li>
        <li><a href="administrador.php?agregar_registro=true">Agregar un nuevo registro</a></li>
    </ul>
</div>


  <!-- Modal -->
<div id="perfilModal" class="modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:999;">
  <div class="modal-content" style="background:#fff; margin:10% auto; padding:20px; width:75%; max-width:500px; border-radius:8px;">
    <span id="cerrarModal" style="float:right; cursor:pointer; font-size:24px;">&times;</span>
    <div id="contenidoPerfil">Cargando...</div>
  </div>
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
            <label for="cedula">Cédula:</label>
            <input type="text" name="cedula" id="cedula" placeholder="Cédula" maxlength="10" pattern="\d{10}" requiredoninput="this.value = this.value.replace(/\D/g, '')"title="La cédula debe contener exactamente 10 dígitos numéricos">
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
            <div class="field-wrapper">
    <label for="zona">Zona:</label>
    <select name="zona" id="zona" required>
        <option value="">Seleccione una zona</option>
        <option value="Facultad de Ingenierías">Facultad de Ingenierías</option>
        <option value="Facultad de Ciencias Sociales y de Servicios">Facultad de Ciencias Sociales y de Servicios</option>
        <option value="Facultad de Ciencias Administrativas y Económicas">Facultad de Ciencias Administrativas y Económicas</option>
        <option value="Facultad de Pedagogía">Facultad de Pedagogía</option>
    </select>
</div>

<input type="hidden" name="fecha_registro" id="fecha_registro">


        </div>
        <button type="submit" name="agregar" class="btn-agregar">Agregar</button>
    </div>
</form>



        <?php } else { ?>

            <!-- Tabla de registros -->
<table style="width: 100%; border-collapse: collapse;">
    <tr style="background-color: #3498db; color: white;">
        <th>Nombre</th>
        <th>Cédula</th>
        <th>Día</th>
        <th>Entrada</th>
        <th>Salida</th>
        <th>Producto</th>
        <th>Zona</th>
        <th>Fecha de Registro</th>
        <th>QR</th>
        <th>Eliminar</th>
        <th>Editar</th>
        
    </tr>

    
    <?php while ($row = $resultado->fetch_assoc()) { ?>
        <tr>
            <form method="POST">
                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                <td><?= htmlspecialchars($row['nombre']) ?></td>
                <td><?= htmlspecialchars($row['cedula']) ?></td>
                <td><?= htmlspecialchars($row['dia']) ?></td>
                <td><?= htmlspecialchars($row['entrada']) ?></td>
                <td><?= htmlspecialchars($row['salida']) ?></td>
                <td><?= htmlspecialchars($row['producto']) ?></td>
                <td><?= htmlspecialchars($row['zona']) ?></td>
                <td><?= htmlspecialchars($row['fecha_registro']) ?></td>


                <td style="min-width: 120px;">
                    <button class="btn-qr" onclick="mostrarQR(event, 'https://forcibly-legible-piglet.ngrok-free.app/Plataforma-registro/src/descargar_pdf.php?id=<?= $row['id'] ?>')" style="padding: 5px 10px; font-size: 13px;">Ver QR</button>

                </td>

                <td>
                 <a href="administrador.php?inactivar=<?= $row['id'] ?>" onclick="return confirm('¿Inactivar este vendedor?');">
                 <button type="button" class="btn-borrar">Inactivar</button>
                 </a>
                </td>


                <td style="min-width: 120px;">
                    <button type="button" class="btn-editar" onclick="abrirEdicion(
    <?= $row['id'] ?>,
    '<?= htmlspecialchars($row['nombre']) ?>',
    '<?= htmlspecialchars($row['cedula']) ?>',
    '<?= htmlspecialchars($row['dia']) ?>',
    '<?= htmlspecialchars($row['entrada']) ?>',
    '<?= htmlspecialchars($row['salida']) ?>',
    '<?= htmlspecialchars($row['producto']) ?>',
    '<?= htmlspecialchars($row['zona']) ?>',
    '<?= htmlspecialchars($row['fecha_registro']) ?>'
)">Editar</button>


                </td>
            </form>
        </tr>
    <?php } ?>

    
</table>

<h2 style="margin-top: 40px;">Vendedores Inactivos</h2>

<!-- Mensaje si no hay vendedores inactivos -->
<p id="mensajeSinInactivos" style="text-align: center; font-weight: bold; color: #777;">
    En esta sección aún no hay vendedores inactivos.
</p>

<!-- Tabla de vendedores inactivos -->
<table id="tablaInactivos" style="width: 100%; border-collapse: collapse; background-color: #f9f9f9; display: none;">
    <tr style="background-color: #e74c3c; color: white;">
        <th>Nombre</th>
        <th>Cédula</th>
        <th>Día</th>
        <th>Entrada</th>
        <th>Salida</th>
        <th>Producto</th>
        <th>Zona</th>
        <th>Fecha de Registro</th>
        <th>Activar</th>
    </tr>

    <?php
    $tieneInactivos = false;
    while ($row = $resultado_inactivos->fetch_assoc()) {
        $tieneInactivos = true;
    ?>
        <tr style="opacity: 0.5;">
            <td><?= htmlspecialchars($row['nombre']) ?></td>
            <td><?= htmlspecialchars($row['cedula']) ?></td>
            <td><?= htmlspecialchars($row['dia']) ?></td>
            <td><?= htmlspecialchars($row['entrada']) ?></td>
            <td><?= htmlspecialchars($row['salida']) ?></td>
            <td><?= htmlspecialchars($row['producto']) ?></td>
            <td><?= htmlspecialchars($row['zona']) ?></td>
            <td><?= htmlspecialchars($row['fecha_registro']) ?></td>
            <td>
                <a href="administrador.php?activar=<?= $row['id'] ?>" onclick="return confirm('¿Activar este vendedor?');">
                    <button type="button" class="btn-editar">Activar</button>
                </a>
            </td>
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

    <label for="edit-nombre">Nombre:</label>
    <input type="text" name="nombre" id="edit-nombre" required>

    <label for="edit-cedula">Cédula:</label>
    <input type="text" name="cedula" id="edit-cedula" maxlength="10" pattern="\d{10}" required
           oninput="this.value = this.value.replace(/\D/g, '')"
           title="La cédula debe contener exactamente 10 dígitos numéricos">

    <label for="edit-dia">Día:</label>
    <input type="text" name="dia" id="edit-dia" required>

    <label for="edit-entrada">Hora de Entrada:</label>
    <input type="time" name="entrada" id="edit-entrada" required>

    <label for="edit-salida">Hora de Salida:</label>
    <input type="time" name="salida" id="edit-salida" required>

    <label for="edit-producto">Producto:</label>
    <input type="text" name="producto" id="edit-producto" required>

    <label for="edit-zona">Zona:</label>
    <select name="zona" id="edit-zona" required>
        <option value="">Seleccione una zona</option>
        <option value="Facultad de Ingenierías">Facultad de Ingenierías</option>
        <option value="Facultad de Ciencias Sociales y de Servicios">Facultad de Ciencias Sociales y de Servicios</option>
        <option value="Facultad de Ciencias Administrativas y Económicas">Facultad de Ciencias Administrativas y Económicas</option>
        <option value="Facultad de Pedagogía">Facultad de Pedagogía</option>
    </select>

    <label for="edit-fecha">Fecha de Registro</label>
    <input type="text" id="edit-fecha" name="fecha_registro" readonly>


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

    function abrirEdicion(id, nombre, cedula, dia, entrada, salida, producto, zona, fecha_registro) {
    document.getElementById('edit-id').value = id;
    document.getElementById('edit-nombre').value = nombre;
    document.getElementById('edit-cedula').value = cedula;
    document.getElementById('edit-dia').value = dia;
    document.getElementById('edit-entrada').value = entrada;
    document.getElementById('edit-salida').value = salida;
    document.getElementById('edit-producto').value = producto;
    document.getElementById('edit-fecha').value = fecha_registro;  // ahora sí definido
    document.getElementById('edit-zona').value = zona;

    document.getElementById('modalEditar').style.display = 'flex';
}


function cerrarEdicion() {
    document.getElementById('modalEditar').style.display = 'none';
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

<script>
    // Previene que se pueda usar el botón de retroceso para regresar al login
    history.pushState(null, null, location.href);
    window.onpopstate = function () {
        history.go(1);
    };
</script>

<!-- Mostrar u ocultar tabla/mensaje según si hay resultados -->
<script>
    const tieneInactivos = <?= $tieneInactivos ? 'true' : 'false' ?>;
    if (tieneInactivos) {
        document.getElementById('tablaInactivos').style.display = 'table';
        document.getElementById('mensajeSinInactivos').style.display = 'none';
    } else {
        document.getElementById('tablaInactivos').style.display = 'none';
        document.getElementById('mensajeSinInactivos').style.display = 'block';
    }
</script>

<script>
document.getElementById('verPerfilBtn').addEventListener('click', function(e) {
    e.preventDefault();

    // Mostrar el modal
    document.getElementById('perfilModal').style.display = 'block';

    // Cargar el contenido desde adminperfil.php
    fetch('adminperfil.php')
        .then(response => response.text())
        .then(data => {
            document.getElementById('contenidoPerfil').innerHTML = data;
        })
        .catch(error => {
            document.getElementById('contenidoPerfil').innerHTML = '<p>Error al cargar el perfil.</p>';
            console.error(error);
        });
});

document.getElementById('cerrarModal').addEventListener('click', function() {
    document.getElementById('perfilModal').style.display = 'none';
});
</script>

 
</body>
</html>