<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <link rel="stylesheet" href="css/style.css">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registro de vendedores</title>
</head>
<body>

  <div class="login-container" id="loginContainer">
    <!-- Logo dentro del cuadro -->
    <div class="logo-container">
      <img src="assets/img/escudoutlvte.png" alt="Logo UTLVTE" class="logo">
    </div>

    <h2>Registro de vendedores informales UTLVTE</h2>
  
    <div class="welcome-message" id="welcomeMessage">Bienvenido</div>

    <form action="login.php" method="POST" id="loginForm">
      <div class="form-group">
        <label for="username">Usuario</label>
        <input type="text" id="username" name="username" required>
      </div>
      <div class="form-group">
        <label for="password">Contraseña</label>
        <input type="password" id="password" name="password" required>
      </div>
      <div class="role-buttons">
        <button type="button" class="role-button" id="adminBtn">Administrador</button>
        <button type="button" class="role-button" id="userBtn">Usuario</button>
      </div>
      <input type="hidden" id="role" name="role" value="">

      <button type="submit" class="btn">Ingresar</button>
    </form>
  </div>

  <script src="js/scripts.js"></script>
</body>
</html>
