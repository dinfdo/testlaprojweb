
(function () {
  'use strict';
  const api = LeG.api;
  let selectedIcon = 'astronaut.jpg';

  function setMsg(id, text, ok) {
    const el = document.getElementById(id);
    if (!el) return;
    el.textContent = text || '';
    el.className = ok ? 'msg-ok' : 'msg-error';
  }

  document.querySelectorAll('.auth-tabs button').forEach(b => {
    b.addEventListener('click', () => {
      document.querySelectorAll('.auth-tabs button').forEach(x => x.classList.remove('active'));
      b.classList.add('active');
      const which = b.dataset.tab;
      document.getElementById('form-login').style.display    = which === 'login'    ? 'flex' : 'none';
      document.getElementById('form-register').style.display = which === 'register' ? 'flex' : 'none';
    });
  });

  api.get('/user/profile-icons').then(res => {
    const grid = document.getElementById('reg-icons');
    grid.innerHTML = '';
    (res.icons || []).forEach((name, i) => {
      const img = document.createElement('img');
      img.src = api.basePath + '/assets/profiles/' + encodeURIComponent(name);
      img.alt = name;
      img.title = name;
      img.dataset.name = name;
      if (i === 0) { img.classList.add('selected'); selectedIcon = name; }
      img.addEventListener('click', () => {
        grid.querySelectorAll('img').forEach(x => x.classList.remove('selected'));
        img.classList.add('selected');
        selectedIcon = name;
      });
      grid.appendChild(img);
    });
  }).catch(() => {});

  document.getElementById('form-login').addEventListener('submit', async (e) => {
    e.preventDefault();
    const fd = new FormData(e.target);
    setMsg('login-msg', '');
    try {
      const res = await api.post('/auth/login', {
        username: fd.get('username'),
        password: fd.get('password'),
      });
      api.setToken(res.token); api.setRefresh(res.refresh_token); api.setUser(res.user);
      window.location.href = api.basePath + '/app';
    } catch (err) {
      setMsg('login-msg', err.message || 'Eroare.');
    }
  });

  document.getElementById('form-register').addEventListener('submit', async (e) => {
    e.preventDefault();
    const fd = new FormData(e.target);
    setMsg('register-msg', '');
    if (fd.get('password') !== fd.get('password2')) {
      setMsg('register-msg', 'Parolele nu coincid.');
      return;
    }
    try {
      const res = await api.post('/auth/register', {
        username: fd.get('username'),
        email:    fd.get('email'),
        password: fd.get('password'),
        profile_icon: selectedIcon,
      });
      api.setToken(res.token); api.setRefresh(res.refresh_token); api.setUser(res.user);
      window.location.href = api.basePath + '/app';
    } catch (err) {
      setMsg('register-msg', err.message || 'Eroare.');
    }
  });
})();
