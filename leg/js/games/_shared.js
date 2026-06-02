
(function (global) {
  'use strict';
  const api = LeG.api;
  const wm  = LeG.wm;

  function esc(s) {
    return String(s == null ? '' : s)
      .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
  }


  function createGameHost({ winId, title, icon, timeLimit, onTick, onClose }) {
    const win = wm.openWindow({
      id: winId, singleton: true, testid: winId,
      title: title, icon: icon, width: 720, height: 500,
      onClose: () => { if (timer) clearInterval(timer); if (onClose) onClose(); },
      content: `
        <div class="game-host">
          <div class="game-hud">
            <span class="pill" data-testid="hud-score">Scor: <b id="hud-score">0</b></span>
            <span class="pill" data-testid="hud-progress" id="hud-progress"></span>
            <span class="pill ${timeLimit ? '' : 'd-none'}" data-testid="hud-time">Timp: <b id="hud-time">${timeLimit || 0}</b>s</span>
          </div>
          <div class="game-stage" id="game-stage" data-testid="game-stage"></div>
          <div class="win-statusbar">
            <span class="cell" id="hud-info">Mult succes!</span>
          </div>
        </div>`,
    });

    const b = win.body;
    const qs  = (sel) => b.querySelector(sel);
    const qsa = (sel) => b.querySelectorAll(sel);

    let score = 0;
    let timeLeft = timeLimit || 0;
    let timer = null;
    function setScore(v) { score = Math.max(0, v); const e = qs('#hud-score'); if (e) e.textContent = score; }
    function addScore(v) { setScore(score + v); }
    function setProgress(text) { const e = qs('#hud-progress'); if (e) e.textContent = text || ''; }
    function setInfo(text) { const e = qs('#hud-info'); if (e) e.textContent = text || ''; }
    function getScore() { return score; }
    function getTimeUsed() { return (timeLimit || 0) - timeLeft; }

    function startTimer(onExpire) {
      if (!timeLimit) return;
      timer = setInterval(() => {
        timeLeft--;
        const e = qs('#hud-time'); if (e) e.textContent = timeLeft;
        if (onTick) onTick(timeLeft);
        if (timeLeft <= 0) {
          clearInterval(timer); timer = null;
          if (onExpire) onExpire();
        }
      }, 1000);
    }
    function stopTimer() { if (timer) { clearInterval(timer); timer = null; } }

    function stage(html) {
      const s = qs('#game-stage');
      if (!s) return;
      if (typeof html === 'string') s.innerHTML = html;
      else { s.innerHTML = ''; s.appendChild(html); }
    }

    async function submit(gameId, difficulty) {
      stopTimer();
      try {
        const r = await api.post('/games/' + gameId + '/submit', {
          score: getScore(),
          time_seconds: getTimeUsed(),
          difficulty: difficulty,
        });
        return r;
      } catch (e) {
        wm.toast('Eroare submit scor: ' + e.message, true);
        return null;
      }
    }

    return { winId, setScore, addScore, getScore, setProgress, setInfo, startTimer, stopTimer, stage, submit, getTimeUsed, qs, qsa };
  }

  function resultScreen(host, { gameId, slug, difficulty, success, mainText }) {
    return host.submit(gameId, difficulty).then(() => {
      host.stage(`
        <div class="result-screen" data-testid="result-screen">
          <h2>${success ? 'Felicitari!' : 'Sfarsit'}</h2>
          <div class="big-score" data-testid="result-score">${host.getScore()} puncte</div>
          <div>${mainText || ''}</div>
          <div class="actions">
            <button class="btn" id="btn-replay" data-testid="btn-replay">Inca o data</button>
            <button class="btn" id="btn-leaderboard" data-testid="btn-leaderboard">Vezi clasament</button>
            <button class="btn" id="btn-close" data-testid="btn-close">Inchide</button>
          </div>
        </div>`);
      const replay = host.qs('#btn-replay');
      const lb     = host.qs('#btn-leaderboard');
      const close  = host.qs('#btn-close');
      if (replay) replay.onclick = () => { wm.closeWindow(host.winId); LeG.games.launch(slug, gameId, difficulty); };
      if (lb)     lb.onclick     = () => LeG.apps.launch('leaderboard');
      if (close)  close.onclick  = () => wm.closeWindow(host.winId);
    });
  }

  global.LeG = global.LeG || {};
  global.LeG.gameUtil = { esc, createGameHost, resultScreen };
})(window);
