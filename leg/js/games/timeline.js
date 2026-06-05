
(function () {
  'use strict';
  const api = LeG.api;
  const wm  = LeG.wm;
  const snd = LeG.snd;
  const { esc, createGameHost, resultScreen } = LeG.gameUtil;

  async function play(gameId, difficulty) {
    let res;
    try { res = await api.get('/games/' + gameId + '/data?difficulty=' + difficulty); }
    catch (e) { wm.toast(e.message, true); return; }

    const host = createGameHost({
      winId: 'g-timeline',
      title: 'Linia Timpului',
      icon: 'assets/icons/webpage_file.png',
      timeLimit: res.data.time_limit,
    });

    let order = res.data.events.slice();
    const correct = res.data.correct_order;
    const points = res.data.points_per_correct;
    let submitted = false;

    function render() {
      host.setProgress('Aranjează de la cel mai vechi la cel mai recent');
      const items = order.map((ev, i) => `
        <div class="tl-item" data-id="${ev.id}" data-testid="tl-item-${ev.id}">
          <div class="tl-num">${i + 1}</div>
          <div class="tl-body">
            <b>${esc(ev.label)}</b>
            <div class="tl-hint">${esc(ev.hint)}</div>
          </div>
          <div class="tl-btns">
            ${i > 0
              ? `<button class="btn-mini" data-up="${i}" title="Mai sus" data-testid="tl-up-${ev.id}">&#x2191;</button>`
              : `<button class="btn-mini" style="visibility:hidden" aria-hidden="true">&#x2191;</button>`}
            ${i < order.length - 1
              ? `<button class="btn-mini" data-down="${i}" title="Mai jos" data-testid="tl-down-${ev.id}">&#x2193;</button>`
              : `<button class="btn-mini" style="visibility:hidden" aria-hidden="true">&#x2193;</button>`}
          </div>
        </div>`).join('');
      host.stage(`
        <div class="tl-wrap">
          <p class="tl-instruction">Foloseste <b>&#x2191; &#x2193;</b> pentru a aranja evenimentele de la <b>cel mai vechi</b> la <b>cel mai recent</b>:</p>
          <div class="tl-list">${items}</div>
          <button class="btn" id="tl-submit" style="margin-top:10px" data-testid="tl-submit">Verifica ordinea</button>
        </div>`);
      bind();
    }

    function bind() {
      host.qsa('[data-up]').forEach(b => {
        b.addEventListener('click', () => {
          const i = +b.dataset.up;
          [order[i - 1], order[i]] = [order[i], order[i - 1]];
          snd.click();
          render();
        });
      });
      host.qsa('[data-down]').forEach(b => {
        b.addEventListener('click', () => {
          const i = +b.dataset.down;
          [order[i], order[i + 1]] = [order[i + 1], order[i]];
          snd.click();
          render();
        });
      });
      const sub = host.qs('#tl-submit');
      if (sub) sub.addEventListener('click', () => { if (!submitted) { submitted = true; check(); } });
    }

    function check() {
      host.stopTimer();
      let correctCount = 0;
      order.forEach((ev, i) => { if (ev.id === correct[i]) correctCount++; });
      host.addScore(correctCount * points);
      host.setInfo('Corect: ' + correctCount + '/' + correct.length + ' poziții');
      snd[correctCount === correct.length ? 'success' : 'error']();
      resultScreen(host, {
        gameId, slug: 'history-timeline', difficulty,
        success: correctCount === correct.length,
        mainText: 'Ai aranjat corect ' + correctCount + ' din ' + correct.length + ' evenimente.',
      });
    }

    render();
    host.startTimer(() => { if (!submitted) { submitted = true; check(); } });
  }

  LeG.games.register('history-timeline', play);
})();
