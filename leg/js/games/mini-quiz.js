
(function () {
  'use strict';
  const api = LeG.api;
  const wm  = LeG.wm;
  const { esc, createGameHost, resultScreen } = LeG.gameUtil;

  async function play(gameId, difficulty) {
    let res;
    try { res = await api.get('/games/' + gameId + '/data?difficulty=' + difficulty); }
    catch (e) { wm.toast(e.message, true); return; }

    let roundTimer = null, msLeft = 0, alive = true;

    const host = createGameHost({
      winId: 'g-mini',
      title: 'Joc: Provocarea Finala',
      icon: 'assets/icons/minecraft.png',
      timeLimit: 0,
      onClose: () => { alive = false; if (roundTimer) { clearInterval(roundTimer); roundTimer = null; } },
    });

    const rounds = res.data.rounds;
    const timePerRound = res.data.time_per_round;
    const points = res.data.points_per_correct;
    let idx = 0;
    const results = new Array(rounds.length).fill(null);

    function renderRound() {
      if (!alive) return;
      if (idx >= rounds.length) return finish();
      const r = rounds[idx];
      host.setProgress(`Intrebare ${idx + 1}/${rounds.length}`);
      const choices = r.choices.map(c =>
        `<button class="btn" data-slug="${esc(c.slug)}" data-testid="mq-choice-${esc(c.slug)}">${esc(c.name)}</button>`
      ).join('');
      host.stage(`
        <div class="mq-card">
          <div><b>${esc(r.prompt)}</b></div>
          <div class="mq-progress-bar"><div class="fill" id="mq-fill" data-testid="mq-fill"></div></div>
          <div class="choice-grid" style="margin-top:10px">${choices}</div>
        </div>`);
      msLeft = timePerRound * 1000;
      const fill = host.qs('#mq-fill');
      const total = msLeft;
      if (roundTimer) clearInterval(roundTimer);
      roundTimer = setInterval(() => {
        msLeft -= 50;
        const pct = Math.max(0, msLeft / total * 100);
        fill.style.width = pct + '%';
        if (pct < 33) fill.classList.add('danger');
        if (msLeft <= 0) { answer(null); }
      }, 50);

      host.qsa('.choice-grid button').forEach(b => {
        b.addEventListener('click', () => answer(b.dataset.slug, b));
      });
    }

    function answer(slug, btn) {
      if (!alive) return;
      if (roundTimer) clearInterval(roundTimer); roundTimer = null;
      const r = rounds[idx];
      const ok = slug === r.answer;
      results[idx] = ok;
      if (ok) {
        if (btn) btn.classList.add('correct');
        const bonus = Math.max(0, Math.round(msLeft / 1000 * 5));
        host.addScore(points + bonus);
        host.setInfo('Corect! +' + (points + bonus));
        LeG.snd.correct();
      } else {
        if (btn) btn.classList.add('wrong');
        host.qsa('.choice-grid button').forEach(b => {
          if (b.dataset.slug === r.answer) b.classList.add('correct');
        });
        host.addScore(-10);
        host.setInfo('Gresit!');
        LeG.snd.error();
      }
      setTimeout(() => { idx++; renderRound(); }, 450);
    }

    function finish() {
      if (roundTimer) clearInterval(roundTimer);
      const right = results.filter(Boolean).length;
      if (right >= Math.ceil(rounds.length * 0.7)) LeG.snd.success();
      resultScreen(host, {
        gameId, slug: 'mini-quiz', difficulty,
        success: right >= Math.ceil(rounds.length * 0.7),
        mainText: `Ai raspuns corect la ${right}/${rounds.length} intrebari.`,
      });
    }

    renderRound();
  }

  LeG.games.register('mini-quiz', play);
})();
