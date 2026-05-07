document.addEventListener('DOMContentLoaded', async () => {
  const leaderboard = document.getElementById('leaderboard');
  const data = await apiRequest('leaderboard/list.php');
  leaderboard.innerText = JSON.stringify(data, null, 2);
});
