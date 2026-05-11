document.addEventListener('DOMContentLoaded', () => {
  const loginForm = document.getElementById('loginForm');
  const registerForm = document.getElementById('registerForm');
  const message = document.getElementById('authMessage');

  const setMessage = (text, isError = false) => {
    if (!message) return;
    message.textContent = text;
    message.classList.toggle('error', isError);
  };

  const handleAuth = async (form, endpoint) => {
    setMessage('Se trimite...');
    const data = new FormData(form);
    const response = await apiRequest(endpoint, {
      method: 'POST',
      body: data,
    });

    if (!response.success) {
      setMessage(response.message || 'A aparut o eroare.', true);
      return;
    }

    setMessage('Succes. Te ducem in dashboard...');
    window.location.href = 'dashboard.html';
  };

  if (loginForm) {
    loginForm.addEventListener('submit', event => {
      event.preventDefault();
      handleAuth(loginForm, 'auth/login.php');
    });
  }

  if (registerForm) {
    registerForm.addEventListener('submit', event => {
      event.preventDefault();
      handleAuth(registerForm, 'auth/register.php');
    });
  }
});
