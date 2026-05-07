document.addEventListener('DOMContentLoaded', async () => {
  const container = document.getElementById('componentsList');
  const data = await apiRequest('components/list.php');
  container.innerText = JSON.stringify(data, null, 2);
});
