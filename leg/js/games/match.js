
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
      winId: 'g-match',
      title: 'Joc: Potriveste specificatiile',
      icon: 'assets/icons/spreadsheet_program.png',
      timeLimit: res.data.time_limit,
    });

    const items = res.data.items.slice();
    const targets = res.data.targets.slice();
    const total = items.length;
    let solved = 0;

    function render() {
      host.setProgress(`Rezolvate: ${solved}/${total}`);
      const itemsHtml = items.map((it, i) =>
        `<div class="match-item" data-slug="${esc(it.answer)}" data-i="${i}" data-testid="match-item-${i}">${esc(it.spec_text)}</div>`
      ).join('');
      const targetsHtml = targets.map((t, i) =>
        `<div class="match-target" data-slug="${esc(t.slug)}" data-i="${i}" data-testid="match-target-${esc(t.slug)}">
           <img src="assets/icons/${esc(t.icon)}" alt=""/><b>${esc(t.name)}</b>
         </div>`).join('');
      host.stage(`
        <div class="match-wrap">
          <div class="match-col">
            <h4>Specificatii (trage-le)</h4>
            <div class="match-list" id="match-items">${itemsHtml}</div>
          </div>
          <div class="match-col">
            <h4>Componente</h4>
            <div class="match-list" id="match-targets">${targetsHtml}</div>
          </div>
        </div>`);
      bind();
    }

    function bind() {
      host.qsa('.match-item').forEach(it => {
        dnd.makeDraggable(it, {
          data: () => ({ slug: it.dataset.slug, text: it.textContent, el: it }),
          onStart: () => { snd.click(); it.classList.add('dragging'); },
          onEnd:   () => it.classList.remove('dragging'),
        });
      });
      host.qsa('.match-target').forEach(tg => {
        dnd.makeDropZone(tg, {
          accept: () => !tg.classList.contains('correct'),
          onEnter: () => tg.classList.add('hover'),
          onLeave: () => tg.classList.remove('hover'),
          onDrop: (payload) => {
            tg.classList.remove('hover');
            const ok = tg.dataset.slug === payload.slug;
            if (ok) {
              tg.classList.add('correct');
              tg.insertAdjacentHTML('beforeend',
                `<div style="margin-left:auto;font-size:11px">${esc(payload.text)}</div>`);
              payload.el.remove();
              solved++;
              host.addScore(res.data.points_per_correct);
              host.setProgress(`Rezolvate: ${solved}/${total}`);
              snd.correct();
              if (solved >= total) finish(true);
              return true;
            }
            tg.classList.add('wrong');
            host.addScore(-10);
            snd.error();
            setTimeout(() => tg.classList.remove('wrong'), 600);
            return false;
          },
        });
      });
    }

    function finish(success) {
      host.stopTimer();
      if (success) snd.success();
      resultScreen(host, {
        gameId, slug: 'match-specs', difficulty, success,
        mainText: success ? 'Toate specificatiile potrivite!' : 'Timpul a expirat.',
      });
    }

    render();
    host.startTimer(() => finish(false));
  }

  LeG.games.register('match-specs', play);
})();
