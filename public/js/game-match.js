document.addEventListener('DOMContentLoaded', async () => {
  const container = document.getElementById('matchContainer');
  const result    = document.getElementById('matchResult');
  const submitBtn = document.getElementById('submitMatch');

  const LEVEL_ID = container ? Number(container.dataset.levelId) : null;
  if (!LEVEL_ID) return;

  let questions = [];
  let selected  = {};   // { question_id: answer_id }

  const empty = text => `<div class="empty-state">${text}</div>`;

  try {
    const data = await apiRequest(`game/questions.php?level_id=${LEVEL_ID}`);
    questions  = data.questions || [];
  } catch {
    container.innerHTML = empty('Nu s-au putut incarca datele.');
    return;
  }

  container.innerHTML = questions.map(q => `
    <article class="match-card" data-qid="${q.id}">
      <p class="match-desc">${q.question_text}</p>
      <div class="match-options">
        ${(q.answers || []).map(a => `
          <button class="match-option" type="button" data-qid="${q.id}" data-aid="${a.id}">
            ${a.answer_text}
          </button>
        `).join('')}
      </div>
    </article>
  `).join('');

  container.addEventListener('click', e => {
    const btn = e.target.closest('.match-option');
    if (!btn) return;
    const qid = btn.dataset.qid;
    const aid = btn.dataset.aid;

    // Deselect previous answer for this question
    container.querySelectorAll(`.match-option[data-qid="${qid}"]`).forEach(b => b.classList.remove('selected'));
    btn.classList.add('selected');
    selected[qid] = Number(aid);
  });

  submitBtn?.addEventListener('click', async () => {
    const answers = Object.entries(selected).map(([qid, aid]) => ({
      question_id: Number(qid),
      answer_id:   aid,
    }));

    if (!answers.length) {
      result.textContent = 'Selecteaza cel putin un raspuns.';
      return;
    }

    result.textContent = 'Se calculeaza scorul...';

    try {
      const response = await apiRequest('game/submit.php', {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify({ level_id: LEVEL_ID, answers }),
      });

      if (!response.success) {
        result.textContent = response.message || 'Nu s-a putut trimite.';
        return;
      }

      // Mark correct/wrong visually
      (response.results || []).forEach(r => {
        const btn = container.querySelector(`.match-option[data-qid="${r.question_id}"][data-aid="${r.answer_id}"]`);
        if (btn) btn.classList.add(r.correct ? 'correct' : 'wrong');
      });

      result.textContent = `Scor: ${response.score}% (${response.correct_answers}/${response.total_questions}). ${response.passed ? 'Nivel trecut!' : 'Mai incearca o data.'}`;
    } catch {
      result.textContent = 'Autentifica-te inainte sa trimiti scorul.';
    }
  });
});
