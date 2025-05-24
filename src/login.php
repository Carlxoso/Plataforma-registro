<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

include 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $cedula = trim($_POST['cedula']);
    $password = trim($_POST['password']);
    $selectedRole = isset($_POST['role']) ? trim(strtolower($_POST['role'])) : '';

    if (empty($cedula) || empty($password) || empty($selectedRole)) {
        echo "<script>
            alert('Por favor, ingresa cédula, contraseña y selecciona un rol.');
            window.location.href = 'index.php';
        </script>";
        exit();
    }

    if (!$conn) {
        die("Error de conexión: " . mysqli_connect_error());
    }

    $stmt = $conn->prepare("SELECT * FROM usuregistro WHERE cedula = ?");
    $stmt->bind_param("s", $cedula);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $usuario = $resultado->fetch_assoc();

        $dbRole = trim(strtolower($usuario['role']));

        // Mapear roles para normalizar
        $rolesValidos = [
            'admin' => 'admin',
            'administrador' => 'admin',
            'user' => 'user',
            'usuario' => 'user'
        ];

        $selectedRoleNorm = isset($rolesValidos[$selectedRole]) ? $rolesValidos[$selectedRole] : '';
        $dbRoleNorm = isset($rolesValidos[$dbRole]) ? $rolesValidos[$dbRole] : '';

        // --- DEPURACIÓN DE ROLES ---
        echo "<script>
            alert('DEBUG - Rol enviado normalizado: $selectedRoleNorm | Rol en BD normalizado: $dbRoleNorm');
        </script>";

        if (password_verify($password, $usuario['password'])) {
            if ($selectedRoleNorm !== $dbRoleNorm) {
                echo "<script>
                    alert('El rol seleccionado no coincide con el rol de usuario.');
                    window.location.href = 'index.php';
                </script>";
                exit();
            }

            $_SESSION['username'] = $usuario['cedula'];
            $_SESSION['nombre'] = $usuario['nombre_completo'];
            $_SESSION['role'] = $dbRoleNorm;
            $_SESSION['cedula'] = $usuario['cedula'];


            $color = $dbRoleNorm === 'admin' ? '#e74c3c' : '#2ecc71';
            $destino = $dbRoleNorm === 'admin' ? 'administrador.php' : 'usuario.php';
            $texto = $dbRoleNorm === 'admin' ? 'Cargando administrador...' : 'Cargando usuario...';
            $mensaje = $dbRoleNorm === 'admin' ? '¡Bienvenido Administrador!' : '¡Bienvenido!';

            echo "
                <html>
                <head>
                    <meta charset='UTF-8'>
                    <title>Redirigiendo...</title>
                    <style>
                        body {
                            margin: 0;
                            padding: 0;
                            display: flex;
                            flex-direction: column;
                            justify-content: center;
                            align-items: center;
                            height: 100vh;
                            background-color: #f0f2f5;
                            font-family: Arial, sans-serif;
                            transition: opacity 1s ease;
                            opacity: 1;
                        }
                        .spinner, .checkmark {
                            width: 60px;
                            height: 60px;
                            margin-bottom: 20px;
                        }
                        .spinner {
                            border: 6px solid #ccc;
                            border-top-color: {$color};
                            border-radius: 50%;
                            animation: spin 1s linear infinite;
                        }
                        @keyframes spin {
                            to { transform: rotate(360deg); }
                        }
                        .checkmark {
                            display: none;
                            font-size: 50px;
                            color: {$color};
                            animation: pop 0.4s ease-out forwards;
                        }
                        @keyframes pop {
                            0% { transform: scale(0); opacity: 0; }
                            100% { transform: scale(1); opacity: 1; }
                        }
                        .loading-text {
                            font-size: 22px;
                            color: #333;
                            animation: pulse 1.5s infinite;
                        }
                        @keyframes pulse {
                            0%, 100% { opacity: 1; }
                            50% { opacity: 0.4; }
                        }
                    </style>
                </head>
                <body>
                    <div class='spinner' id='loader'></div>
                    <div class='checkmark' id='check'>✔️</div>
                    <div class='loading-text' id='loadingText'>{$texto}</div>
                    <script>
                        setTimeout(() => {
                            document.getElementById('loader').style.display = 'none';
                            document.getElementById('check').style.display = 'block';
                            document.getElementById('loadingText').textContent = '{$mensaje}';
                        }, 2000);
                        setTimeout(() => {
                            document.body.style.opacity = '0';
                        }, 2800);
                        setTimeout(() => {
                            window.location.href = '{$destino}';
                        }, 3500);
                    </script>
                </body>
                </html>
            ";
            exit();
        } else {
            echo "<script>
                alert('Cédula o contraseña incorrectos');
                window.location.href = 'index.php';
            </script>";
            exit();
        }
    } else {
        echo "<script>
            alert('Cédula o contraseña incorrectos');
            window.location.href = 'index.php';
        </script>";
        exit();
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: index.php");
    exit();
}
