document.addEventListener('DOMContentLoaded', async () => {
  const data = await apiRequest('auth/me.php');
  console.log('User data:', data);
});
