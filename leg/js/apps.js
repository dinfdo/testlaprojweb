
(function (global) {
  'use strict';
  const api = LeG.api;
  const wm  = LeG.wm;

  function esc(s) {
    return String(s == null ? '' : s)
      .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
  }

  async function launchGames() {
    const win = wm.openWindow({
      id: 'games-launcher', singleton: true, testid: 'games-launcher',
      title: 'Jocuri LeG', icon: 'assets/icons/tools.png', width: 620, height: 420,
      content: '<div class="muted">Se încarcă...</div>',
    });
    try {
      const res = await api.get('/games');
      const html = [];
      html.push('<p>Alege un joc pentru a învăța distrându-te:</p>');
      html.push('<div class="enc-list" data-testid="games-list">');
      res.games.forEach(g => {
        html.push(`
          <div class="enc-card" data-testid="game-card-${esc(g.slug)}">
            <div class="h"><img src="assets/icons/${esc(g.icon || 'tools.png')}" alt=""/><b>${esc(g.name)}</b></div>
            <div class="desc">${esc(g.description)}</div>
            <div style="margin-top:8px;display:flex;gap:6px;align-items:center;flex-wrap:wrap">
              <label style="margin:0;font-size:11px">Dificultate:</label>
              <select class="input" style="width:120px" data-diff="${g.id}" data-testid="diff-${esc(g.slug)}">
                <option value="1">1 - Ușor</option>
                <option value="2">2 - Mediu</option>
                <option value="3">3 - Greu</option>
              </select>
              <button class="btn" data-play="${g.id}" data-slug="${esc(g.slug)}" data-testid="play-${esc(g.slug)}">
                <img src="assets/icons/program.png" style="width:14px;height:14px" alt=""/> Joacă
              </button>
            </div>
          </div>`);
      });
      html.push('</div>');
      wm.setBody('games-launcher', html.join(''));
      win.body.querySelectorAll('[data-play]').forEach(btn => {
        btn.addEventListener('click', () => {
          const gid  = +btn.dataset.play;
          const slug = btn.dataset.slug;
          const sel  = win.body.querySelector(`select[data-diff="${gid}"]`);
          const diff = +sel.value;
          LeG.games.launch(slug, gid, diff);
        });
      });
    } catch (e) {
      wm.setBody('games-launcher', '<div class="msg-error">' + esc(e.message) + '</div>');
    }
  }

  async function launchEncyclopedia() {
    wm.openWindow({
      id: 'enc', singleton: true, testid: 'encyclopedia',
      title: 'Enciclopedie Hardware', icon: 'assets/icons/search.png',
      width: 720, height: 460, content: '<div class="muted">Se incarca...</div>',
    });
    try {
      const res = await api.get('/components');
      const catOrder = ['all', 'core', 'storage', 'power', 'cooling', 'peripheral', 'chassis', 'history'];
      const allCats = [...new Set(res.components.map(c => c.category))];
      const cats = ['all'].concat(catOrder.filter(c => c !== 'all' && allCats.includes(c)))
                          .concat(allCats.filter(c => !catOrder.includes(c)));
      const labels = { all: 'Toate', core: 'Componente de bază', storage: 'Stocare', power: 'Alimentare',
                       cooling: 'Răcire', peripheral: 'Periferice', chassis: 'Carcasă', history: 'Istorie' };
      const historyImages = {
        'history-abacus':      'abacus.jfif',
        'history-babbage':     'Babbage.jpg',
        'history-eniac':       'ENIAC.jpg',
        'history-intel4004':   'Intel_4004.jpg',
        'history-moores-law':  'legea_moore.png',
      };
      let active = 'all';
      function render() {
        const list = active === 'all' ? res.components : res.components.filter(c => c.category === active);
        const sidebar = cats.map(c =>
          `<button class="cat-btn ${c === active ? 'active' : ''}" data-cat="${esc(c)}" data-testid="cat-${esc(c)}">${esc(labels[c] || c)}</button>`).join('');
        const cards = list.map(c => {
          const specs = Object.entries(c.specs || {})
            .map(([k, v]) => `<div><span>${esc(k)}</span><b>${esc(v)}</b></div>`).join('');
          const histFile = historyImages[c.slug];
          const photoSrc = histFile ? `assets/history/${histFile}` : `assets/hardware/${esc(c.slug)}.jpg`;
          return `
            <div class="enc-card" data-testid="enc-card-${esc(c.slug)}">
              <div class="enc-photo">
                <img src="${photoSrc}"
                     alt="${esc(c.name)}"
                     onerror="this.onerror=null;this.src='${LeG.iconSrc(c.icon)}';this.classList.add('enc-photo-fallback')"/>
              </div>
              <div class="h"><img src="${LeG.iconSrc(c.icon)}" alt=""/><b>${esc(c.name)}</b></div>
              <div class="desc">${esc(c.description)}</div>
              <div class="specs">${specs}</div>
            </div>`;
        }).join('');
        const body = `
          <div class="enc-wrap">
            <div class="enc-cats">${sidebar}</div>
            <div class="enc-list">${cards || '<div class="muted">Nu sunt componente.</div>'}</div>
          </div>`;
        wm.setBody('enc', body);
        document.querySelectorAll('.enc-cats .cat-btn').forEach(b => b.addEventListener('click', () => {
          active = b.dataset.cat; render();
        }));
      }
      render();
    } catch (e) {
      wm.setBody('enc', '<div class="msg-error">' + esc(e.message) + '</div>');
    }
  }

  async function launchLeaderboard() {
    wm.openWindow({
      id: 'lb', singleton: true, testid: 'leaderboard',
      title: 'Leaderboard', icon: 'assets/icons/news.png',
      width: 680, height: 480, content: '<div class="muted">Se incarca...</div>',
    });

    const state = { period: 'all', gameId: 0, games: [] };

    function rssLink() {
      const p = state.period === 'all' ? '' : '?period=' + state.period;
      return (api.basePath || '') + '/rss.xml' + p;
    }

    async function loadGames() {
      if (state.games.length) return;
      try { state.games = (await api.get('/games')).games || []; } catch (e) {}
    }

    async function render() {
      const periods = [
        ['all',   'Toate timpurile'],
        ['week',  'Săptămâna'],
        ['month', 'Luna'],
      ];
      const periodTabs = periods.map(([k, label]) =>
        `<button class="btn ${state.period === k ? 'is-pressed' : ''}"
                 data-period="${k}" data-testid="period-${k}">${label}</button>`
      ).join('');

      await loadGames();
      const gameOptions = [`<option value="0">Global (cumulat)</option>`]
        .concat(state.games.map(g => `<option value="${g.id}" ${state.gameId == g.id ? 'selected' : ''}>${esc(g.name)}</option>`))
        .join('');

      let body = `
        <div class="row" style="gap:8px;flex-wrap:wrap;align-items:center;margin-bottom:6px">
          <b>Perioada:</b>
          <div class="row" style="gap:4px">${periodTabs}</div>
        </div>
        <div class="row" style="gap:8px;flex-wrap:wrap;align-items:center;margin-bottom:6px">
          <label style="margin:0"><b>Joc:</b></label>
          <select class="input" id="lb-game-sel" style="flex:1;max-width:240px" data-testid="lb-game-sel">
            ${gameOptions}
          </select>
          <div style="margin-left:auto" class="row">
            <a class="btn" href="${rssLink()}" target="_blank" data-testid="lb-rss">
              <img src="assets/icons/webpage_file.png" style="width:14px;height:14px" alt=""/> RSS
            </a>
          </div>
        </div>
        <div id="lb-content" data-testid="lb-content"><div class="muted">Se incarca...</div></div>`;
      wm.setBody('lb', body);

      document.querySelectorAll('[data-period]').forEach(b =>
        b.addEventListener('click', () => { state.period = b.dataset.period; render(); }));
      document.getElementById('lb-game-sel').addEventListener('change', (e) => {
        state.gameId = +e.target.value; render();
      });

      const me = api.getUser();
      const periodQ = state.period === 'all' ? '' : '?period=' + state.period;
      const url = state.gameId === 0
        ? '/leaderboard' + periodQ
        : '/leaderboard/game/' + state.gameId + periodQ;
      let res;
      try { res = await api.get(url); }
      catch (e) {
        document.getElementById('lb-content').innerHTML = '<div class="msg-error">' + esc(e.message) + '</div>';
        return;
      }

      const isGlobal = state.gameId === 0;
      const rows = res.leaderboard || [];
      let table;
      if (isGlobal) {
        const tr = rows.map((r, i) => `
          <tr class="${me && me.username === r.username ? 'me' : ''}" data-testid="lb-row-${i+1}">
            <td>${i + 1}</td>
            <td><img src="assets/profiles/${esc(r.profile_icon)}" alt=""/>${esc(r.username)}</td>
            <td>${esc(r.total_score)}</td>
            <td>${esc(r.plays)}</td>
          </tr>`).join('');
        table = `
          <table class="lb-table" data-testid="lb-table">
            <thead><tr><th>#</th><th>Jucător</th><th>Scor total</th><th>Jocuri</th></tr></thead>
            <tbody>${tr || '<tr><td colspan="4" class="muted">Niciun rezultat în perioada selectată.</td></tr>'}</tbody>
          </table>`;
      } else {
        const tr = rows.map((r, i) => `
          <tr class="${me && me.username === r.username ? 'me' : ''}" data-testid="lb-row-${i+1}">
            <td>${i + 1}</td>
            <td><img src="assets/profiles/${esc(r.profile_icon)}" alt=""/>${esc(r.username)}</td>
            <td>${esc(r.score)}</td>
            <td>${esc(r.time_seconds)}s</td>
            <td>${esc(r.difficulty)}</td>
            <td style="font-size:11px">${esc(r.played_at)}</td>
          </tr>`).join('');
        table = `
          <table class="lb-table" data-testid="lb-table">
            <thead><tr><th>#</th><th>Jucator</th><th>Scor</th><th>Timp</th><th>Dif.</th><th>Data</th></tr></thead>
            <tbody>${tr || '<tr><td colspan="6" class="muted">Niciun rezultat în perioada selectată.</td></tr>'}</tbody>
          </table>`;
      }
      document.getElementById('lb-content').innerHTML = table;
    }

    render();
  }

  async function launchProgress() {
    wm.openWindow({
      id: 'prog', singleton: true, testid: 'progress',
      title: 'Progresul meu', icon: 'assets/icons/spreadsheet_program.png',
      width: 520, height: 360, content: '<div class="muted">Se incarca...</div>',
    });
    try {
      const res = await api.get('/user/progress');
      const total = res.progress.reduce((s, p) => s + (+p.best_score), 0);
      const max = Math.max(1, ...res.progress.map(p => +p.best_score || 0));
      const rows = res.progress.map(p => `
        <div class="prog-row" data-testid="prog-row-${esc(p.slug)}">
          <img src="${LeG.iconSrc(p.icon)}" alt=""/>
          <div>
            <div><b>${esc(p.name)}</b></div>
            <div class="prog-bar"><div class="fill" style="width:${Math.round(((+p.best_score)/max)*100)}%"></div></div>
          </div>
          <div>Scor: <b>${esc(p.best_score)}</b></div>
          <div>Sesiuni: ${esc(p.plays)}</div>
        </div>`).join('');
      const body = `
        <p>Scor cumulat: <b>${total}</b></p>
        ${rows || '<div class="muted">Joacă primul joc pentru a vedea progresul aici.</div>'}`;
      wm.setBody('prog', body);
    } catch (e) {
      wm.setBody('prog', '<div class="msg-error">' + esc(e.message) + '</div>');
    }
  }

  async function launchProfile() {
    wm.openWindow({
      id: 'profile', singleton: true, testid: 'profile',
      title: 'Profil ' + api.getUser().username, icon: 'assets/icons/password_manager.png',
      width: 500, height: 460, content: '<div class="muted">Se incarca...</div>',
    });
    try {
      const u = api.getUser();
      const icons = await api.get('/user/profile-icons');
      const base = api.basePath;
      const iconsHtml = icons.icons.map(n =>
        `<img src="${base}/assets/profiles/${esc(n)}" alt="${esc(n)}" title="${esc(n)}"
          class="${n === u.profile_icon ? 'selected' : ''}" data-name="${esc(n)}" />`
      ).join('');
      const html = `
        <div class="row" style="gap:14px;align-items:flex-start;margin-bottom:10px">
          <img id="profile-big-icon"
               src="${base}/assets/profiles/${esc(u.profile_icon || 'astronaut.jpg')}"
               style="width:72px;height:72px;flex-shrink:0;object-fit:cover;
                      border:2px solid;border-color:#808080 #fff #fff #808080"/>
          <div class="col" style="gap:4px">
            <div style="font-size:14px;font-weight:bold">${esc(u.username)}</div>
            <div style="font-size:11px">Email: ${esc(u.email || '-')}</div>
            <div style="font-size:11px">Înregistrat: ${esc((u.created_at || '').slice(0, 10))}</div>
          </div>
        </div>
        <div class="sep"></div>
        <div style="margin:6px 0 4px;font-size:12px"><b>Schimbă imaginea de profil:</b></div>
        <div class="icon-grid-sel" id="profile-icons" data-testid="profile-icons">
          ${iconsHtml}
        </div>
        <div class="msg-error" id="profile-msg" style="min-height:16px;margin-top:4px"></div>`;
      wm.setBody('profile', html);
      document.querySelectorAll('#profile-icons img').forEach(img => {
        img.addEventListener('click', async () => {
          try {
            const r = await api.put('/user/profile', { profile_icon: img.dataset.name });
            api.setUser(r.user);
            const newSrc = base + '/assets/profiles/' + encodeURIComponent(r.user.profile_icon);
            document.getElementById('tray-icon').src = newSrc;
            const bigIcon = document.getElementById('profile-big-icon');
            if (bigIcon) bigIcon.src = newSrc;
            document.querySelectorAll('#profile-icons img').forEach(x => x.classList.remove('selected'));
            img.classList.add('selected');
            wm.toast('Profil actualizat.');
          } catch (e) {
            const m = document.getElementById('profile-msg'); if (m) m.textContent = e.message;
          }
        });
      });
    } catch (e) {
      wm.setBody('profile', '<div class="msg-error">' + esc(e.message) + '</div>');
    }
  }

  function launchRss() {
    wm.openWindow({
      id: 'rss', singleton: true, testid: 'rss-viewer',
      title: 'RSS feed - Leaderboard', icon: 'assets/icons/webpage_file.png',
      width: 600, height: 420,
      content: `
        <p>Feed-ul RSS al clasamentului este disponibil la:</p>
        <p><code>${api.basePath || ''}/rss.xml</code></p>
        <p>Poate fi consumat de orice client RSS (Thunderbird, Feedly, etc.).</p>
        <p><a class="btn" href="${api.basePath || ''}/rss.xml" target="_blank" data-testid="rss-open">Deschide feed-ul</a></p>
        <iframe src="${api.basePath || ''}/rss.xml" style="width:100%;height:230px;border:1px inset #fff;background:#fff" data-testid="rss-iframe"></iframe>`,
    });
  }

  function launchAdmin() {
    if (!api.getUser()?.is_admin) { wm.toast('Acces permis doar administratorilor.'); return; }

    const win = wm.openWindow({
      id: 'admin-panel', singleton: true, testid: 'admin-panel',
      title: 'Panou Administrare LeG', icon: 'assets/icons/this_computer.png',
      width: 780, height: 540,
      content: '<div class="muted" style="padding:8px">Se incarca...</div>',
    });

    win.body.innerHTML = `
      <div style="padding:8px;overflow:auto;height:100%;box-sizing:border-box">
        <div class="row" style="justify-content:space-between;margin-bottom:8px">
          <span>Conectat ca: <b>${esc(api.getUser().username)}</b></span>
          <button class="btn" id="ap-logout">Deconectare</button>
        </div>
        <h3 style="margin:10px 0 4px 0">Statistici</h3>
        <div class="admin-grid" id="ap-stats" data-testid="admin-stats"></div>
        <h3>Utilizatori</h3>
        <div style="margin-bottom:8px">
          <button class="btn" id="ap-exp-csv" data-testid="admin-export-csv">Export CSV</button>
          <button class="btn" id="ap-exp-json" data-testid="admin-export-json">Export JSON</button>
          <a class="btn" href="${api.basePath}/rss.xml" target="_blank" data-testid="admin-rss">RSS feed</a>
        </div>
        <table class="lb-table" id="ap-users" data-testid="admin-users"></table>
        <h3 style="margin-top:14px">Componente</h3>
        <details>
          <summary style="cursor:pointer">+ Adaugă componentă</summary>
          <form id="ap-form-comp" class="col" style="background:#fff;padding:10px;border:2px inset #fff;margin-top:6px" data-testid="form-comp">
            <div class="row">
              <div style="flex:1"><label>Slug</label><input class="input" name="slug" required pattern="[a-z0-9\\-]+"/></div>
              <div style="flex:1"><label>Nume</label><input class="input" name="name" required/></div>
            </div>
            <div class="row">
              <div style="flex:1"><label>Categorie</label><input class="input" name="category" value="core"/></div>
              <div style="flex:1">
                <label>Icon</label>
                <div style="display:flex;align-items:center;gap:6px">
                  <img id="ap-icon-preview" src="assets/icons/this_computer.png" style="width:24px;height:24px;border:1px solid #aaa"/>
                  <select class="input" name="icon" id="ap-icon-select" style="flex:1">
                    <option value="this_computer.png">Se incarca...</option>
                  </select>
                </div>
              </div>
            </div>
            <label>Descriere scurtă</label><input class="input" name="short_desc" required maxlength="160"/>
            <label>Descriere completă</label><textarea class="input" name="description" rows="3" required></textarea>
            <button class="btn" type="submit" data-testid="comp-submit">Salvează</button>
            <div class="msg-error" id="ap-comp-msg"></div>
          </form>
        </details>
        <details style="margin-top:6px">
          <summary style="cursor:pointer">&#8593; Importă componente (CSV / JSON)</summary>
          <form id="ap-form-import" class="col" style="background:#fff;padding:10px;border:2px inset #fff;margin-top:6px" data-testid="form-import">
            <label>Fișier <b>.csv</b> sau <b>.json</b></label>
            <input type="file" class="input" name="file" accept=".csv,.json" required data-testid="import-file"/>
            <div style="font-size:11px;color:#555;margin:2px 0 6px">
              CSV: coloane <code>slug,name,category,short_desc,description,icon,specs</code><br/>
              JSON: <code>{"components":[{...}]}</code> sau array direct
            </div>
            <button class="btn" type="submit" data-testid="import-submit">Importă</button>
            <div id="ap-import-msg" style="margin-top:6px"></div>
          </form>
        </details>
        <table class="lb-table" id="ap-components" data-testid="admin-components" style="margin-top:8px"></table>
      </div>`;

    const q = id => win.body.querySelector('#' + id);

    q('ap-logout').onclick = () => { api.logout(); window.location.href = api.basePath + '/'; };

    async function dlExport(path, filename) {
      try {
        const res = await fetch(api.basePath + path, { headers: { 'Authorization': 'Bearer ' + api.getToken() } });
        if (!res.ok) throw new Error('HTTP ' + res.status);
        const a = document.createElement('a');
        a.href = URL.createObjectURL(await res.blob());
        a.download = filename; a.click();
        URL.revokeObjectURL(a.href);
      } catch (e) { wm.toast('Eroare export: ' + e.message); }
    }
    q('ap-exp-csv').onclick  = () => dlExport('/api/admin/export/users.csv',  'leg_users.csv');
    q('ap-exp-json').onclick = () => dlExport('/api/admin/export/scores.json','leg_scores.json');

    async function apLoadStats() {
      try {
        const s = await api.get('/admin/stats');
        const items = [
          { l:'Utilizatori', v:s.users,      t:'stat-users' },
          { l:'Componente',  v:s.components, t:'stat-components' },
          { l:'Jocuri',      v:s.games,      t:'stat-games' },
          { l:'Scoruri',     v:s.scores,     t:'stat-scores' },
        ];
        q('ap-stats').innerHTML = items.map(i =>
          `<div class="admin-stat" data-testid="${i.t}"><div class="v">${i.v}</div><div class="l">${i.l}</div></div>`
        ).join('');
      } catch (e) {
        q('ap-stats').innerHTML = `<div style="color:#a00;padding:4px">Eroare statistici: ${esc(e.message)} (${e.status||'?'})</div>`;
      }
    }

    async function apLoadUsers() {
      const tb = q('ap-users');
      tb.innerHTML = '<tbody><tr><td colspan="6" style="padding:8px;color:#555">Se incarca...</td></tr></tbody>';
      try {
        const r = await api.get('/admin/users');
        const rows = (r.users || []).map(usr => `
          <tr data-testid="admin-user-${usr.id}">
            <td>${usr.id}</td>
            <td><img src="${api.basePath}/assets/profiles/${esc(usr.profile_icon)}" style="width:18px;height:18px;vertical-align:middle"/> ${esc(usr.username)}</td>
            <td>${esc(usr.email||'-')}</td><td>${usr.is_admin?'Admin':'User'}</td>
            <td>${esc(usr.created_at)}</td>
            <td>${usr.is_admin?'':`<button class="btn" data-du="${usr.id}" data-testid="del-user-${usr.id}">Șterge</button>`}</td>
          </tr>`).join('');
        tb.innerHTML = `<thead><tr><th>#</th><th>Username</th><th>Email</th><th>Rol</th><th>Creat</th><th></th></tr></thead>
          <tbody>${rows||'<tr><td colspan="6" class="muted" style="padding:8px">Niciun utilizator.</td></tr>'}</tbody>`;
        tb.querySelectorAll('[data-du]').forEach(b => b.onclick = async () => {
          if (!confirm('Sigur ștergi userul?')) return;
          try { await api.delete('/admin/users/' + b.dataset.du); apLoadUsers(); apLoadStats(); }
          catch (e) { alert('Eroare: ' + e.message); }
        });
      } catch (e) {
        tb.innerHTML = `<thead></thead><tbody><tr><td colspan="6" style="color:#a00;padding:8px">
          Eroare utilizatori: ${esc(e.message)} (${e.status||'?'})</td></tr></tbody>`;
      }
    }

    async function apLoadComponents() {
      const tb = q('ap-components');
      try {
        const r = await api.get('/components');
        const rows = (r.components||[]).map(c => `
          <tr data-testid="admin-comp-${c.id}">
            <td>${c.id}</td>
            <td><img src="${api.basePath}/${LeG.iconSrc(c.icon)}" style="width:18px;height:18px;vertical-align:middle"/> ${esc(c.name)}</td>
            <td>${esc(c.category)}</td><td>${esc(c.slug)}</td>
            <td><button class="btn" data-dc="${c.id}" data-testid="del-comp-${c.id}">Șterge</button></td>
          </tr>`).join('');
        tb.innerHTML = `<thead><tr><th>#</th><th>Nume</th><th>Categorie</th><th>Slug</th><th></th></tr></thead>
          <tbody>${rows}</tbody>`;
        tb.querySelectorAll('[data-dc]').forEach(b => b.onclick = async () => {
          if (!confirm('Sigur ștergi componenta?')) return;
          try { await api.delete('/admin/components/' + b.dataset.dc); apLoadComponents(); apLoadStats(); }
          catch (e) { alert('Eroare: ' + e.message); }
        });
      } catch (e) {
        tb.innerHTML = `<thead></thead><tbody><tr><td colspan="5" style="color:#a00;padding:8px">Eroare componente: ${esc(e.message)}</td></tr></tbody>`;
      }
    }

    q('ap-form-comp').addEventListener('submit', async (e) => {
      e.preventDefault();
      const fd = new FormData(e.target);
      q('ap-comp-msg').textContent = '';
      try {
        await api.post('/admin/components', {
          slug: fd.get('slug'), name: fd.get('name'),
          category: fd.get('category'), icon: fd.get('icon'),
          short_desc: fd.get('short_desc'), description: fd.get('description'), specs: {},
        });
        e.target.reset(); apLoadComponents(); apLoadStats();
      } catch (err) { q('ap-comp-msg').textContent = err.message; }
    });

    q('ap-form-import').addEventListener('submit', async (e) => {
      e.preventDefault();
      const msg = q('ap-import-msg');
      msg.textContent = 'Se importă...'; msg.className = '';
      try {
        const res = await fetch(api.basePath + '/api/admin/import/components', {
          method: 'POST', headers: { 'Authorization': 'Bearer ' + api.getToken() }, body: new FormData(e.target),
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.error || 'Eroare server');
        msg.textContent = `Importat: ${data.inserted} adăugate, ${data.skipped} sărite/duplicate.`;
        msg.className = 'msg-ok';
        if (data.inserted > 0) { apLoadComponents(); apLoadStats(); }
      } catch (err) { msg.textContent = err.message; msg.className = 'msg-error'; }
    });

    async function apLoadIcons() {
      try {
        const r = await api.get('/admin/icons');
        const sel = q('ap-icon-select');
        if (!sel) return;
        sel.innerHTML = (r.icons || []).map(ic =>
          `<option value="${esc(ic)}">${esc(ic)}</option>`
        ).join('');
        sel.value = 'this_computer.png';
        const preview = q('ap-icon-preview');
        if (preview) preview.src = LeG.iconSrc(sel.value);
        sel.addEventListener('change', () => {
          if (preview) preview.src = LeG.iconSrc(sel.value);
        });
      } catch (e) { /* non-critical */ }
    }

    apLoadStats(); apLoadUsers(); apLoadComponents(); apLoadIcons();
  }

  function launchVideo() {
    wm.openWindow({
      id: 'intro-video', singleton: true, testid: 'intro-video',
      title: 'Intro Video - LeG 95', icon: 'assets/icons/movies.png',
      width: 680, height: 460,
      content: `
        <div style="display:flex;flex-direction:column;padding:8px;height:100%;box-sizing:border-box;gap:6px">
          <iframe
            src="https://www.youtube.com/embed/VHhAZx4EvM8"
            title="Intro Video - LeG 95"
            frameborder="0"
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
            allowfullscreen
            style="border:2px inset #fff;width:100%;flex:1;min-height:0">
          </iframe>
          <div style="font-size:11px;color:#444;text-align:center;border-top:1px solid #808080;padding-top:6px;flex-shrink:0">
            &copy; Video original disponibil pe YouTube &mdash;
            <a href="https://www.youtube.com/watch?v=VHhAZx4EvM8" target="_blank" style="color:#000080">
              Vizualizeaza pe YouTube
            </a>
            &mdash; Toate drepturile rezervate autorului original.
          </div>
        </div>`,
    });
  }

  const apps = {
    video: launchVideo,
    games: launchGames,
    encyclopedia: launchEncyclopedia,
    leaderboard: launchLeaderboard,
    progress: launchProgress,
    profile: launchProfile,
    rss: launchRss,
    admin: launchAdmin,
  };

  global.LeG = global.LeG || {};
  global.LeG.apps = { launch(name) { (apps[name] || (() => {}))(); } };
})(window);
