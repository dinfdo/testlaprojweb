
(function (global) {
  'use strict';

  let zCounter = 100;
  const openWindows = new Map();

  function uid() { return 'w' + Math.random().toString(36).slice(2, 9); }

  function openWindow(opts) {
    if (opts.singleton && opts.id && openWindows.has(opts.id)) {
      focus(opts.id);
      return openWindows.get(opts.id);
    }
    const id = opts.id || uid();
    const w  = opts.width  || 560;
    const h  = opts.height || 380;
    const cx = Math.max(10, (window.innerWidth  - w) / 2 + (Math.random() * 80 - 40));
    const cy = Math.max(10, (window.innerHeight - h) / 2 + (Math.random() * 60 - 30) - 16);

    const el = document.createElement('section');
    el.className = 'win active';
    el.style.left = cx + 'px';
    el.style.top  = cy + 'px';
    el.style.width  = w + 'px';
    el.style.height = h + 'px';
    el.style.zIndex = ++zCounter;
    el.dataset.winId = id;
    el.dataset.testid = 'window-' + (opts.testid || id);

    const titleHTML = `
      <div class="win-title" data-testid="window-title-${opts.testid || id}">
        <div class="title-l">
          ${opts.icon ? `<img src="${opts.icon}" alt=""/>` : ''}
          <span>${opts.title || 'Window'}</span>
        </div>
        <div class="title-r">
          <button class="btn-mini" data-act="min" title="Minimize" data-testid="win-min-${opts.testid || id}">_</button>
          <button class="btn-mini" data-act="max" title="Maximize" data-testid="win-max-${opts.testid || id}">□</button>
          <button class="btn-mini" data-act="close" title="Close" data-testid="win-close-${opts.testid || id}">x</button>
        </div>
      </div>`;
    el.innerHTML = titleHTML;

    const body = document.createElement('div');
    body.className = 'win-body inset';
    if (typeof opts.content === 'string') body.innerHTML = opts.content;
    else if (opts.content instanceof HTMLElement) body.appendChild(opts.content);
    el.appendChild(body);

    document.getElementById('windows-root').appendChild(el);

    const task = document.createElement('button');
    task.className = 'task active';
    task.dataset.winId = id;
    task.dataset.testid = 'task-' + (opts.testid || id);
    task.innerHTML = `${opts.icon ? `<img src="${opts.icon}" alt=""/>` : ''}<span>${opts.title}</span>`;
    task.addEventListener('click', () => activateTask(id));
    document.getElementById('tasks').appendChild(task);

    const focusEvent = window.PointerEvent ? 'pointerdown' : 'mousedown';
    el.addEventListener(focusEvent, () => focus(id), true);

    const titleEl = el.querySelector('.win-title');
    titleEl.addEventListener(focusEvent, startDrag);

    el.querySelector('[data-act="close"]').addEventListener('click', () => closeWindow(id));
    el.querySelector('[data-act="min"]').addEventListener('click', () => toggleMinimize(id));
    el.querySelector('[data-act="max"]').addEventListener('click', () => toggleMaximize(id));
    titleEl.addEventListener('dblclick', (e) => { if (!e.target.closest('.title-r')) toggleMaximize(id); });

    const entry = { el, taskEl: task, body, opts, minimized: false, maximized: false, savedPos: null };
    openWindows.set(id, entry);
    focus(id);
    if (LeG.snd) LeG.snd.open();
    return entry;
  }

  function closeWindow(id) {
    const w = openWindows.get(id);
    if (!w) return;
    if (LeG.snd) LeG.snd.close();
    if (w.opts.onClose) try { w.opts.onClose(); } catch (e) {}
    w.el.remove();
    w.taskEl.remove();
    openWindows.delete(id);
  }

  function focus(id) {
    const w = openWindows.get(id);
    if (!w) return;
    if (w.minimized) toggleMinimize(id);
    document.querySelectorAll('.win').forEach(x => {
      x.classList.remove('active');
      const t = x.querySelector('.win-title');
      if (t) t.classList.add('inactive');
    });
    w.el.classList.add('active');
    w.el.querySelector('.win-title').classList.remove('inactive');
    w.el.style.zIndex = ++zCounter;
    document.querySelectorAll('.taskbar .task').forEach(x => x.classList.remove('active'));
    w.taskEl.classList.add('active');
  }

  function activateTask(id) {
    const w = openWindows.get(id);
    if (!w) return;
    if (w.minimized) {
      focus(id);
      return;
    }
    if (w.el.classList.contains('active')) {
      toggleMinimize(id);
      return;
    }
    focus(id);
  }

  function toggleMinimize(id) {
    const w = openWindows.get(id);
    if (!w) return;
    w.minimized = !w.minimized;
    w.el.style.display = w.minimized ? 'none' : 'flex';
    if (!w.minimized) focus(id);
  }

  function toggleMaximize(id) {
    const w = openWindows.get(id);
    if (!w) return;
    const btn = w.el.querySelector('[data-act="max"]');
    if (w.maximized) {
      w.el.style.left   = w.savedPos.left;
      w.el.style.top    = w.savedPos.top;
      w.el.style.width  = w.savedPos.width;
      w.el.style.height = w.savedPos.height;
      w.el.classList.remove('maximized');
      w.maximized = false;
      if (btn) { btn.textContent = '□'; btn.title = 'Maximize'; }
    } else {
      w.savedPos = { left: w.el.style.left, top: w.el.style.top,
                     width: w.el.style.width, height: w.el.style.height };
      w.el.style.left   = '0px';
      w.el.style.top    = '0px';
      w.el.style.width  = window.innerWidth + 'px';
      w.el.style.height = (window.innerHeight - 34) + 'px';
      w.el.classList.add('maximized');
      w.maximized = true;
      if (btn) { btn.textContent = '❐'; btn.title = 'Restore'; }
    }
    focus(id);
  }

  let dragging = null;
  function startDrag(e) {
    if (e.button != null && e.button !== 0) return;
    if (e.target.closest('.title-r')) return;
    const win = e.currentTarget.parentElement;
    const wEntry = openWindows.get(win.dataset.winId);
    if (wEntry && wEntry.maximized) return;
    e.preventDefault();
    focus(win.dataset.winId);
    dragging = {
      el: win,
      pointerId: e.pointerId,
      pointerMode: e.type.indexOf('pointer') === 0,
      offX: e.clientX - win.offsetLeft,
      offY: e.clientY - win.offsetTop,
    };
    if (dragging.pointerMode) {
      e.currentTarget.setPointerCapture(e.pointerId);
      document.addEventListener('pointermove', onMove);
      document.addEventListener('pointerup', stopDrag);
      document.addEventListener('pointercancel', stopDrag);
    } else {
      document.addEventListener('mousemove', onMove);
      document.addEventListener('mouseup', stopDrag);
    }
  }
  function onMove(e) {
    if (!dragging) return;
    if (dragging.pointerMode && e.pointerId !== dragging.pointerId) return;
    const x = Math.max(0, Math.min(window.innerWidth  - 60, e.clientX - dragging.offX));
    const y = Math.max(0, Math.min(window.innerHeight - 40, e.clientY - dragging.offY));
    dragging.el.style.left = x + 'px';
    dragging.el.style.top  = y + 'px';
  }
  function stopDrag(e) {
    if (dragging && dragging.pointerMode && e && e.pointerId !== dragging.pointerId) return;
    const pointerMode = dragging && dragging.pointerMode;
    dragging = null;
    if (pointerMode) {
      document.removeEventListener('pointermove', onMove);
      document.removeEventListener('pointerup', stopDrag);
      document.removeEventListener('pointercancel', stopDrag);
    } else {
      document.removeEventListener('mousemove', onMove);
      document.removeEventListener('mouseup', stopDrag);
    }
  }

  function setBody(id, html) {
    const w = openWindows.get(id);
    if (!w) return;
    if (typeof html === 'string') w.body.innerHTML = html;
    else { w.body.innerHTML = ''; w.body.appendChild(html); }
  }

  function toast(msg, isError) {
    const t = document.createElement('div');
    t.className = 'toast' + (isError ? ' error' : '');
    t.textContent = msg;
    t.dataset.testid = 'toast';
    document.body.appendChild(t);
    setTimeout(() => { t.style.opacity = '0'; t.style.transition = 'opacity .3s'; }, 2200);
    setTimeout(() => t.remove(), 2800);
  }

  global.LeG = global.LeG || {};
  global.LeG.wm = { openWindow, closeWindow, focus, setBody, toast, toggleMaximize };
})(window);
