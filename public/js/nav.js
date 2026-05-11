(function () {
  // nav.js lives at public/js/nav.js
  // going one level up (..) gives public/ — the root for all pages
  const _src = document.currentScript && document.currentScript.src;
  const publicBase = _src
    ? new URL('..', _src).pathname.replace(/\/$/, '')
    : '';

  // Debug: remove after confirming it works
  console.log('[nav] publicBase =', publicBase);

  const url = {
    home:        publicBase + '/index.html',
    learn:       publicBase + '/pages/app/learn.html',
    game:        publicBase + '/pages/app/game.html',
    leaderboard: publicBase + '/pages/app/leaderboard.html',
    dashboard:   publicBase + '/pages/app/dashboard.html',
    admin:       publicBase + '/pages/app/admin.html',
    login:       publicBase + '/pages/auth/login.html',
    register:    publicBase + '/pages/auth/register.html',
  };

  let cached = null;
  try { cached = JSON.parse(localStorage.getItem('leg_user')); } catch {}

  function render(user) {
    const header = document.querySelector('.site-header');
    if (!header) return;

    const brand = header.querySelector('.brand');
    if (brand) brand.href = url.home;

    const old = header.querySelector('nav');
    if (old) old.remove();

    const nav = document.createElement('nav');
    nav.className = 'nav-links';
    nav.setAttribute('aria-label', 'Navigatie principala');

    if (user) {
      nav.innerHTML =
        `<a href="${url.learn}">Invata</a>` +
        `<a href="${url.game}">Joc</a>` +
        `<a href="${url.leaderboard}">Clasament</a>` +
        `<a href="${url.dashboard}">Dashboard</a>` +
        (user.role === 'admin' ? `<a href="${url.admin}">Admin</a>` : '') +
        `<button class="nav-button" id="logoutBtn" type="button">Deconectare</button>`;
    } else {
      nav.innerHTML =
        `<a href="${url.learn}">Componente</a>` +
        `<a href="${url.leaderboard}">Clasament</a>` +
        `<a href="${url.login}">Login</a>` +
        `<a class="nav-button" href="${url.register}">Cont nou</a>`;
    }

    header.appendChild(nav);

    const logoutBtn = document.getElementById('logoutBtn');
    if (logoutBtn) {
      logoutBtn.addEventListener('click', async () => {
        await apiRequest('auth/logout.php', { method: 'POST' }).catch(() => {});
        localStorage.removeItem('leg_user');
        window.location.href = url.home;
      });
    }
  }

  render(cached);

  apiRequest('auth/me.php')
    .then(data => {
      const user = data.success ? data.user : null;
      if (user) {
        localStorage.setItem('leg_user', JSON.stringify(user));
      } else {
        localStorage.removeItem('leg_user');
      }
      const changed = !!cached !== !!user
        || (user && cached && (user.id !== cached.id || user.role !== cached.role));
      if (changed) render(user);
    })
    .catch(() => {});
})();
