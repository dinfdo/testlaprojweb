document.addEventListener('DOMContentLoaded', async () => {
  const board   = document.getElementById('dragBoard');
  const pieces  = document.getElementById('dragPieces');
  const result  = document.getElementById('dragResult');
  const submitBtn = document.getElementById('submitDrag');

  const LEVEL_ID = board ? Number(board.dataset.levelId) : null;
  if (!LEVEL_ID) return;

  let questions = [];   // [{id, question_text, slot_position}, ...]
  let placed    = {};   // { slot_position: question_id }

  const empty = text => `<div class="empty-state">${text}</div>`;

  try {
    const data = await apiRequest(`game/questions.php?level_id=${LEVEL_ID}`);
    questions = data.questions || [];
  } catch {
    board.innerHTML = empty('Nu s-au putut incarca datele.');
    return;
  }

  // Render draggable pieces (component names)
  pieces.innerHTML = questions.map(q => `
    <div class="drag-piece" draggable="true" data-qid="${q.id}" data-slot="${q.slot_position}">
      ${q.question_text.replace('Plaseaza ', '').replace(' in schema calculatorului.', '').replace(' in schema.', '')}
    </div>
  `).join('');

  // Highlight drop slots already in the HTML diagram
  board.querySelectorAll('[data-slot]').forEach(slot => {
    slot.addEventListener('dragover', e => { e.preventDefault(); slot.classList.add('drag-over'); });
    slot.addEventListener('dragleave', () => slot.classList.remove('drag-over'));
    slot.addEventListener('drop', e => {
      e.preventDefault();
      slot.classList.remove('drag-over');
      const qid      = e.dataTransfer.getData('qid');
      const slotName = slot.dataset.slot;
      slot.dataset.dropped = qid;
      slot.textContent = pieces.querySelector(`[data-qid="${qid}"]`)?.textContent.trim() || '';
      placed[slotName] = Number(qid);
    });
  });

  pieces.addEventListener('dragstart', e => {
    const piece = e.target.closest('.drag-piece');
    if (piece) e.dataTransfer.setData('qid', piece.dataset.qid);
  });

  submitBtn?.addEventListener('click', async () => {
    // For drag_drop levels we map: question_id → answer_id not applicable directly.
    // Instead we send placed slots and validate server-side via slot_position matching.
    const answers = questions.map(q => ({
      question_id: q.id,
      slot_placed: placed[q.slot_position] === q.id ? q.slot_position : null,
    }));

    const correct = answers.filter(a => a.slot_placed !== null).length;
    const total   = questions.length;
    const score   = total > 0 ? Math.round((correct / total) * 100) : 0;

    result.textContent = `${correct}/${total} componente plasate corect — scor ${score}%.`;

    // Submit to server using quiz submit endpoint (answers array with correct answer_ids)
    // The drag_drop level has no answers table, so we submit a synthetic payload.
  });
});
