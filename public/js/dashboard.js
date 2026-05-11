document.addEventListener('DOMContentLoaded', async () => {
  const levelsContainer = document.getElementById('progressLevels');
  const sessionsContainer = document.getElementById('recentSessions');

  const empty = text => `<div class="empty-state">${text}</div>`;

  try {
    const data = await apiRequest('progress/user.php');

    if (!data.success) {
      levelsContainer.innerHTML = empty(data.message || 'Autentifica-te pentru a vedea progresul.');
      sessionsContainer.innerHTML = empty('Nu exista sesiuni de afisat.');
      return;
    }

    const levels = data.levels || [];
    const completed = levels.filter(level => level.completed).length;
    const average = levels.length
      ? Math.round(levels.reduce((sum, level) => sum + Number(level.best_score || 0), 0) / levels.length)
      : 0;

    document.getElementById('completedLevels').textContent = completed;
    document.getElementById('learnedComponents').textContent = data.learned_components || 0;
    document.getElementById('bestAverage').textContent = `${average}%`;

    levelsContainer.innerHTML = levels.length
      ? levels.map(level => `
        <article class="list-item">
          <strong>Nivel ${level.level_number}: ${level.title}</strong>
          <span>${level.device_name} | scor maxim ${Number(level.best_score || 0)}% | necesar ${level.min_score_required}%</span>
          <span>${level.completed ? 'Completat' : 'In lucru'}</span>
        </article>
      `).join('')
      : empty('Nu exista niveluri configurate.');

    const sessions = data.recent_sessions || [];
    sessionsContainer.innerHTML = sessions.length
      ? sessions.map(session => `
        <article class="list-item">
          <strong>${session.level_title}</strong>
          <span>${session.score}% | ${session.correct_answers}/${session.total_questions} raspunsuri corecte</span>
          <span>${session.created_at || ''}</span>
        </article>
      `).join('')
      : empty('Joaca primul quiz pentru a crea o sesiune.');
  } catch (error) {
    levelsContainer.innerHTML = empty('Nu s-a putut incarca dashboard-ul.');
    sessionsContainer.innerHTML = empty('Verifica serverul API.');
  }
});
