<?php
session_start();
include 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $role = trim($_POST['role'] ?? ''); // Capturamos el rol

    // Validar que usuario, contraseña y rol no estén vacíos
    if (empty($username) || empty($password) || empty($role)) {
        echo "<script>
            alert('Por favor, ingresa usuario, contraseña y selecciona un rol.');
            window.location.href = 'index.php';
        </script>";
        exit();
    }

    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $usuario = $resultado->fetch_assoc();

        // Validar contraseña
        if ($password === $usuario['password']) {

            // Validar que el rol enviado coincida con el rol del usuario en BD
            if ($role !== $usuario['role']) {
                echo "<script>
                    alert('El rol seleccionado no coincide con el usuario.');
                    window.location.href = 'index.php';
                </script>";
                exit();
            }

            $_SESSION['username'] = $usuario['username'];
            $_SESSION['role'] = $usuario['role'];

            $destino = ($usuario['role'] === 'admin') ? 'administrador.php' : 'usuario.php';

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
                            border-top-color: #3498db;
                            border-radius: 50%;
                            animation: spin 1s linear infinite;
                        }

                        @keyframes spin {
                            to { transform: rotate(360deg); }
                        }

                        .checkmark {
                            display: none;
                            font-size: 50px;
                            color: #2ecc71;
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
                    <div class='loading-text' id='loadingText'>Cargando, por favor espera...</div>

                    <script>
                        setTimeout(() => {
                            // Cambia el spinner por el check
                            document.getElementById('loader').style.display = 'none';
                            document.getElementById('check').style.display = 'block';
                            document.getElementById('loadingText').textContent = '¡Listo! Redirigiendo...';
                        }, 2000);

                        setTimeout(() => {
                            // Hace un fade-out antes de redirigir
                            document.body.style.opacity = '0';
                        }, 2800);

                        setTimeout(() => {
                            window.location.href = '$destino';
                        }, 3500);
                    </script>
                </body>
                </html>
            ";
            exit();
        } else {
            echo "<script>
                alert('Usuario o contraseña incorrectos');
                window.location.href = 'index.php';
            </script>";
            exit();
        }
    } else {
        echo "<script>
            alert('Usuario o contraseña incorrectos');
            window.location.href = 'index.php';
        </script>";
        exit();
    }

    $stmt->close();
    $conn->close();
}
?>
