
(function () {
  'use strict';
  const api = LeG.api;
  const wm  = LeG.wm;
  const snd = LeG.snd;
  const dnd = LeG.dnd;
  const { esc, createGameHost, resultScreen } = LeG.gameUtil;

  async function play(gameId, difficulty) {
    let res;
    try { res = await api.get('/games/' + gameId + '/data?difficulty=' + difficulty); }
    catch (e) { wm.toast(e.message, true); return; }

    const host = createGameHost({
      winId: 'g-cables',
      title: 'Joc: Cable Manager',
      icon: 'assets/icons/briefcase.png',
      timeLimit: res.data.time_limit,
    });

    const cables = res.data.cables;
    const targets = res.data.targets;
    const total = cables.length;
    let solved = 0;

    function render() {
      host.setProgress(`Cabluri conectate: ${solved}/${total}`);
      const cHtml = cables.map((c, i) =>
        `<div class="cable" data-target="${esc(c.target)}" data-i="${i}" data-testid="cable-${esc(c.target)}">
           <img src="${LeG.iconSrc(c.icon || 'tools.png')}" style="width:18px;height:18px;vertical-align:middle" alt=""/>
           ${esc(c.cable)}
         </div>`).join('');
      const tHtml = targets.map((t, i) =>
        `<div class="t-slot" data-target="${esc(t.target)}" data-i="${i}" data-testid="t-slot-${esc(t.target)}">
           <img src="${LeG.iconSrc(t.icon || 'tools.png')}" alt=""/>
           <b>${esc(t.label)}</b>
           <span style="margin-left:auto;font-size:11px;color:#666">conector?</span>
         </div>`).join('');
      host.stage(`
        <p><b>Trage fiecare cablu de la sursa (PSU) la componenta potrivita:</b></p>
        <div class="cable-grid">
          <div>
            <h4 style="margin:0 0 6px 0">Cabluri PSU</h4>
            <div class="cable-list" id="cable-list">${cHtml}</div>
          </div>
          <div>
            <h4 style="margin:0 0 6px 0">Componente</h4>
            <div class="target-list" id="target-list">${tHtml}</div>
          </div>
        </div>`);
      bind();
    }

    function bind() {
      host.qsa('.cable').forEach(c => {
        dnd.makeDraggable(c, {
          data: () => ({ target: c.dataset.target, el: c }),
          onStart: () => snd.click(),
        });
      });
      host.qsa('.t-slot').forEach(t => {
        dnd.makeDropZone(t, {
          accept: () => !t.classList.contains('correct'),
          onEnter: () => t.classList.add('hover'),
          onLeave: () => t.classList.remove('hover'),
          onDrop: (payload) => {
            t.classList.remove('hover');
            const ok = t.dataset.target === payload.target;
            if (ok) {
              t.classList.add('correct');
              t.querySelector('span').textContent = 'CONECTAT';
              payload.el.classList.add('placed');
              host.addScore(res.data.points_per_correct);
              solved++; host.setProgress(`Cabluri conectate: ${solved}/${total}`);
              snd.correct();
              if (solved >= total) finish(true);
              return true;
            }
            t.classList.add('wrong'); host.addScore(-10);
            snd.error();
            setTimeout(() => t.classList.remove('wrong'), 600);
            return false;
          },
        });
      });
    }

    function finish(success) {
      host.stopTimer();
      if (success) snd.success();
      resultScreen(host, {
        gameId, slug: 'cable-manager', difficulty, success,
        mainText: success ? 'PC alimentat corect!' : 'Timpul a expirat.',
      });
    }

    render();
    host.startTimer(() => finish(false));
  }

  LeG.games.register('cable-manager', play);
})();
