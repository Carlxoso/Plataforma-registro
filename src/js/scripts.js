const adminBtn = document.getElementById('adminBtn');
    const userBtn = document.getElementById('userBtn');
    const roleInput = document.getElementById('role');
    const loginContainer = document.getElementById('loginContainer');
    const welcomeMessage = document.getElementById('welcomeMessage');

    adminBtn.addEventListener('click', () => {
      adminBtn.classList.add('active');
      userBtn.classList.remove('active');
      roleInput.value = 'admin';
      
      // Cambiar el fondo a azul claro
      loginContainer.style.background = '#186c1c';
      
      // Deslizar el login
      loginContainer.style.transform = 'translateX(-20px)';
      
      // Mostrar mensaje de bienvenida para administrador
      welcomeMessage.textContent = 'Bienvenido Administrador';
      welcomeMessage.style.display = 'block';
      welcomeMessage.style.opacity = 1; // Aparecer suavemente
    });

    userBtn.addEventListener('click', () => {
      userBtn.classList.add('active');
      adminBtn.classList.remove('active');
      roleInput.value = 'user';
      
      // Cambiar el fondo a un tono más oscuro
      loginContainer.style.background = '#6366f1';
      
      // Deslizar el login
      loginContainer.style.transform = 'translateX(20px)';
      
      // Mostrar mensaje de bienvenida para usuario
      welcomeMessage.textContent = 'Bienvenido Usuario';
      welcomeMessage.style.display = 'block';
      welcomeMessage.style.opacity = 1; // Aparecer suavemente
    });