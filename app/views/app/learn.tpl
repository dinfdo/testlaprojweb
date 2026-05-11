<?php include VIEW_PATH . '/layout/header.tpl'; ?>

<main>
  <section class="page-heading split-heading">
    <div>
      <p class="eyebrow">Biblioteca de invatare</p>
      <h1>Componente hardware</h1>
      <p class="muted">Exploreaza rolul fiecarei componente si nivelul la care apare in joc.</p>
    </div>
    <div class="filters" aria-label="Filtre componente">
      <a class="filter-button <?= $activeDifficulty === null ? 'active' : '' ?>"
         href="<?= $base ?>/learn">Toate</a>
      <a class="filter-button <?= $activeDifficulty === 1 ? 'active' : '' ?>"
         href="<?= $base ?>/learn?difficulty=1">Baza</a>
      <a class="filter-button <?= $activeDifficulty === 2 ? 'active' : '' ?>"
         href="<?= $base ?>/learn?difficulty=2">Mediu</a>
      <a class="filter-button <?= $activeDifficulty === 3 ? 'active' : '' ?>"
         href="<?= $base ?>/learn?difficulty=3">Avansat</a>
    </div>
  </section>

  <section class="component-grid" aria-live="polite">
    <?php if (empty($components)): ?>
      <div class="empty-state">Nu exista componente pentru filtrul selectat.</div>
    <?php else: ?>
      <?php foreach ($components as $c): ?>
        <article class="component-card">
          <div class="component-icon">
            <?php if (!empty($c['image_path'])): ?>
              <img src="<?= $base ?>/assets/components/<?= htmlspecialchars($c['image_path']) ?>"
                   alt="<?= htmlspecialchars($c['name']) ?>" loading="lazy">
            <?php else: ?>
              <span class="component-initial"><?= htmlspecialchars(mb_substr($c['name'], 0, 3)) ?></span>
            <?php endif; ?>
          </div>
          <div class="component-body">
            <span class="difficulty-pill">Nivel <?= (int)$c['difficulty_level'] ?> — <?= htmlspecialchars($c['device_name']) ?></span>
            <h3><?= htmlspecialchars($c['name']) ?></h3>
            <p><?= htmlspecialchars($c['description']) ?></p>
          </div>
        </article>
      <?php endforeach; ?>
    <?php endif; ?>
  </section>
</main>

<?php include VIEW_PATH . '/layout/footer.tpl'; ?>
