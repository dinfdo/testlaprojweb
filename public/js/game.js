document.addEventListener('DOMContentLoaded', async () => {
  const container = document.getElementById('questionContainer');
  const questions = await apiRequest('game/questions.php');
  container.innerText = JSON.stringify(questions, null, 2);
});
