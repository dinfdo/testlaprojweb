// Capture the script's own src at parse time (works for non-module, non-defer scripts)
const _apiSrc = document.currentScript ? document.currentScript.src : null;
const API_BASE = _apiSrc
  ? new URL('../../api', _apiSrc).pathname.replace(/\/$/, '')
  : '/v1leg/testlaprojweb/api';

async function apiRequest(path, options = {}) {
  const response = await fetch(`${API_BASE}/${path}`, {
    credentials: 'include',
    ...options,
  });
  return response.json();
}
