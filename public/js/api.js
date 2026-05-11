const API_BASE = new URL('../../api', document.currentScript.src).pathname;

async function apiRequest(path, options = {}) {
  const response = await fetch(`${API_BASE}/${path}`, options);
  const data = await response.json();
  return data;
}
