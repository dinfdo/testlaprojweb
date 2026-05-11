<?php include VIEW_PATH . '/layout/header.tpl'; ?>

<main>
  <section class="page-heading">
    <p class="eyebrow">Administrare continut</p>
    <h1>Panou Admin</h1>
    <p class="muted">Privire rapida asupra utilizatorilor, componentelor si sesiunilor de joc.</p>
    <?php if ($notice === 'deleted'): ?>
      <p class="form-message" role="status">Utilizator sters cu succes.</p>
    <?php endif; ?>
  </section>

  <section class="stats-row" aria-label="Statistici admin">
    <article class="stat-card">
      <span class="stat-value"><?= (int)$stats['total_users'] ?></span>
      <span class="stat-label">utilizatori</span>
    </article>
    <article class="stat-card">
      <span class="stat-value"><?= (int)$stats['total_components'] ?></span>
      <span class="stat-label">componente</span>
    </article>
    <article class="stat-card">
      <span class="stat-value"><?= (int)$stats['total_sessions'] ?></span>
      <span class="stat-label">sesiuni quiz</span>
    </article>
    <article class="stat-card">
      <span class="stat-value"><?= number_format((float)$stats['avg_score'], 1) ?>%</span>
      <span class="stat-label">scor mediu</span>
    </article>
  </section>

  <section class="content-grid">
    <div class="panel">
      <div class="section-title">
        <h2>Componente</h2>
      </div>
      <div class="list-stack">
        <?php if (empty($components)): ?>
          <div class="empty-state">Nu exista componente.</div>
        <?php else: ?>
          <?php foreach ($components as $c): ?>
            <article class="list-item">
              <strong><?= htmlspecialchars($c['name']) ?></strong>
              <span>Nivel <?= (int)$c['difficulty_level'] ?> — <?= htmlspecialchars($c['device_name']) ?></span>
            </article>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>

    <div class="panel">
      <div class="section-title">
        <h2>Utilizatori</h2>
      </div>
      <div class="list-stack">
        <?php if (empty($users)): ?>
          <div class="empty-state">Nu exista utilizatori.</div>
        <?php else: ?>
          <?php foreach ($users as $u): ?>
            <article class="list-item">
              <strong><?= htmlspecialchars($u['username']) ?></strong>
              <span><?= htmlspecialchars($u['email']) ?> | <?= htmlspecialchars($u['role']) ?></span>
              <?php if ((int)$u['id'] !== $currentId): ?>
                <div style="display:flex;gap:6px;align-items:center">
                  <form method="POST" action="<?= $base ?>/admin/set-role">
                    <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                    <input type="hidden" name="role" value="<?= $u['role'] === 'admin' ? 'user' : 'admin' ?>">
                    <button class="button ghost" type="submit" style="padding:4px 10px;font-size:.82rem">
                      <?= $u['role'] === 'admin' ? 'Retrograda' : 'Fa admin' ?>
                    </button>
                  </form>
                  <form method="POST" action="<?= $base ?>/admin/delete-user"
                        onsubmit="return confirm('Stergi utilizatorul <?= htmlspecialchars($u['username'], ENT_QUOTES) ?>?')">
                    <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                    <button class="btn-danger" type="submit">Sterge</button>
                  </form>
                </div>
              <?php else: ?>
                <span class="muted">(tu)</span>
              <?php endif; ?>
            </article>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </section>
</main>

<?php include VIEW_PATH . '/layout/footer.tpl'; ?>
