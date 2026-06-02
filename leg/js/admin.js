
(function () {
  'use strict';
  const api = LeG.api;

  function esc(s) {
    return String(s == null ? '' : s)
      .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
  }

  if (!api.getToken()) { window.location.href = api.basePath + '/'; return; }
  const u = api.getUser();
  if (!u || !u.is_admin) {
    alert('Doar administratorii pot accesa acest panou.');
    window.location.href = api.basePath + '/app';
    return;
  }
  document.getElementById('admin-user').textContent = u.username;

  document.getElementById('btn-back').onclick = () => { window.location.href = api.basePath + '/app'; };
  document.getElementById('btn-logout').onclick = () => { api.logout(); window.location.href = api.basePath + '/'; };

  function showError(elId, msg) {
    const el = document.getElementById(elId);
    if (el) el.innerHTML = `<tr><td colspan="10" style="color:#a00;padding:8px">${esc(msg)}</td></tr>`;
  }

  async function loadStats() {
    try {
      const s = await api.get('/admin/stats');
      const items = [
        { l: 'Utilizatori', v: s.users, t: 'stat-users' },
        { l: 'Componente', v: s.components, t: 'stat-components' },
        { l: 'Jocuri', v: s.games, t: 'stat-games' },
        { l: 'Scoruri', v: s.scores, t: 'stat-scores' },
      ];
      document.getElementById('admin-stats').innerHTML = items.map(i =>
        `<div class="admin-stat" data-testid="${i.t}"><div class="v">${i.v}</div><div class="l">${i.l}</div></div>`
      ).join('');
    } catch (e) {
      document.getElementById('admin-stats').innerHTML =
        `<div style="color:#a00;padding:8px">Eroare statistici: ${esc(e.message)}</div>`;
    }
  }

  async function loadUsers() {
    const tbody = document.getElementById('admin-users');
    tbody.innerHTML = '<tbody><tr><td colspan="6" style="padding:8px;color:#555">Se incarca...</td></tr></tbody>';
    try {
      const r = await api.get('/admin/users');
      const rows = (r.users || []).map(usr => `
        <tr data-testid="admin-user-${usr.id}">
          <td>${usr.id}</td>
          <td><img src="${api.basePath}/assets/profiles/${esc(usr.profile_icon)}" style="width:18px;height:18px;vertical-align:middle"/> ${esc(usr.username)}</td>
          <td>${esc(usr.email || '-')}</td>
          <td>${usr.is_admin ? 'Admin' : 'User'}</td>
          <td>${esc(usr.created_at)}</td>
          <td>${usr.is_admin ? '' : `<button class="btn" data-del-user="${usr.id}" data-testid="del-user-${usr.id}">Sterge</button>`}</td>
        </tr>`).join('');
      tbody.innerHTML = `
        <thead><tr><th>#</th><th>Username</th><th>Email</th><th>Rol</th><th>Creat</th><th></th></tr></thead>
        <tbody>${rows || '<tr><td colspan="6" class="muted" style="padding:8px">Niciun utilizator.</td></tr>'}</tbody>`;
      document.querySelectorAll('[data-del-user]').forEach(b => b.onclick = async () => {
        if (!confirm('Sigur stergi userul?')) return;
        try {
          await api.delete('/admin/users/' + b.dataset.delUser);
          loadUsers(); loadStats();
        } catch (e) { alert('Eroare la stergere: ' + e.message); }
      });
    } catch (e) {
      tbody.innerHTML = `<thead></thead><tbody><tr><td colspan="6" style="color:#a00;padding:8px">
        Eroare la incarcarea utilizatorilor: ${esc(e.message)} (${e.status || '?'})
      </td></tr></tbody>`;
    }
  }

  async function loadComponents() {
    const tbody = document.getElementById('admin-components');
    try {
      const r = await api.get('/components');
      const rows = (r.components || []).map(c => `
        <tr data-testid="admin-comp-${c.id}">
          <td>${c.id}</td>
          <td><img src="${api.basePath}/${LeG.iconSrc(c.icon)}" style="width:18px;height:18px;vertical-align:middle"/> ${esc(c.name)}</td>
          <td>${esc(c.category)}</td>
          <td>${esc(c.slug)}</td>
          <td><button class="btn" data-del-comp="${c.id}" data-testid="del-comp-${c.id}">Sterge</button></td>
        </tr>`).join('');
      tbody.innerHTML = `
        <thead><tr><th>#</th><th>Nume</th><th>Categorie</th><th>Slug</th><th></th></tr></thead>
        <tbody>${rows}</tbody>`;
      document.querySelectorAll('[data-del-comp]').forEach(b => b.onclick = async () => {
        if (!confirm('Sigur stergi componenta?')) return;
        try {
          await api.delete('/admin/components/' + b.dataset.delComp);
          loadComponents(); loadStats();
        } catch (e) { alert('Eroare la stergere: ' + e.message); }
      });
    } catch (e) {
      tbody.innerHTML = `<thead></thead><tbody><tr><td colspan="5" style="color:#a00;padding:8px">
        Eroare componente: ${esc(e.message)}
      </td></tr></tbody>`;
    }
  }

  document.getElementById('form-comp').addEventListener('submit', async (e) => {
    e.preventDefault();
    const fd = new FormData(e.target);
    document.getElementById('comp-msg').textContent = '';
    try {
      await api.post('/admin/components', {
        slug: fd.get('slug'), name: fd.get('name'),
        category: fd.get('category'), icon: fd.get('icon'),
        short_desc: fd.get('short_desc'), description: fd.get('description'),
        specs: {},
      });
      e.target.reset();
      loadComponents(); loadStats();
    } catch (err) {
      document.getElementById('comp-msg').textContent = err.message;
    }
  });

  document.getElementById('form-import').addEventListener('submit', async (e) => {
    e.preventDefault();
    const msg = document.getElementById('import-msg');
    msg.textContent = 'Se importa...';
    msg.className = '';
    try {
      const fd = new FormData(e.target);
      const res = await fetch(api.basePath + '/api/admin/import/components', {
        method: 'POST',
        headers: { 'Authorization': 'Bearer ' + api.getToken() },
        body: fd,
      });
      const data = await res.json();
      if (!res.ok) throw new Error(data.error || 'Eroare server');
      msg.textContent = `Importat: ${data.inserted} adaugate, ${data.skipped} sarite/duplicate.`;
      msg.className = 'msg-ok';
      if (data.inserted > 0) { loadComponents(); loadStats(); }
    } catch (err) {
      msg.textContent = err.message;
      msg.className = 'msg-error';
    }
  });

  async function downloadAdminFile(path, filename) {
    try {
      const res = await fetch(api.basePath + path, {
        headers: { Authorization: 'Bearer ' + api.getToken() }
      });
      if (!res.ok) { alert('Export esuat: ' + res.status); return; }
      const a = document.createElement('a');
      a.href = URL.createObjectURL(await res.blob());
      a.download = filename; a.click();
      URL.revokeObjectURL(a.href);
    } catch (e) { alert('Eroare export: ' + e.message); }
  }

  document.getElementById('export-users').onclick  = () => downloadAdminFile('/api/admin/export/users.csv',  'leg_users.csv');
  document.getElementById('export-scores').onclick = () => downloadAdminFile('/api/admin/export/scores.json','leg_scores.json');

  loadStats(); loadUsers(); loadComponents();
})();
