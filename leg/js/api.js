
(function (global) {
  'use strict';

  const TOKEN_KEY   = 'leg.jwt';
  const REFRESH_KEY = 'leg.refresh';
  const USER_KEY    = 'leg.user';

  let basePath = '';
  try {
    const src = document.currentScript && document.currentScript.src;
    if (src) {
      const parts = new URL(src).pathname.split('/');
      parts.pop();
      parts.pop();
      basePath = parts.join('/').replace(/\/$/, '');
    }
  } catch (e) {
    const pp = window.location.pathname.split('/').filter(Boolean);
    if (pp.length && /\.html?$/.test(pp[pp.length - 1])) pp.pop();
    const PRETTY = new Set(['admin', 'encyclopedia']);
    if (pp.length && PRETTY.has(pp[pp.length - 1])) pp.pop();
    basePath = pp.length ? '/' + pp.join('/') : '';
  }
  const API_BASE = basePath + '/api';

  function getToken()   { return localStorage.getItem(TOKEN_KEY); }
  function setToken(t)  { t ? localStorage.setItem(TOKEN_KEY, t) : localStorage.removeItem(TOKEN_KEY); }
  function getRefresh() { return localStorage.getItem(REFRESH_KEY); }
  function setRefresh(t){ t ? localStorage.setItem(REFRESH_KEY, t) : localStorage.removeItem(REFRESH_KEY); }
  function getUser()    { try { return JSON.parse(localStorage.getItem(USER_KEY) || 'null'); } catch (e) { return null; } }
  function setUser(u)   { u ? localStorage.setItem(USER_KEY, JSON.stringify(u)) : localStorage.removeItem(USER_KEY); }

  function tokenExp(tk) {
    try {
      const b64 = tk.split('.')[1].replace(/-/g, '+').replace(/_/g, '/');
      return JSON.parse(atob(b64)).exp || 0;
    } catch (e) { return 0; }
  }

  async function ensureFreshToken() {
    const tk = getToken();
    if (!tk) return;
    const exp = tokenExp(tk);
    if (!exp || Date.now() / 1000 <= exp - 30) return;
    const rt = getRefresh();
    if (!rt) return;
    try {
      const rv = await fetch(API_BASE + '/auth/refresh', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ refresh_token: rt }),
      });
      if (rv.ok) { const rd = await rv.json(); setToken(rd.token); }
    } catch (e) {}
  }

  async function request(method, url, body, _retry = true) {
    if (_retry) await ensureFreshToken();
    const headers = { 'Content-Type': 'application/json' };
    const tk = getToken();
    if (tk) headers['Authorization'] = 'Bearer ' + tk;
    const opts = { method, headers };
    if (body !== undefined) opts.body = JSON.stringify(body);
    const r = await fetch(API_BASE + url, opts);
    let data = null;
    const ct = r.headers.get('Content-Type') || '';
    if (ct.includes('application/json')) data = await r.json();
    else data = { raw: await r.text() };
    if (!r.ok) {
      if (r.status === 401 && _retry) {
        const rt = getRefresh();
        if (rt) {
          const rv = await fetch(API_BASE + '/auth/refresh', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ refresh_token: rt }),
          });
          if (rv.ok) {
            const rd = await rv.json();
            setToken(rd.token);
            return request(method, url, body, false);
          }
        } else if (getToken()) {
          await new Promise(res => setTimeout(res, 150));
          return request(method, url, body, false);
        }
        setToken(null); setRefresh(null); setUser(null);
        window.location.href = basePath + '/';
        return;
      }
      const err = new Error(data.error || 'Eroare ' + r.status);
      err.status = r.status; err.data = data;
      throw err;
    }
    return data;
  }

  const api = {
    BASE: API_BASE,
    basePath,
    getToken, setToken, getUser, setUser,
    get:    (u)     => request('GET', u),
    post:   (u, b)  => request('POST', u, b),
    put:    (u, b)  => request('PUT', u, b),
    delete: (u)     => request('DELETE', u),
    getRefresh, setRefresh,
    logout() { setToken(null); setRefresh(null); setUser(null); },
  };

  function iconSrc(icon) {
    if (!icon) return 'assets/icons/tools.png';
    return 'assets/icons/' + icon;
  }

  global.LeG = global.LeG || {};
  global.LeG.api = api;
  global.LeG.iconSrc = iconSrc;
})(window);
