document.addEventListener('DOMContentLoaded', async () => {
  const container = document.getElementById('componentsList');
  const filters = document.querySelectorAll('[data-difficulty]');
  let components = [];

  const labels = {
    1: 'Baza',
    2: 'Mediu',
    3: 'Avansat',
  };

  const render = difficulty => {
    const items = difficulty === 'all'
      ? components
      : components.filter(component => String(component.difficulty_level) === difficulty);

    container.innerHTML = items.length
      ? items.map(component => `
        <article class="component-card">
          <span class="difficulty-pill">${labels[component.difficulty_level] || 'Nivel'}</span>
          <h2>${component.name}</h2>
          <p>${component.description}</p>
          <p><strong>Scop:</strong> ${component.purpose}</p>
          <p><strong>Dispozitiv:</strong> ${component.device_name}</p>
        </article>
      `).join('')
      : '<div class="empty-state">Nu exista componente pentru filtrul ales.</div>';
  };

  try {
    const data = await apiRequest('components/list.php');
    components = data.components || [];
    render('all');
  } catch (error) {
    container.innerHTML = '<div class="empty-state">Nu s-au putut incarca componentele.</div>';
  }

  filters.forEach(button => {
    button.addEventListener('click', () => {
      filters.forEach(item => item.classList.remove('active'));
      button.classList.add('active');
      render(button.dataset.difficulty);
    });
  });
});
