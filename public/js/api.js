const API_BASE = '/api';

async function apiRequest(path, options = {}) {
  const response = await fetch(`${API_BASE}/${path}`, options);
  return response.json();
}
