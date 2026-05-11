document.addEventListener('DOMContentLoaded', async () => {
  const levelSelect = document.getElementById('levelSelect');
  const container = document.getElementById('questionContainer');
  const submitButton = document.getElementById('submitQuiz');
  const result = document.getElementById('quizResult');
  let currentLevelId = null;

  const empty = text => `<div class="empty-state">${text}</div>`;

  const loadQuestions = async levelId => {
    currentLevelId = levelId;
    result.textContent = '';
    container.innerHTML = empty('Se incarca intrebarile...');

    const data = await apiRequest(`game/questions.php?level_id=${levelId}`);
    const questions = data.questions || [];

    container.innerHTML = questions.length
      ? questions.map((question, index) => `
        <article class="quiz-card">
          <span class="difficulty-pill">Intrebarea ${index + 1}</span>
          <h2>${question.question_text}</h2>
          <div class="answer-list">
            ${(question.answers || []).map(answer => `
              <label class="answer-option">
                <input type="radio" name="question_${question.id}" value="${answer.id}" data-question-id="${question.id}">
                ${answer.answer_text}
              </label>
            `).join('')}
          </div>
        </article>
      `).join('')
      : empty('Nu exista intrebari pentru nivelul ales.');
  };

  try {
    const levelsData = await apiRequest('levels/list.php');
    const levels = levelsData.levels || [];

    levelSelect.innerHTML = levels.map(level => `
      <option value="${level.id}">Nivel ${level.level_number} - ${level.title}</option>
    `).join('');

    if (levels.length) {
      await loadQuestions(levels[0].id);
    } else {
      container.innerHTML = empty('Nu exista niveluri configurate.');
      submitButton.disabled = true;
    }
  } catch (error) {
    container.innerHTML = empty('Nu s-au putut incarca nivelurile.');
    submitButton.disabled = true;
  }

  levelSelect.addEventListener('change', event => {
    loadQuestions(event.target.value);
  });

  submitButton.addEventListener('click', async () => {
    const checked = [...container.querySelectorAll('input[type="radio"]:checked')];
    const answers = checked.map(input => ({
      question_id: Number(input.dataset.questionId),
      answer_id: Number(input.value),
    }));

    if (!answers.length) {
      result.textContent = 'Alege cel putin un raspuns.';
      result.classList.add('error');
      return;
    }

    result.textContent = 'Se calculeaza scorul...';
    result.classList.remove('error');

    try {
      const response = await apiRequest('game/submit.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ level_id: Number(currentLevelId), answers }),
      });

      if (!response.success) {
        result.textContent = response.message || 'Nu s-a putut trimite quiz-ul.';
        result.classList.add('error');
        return;
      }

      result.textContent = `Scor: ${response.score}% (${response.correct_answers}/${response.total_questions}). ${response.passed ? 'Nivel trecut.' : 'Mai incearca o data.'}`;
    } catch (error) {
      result.textContent = 'Autentifica-te inainte sa trimiti scorul.';
      result.classList.add('error');
    }
  });
});
