        <?php
        session_start();

        if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require 'conexion.php';

    $nombre = $_POST['nombre'];
    $cedula = $_POST['cedula'];
    $dia = $_POST['dia'];
    $entrada = $_POST['entrada'];
    $salida = $_POST['salida'];
    $producto = $_POST['producto'];
    $zona = $_POST['zona'];

    if ($dia && $entrada && $salida && $producto && $zona && ($salida > $entrada)) {
        $sql = "INSERT INTO vendedores (nombre, cedula, dia, entrada, salida, producto, zona) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssss", $nombre, $cedula, $dia, $entrada, $salida, $producto, $zona);

        if ($stmt->execute()) {
            echo "<script>alert('Registro exitoso');</script>";
        } else {
            echo "<script>alert('Error al registrar');</script>";
        }

        $stmt->close();
        $conn->close();
    } else {
        echo "<script>alert('Por favor complete todos los campos correctamente');</script>";
    }
}

        if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'user') {
            header("Location: index.php");
            exit();
        }

        $nombre = htmlspecialchars($_SESSION['nombre']);
        $cedula = $_SESSION['username']; // cédula guardada en sesión

        require 'conexion.php';

        // Verificar si ya es vendedor
        $stmt = $conn->prepare("SELECT * FROM vendedores WHERE cedula = ?");
        $stmt->bind_param("s", $cedula);
        $stmt->execute();
        $result = $stmt->get_result();

        $esVendedor = $result->num_rows > 0;
        $datosVendedor = $esVendedor ? $result->fetch_assoc() : null;

        $stmt->close();
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
                <img src="assets/img/userlogo.png" alt="User Logo" />
                <div class="user-name" title="<?php echo $nombre; ?>"><?php echo $nombre; ?></div>
            </div>
            <button class="logout-btn" onclick="location.href='logout.php'">Cerrar Sesión</button>
        </header>

        <div class="main-container">
            <div class="button-grid">
                <div class="row">
                    <button class="btn-large" onclick="location.href='soporte.php'">
                        <i class="fas fa-headset"></i>
                        Soporte
                    </button>
                    <button class="btn-large" onclick="location.href='cambiar_contrasena.php'">
                        <i class="fas fa-lock"></i>
                        Cambiar Contraseña
                    </button>
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
                    <button class="btn-large" onclick="location.href='mis_ventas.php'">
                        <i class="fas fa-chart-line"></i>
                        Mis Ventas
                    </button>
                    <button class="btn-large" onclick="location.href='perfil.php'">
                        <i class="fas fa-id-badge"></i>
                        Ver Perfil
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal para ver registro (solo si es vendedor) -->
        <?php if ($esVendedor): ?>
        <div id="registroModal" style="display: none; position: fixed; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.6); justify-content: center; align-items: center; z-index: 999;">
            <div style="background: white; padding: 25px 35px 35px 35px; border-radius: 10px; width: 90%; max-width: 600px; position: relative; display: flex; gap: 25px; align-items: center;">

                <button onclick="cerrarModal()" style="position: absolute; top: 15px; right: 15px; background: transparent; border: none; font-size: 26px; font-weight: bold; cursor: pointer; color: #e74c3c;">&times;</button>

                <!-- FOTO LADO IZQUIERDO -->
                <div style="flex: 0 0 120px; height: 125px; border: 3px solid rgb(0, 0, 0); border-radius: 8px; overflow: visible; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.5); background: white; display: flex; justify-content: center; align-items: center;">
                    <img src="assets/img/carnetlogo.png" alt="Foto del vendedor" style="max-width: 100%; max-height: 100%; object-fit: contain;" />
                </div>

                <!-- INFO LADO MEDIO -->
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

                <!-- QR LADO DERECHO -->
                <div style="flex: 0 0 160px; height: auto; display: flex; justify-content: center; align-items: center; padding: 10px;">
                    <div id="qrcode" style="width: 140px; height: 140px;"></div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Modal para registrar vendedor -->
        <div id="modalRegistroVendedor">
            <div class="modal-content">
                <button class="close-btn" onclick="cerrarModalRegistro()">&times;</button>
                <h2>Registro de Vendedor</h2>
                <form id="formRegistroVendedor" action="" method="POST">
                    <label for="nombre">Nombre completo:</label>
                    <input type="text" id="nombre" name="nombre" value="<?php echo $nombre; ?>" readonly>

                    <label for="cedula">Cédula:</label>
                    <input type="text" id="cedula" name="cedula" value="<?php echo $cedula; ?>" readonly>

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

                let errores = [];

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
