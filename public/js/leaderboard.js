document.addEventListener('DOMContentLoaded', async () => {
  const leaderboard = document.getElementById('leaderboard');

  try {
    const data = await apiRequest('leaderboard/list.php');
    const rows = data.leaderboard || [];

    leaderboard.innerHTML = rows.length
      ? `
        <table>
          <thead>
            <tr>
              <th>Loc</th>
              <th>Utilizator</th>
              <th>Scor total</th>
              <th>Niveluri incercate</th>
              <th>Niveluri completate</th>
            </tr>
          </thead>
          <tbody>
            ${rows.map(row => `
              <tr>
                <td>#${row.rank}</td>
                <td>${row.username}</td>
                <td>${row.total_score}</td>
                <td>${row.levels_attempted}</td>
                <td>${row.levels_completed}</td>
              </tr>
            `).join('')}
          </tbody>
        </table>
      `
      : '<div class="empty-state">Clasamentul este gol momentan.</div>';
  } catch (error) {
    leaderboard.innerHTML = '<div class="empty-state">Nu s-a putut incarca clasamentul.</div>';
  }
});
