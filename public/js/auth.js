document.addEventListener('DOMContentLoaded', () => {
  const loginForm    = document.getElementById('loginForm');
  const registerForm = document.getElementById('registerForm');
  const logoutBtn    = document.getElementById('logoutBtn');
  const message      = document.getElementById('authMessage');

  const p = window.location.pathname;
  const inAuthDir = p.includes('/pages/auth/');
  const inAppDir  = p.includes('/pages/app/');

  // After login/register → dashboard
  const dashboardUrl = inAuthDir ? '../app/dashboard.html'
                     : inAppDir  ? 'dashboard.html'
                     :             'pages/app/dashboard.html';   // root index.html

  // After logout → landing
  const homeUrl = (inAuthDir || inAppDir) ? '../../index.html' : 'index.html';

  const setMessage = (text, isError = false) => {
    if (!message) return;
    message.textContent = text;
    message.classList.toggle('error', isError);
  };

  const handleAuth = async (form, endpoint) => {
    setMessage('Se trimite...');
    const data = new FormData(form);
    try {
      const response = await apiRequest(endpoint, { method: 'POST', body: data });
      if (!response.success) {
        setMessage(response.message || 'A aparut o eroare.', true);
        return;
      }
      localStorage.setItem('leg_user', JSON.stringify(response.user));
      setMessage('Succes. Te ducem in dashboard...');
      window.location.href = dashboardUrl;
    } catch {
      setMessage('Serverul nu raspunde. Verifica conexiunea.', true);
    }
  };

  if (loginForm) {
    loginForm.addEventListener('submit', e => {
      e.preventDefault();
      handleAuth(loginForm, 'auth/login.php');
    });
  }

  if (registerForm) {
    registerForm.addEventListener('submit', e => {
      e.preventDefault();
      handleAuth(registerForm, 'auth/register.php');
    });
  }

  if (logoutBtn) {
    logoutBtn.addEventListener('click', async () => {
      await apiRequest('auth/logout.php', { method: 'POST' }).catch(() => {});
      window.location.href = homeUrl;
    });
  }
});
