<?php
session_start();
include 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Verificamos que los campos no estén vacíos
    if (empty($username) || empty($password)) {
        echo "<script>
            alert('Por favor, ingresa usuario y contraseña.');
            window.location.href = 'login.php';
        </script>";
        exit();
    }

    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $usuario = $resultado->fetch_assoc();

        if ($password === $usuario['password']) {
            $_SESSION['username'] = $usuario['username'];
            $_SESSION['role'] = $usuario['role'];

            if ($usuario['role'] === 'admin') {
                echo "<script>
                    alert('¡Has iniciado sesión correctamente como administrador!');
                    window.location.href = 'administrador.php';
                </script>";
                exit();
            } else {
                echo "<script>
                    alert('¡Has iniciado sesión correctamente como usuario!');
                    window.location.href = 'usuario.php';
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
