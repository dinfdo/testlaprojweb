<!DOCTYPE html>
<html lang="ro">
<head>
<meta charset="UTF-8">
<title>Test — admin/content.php</title>
<style>
  body { font-family: monospace; padding: 20px; background: #1e1e1e; color: #d4d4d4; }
  h2 { color: #569cd6; border-bottom: 1px solid #444; padding-bottom: 6px; }
  .section { margin-bottom: 30px; }
  button { background: #0e639c; color: #fff; border: none; padding: 8px 14px;
           cursor: pointer; border-radius: 4px; margin: 4px 2px; font-size: 13px; }
  button:hover { background: #1177bb; }
  button.del { background: #8b1a1a; }
  button.del:hover { background: #b52424; }
  label { display: block; margin: 6px 0 2px; font-size: 12px; color: #9cdcfe; }
  input, select { background: #2d2d2d; color: #d4d4d4; border: 1px solid #555;
                  padding: 5px 8px; border-radius: 3px; width: 280px; font-size: 13px; }
  pre { background: #2d2d2d; padding: 12px; border-radius: 4px; overflow-x: auto;
        font-size: 12px; max-height: 300px; overflow-y: auto; border-left: 3px solid #569cd6; }
  pre.err { border-left-color: #f44; }
  .status { font-size: 12px; color: #888; margin-bottom: 4px; }
</style>
</head>
<body>

<h1>Test API — admin/content.php</h1>
<p style="color:#ce9178">Trebuie sa fii autentificat ca <strong>admin</strong>.
   <a href="../../public/login.html" style="color:#569cd6">Login</a></p>

<!-- ── GET ─────────────────────────────────────── -->
<div class="section">
  <h2>GET — lista resurse</h2>
  <button onclick="get('components')">GET components</button>
  <button onclick="get('levels')">GET levels</button>
  <button onclick="get('devices')">GET devices</button>
  <button onclick="get('invalid')">GET resource invalida (422)</button>
  <div class="status" id="get-status"></div>
  <pre id="get-out">— asteapta request —</pre>
</div>

<!-- ── POST component ──────────────────────────── -->
<div class="section">
  <h2>POST — adauga componenta</h2>
  <label>device_id</label>
  <input id="c-device_id" value="1">
  <label>name</label>
  <input id="c-name" value="Rezistor 10kΩ">
  <label>category</label>
  <input id="c-category" value="Pasiv">
  <label>description</label>
  <input id="c-description" value="Rezistor de uz general">
  <label>image_url (optional)</label>
  <input id="c-image_url" value="">
  <label>difficulty_level (1-10)</label>
  <input id="c-difficulty_level" value="2">
  <br>
  <button onclick="postComponent()">POST component (valid)</button>
  <button onclick="postComponentInvalid()">POST component invalid (422)</button>
  <div class="status" id="post-c-status"></div>
  <pre id="post-c-out">— asteapta request —</pre>
</div>

<!-- ── POST level ──────────────────────────────── -->
<div class="section">
  <h2>POST — adauga nivel</h2>
  <label>device_id</label>
  <input id="l-device_id" value="1">
  <label>level_number</label>
  <input id="l-level_number" value="1">
  <label>title</label>
  <input id="l-title" value="Nivel introductiv">
  <label>min_score_required</label>
  <input id="l-min_score_required" value="0">
  <br>
  <button onclick="postLevel()">POST level (valid)</button>
  <button onclick="postLevelInvalid()">POST level invalid (422)</button>
  <div class="status" id="post-l-status"></div>
  <pre id="post-l-out">— asteapta request —</pre>
</div>

<!-- ── DELETE ──────────────────────────────────── -->
<div class="section">
  <h2>DELETE — sterge inregistrare</h2>
  <label>resource</label>
  <select id="d-resource">
    <option value="component">component</option>
    <option value="level">level</option>
  </select>
  <label>id</label>
  <input id="d-id" value="1">
  <br>
  <button class="del" onclick="deleteResource()">DELETE</button>
  <button class="del" onclick="deleteInvalid()">DELETE id invalid (422)</button>
  <button class="del" onclick="deleteNotFound()">DELETE id inexistent (404)</button>
  <div class="status" id="del-status"></div>
  <pre id="del-out">— asteapta request —</pre>
</div>

<!-- ── Method not allowed ──────────────────────── -->
<div class="section">
  <h2>Metoda nepermisa (405)</h2>
  <button onclick="methodNotAllowed()">PATCH request</button>
  <div class="status" id="patch-status"></div>
  <pre id="patch-out">— asteapta request —</pre>
</div>

<script>
const BASE = 'content.php';

const headers = {
  'Content-Type': 'application/json',
  'X-Requested-With': 'XMLHttpRequest'
};

async function show(outId, statusId, res) {
  const text = await res.text();
  let pretty;
  try { pretty = JSON.stringify(JSON.parse(text), null, 2); }
  catch { pretty = text; }
  const pre = document.getElementById(outId);
  pre.textContent = pretty;
  pre.className = res.ok ? '' : 'err';
  document.getElementById(statusId).textContent =
    `HTTP ${res.status} ${res.statusText}`;
}

async function get(resource) {
  const res = await fetch(`${BASE}?resource=${resource}`, { headers });
  show('get-out', 'get-status', res);
}

async function postComponent() {
  const body = {
    resource: 'component',
    device_id:        document.getElementById('c-device_id').value,
    name:             document.getElementById('c-name').value,
    category:         document.getElementById('c-category').value,
    description:      document.getElementById('c-description').value,
    image_url:        document.getElementById('c-image_url').value,
    difficulty_level: document.getElementById('c-difficulty_level').value
  };
  const res = await fetch(BASE, { method: 'POST', headers, body: JSON.stringify(body) });
  show('post-c-out', 'post-c-status', res);
}

async function postComponentInvalid() {
  // campuri lipsa si difficulty_level out of range
  const body = { resource: 'component', device_id: -1, name: '', category: '', description: '', difficulty_level: 99 };
  const res = await fetch(BASE, { method: 'POST', headers, body: JSON.stringify(body) });
  show('post-c-out', 'post-c-status', res);
}

async function postLevel() {
  const body = {
    resource: 'level',
    device_id:          document.getElementById('l-device_id').value,
    level_number:       document.getElementById('l-level_number').value,
    title:              document.getElementById('l-title').value,
    min_score_required: document.getElementById('l-min_score_required').value
  };
  const res = await fetch(BASE, { method: 'POST', headers, body: JSON.stringify(body) });
  show('post-l-out', 'post-l-status', res);
}

async function postLevelInvalid() {
  const body = { resource: 'level', device_id: 0, level_number: 0, title: '', min_score_required: -5 };
  const res = await fetch(BASE, { method: 'POST', headers, body: JSON.stringify(body) });
  show('post-l-out', 'post-l-status', res);
}

async function deleteResource() {
  const body = {
    resource: document.getElementById('d-resource').value,
    id: parseInt(document.getElementById('d-id').value)
  };
  const res = await fetch(BASE, { method: 'DELETE', headers, body: JSON.stringify(body) });
  show('del-out', 'del-status', res);
}

async function deleteInvalid() {
  const body = { resource: 'component', id: -99 };
  const res = await fetch(BASE, { method: 'DELETE', headers, body: JSON.stringify(body) });
  show('del-out', 'del-status', res);
}

async function deleteNotFound() {
  const body = { resource: 'component', id: 999999 };
  const res = await fetch(BASE, { method: 'DELETE', headers, body: JSON.stringify(body) });
  show('del-out', 'del-status', res);
}

async function methodNotAllowed() {
  const res = await fetch(BASE, { method: 'PATCH', headers });
  show('patch-out', 'patch-status', res);
}
</script>
</body>
</html>