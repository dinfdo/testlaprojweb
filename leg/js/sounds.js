
(function (global) {
  'use strict';

  let ctx = null;
  let muted = (() => {
    try { return localStorage.getItem('leg.muted') === '1'; } catch (e) { return false; }
  })();

  function getCtx() {
    if (!ctx) {
      const AC = window.AudioContext || window.webkitAudioContext;
      if (!AC) return null;
      ctx = new AC();
    }
    if (ctx.state === 'suspended') ctx.resume().catch(() => {});
    return ctx;
  }

  function unlockAudio() {
    const c = getCtx();
    if (c && c.state === 'suspended') c.resume().catch(() => {});
    document.removeEventListener('click', unlockAudio, true);
    document.removeEventListener('touchend', unlockAudio, true);
  }
  document.addEventListener('click',    unlockAudio, true);
  document.addEventListener('touchend', unlockAudio, true);

  function isMuted() { return muted; }
  function setMuted(v) {
    muted = !!v;
    try { localStorage.setItem('leg.muted', muted ? '1' : '0'); } catch (e) {}
  }


  function tone({ freq, dur, type = 'sine', gain = 0.25, attack = 0.005, release = 0.05, start = 0 }) {
    const c = getCtx(); if (!c || muted) return;
    const t0 = c.currentTime + start;
    const osc = c.createOscillator();
    const g = c.createGain();
    osc.type = type;
    osc.frequency.setValueAtTime(freq, t0);
    g.gain.setValueAtTime(0, t0);
    g.gain.linearRampToValueAtTime(gain, t0 + attack);
    g.gain.linearRampToValueAtTime(0, t0 + dur + release);
    osc.connect(g).connect(c.destination);
    osc.start(t0);
    osc.stop(t0 + dur + release + 0.02);
  }


  function noise({ dur = 0.05, gain = 0.12, hp = 1500, start = 0 }) {
    const c = getCtx(); if (!c || muted) return;
    const t0 = c.currentTime + start;
    const buf = c.createBuffer(1, Math.max(1, Math.floor(c.sampleRate * dur)), c.sampleRate);
    const data = buf.getChannelData(0);
    for (let i = 0; i < data.length; i++) data[i] = Math.random() * 2 - 1;
    const src = c.createBufferSource();
    src.buffer = buf;
    const filter = c.createBiquadFilter();
    filter.type = 'highpass';
    filter.frequency.value = hp;
    const g = c.createGain();
    g.gain.setValueAtTime(gain, t0);
    g.gain.exponentialRampToValueAtTime(0.0001, t0 + dur);
    src.connect(filter).connect(g).connect(c.destination);
    src.start(t0);
    src.stop(t0 + dur + 0.02);
  }



  function boot() {
    const c = getCtx(); if (!c || muted) return;
    const notes = [
      { f: 261.6, d: 0.9, t: 0.00, g: 0.18 },
      { f: 392.0, d: 0.9, t: 0.06, g: 0.16 },
      { f: 523.2, d: 0.9, t: 0.14, g: 0.14 },
      { f: 659.2, d: 1.0, t: 0.22, g: 0.12 },
      { f: 783.9, d: 1.2, t: 0.32, g: 0.10 },
    ];
    notes.forEach(n => tone({ freq: n.f, dur: n.d, type: 'triangle', gain: n.g, attack: 0.06, release: 0.6, start: n.t }));
    tone({ freq: 130.8, dur: 1.6, type: 'sine', gain: 0.12, attack: 0.1, release: 0.6, start: 0.05 });
  }


  function click() { noise({ dur: 0.03, gain: 0.10, hp: 2500 }); }


  function open() {
    tone({ freq: 600, dur: 0.05, type: 'square', gain: 0.06 });
    tone({ freq: 900, dur: 0.05, type: 'square', gain: 0.05, start: 0.05 });
  }

  function close() {
    tone({ freq: 300, dur: 0.06, type: 'square', gain: 0.06 });
  }


  function error() {
    tone({ freq: 220, dur: 0.18, type: 'square', gain: 0.16, attack: 0.005, release: 0.15 });
    tone({ freq: 165, dur: 0.32, type: 'square', gain: 0.12, attack: 0.005, release: 0.2, start: 0.18 });
  }


  function correct() {
    tone({ freq: 523, dur: 0.08, type: 'triangle', gain: 0.18 });
    tone({ freq: 784, dur: 0.12, type: 'triangle', gain: 0.16, start: 0.07 });
  }


  function success() {
    [523, 659, 784, 1046].forEach((f, i) =>
      tone({ freq: f, dur: 0.6, type: 'triangle', gain: 0.14, release: 0.5, start: i * 0.06 }));
  }


  function hover() { noise({ dur: 0.012, gain: 0.04, hp: 4000 }); }

  global.LeG = global.LeG || {};
  global.LeG.snd = {
    boot, click, open, close, error, correct, success, hover,
    isMuted, setMuted,

    bindGlobal(root) {
      const r = root || document;
      r.addEventListener('click', (e) => {
        if (e.target.closest('.btn, .dicon, .startmenu li, .auth-tabs button, .cat-btn, .btn-mini')) click();
      }, true);
    },
  };
})(window);
