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
      
      <!-- Campos Login -->
      <div id="loginFields">
        <div class="form-group">
          <label for="username">Usuario</label>
          <input type="text" id="username" name="cedula" required>
        </div>
        <div class="form-group">
          <label for="password">Contraseña</label>
          <input type="password" id="password" name="password" required>
        </div>
        <div class="role-buttons">
          <button type="button" class="role-button" id="adminBtn" data-role="admin">Administrador</button>
          <button type="button" class="role-button" id="userBtn" data-role="user">Usuario</button>
        </div>
        <input type="hidden" id="role" name="role" value="">
      </div>

      <!-- Campos Registro -->
      <div id="registerFields" style="display:none;">
        <div class="form-group">
          <label for="nombre_completo">Nombre completo</label>
          <input type="text" id="nombre_completo" name="nombre_completo" >
        </div>
        <div class="form-group">
          <label for="cedula_registro">Cédula</label>
          <input type="text" id="cedula_registro" name="cedula_registro" >
        </div>
        <div class="form-group">
          <label for="correo">Correo</label>
          <input type="email" id="correo" name="correo" >
        </div>
      </div>

      <button type="button" id="showRegisterBtn" class="btn" style="margin-bottom: 10px;">Registrarse</button>

      <button type="submit" class="btn" id="submitBtn" disabled>Ingresar</button>


    </form>
  </div>

  <script src="js/scripts.js"></script>
</body>
</html>
