
(function () {
  'use strict';
  const api = LeG.api;
  const wm  = LeG.wm;
  const snd = LeG.snd;

  if (!api.getToken() || !api.getUser()) {
    window.location.href = api.basePath + '/';
    return;
  }
  const user = api.getUser();

  const lines = [
    'LeG BIOS v1.0.0 (c) 2026 Emergent Hardware Inc.',
    '',
    'Initializing memory... 32768 KB OK',
    'Detecting devices...',
    '  Primary master  : LeG-DISK01',
    '  Secondary master: LeG-NET01',
    'Booting from disk...',
    '',
    'Welcome ' + user.username + ' !',
    'Starting LeG 95...'
  ];
  const bootText = document.getElementById('boot-text');

  function showDesktop(playBootSound) {
    document.getElementById('boot').style.display = 'none';
    document.getElementById('desktop').style.display = 'block';
    afterBoot(playBootSound);
  }

  if (sessionStorage.getItem('leg.booted')) {
    setTimeout(() => showDesktop(false), 0);
  } else {
    let li = 0, ci = 0;
    function typeBoot() {
      if (li >= lines.length) {
        setTimeout(() => {
          sessionStorage.setItem('leg.booted', '1');
          showDesktop(true);
        }, 400);
        return;
      }
      const target = lines[li];
      if (ci < target.length) {
        bootText.textContent += target[ci++];
        setTimeout(typeBoot, 8);
      } else {
        bootText.textContent += '\n';
        li++; ci = 0;
        setTimeout(typeBoot, 80);
      }
    }
    typeBoot();
  }

  function afterBoot(playBootSound) {
    if (playBootSound) snd.boot();
    setupTray();
    setupIcons();
    setupStartMenu();
    setupClock();
    setupMute();
    snd.bindGlobal(document);
  }

  function setupMute() {
    const btn = document.getElementById('btn-mute');
    const icon = document.getElementById('mute-icon');
    function refresh() {
      icon.src = 'assets/icons/' + (snd.isMuted() ? 'mute.png' : 'sounds.png');
      btn.title = snd.isMuted() ? 'Sunet OFF (click pentru ON)' : 'Sunet ON (click pentru OFF)';
    }
    refresh();
    btn.addEventListener('click', () => {
      snd.setMuted(!snd.isMuted());
      refresh();
      if (!snd.isMuted()) snd.click();
    });
  }

  function setupTray() {
    document.getElementById('tray-name').textContent = user.username;
    document.getElementById('tray-icon').src = 'assets/profiles/' + encodeURIComponent(user.profile_icon || 'astronaut.jpg');
    if (user.is_admin) document.getElementById('startmenu-admin').style.display = 'flex';
  }

  function setupClock() {
    const el = document.getElementById('tray-clock');
    function tick() {
      const d = new Date();
      const hh = String(d.getHours()).padStart(2, '0');
      const mm = String(d.getMinutes()).padStart(2, '0');
      el.textContent = hh + ':' + mm;
    }
    tick();
    setInterval(tick, 60000);
  }

  const desktopIcons = [
    { app: 'video',        title: 'Intro Video',    icon: 'movies.png' },
    { app: 'games',        title: 'Jocuri',         icon: 'tools.png' },
    { app: 'encyclopedia', title: 'Enciclopedie',   icon: 'search.png' },
    { app: 'leaderboard',  title: 'Leaderboard',    icon: 'news.png' },
    { app: 'progress',     title: 'Progresul meu',  icon: 'spreadsheet_program.png' },
    { app: 'profile',      title: 'Profil',         icon: 'password_manager.png' },
    { app: 'rss',          title: 'Feed RSS',       icon: 'webpage_file.png' },
  ];
  if (user.is_admin) desktopIcons.push({ app: 'admin', title: 'Admin', icon: 'this_computer.png' });

  const isTouchDevice = 'ontouchstart' in window || navigator.maxTouchPoints > 0;

  function setupIcons() {
    const root = document.getElementById('desktop-icons');
    root.innerHTML = '';
    desktopIcons.forEach(d => {
      const el = document.createElement('div');
      el.className = 'dicon';
      el.dataset.testid = 'dicon-' + d.app;
      el.dataset.app = d.app;
      el.innerHTML = `<img src="assets/icons/${d.icon}" alt=""/><span class="lbl">${d.title}</span>`;
      if (isTouchDevice) {
        el.addEventListener('click', (e) => { e.stopPropagation(); LeG.apps.launch(d.app); });
      } else {
        el.addEventListener('dblclick', () => LeG.apps.launch(d.app));
        el.addEventListener('click', (e) => {
          document.querySelectorAll('.dicon').forEach(x => x.classList.remove('selected'));
          el.classList.add('selected');
          e.stopPropagation();
        });
      }
      root.appendChild(el);
    });
    document.getElementById('desktop').addEventListener('click', (e) => {
      if (e.target.closest('.dicon')) return;
      document.querySelectorAll('.dicon').forEach(x => x.classList.remove('selected'));
      document.getElementById('startmenu').classList.remove('open');
      document.getElementById('btn-start').classList.remove('open');
    });
  }

  function setupStartMenu() {
    const btn  = document.getElementById('btn-start');
    const menu = document.getElementById('startmenu');
    btn.addEventListener('click', (e) => {
      e.stopPropagation();
      menu.classList.toggle('open');
      btn.classList.toggle('open');
    });
    menu.addEventListener('click', (e) => {
      const li = e.target.closest('li');
      if (!li) return;
      menu.classList.remove('open');
      btn.classList.remove('open');
      if (li.id === 'btn-logout') {
        sessionStorage.removeItem('leg.booted');
        api.logout();
        window.location.href = api.basePath + '/';
        return;
      }
      const app = li.dataset.app;
      if (app) LeG.apps.launch(app);
    });
  }

  setTimeout(() => wm.toast('Bun venit, ' + user.username + '!'), 1200);
})();
