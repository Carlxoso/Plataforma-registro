const adminBtn = document.getElementById('adminBtn');
const userBtn = document.getElementById('userBtn');
const roleInput = document.getElementById('role');
const loginContainer = document.getElementById('loginContainer');
const welcomeMessage = document.getElementById('welcomeMessage');

adminBtn.addEventListener('click', () => {
  adminBtn.classList.add('active');
  userBtn.classList.remove('active');
  roleInput.value = 'admin';
  
});

userBtn.addEventListener('click', () => {
  userBtn.classList.add('active');
  adminBtn.classList.remove('active');
  roleInput.value = 'user';
  

});