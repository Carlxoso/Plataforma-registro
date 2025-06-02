<?php
session_start();

// Verificación de sesión
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

require 'conexion.php';

// Datos de sesión
$cedula = $_SESSION['username']; // la cédula es el "username"
$nombre = htmlspecialchars($_SESSION['nombre']); // nombre completo desde sesión

// Verificar si el usuario tiene un registro en la tabla vendedores
$stmt = $conn->prepare("SELECT activo FROM vendedores WHERE cedula = ? ORDER BY id DESC LIMIT 1");
$stmt->bind_param("s", $cedula);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $fila = $result->fetch_assoc();
    if ($fila['activo'] == 0) {
        // Overlay estilizado para cuenta en revisión
        $overlayHTML = "
        <style>
          #bloqueo-overlay {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(255, 255, 255, 0.98);
            z-index: 9999;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
            box-sizing: border-box;
          }
          #bloqueo-overlay .content-box {
            background: #fff;
            padding: 40px 50px;
            border-radius: 12px;
            box-shadow: 0 12px 32px rgba(217, 83, 79, 0.2);
            max-width: 400px;
            width: 100%;
            text-align: center;
            color: #d9534f;
          }
          #bloqueo-overlay .content-box h2 {
            font-weight: 700;
            font-size: 26px;
            margin-bottom: 20px;
          }
          #bloqueo-overlay .content-box p {
            font-size: 16px;
            margin-bottom: 40px;
            line-height: 1.5;
          }
          #bloqueo-overlay .logout-btn {
            background-color: #d9534f;
            color: white;
            border: none;
            padding: 14px 40px;
            font-size: 16px;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            box-shadow: 0 6px 16px rgba(217, 83, 79, 0.5);
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
            user-select: none;
          }
          #bloqueo-overlay .logout-btn:hover {
            background-color: #c9302c;
            box-shadow: 0 8px 20px rgba(201, 48, 44, 0.7);
          }
        </style>

        <div id='bloqueo-overlay'>
          <div class='content-box'>
            <h2>¡Cuenta en revisión!</h2>
            <p>
              Un administrador está evaluando su solicitud.<br>
              Por favor, espere hasta que su cuenta sea activada.
            </p>
            <button class='logout-btn' onclick=\"location.href='logout.php'\">Cerrar Sesión</button>
          </div>
        </div>
        ";
        echo $overlayHTML;
        exit();
    }
}
$stmt->close();

// Procesar registro si viene por POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre   = $_POST['nombre'];
    $cedula   = $_POST['cedula'];
    $dia      = $_POST['dia'];
    $entrada  = $_POST['entrada'];
    $salida   = $_POST['salida'];
    $producto = $_POST['producto'];
    $zona     = $_POST['zona'];

    if ($dia && $entrada && $salida && $producto && $zona && ($salida > $entrada)) {
        // Guardar como inactivo (activo = 0)
        $sql = "INSERT INTO vendedores (nombre, cedula, dia, entrada, salida, producto, zona, activo)
                VALUES (?, ?, ?, ?, ?, ?, ?, 0)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssss", $nombre, $cedula, $dia, $entrada, $salida, $producto, $zona);

        if ($stmt->execute()) {
            echo "
            <script>
                alert('Registro exitoso. Su solicitud está en revisión.');
                window.location.reload(); // Forzar recarga para mostrar el overlay
            </script>
            ";
        } else {
            echo "<script>alert('Error al registrar');</script>";
        }

        $stmt->close();
    } else {
        echo "<script>alert('Por favor complete todos los campos correctamente');</script>";
    }
}

// Verificar si ya es vendedor
$stmt = $conn->prepare("SELECT * FROM vendedores WHERE cedula = ?");
$stmt->bind_param("s", $cedula);
$stmt->execute();
$result = $stmt->get_result();

$esVendedor = $result->num_rows > 0;
$datosVendedor = $esVendedor ? $result->fetch_assoc() : null;
$stmt->close();

// Obtener la foto desde la tabla usuregistro
$stmtFoto = $conn->prepare("SELECT foto_perfil FROM usuregistro WHERE cedula = ?");
$stmtFoto->bind_param("s", $cedula);
$stmtFoto->execute();
$resultFoto = $stmtFoto->get_result();

$fotoVendedor = 'uploads/default.jpg'; // Valor por defecto
if ($rowFoto = $resultFoto->fetch_assoc()) {
    if (!empty($rowFoto['foto_perfil'])) {
        $fotoVendedor = $rowFoto['foto_perfil'];
    }
}

$stmtFoto->close();
$conn->close();
?>



        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8" />
            <title>Panel de Usuario</title>
            <meta name="viewport" content="width=device-width, initial-scale=1" />
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
            <link rel="stylesheet" href="css/usuario.css">
            <style>
                /* Estilos básicos para el modal registro vendedor */
                #modalRegistroVendedor {
                    display: none;
                    position: fixed; top:0; left:0; width:100%; height:100%;
                    background: rgba(0,0,0,0.6);
                    justify-content: center;
                    align-items: center;
                    z-index: 1000;
                }
                #modalRegistroVendedor .modal-content {
                    background: white;
                    padding: 30px 40px;
                    border-radius: 10px;
                    width: 90%;
                    max-width: 500px;
                    position: relative;
                }
                #modalRegistroVendedor .modal-content h2 {
                    margin-top: 0;
                    color: #27ae60;
                    margin-bottom: 20px;
                    text-align: center;
                }
                #modalRegistroVendedor .modal-content label {
                    display: block;
                    margin-top: 10px;
                    font-weight: 600;
                }
                #modalRegistroVendedor .modal-content input,
                #modalRegistroVendedor .modal-content select {
                    width: 100%;
                    padding: 8px 10px;
                    margin-top: 6px;
                    border-radius: 5px;
                    border: 1px solid #ccc;
                    font-size: 14px;
                }
                #modalRegistroVendedor .modal-content button.close-btn {
                    position: absolute;
                    top: 15px;
                    right: 15px;
                    font-size: 26px;
                    font-weight: bold;
                    background: transparent;
                    border: none;
                    cursor: pointer;
                    color: #e74c3c;
                }
                #modalRegistroVendedor .modal-content button.submit-btn {
                    margin-top: 20px;
                    background: #27ae60;
                    border: none;
                    color: white;
                    padding: 12px 0;
                    font-size: 16px;
                    border-radius: 6px;
                    width: 100%;
                    cursor: pointer;
                    transition: background 0.3s ease;
                }
                #modalRegistroVendedor .modal-content button.submit-btn:hover {
                    background: #219150;
                }

                
            </style>
        </head>
        <body>

        <header>
            <div class="logo-container">
    <img src="<?php echo $fotoVendedor; ?>" alt="Foto de perfil" style="width: 45px; height: 45px; border-radius: 50%; object-fit: cover;" />
    <div class="user-name" title="<?php echo $nombre; ?>"><?php echo $nombre; ?></div>
</div>
            <button class="logout-btn" onclick="location.href='logout.php'">Cerrar Sesión</button>
        </header>

        <div class="main-container">
            <div class="button-grid">
                <div class="row">
                    <button class="btn-large" onclick="document.getElementById('faqModal').style.display='flex'">
    <i class="fas fa-headset"></i> Soporte
</button>

<!-- MODAL FAQ -->
<div id="faqModal" style="
    display:none;
    position:fixed;
    top:0; left:0;
    width:100%; height:100%;
    background: rgba(0, 0, 0, 0.7);
    justify-content: center;
    align-items: center;
    z-index: 1000;
    overflow-y: auto;
    padding: 20px;
    box-sizing: border-box;
">
    <div style="
        background: #fff;
        border-radius: 12px;
        max-width: 700px;
        width: 100%;
        box-shadow: 0 8px 24px rgba(0,0,0,0.2);
        padding: 30px 40px;
        position: relative;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    ">
        <!-- Botón cerrar -->
        <button onclick="document.getElementById('faqModal').style.display='none'" 
            style="
                position: absolute;
                top: 15px; right: 15px;
                background: transparent;
                border: none;
                font-size: 28px;
                font-weight: bold;
                cursor: pointer;
                color: #e74c3c;
                transition: color 0.3s ease;
            "
            onmouseover="this.style.color='#c0392b'"
            onmouseout="this.style.color='#e74c3c'">&times;</button>

        <h2 style="margin-bottom: 25px; color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 10px;">
            Preguntas Frecuentes (FAQ)
        </h2>

        <div style="margin-bottom: 20px;">
            <h3 style="margin-bottom: 8px; color: #2980b9; cursor: pointer;" onclick="toggleFAQ(this)">
                ¿Cómo puedo actualizar mi foto de perfil? <span style="float: right;">&#x25BC;</span>
            </h3>
            <p style="margin-left: 10px; line-height: 1.5; display: none;">
                Ve a tu perfil, selecciona una nueva foto y haz clic en "Actualizar Foto".
            </p>
        </div>

        <div style="margin-bottom: 20px;">
            <h3 style="margin-bottom: 8px; color: #2980b9; cursor: pointer;" onclick="toggleFAQ(this)">
                ¿Dónde puedo ver mi horario de ventas? <span style="float: right;">&#x25BC;</span>
            </h3>
            <p style="margin-left: 10px; line-height: 1.5; display: none;">
                Después de registrarte como vendedor, tu horario aparece en la sección principal.
            </p>
        </div>

        <div style="margin-bottom: 20px;">
            <h3 style="margin-bottom: 8px; color: #2980b9; cursor: pointer;" onclick="toggleFAQ(this)">
                ¿Cómo cambio mi zona de ventas? <span style="float: right;">&#x25BC;</span>
            </h3>
            <p style="margin-left: 10px; line-height: 1.5; display: none;">
                Para cambiar tu zona, contacta con el administrador o edita tu información desde el perfil si está habilitado.
            </p>
        </div>

        <div style="margin-bottom: 0;">
            <h3 style="margin-bottom: 8px; color: #2980b9; cursor: pointer;" onclick="toggleFAQ(this)">
                ¿Tengo que registrar mi cédula cada vez? <span style="float: right;">&#x25BC;</span>
            </h3>
            <p style="margin-left: 10px; line-height: 1.5; display: none;">
                No, tu cédula se guarda automáticamente al iniciar sesión.
            </p>
        </div>
    </div>
</div>

<script>
function toggleFAQ(header) {
    const p = header.nextElementSibling;
    const arrow = header.querySelector('span');
    if (p.style.display === 'block') {
        p.style.display = 'none';
        arrow.innerHTML = '&#x25BC;';
    } else {
        p.style.display = 'block';
        arrow.innerHTML = '&#x25B2;';
    }
}
</script>



                    <button class="btn-large" onclick="document.getElementById('modalCambioPass').style.display='flex'">
    <i class="fas fa-lock"></i>
    Cambiar Contraseña
</button>

<div id="modalCambioPass" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); justify-content:center; align-items:center; z-index:1000; padding:20px; box-sizing:border-box;">
  <div style="background:#fff; padding:30px; border-radius:10px; max-width:400px; width:100%; position:relative; box-shadow:0 5px 15px rgba(0,0,0,0.3);">
    <button onclick="document.getElementById('modalCambioPass').style.display='none'" style="position:absolute; top:15px; right:15px; background:none; border:none; font-size:28px; font-weight:bold; cursor:pointer; color:#e74c3c;">&times;</button>
    <h2 style="margin-bottom:20px; color:#333;">Cambiar Contraseña</h2>
    <form id="formCambioPass" method="POST" action="procesar_cambio_password.php">
      <label for="password_actual" style="display:block; margin-bottom:6px;">Contraseña Actual:</label>
      <input type="password" id="password_actual" name="password_actual" required style="width:100%; padding:8px; margin-bottom:15px;" />

      <label for="nueva_password" style="display:block; margin-bottom:6px;">Nueva Contraseña:</label>
      <input type="password" id="nueva_password" name="nueva_password" required style="width:100%; padding:8px; margin-bottom:15px;" />

      <label for="confirmar_password" style="display:block; margin-bottom:6px;">Confirmar Nueva Contraseña:</label>
      <input type="password" id="confirmar_password" name="confirmar_password" required style="width:100%; padding:8px; margin-bottom:20px;" />

      <button type="submit" style="background-color:#3498db; color:#fff; padding:10px 20px; border:none; border-radius:5px; cursor:pointer; width:100%;">Guardar Cambios</button>
    </form>
  </div>
</div>

<script>
  // Cerrar modal si haces clic fuera del contenido
  window.onclick = function(event) {
    const modal = document.getElementById('modalCambioPass');
    if (event.target === modal) {
      modal.style.display = "none";
    }
  }

  // Validación simple antes de enviar el formulario
  document.getElementById('formCambioPass').addEventListener('submit', function(e) {
    const nueva = document.getElementById('nueva_password').value;
    const confirmar = document.getElementById('confirmar_password').value;
    if (nueva !== confirmar) {
      alert("La nueva contraseña y la confirmación no coinciden.");
      e.preventDefault();
    }
  });
</script>



                    <button class="btn-large" onclick="abrirModalRegistro()" id="btnRegistroVendedor">
                        <i class="fas fa-user-plus"></i>
                        Registrarme como Vendedor
                    </button>

                </div>
                <div class="row">
                    <button class="btn-large" onclick="mostrarRegistro()">
                        <i class="fas fa-list"></i>
                        Ver Registro
                    </button>
                    <button class="btn-large" onclick="document.getElementById('perfilModal').style.display='flex'">
    <i class="fas fa-id-badge"></i>
    Ver Perfil
</button>

                </div>
            </div>
        </div>

        <!-- Modal perfil -->
<div id="perfilModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); justify-content:center; align-items:center; z-index:1000; overflow:auto; padding:20px; box-sizing:border-box;">
    <div style="background:#fff; padding:30px; border-radius:10px; max-width:500px; width:100%; position:relative; box-shadow:0 5px 15px rgba(0,0,0,0.3);">
        <button onclick="document.getElementById('perfilModal').style.display='none'" style="position:absolute; top:15px; right:15px; background:none; border:none; font-size:28px; font-weight:bold; cursor:pointer; color:#e74c3c;">&times;</button>

        <h2 style="margin-bottom: 20px; color:#333;">Editar Perfil</h2>

        <form id="formPerfil" method="POST" action="actualizar_perfil.php">
            <input type="hidden" name="cedula" value="<?php echo htmlspecialchars($cedula); ?>">

            <label for="nombre_completo" style="display:block; margin-bottom:6px;">Nombre Completo:</label>
            <input type="text" id="nombre_completo" name="nombre_completo" value="<?php echo htmlspecialchars($_SESSION['nombre']); ?>" required style="width:100%; padding:8px; margin-bottom:15px;">

            <label for="correo" style="display:block; margin-bottom:6px;">Correo Electrónico:</label>
            <input type="email" id="correo" name="correo" value="<?php 
                // Para mostrar correo, necesitas consultarlo de la base. Puedes cargarlo con JS o PHP. 
                // Aquí un ejemplo simple con PHP:
                require 'conexion.php';
                $correo = '';
                if ($conn) {
                    $stmtCorreo = $conn->prepare("SELECT correo FROM usuregistro WHERE cedula=?");
                    $stmtCorreo->bind_param("s", $cedula);
                    $stmtCorreo->execute();
                    $resCorreo = $stmtCorreo->get_result();
                    if ($rowCorreo = $resCorreo->fetch_assoc()) {
                        $correo = $rowCorreo['correo'];
                    }
                    $stmtCorreo->close();
                    $conn->close();
                }
                echo htmlspecialchars($correo);
            ?>" required style="width:100%; padding:8px; margin-bottom:15px;">

            <button type="submit" style="background-color:#3498db; color:#fff; padding:10px 20px; border:none; border-radius:5px; cursor:pointer;">Guardar Cambios</button>
        </form>
    </div>
</div>

<script>
window.onclick = function(event) {
    const modal = document.getElementById('perfilModal');
    if (event.target === modal) {
        modal.style.display = "none";
    }
}
</script>



<?php if ($esVendedor): ?>
<div id="registroModal" style="display: none; position: fixed; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.6); justify-content: center; align-items: center; z-index: 999;">
    <div style="background: white; padding: 25px 35px 35px 35px; border-radius: 10px; width: 95%; max-width: 850px; position: relative; display: flex; gap: 30px; align-items: center; flex-wrap: wrap;">

        <!-- Botón cerrar -->
        <button onclick="cerrarModal()" style="position: absolute; top: 15px; right: 15px; background: transparent; border: none; font-size: 26px; font-weight: bold; cursor: pointer; color: #e74c3c;">&times;</button>

        <!-- FOTO PERFIL -->
<div style="flex: 0 0 160px; display: flex; flex-direction: column; align-items: center; gap: 10px;">
    <div style="width: 140px; height: 140px; border-radius: 50%; overflow: hidden; border: 2px solid #ccc; background-color: #f9f9f9;">
        <img id="fotoVendedor" src="<?php echo $fotoVendedor . '?v=' . time(); ?>" alt="Foto del vendedor" style="width: 100%; height: 100%; object-fit: cover;" />

    </div>

    <!-- Botón para abrir selector -->
    <button type="button" onclick="document.getElementById('inputFoto').click();" 
        style="padding: 6px 14px; font-size: 13px; cursor: pointer; border-radius: 6px; border: none; background-color: #3498db; color: white;">
        Seleccionar Foto
    </button>

    <!-- Nuevo botón actualizar -->
    <button type="button" id="btnActualizarFoto" 
        style="padding: 6px 14px; font-size: 13px; margin-top: 6px; cursor: pointer; border-radius: 6px; border: none; background-color: #27ae60; color: white;">
        Actualizar Foto
    </button>

    <!-- Formulario oculto -->
    <form id="formFoto" method="POST" enctype="multipart/form-data" action="usufoto.php" style="display:none;">
        <input type="hidden" name="cedula" value="<?php echo htmlspecialchars($datosVendedor['cedula']); ?>">
        <input type="file" name="foto_perfil" id="inputFoto" accept="image/*">
    </form>
</div>

<script>
    const inputFoto = document.getElementById('inputFoto');
    const fotoVendedor = document.getElementById('fotoVendedor');
    const btnActualizarFoto = document.getElementById('btnActualizarFoto');
    const formFoto = document.getElementById('formFoto');

    // Mostrar la previsualización cuando seleccionas un archivo
    inputFoto.addEventListener('change', () => {
        const file = inputFoto.files[0];
        if (file) {
            const url = URL.createObjectURL(file);
            fotoVendedor.src = url;
        }
    });

    // Enviar formulario al hacer click en actualizar foto
    btnActualizarFoto.addEventListener('click', () => {
        if (!inputFoto.files.length) {
            alert('Por favor selecciona una imagen primero.');
            return;
        }
        
        const formData = new FormData(formFoto);

        fetch('usufoto.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('Foto actualizada correctamente.');
                // Actualiza la imagen con la URL que devuelve el servidor y evita caché
                fotoVendedor.src = data.urlFoto + '?t=' + new Date().getTime();
            } else {
                alert('Error al actualizar foto: ' + data.error);
            }
        })
        .catch(() => alert('Error en la conexión.'));
    });
</script>



        <!-- INFORMACIÓN DEL REGISTRO -->
        <div style="flex: 1; display: flex; flex-direction: column; gap: 8px; font-size: 14px; color: #333;">
            <h2 style="margin: 0 0 10px 0; color: #2ecc71; font-size: 18px;">Información de Registro</h2>
            <p><strong>Nombre:</strong> <?php echo htmlspecialchars($datosVendedor['nombre']); ?></p>
            <p><strong>Cédula:</strong> <?php echo htmlspecialchars($datosVendedor['cedula']); ?></p>
            <p><strong>Día:</strong> <?php echo htmlspecialchars($datosVendedor['dia']); ?></p>
            <p><strong>Hora Entrada:</strong> <?php echo htmlspecialchars($datosVendedor['entrada']); ?></p>
            <p><strong>Hora Salida:</strong> <?php echo htmlspecialchars($datosVendedor['salida']); ?></p>
            <p><strong>Producto:</strong> <?php echo htmlspecialchars($datosVendedor['producto']); ?></p>
            <p><strong>Zona Autorizada:</strong> <?php echo htmlspecialchars($datosVendedor['zona']); ?></p>
        </div>

        <!-- QR -->
        <div style="flex: 0 0 160px; display: flex; justify-content: center; align-items: center;">
            <div id="qrcode" style="width: 140px; height: 140px;"></div>
        </div>
    </div>
</div>

<script>
function cerrarModal() {
    document.getElementById('registroModal').style.display = 'none';
}
</script>
<?php endif; ?>

        <!-- Modal para registrar vendedor -->
<div id="modalRegistroVendedor">
    <div class="modal-content">
        <button class="close-btn" onclick="cerrarModalRegistro()">&times;</button>
        <h2>Registro de Vendedor</h2>
        <form id="formRegistroVendedor" action="" method="POST">
            <label for="nombre">Nombre completo:</label>
            <input type="text" id="nombre" name="nombre" value="<?php echo strtoupper($nombre); ?>" readonly>

            <label for="cedula">Cédula:</label>
            <input type="text" id="cedula" name="cedula" value="<?php echo $cedula; ?>" readonly>

            <label for="correo">Correo Gmail:</label>
            <input type="email" id="correo" name="correo" required placeholder="ejemplo@gmail.com">

            <label for="dia">Día:</label>
            <select id="dia" name="dia" required>
                <option value="">Selecciona un día</option>
                <option value="Lunes">Lunes</option>
                <option value="Martes">Martes</option>
                <option value="Miércoles">Miércoles</option>
                <option value="Jueves">Jueves</option>
                <option value="Viernes">Viernes</option>
                <option value="Sábado">Sábado</option>
                <option value="Domingo">Domingo</option>
            </select>

            <label for="entrada">Hora Entrada:</label>
            <input type="time" id="entrada" name="entrada" required>

            <label for="salida">Hora Salida:</label>
            <input type="time" id="salida" name="salida" required>

            <label for="producto">Producto:</label>
            <input type="text" id="producto" name="producto" maxlength="50" required>

            <label for="zona">Zona Autorizada:</label>
            <select name="zona" id="zona" required>
                <option value="">Seleccione una zona</option>
                <option value="Facultad de Ingenierías">Facultad de Ingenierías</option>
                <option value="Facultad de Ciencias Sociales y de Servicios">Facultad de Ciencias Sociales y de Servicios</option>
                <option value="Facultad de Ciencias Administrativas y Económicas">Facultad de Ciencias Administrativas y Económicas</option>
                <option value="Facultad de Pedagogía">Facultad de Pedagogía</option>
            </select>

            <button type="submit" class="submit-btn">Registrar</button>
        </form>
    </div>
</div>

<script>
function mostrarRegistro() {
    var modal = document.getElementById('registroModal');
    if (modal) {
        modal.style.display = 'flex';
    } else {
        alert("No estás registrado como vendedor.");
    }
}

function cerrarModal() {
    document.getElementById('registroModal').style.display = 'none';
}

function abrirModalRegistro() {
    document.getElementById('modalRegistroVendedor').style.display = 'flex';
}

function cerrarModalRegistro() {
    document.getElementById('modalRegistroVendedor').style.display = 'none';
}

document.addEventListener('DOMContentLoaded', function() {
    // Deshabilitar botón si ya es vendedor
    <?php if ($esVendedor): ?>
        const btnRegistro = document.getElementById('btnRegistroVendedor');
        if (btnRegistro) {
            btnRegistro.disabled = true;
            btnRegistro.style.opacity = "0.5";
            btnRegistro.style.cursor = "not-allowed";
            btnRegistro.title = "Ya estás registrado como vendedor";
        }
    <?php endif; ?>

    // Validación del formulario antes de enviar
    const formRegistro = document.getElementById('formRegistroVendedor');
    formRegistro.addEventListener('submit', function(e) {
        const dia = formRegistro.dia.value.trim();
        const entrada = formRegistro.entrada.value;
        const salida = formRegistro.salida.value;
        const producto = formRegistro.producto.value.trim();
        const zona = formRegistro.zona.value.trim();
        const correo = formRegistro.correo.value.trim();

        let errores = [];

        const patronGmail = /^[a-zA-Z0-9._%+-]+@gmail\.com$/;
        if (!patronGmail.test(correo)) {
            errores.push("El correo debe ser una cuenta de Gmail válida.");
        }

        if (!dia) errores.push("Selecciona un día válido.");
        if (!entrada) errores.push("Ingresa hora de entrada.");
        if (!salida) errores.push("Ingresa hora de salida.");
        if (entrada && salida && salida <= entrada) errores.push("La hora de salida debe ser mayor que la de entrada.");
        if (!producto) errores.push("El producto no puede estar vacío.");
        if (producto.length > 50) errores.push("El producto debe tener máximo 50 caracteres.");
        if (!zona) errores.push("Selecciona una zona autorizada.");

        if (errores.length > 0) {
            e.preventDefault();
            alert("Por favor corrige los siguientes errores:\n- " + errores.join("\n- "));
        }
    });
});
</script>


        <?php if ($esVendedor): ?>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
        
        <script>
        document.addEventListener("DOMContentLoaded", function () {
            const qrUrl = "https://forcibly-legible-piglet.ngrok-free.app/Plataforma-registro/src/descargar_pdf.php?id=<?php echo $datosVendedor['id']; ?>";
            new QRCode(document.getElementById("qrcode"), {
                text: qrUrl,
                width: 140,
                height: 140,
                colorDark : "#000000",
                colorLight : "#ffffff",
                correctLevel : QRCode.CorrectLevel.H
            });
        });
        </script>
        
        <?php endif; ?>

        <script>
    // Bloquea el botón de retroceso y gestos
    history.pushState(null, null, location.href);
    window.onpopstate = function () {
        history.go(1);
    };

    // Previene teclas comunes de navegación (Alt+← o Backspace fuera de inputs)
    document.addEventListener('keydown', function (e) {
        if ((e.altKey && e.key === 'ArrowLeft') || 
            (e.key === 'Backspace' && !['INPUT', 'TEXTAREA'].includes(document.activeElement.tagName))) {
            e.preventDefault();
        }
    });
</script>

</body>
</html>
