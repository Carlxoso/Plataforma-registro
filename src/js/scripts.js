document.addEventListener('DOMContentLoaded', () => {
  const adminBtn = document.getElementById('adminBtn');
  const userBtn = document.getElementById('userBtn');
  const roleInput = document.getElementById('role');

  function selectRole(role) {
    roleInput.value = role;

    if (role === 'admin') {
      adminBtn.classList.add('active');
      userBtn.classList.remove('active');
    } else if (role === 'user') {
      userBtn.classList.add('active');
      adminBtn.classList.remove('active');
    }
  }

  adminBtn.addEventListener('click', () => selectRole('admin'));
  userBtn.addEventListener('click', () => selectRole('user'));

  document.getElementById('loginForm').addEventListener('submit', (e) => {
    if (!roleInput.value) {
      alert('Por favor, selecciona un rol antes de ingresar.');
      e.preventDefault();
    }
  });
});
