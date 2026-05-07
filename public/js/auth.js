document.addEventListener('DOMContentLoaded', () => {
  const loginForm = document.getElementById('loginForm');
  const registerForm = document.getElementById('registerForm');

  if (loginForm) {
    loginForm.addEventListener('submit', async event => {
      event.preventDefault();
      const data = new FormData(loginForm);
      const response = await apiRequest('auth/login.php', {
        method: 'POST',
        body: data,
      });
      console.log(response);
    });
  }

  if (registerForm) {
    registerForm.addEventListener('submit', async event => {
      event.preventDefault();
      const data = new FormData(registerForm);
      const response = await apiRequest('auth/register.php', {
        method: 'POST',
        body: data,
      });
      console.log(response);
    });
  }
});
