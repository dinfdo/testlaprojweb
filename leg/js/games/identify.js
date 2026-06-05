
(function () {
  'use strict';
  const api = LeG.api;
  const wm  = LeG.wm;
  const { esc, createGameHost, resultScreen } = LeG.gameUtil;

  async function play(gameId, difficulty) {
    let res;
    try { res = await api.get('/games/' + gameId + '/data?difficulty=' + difficulty); }
    catch (e) { wm.toast(e.message, true); return; }

    const host = createGameHost({
      winId: 'g-identify',
      title: 'Joc: Identifică Hardware',
      icon: 'assets/icons/search.png',
      timeLimit: res.data.time_limit,
    });
    const rounds = res.data.rounds;
    const points = res.data.points_per_correct;
    let idx = 0;
    let acceptingAnswer = false;
    const results = new Array(rounds.length).fill(null);

    function dots() {
      return `<div class="rounds-progress" data-testid="rounds-progress">${
        results.map((r, i) =>
          `<span class="dot ${i === idx ? 'current' : ''} ${r === true ? 'correct' : ''} ${r === false ? 'wrong' : ''}"></span>`
        ).join('')}</div>`;
    }

    function renderRound() {
      if (idx >= rounds.length) return finish();
      const r = rounds[idx];
      host.setProgress(`Întrebare ${idx + 1}/${rounds.length}`);
      const choices = r.choices.map(c =>
        `<button class="btn" data-slug="${esc(c.slug)}" data-testid="choice-${esc(c.slug)}">${esc(c.name)}</button>`
      ).join('');
      const photoSlug = r.answer;
      host.stage(`
        ${dots()}
        <div class="question-card" data-testid="question-card">
          <img src="assets/hardware/${esc(photoSlug)}.jpg"
               onerror="this.onerror=null;this.src='${LeG.iconSrc(r.icon || 'search.png')}'"
               style="width:96px;height:96px;object-fit:contain;background:#fff;border:1px solid #888"
               alt=""/>
          <div>${esc(r.question)}</div>
        </div>
        <div class="choice-grid">${choices}</div>`);
      host.qsa('.choice-grid button').forEach(btn => {
        btn.addEventListener('click', () => {
          if (!acceptingAnswer) return;
          acceptingAnswer = false;
          host.qsa('.choice-grid button').forEach(b => {
            b.disabled = true;
            b.style.pointerEvents = 'none';
          });
          const ok = btn.dataset.slug === r.answer;
          btn.classList.add(ok ? 'correct' : 'wrong');
          if (ok) {
            host.addScore(points); LeG.snd.correct();
          } else {
            host.qsa('.choice-grid button').forEach(b => {
              if (b.dataset.slug === r.answer) b.classList.add('correct');
            });
            host.addScore(-10); LeG.snd.error();
          }
          results[idx] = ok;
          host.setInfo(ok ? 'Corect! +' + points : 'Greșit!');
          setTimeout(() => { idx++; renderRound(); }, 900);
        });
      });
      acceptingAnswer = false;
      setTimeout(() => { acceptingAnswer = true; }, 140);
    }

    function finish() {
      const right = results.filter(Boolean).length;
      if (right === rounds.length) LeG.snd.success();
      resultScreen(host, {
        gameId, slug: 'identify-hw', difficulty, success: right === rounds.length,
        mainText: `Ai răspuns corect la ${right}/${rounds.length} întrebări.`,
      });
    }

    renderRound();
    host.startTimer(() => { idx = rounds.length; finish(); });
  }

  LeG.games.register('identify-hw', play);
})();
