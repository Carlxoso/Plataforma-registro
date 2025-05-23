<?php
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'user') {
    header("Location: index.php");
    exit();
}

$nombre = htmlspecialchars($_SESSION['nombre']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Usuario</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />


    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        body {
            background: #f4f6f8;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        header {
            background-color: #2ecc71;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 20px;
            color: white;
            box-shadow: 0 2px 6px rgba(0,0,0,0.15);
        }

        .logo-container {
            display: flex;
            align-items: center;
        }

        .logo-container img {
            height: 40px;
            width: 40px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 12px;
            border: 2px solid white;
            background: white;
        }

        .user-name {
            font-size: 18px;
            font-weight: 600;
            max-width: 180px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .logout-btn {
            background-color: transparent;
            border: 2px solid #e74c3c;
            color: #e74c3c;
            padding: 10px 20px;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            transition: 0.3s ease;
        }

        .logout-btn:hover {
            background-color: #e74c3c;
            color: white;
        }

        .main-container {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px 20px;
        }

        .button-grid {
            display: flex;
            flex-direction: column;
            gap: 30px;
            width: 100%;
            max-width: 900px;
        }

        .row {
            display: flex;
            justify-content: space-around;
            gap: 30px;
            flex-wrap: wrap;
        }

        .btn-large {
            background-color: #ffffff;
            border: 3px solid #2ecc71;
            color: #2ecc71;
            width: 250px;
            height: 150px;
            border-radius: 15px;
            font-size: 20px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s ease;
            box-shadow: 0 6px 12px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
        }

        .btn-large i {
            font-size: 32px;
            margin-bottom: 10px;
        }

        .btn-large:hover {
            background-color: #2ecc71;
            color: white;
        }

        @media (max-width: 768px) {
            .row {
                flex-direction: column;
                align-items: center;
            }
        }
    </style>
</head>
<body>

<header>
    <div class="logo-container">
        <img src="assets/img/userlogo.png" alt="User Logo">
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
            <button class="btn-large" onclick="location.href='registro_vendedor.php'">
                <i class="fas fa-user-plus"></i>
                Registrarme como Vendedor
            </button>
        </div>
        <div class="row">
            <button class="btn-large" onclick="location.href='ver_registros.php'">
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

</body>
</html>
