document.addEventListener('DOMContentLoaded', async () => {
  const componentsContainer = document.getElementById('adminComponents');
  const usersContainer = document.getElementById('adminUsers');
  const empty = text => `<div class="empty-state">${text}</div>`;

  try {
    const stats = await apiRequest('admin/stats.php');
    if (stats.success) {
      document.getElementById('totalUsers').textContent = stats.total_users;
      document.getElementById('totalComponents').textContent = stats.total_components;
      document.getElementById('totalSessions').textContent = stats.total_sessions;
      document.getElementById('avgScore').textContent = stats.avg_score === null ? '0%' : `${stats.avg_score}%`;
    }

    const components = await apiRequest('admin/content.php?resource=components');
    componentsContainer.innerHTML = components.success && components.components.length
      ? components.components.slice(0, 8).map(component => `
        <article class="list-item">
          <strong>${component.name}</strong>
          <span>${component.device_name} | nivel ${component.difficulty_level}</span>
        </article>
      `).join('')
      : empty('Nu exista componente sau nu ai acces admin.');

    const users = await apiRequest('admin/users.php');
    usersContainer.innerHTML = users.success && users.users.length
      ? users.users.slice(0, 8).map(user => `
        <article class="list-item">
          <strong>${user.username}</strong>
          <span>${user.email} | ${user.role} | scor ${Number(user.total_score || 0)}</span>
        </article>
      `).join('')
      : empty('Nu exista utilizatori sau nu ai acces admin.');
  } catch (error) {
    componentsContainer.innerHTML = empty('Autentifica-te ca admin pentru a vedea continutul.');
    usersContainer.innerHTML = empty('Datele admin nu sunt disponibile.');
  }
});
