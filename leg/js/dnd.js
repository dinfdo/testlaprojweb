
(function (global) {
  'use strict';

  const DROP_ATTR = 'data-dnd-drop';
  const DRAG_ATTR = 'data-dnd-drag';
  let zid = 0;

  const dropRegistry = new Map();
  let active = null;
  let lastZone = null;

  function _zoneAt(x, y) {
    const stack = document.elementsFromPoint(x, y) || [];
    for (const el of stack) {
      if (el.hasAttribute(DROP_ATTR)) return el;
    }
    return null;
  }

  function makeDropZone(el, opts) {
    if (!el) return;
    const id = 'z' + (++zid);
    el.setAttribute(DROP_ATTR, id);
    dropRegistry.set(id, opts || {});
    return () => {
      el.removeAttribute(DROP_ATTR);
      dropRegistry.delete(id);
    };
  }

  function makeDraggable(el, opts) {
    if (!el) return;
    opts = opts || {};
    el.setAttribute(DRAG_ATTR, '1');
    el.style.touchAction = 'none';

    const handleSel = opts.handle;

    function start(ev) {
      if (ev.button !== undefined && ev.button !== 0) return;
      if (handleSel && !ev.target.closest(handleSel)) return;
      if (el.classList.contains('placed')) return;
      ev.preventDefault();

      const rect = el.getBoundingClientRect();
      const ghost = el.cloneNode(true);
      ghost.style.position = 'fixed';
      ghost.style.left = rect.left + 'px';
      ghost.style.top  = rect.top + 'px';
      ghost.style.width = rect.width + 'px';
      ghost.style.height = rect.height + 'px';
      ghost.style.pointerEvents = 'none';
      ghost.style.opacity = '0.85';
      ghost.style.zIndex = '99999';
      ghost.style.transform = 'rotate(-2deg)';
      ghost.style.boxShadow = '2px 2px 0 rgba(0,0,0,.4)';
      document.body.appendChild(ghost);

      if (opts.ghostClass) ghost.classList.add(opts.ghostClass);
      el.classList.add('source-dragging');

      active = {
        el, ghost, opts,
        offX: ev.clientX - rect.left,
        offY: ev.clientY - rect.top,
        data: typeof opts.data === 'function' ? opts.data() : opts.data,
        pointerId: ev.pointerId,
      };
      try { el.setPointerCapture(ev.pointerId); } catch (e) {}

      if (opts.onStart) opts.onStart(ev);
      move(ev);
      document.addEventListener('pointermove', move, { passive: false });
      document.addEventListener('pointerup', end);
      document.addEventListener('pointercancel', end);
    }

    function move(ev) {
      if (!active) return;
      ev.preventDefault();
      active.ghost.style.left = (ev.clientX - active.offX) + 'px';
      active.ghost.style.top  = (ev.clientY - active.offY) + 'px';

      const zoneEl = _zoneAt(ev.clientX, ev.clientY);
      if (zoneEl !== lastZone) {
        if (lastZone) {
          const prev = dropRegistry.get(lastZone.getAttribute(DROP_ATTR));
          if (prev && prev.onLeave) prev.onLeave(active.data, ev);
          lastZone.classList.remove('dnd-hover');
        }
        lastZone = zoneEl;
        if (lastZone) {
          const cur = dropRegistry.get(lastZone.getAttribute(DROP_ATTR));
          if (cur) {
            if (cur.accept && !cur.accept(active.data)) {
            } else {
              lastZone.classList.add('dnd-hover');
              if (cur.onEnter) cur.onEnter(active.data, ev);
            }
          }
        }
      }
    }

    function end(ev) {
      if (!active) return;
      document.removeEventListener('pointermove', move);
      document.removeEventListener('pointerup', end);
      document.removeEventListener('pointercancel', end);

      let dropped = false;
      if (lastZone) {
        const cur = dropRegistry.get(lastZone.getAttribute(DROP_ATTR));
        lastZone.classList.remove('dnd-hover');
        if (cur && (!cur.accept || cur.accept(active.data))) {
          if (cur.onDrop) dropped = !!cur.onDrop(active.data, ev);
        }
      }

      const ghost = active.ghost;
      el.classList.remove('source-dragging');
      try { el.releasePointerCapture(active.pointerId); } catch (e) {}
      ghost.remove();
      if (active.opts.onEnd) active.opts.onEnd(ev, dropped);
      active = null;
      lastZone = null;
    }

    el.addEventListener('pointerdown', start);
    return () => {
      el.removeEventListener('pointerdown', start);
      el.removeAttribute(DRAG_ATTR);
    };
  }

  global.LeG = global.LeG || {};
  global.LeG.dnd = { makeDraggable, makeDropZone };
})(window);
