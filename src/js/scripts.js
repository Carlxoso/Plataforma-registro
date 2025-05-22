document.addEventListener('DOMContentLoaded', function () {
  const loginForm = document.getElementById('loginForm');
  const roleInput = document.getElementById('role');
  const adminBtn = document.getElementById('adminBtn');
  const userBtn = document.getElementById('userBtn');

  // Función para seleccionar el rol y marcar el botón
  function selectRole(btn) {
    const selectedRole = btn.getAttribute('data-role');
    roleInput.value = selectedRole;
    console.log('Rol seleccionado:', selectedRole);

    // Limpiar selección visual previa
    adminBtn.classList.remove('active');
    userBtn.classList.remove('active');
    btn.classList.add('active');
  }

  // Asociar atributo data-role a los botones (puedes ponerlo también en HTML)
  adminBtn.setAttribute('data-role', 'admin');
  userBtn.setAttribute('data-role', 'user');

  // Eventos para los botones de rol
  adminBtn.addEventListener('click', () => selectRole(adminBtn));
  userBtn.addEventListener('click', () => selectRole(userBtn));

  // Validación antes de enviar el formulario
  loginForm.addEventListener('submit', function (event) {
    const username = document.getElementById('username').value.trim();
    const role = roleInput.value;

    if (!role) {
      alert('Por favor, selecciona un rol antes de ingresar.');
      event.preventDefault();
      return;
    }

    // Ejemplo simple: usuarios que empiezan con "admin" deben elegir rol admin
    if (username.toLowerCase().startsWith('admin') && role !== 'admin') {
      alert('El rol seleccionado no coincide con el usuario.');
      event.preventDefault();
      return;
    }

    // Usuarios que no empiezan con "admin" deben elegir rol usuario
    if (!username.toLowerCase().startsWith('admin') && role !== 'user') {
      alert('El rol seleccionado no coincide con el usuario.');
      event.preventDefault();
      return;
    }

    // Si pasa validaciones, el formulario se envía normalmente
  });
});
