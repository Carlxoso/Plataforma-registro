<?php
session_start();
session_destroy();

// Mostrar una pantalla animada de "cerrando sesión"
echo "
<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <title>Cerrando sesión...</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f8f9fa;
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
            border-top-color: #e74c3c;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .checkmark {
            display: none;
            font-size: 50px;
            color: #27ae60;
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
    <div class='loading-text' id='loadingText'>Cerrando sesión...</div>

    <script>
        setTimeout(() => {
            document.getElementById('loader').style.display = 'none';
            document.getElementById('check').style.display = 'block';
            document.getElementById('loadingText').textContent = '¡Hasta pronto!';
        }, 2000);

        setTimeout(() => {
            document.body.style.opacity = '0';
        }, 2800);

        setTimeout(() => {
            window.location.href = 'index.php';
        }, 3500);
    </script>
</body>
</html>
";
exit();
?>
