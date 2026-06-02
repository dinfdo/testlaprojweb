
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
      winId: 'g-assemble',
      title: 'Joc: Asamblare PC',
      icon: 'assets/icons/tools.png',
      timeLimit: res.data.time_limit,
    });

    const slots = res.data.slots;
    const pieces = res.data.pieces;
    const points = res.data.points_per_slot;
    const totalSlots = slots.length;
    let placed = 0;

    function render() {
      host.setProgress(`Piese plasate: ${placed}/${totalSlots}`);
      const piecesHtml = pieces.map((p, i) =>
        `<div class="part-chip" data-slug="${esc(p.slug)}" data-idx="${i}" data-testid="part-${esc(p.slug)}">
            <img src="${LeG.iconSrc(p.icon)}" alt=""/>
            <span>${esc(p.name)}</span>
         </div>`).join('');
      const slotsHtml = slots.map(s =>
        `<div class="slot" data-target="${esc(s.slug)}" data-testid="slot-${esc(s.slug)}">
           <div><b>${esc(s.label)}</b></div>
         </div>`).join('');
      host.stage(`
        <div>
          <div><b>Trage fiecare componenta in slot-ul corect:</b></div>
          <div class="parts-bar" id="parts-bar">${piecesHtml}</div>
          <div class="board" id="board">${slotsHtml}</div>
        </div>`);
      bind();
    }

    function bind() {
      host.qsa('.part-chip').forEach(chip => {
        dnd.makeDraggable(chip, {
          data: chip.dataset.slug,
          onStart: () => snd.click(),
        });
      });

      host.qsa('.slot').forEach(slot => {
        dnd.makeDropZone(slot, {
          accept: () => !slot.classList.contains('correct'),
          onEnter: () => slot.classList.add('hover'),
          onLeave: () => slot.classList.remove('hover'),
          onDrop: (slug) => {
            slot.classList.remove('hover');
            const chip = host.qs(`.part-chip[data-slug="${CSS.escape(slug)}"]`);
            if (!chip) return false;
            const target = slot.dataset.target;
            if (target === slug) {
              slot.classList.add('correct');
              slot.innerHTML = `<img src="${chip.querySelector('img').src}" alt=""/>
                <div><b>${chip.querySelector('span').textContent}</b></div>
                <div style="font-size:11px">OK</div>`;
              chip.classList.add('placed');
              host.addScore(points);
              placed++;
              host.setProgress(`Piese plasate: ${placed}/${totalSlots}`);
              host.setInfo('Bravo! +' + points + ' puncte.');
              snd.correct();
              if (placed >= totalSlots) finish(true);
              return true;
            }
            slot.classList.add('wrong');
            host.setInfo('Gresit! Slot-ul nu accepta ' + chip.querySelector('span').textContent + '.');
            snd.error();
            setTimeout(() => slot.classList.remove('wrong'), 600);
            host.addScore(-10);
            return false;
          },
        });
      });
    }

    function finish(success) {
      host.stopTimer();
      if (success) snd.success(); else snd.error();
      resultScreen(host, {
        gameId, slug: 'assemble-pc', difficulty,
        success,
        mainText: success ? 'Ai asamblat PC-ul cu succes!' : 'Timpul a expirat. Mai incearca!',
      });
    }

    render();
    host.startTimer(() => finish(false));
  }

  LeG.games.register('assemble-pc', play);
})();
