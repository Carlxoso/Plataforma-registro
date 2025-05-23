document.addEventListener('DOMContentLoaded', function () {
  const loginForm = document.getElementById('loginForm');
  const roleInput = document.getElementById('role');
  const adminBtn = document.getElementById('adminBtn');
  const userBtn = document.getElementById('userBtn');
  const showRegisterBtn = document.getElementById('showRegisterBtn');
  const submitBtn = document.getElementById('submitBtn');

  // Inicialmente deshabilitamos el botón
  submitBtn.disabled = true;

  function selectRole(btn) {
    const selectedRole = btn.getAttribute('data-role') === 'user' ? 'usuario' : 'admin';
    roleInput.value = selectedRole;

    // Removemos la clase active de ambos y la agregamos solo al seleccionado
    adminBtn.classList.remove('active');
    userBtn.classList.remove('active');
    btn.classList.add('active');

    // Activamos el botón ingresar porque ya hay rol seleccionado
    submitBtn.disabled = false;
  }

  adminBtn.addEventListener('click', () => selectRole(adminBtn));
  userBtn.addEventListener('click', () => selectRole(userBtn));

  loginForm.addEventListener('submit', function (event) {
    if (!roleInput.value) {
      alert('Por favor, selecciona un rol antes de ingresar.');
      event.preventDefault();
    }
  });

  if (showRegisterBtn) {
    showRegisterBtn.addEventListener('click', function () {
      window.location.href = 'registro.php';
    });
  }
});
