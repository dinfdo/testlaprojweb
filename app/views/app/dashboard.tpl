<?php include VIEW_PATH . '/layout/header.tpl'; ?>

<main>
  <section class="page-heading">
    <p class="eyebrow">Progres personal</p>
    <h1>Buna, <?= htmlspecialchars($user['username']) ?>!</h1>
    <p class="muted">Urmareste nivelurile, scorurile maxime si componentele pe care le-ai invatat.</p>
  </section>

  <section class="stats-row" aria-label="Rezumat progres">
    <article class="stat-card">
      <span class="stat-value"><?= (int)$data['completed_levels'] ?></span>
      <span class="stat-label">niveluri completate</span>
    </article>
    <article class="stat-card">
      <span class="stat-value"><?= (int)$data['learned_components'] ?></span>
      <span class="stat-label">componente invatate</span>
    </article>
    <article class="stat-card">
      <span class="stat-value"><?= (int)$data['best_average'] ?>%</span>
      <span class="stat-label">medie scoruri</span>
    </article>
  </section>

  <section class="content-grid">
    <div class="panel">
      <div class="section-title">
        <h2>Niveluri</h2>
        <a class="text-link" href="<?= $base ?>/game">Joaca</a>
      </div>
      <div class="list-stack">
        <?php if (empty($data['levels'])): ?>
          <div class="empty-state">Nu exista niveluri configurate.</div>
        <?php else: ?>
          <?php foreach ($data['levels'] as $level): ?>
            <article class="list-item">
              <strong><?= htmlspecialchars($level['title']) ?></strong>
              <span>Nivel <?= (int)$level['level_number'] ?> | Scor maxim: <?= (int)$level['best_score'] ?>% | Necesar: <?= (int)$level['min_score_required'] ?>%</span>
              <span class="status-<?= $level['completed'] ? 'done' : 'open' ?>">
                <?= $level['completed'] ? 'Completat' : 'In lucru' ?>
              </span>
            </article>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>

    <div class="panel">
      <div class="section-title">
        <h2>Incercari recente</h2>
      </div>
      <div class="list-stack">
        <?php if (empty($data['recent_sessions'])): ?>
          <div class="empty-state">Joaca primul quiz pentru a crea o sesiune.</div>
        <?php else: ?>
          <?php foreach ($data['recent_sessions'] as $s): ?>
            <article class="list-item">
              <strong><?= htmlspecialchars($s['level_name']) ?></strong>
              <span><?= (int)$s['score'] ?>% | <?= htmlspecialchars($s['created_at'] ?? '') ?></span>
            </article>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </section>
</main>

<?php include VIEW_PATH . '/layout/footer.tpl'; ?>
