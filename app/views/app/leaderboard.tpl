<?php include VIEW_PATH . '/layout/header.tpl'; ?>

<main>
  <section class="page-heading">
    <p class="eyebrow">Top utilizatori</p>
    <h1>Clasament</h1>
    <p class="muted">Scorurile sunt calculate din progresul pe niveluri si completari.</p>
  </section>

  <section class="panel">
    <div class="section-title">
      <h2>Rezultate</h2>
      <a class="text-link" href="<?= $base ?>/../api/rss/leaderboard.php">Flux RSS</a>
    </div>

    <div class="table-wrap">
      <?php if (empty($entries)): ?>
        <div class="empty-state">Nu exista date in clasament inca.</div>
      <?php else: ?>
        <table class="data-table">
          <thead>
            <tr>
              <th>#</th>
              <th>Utilizator</th>
              <th>Niveluri completate</th>
              <th>Scor maxim</th>
              <th>Medie</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($entries as $rank => $row): ?>
              <tr>
                <td><?= $rank + 1 ?></td>
                <td><?= htmlspecialchars($row['username']) ?></td>
                <td><?= (int)$row['levels_completed'] ?></td>
                <td><?= round((float)$row['best_score'], 1) ?>%</td>
                <td><?= round((float)$row['avg_score'], 1) ?>%</td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
  </section>
</main>

<?php include VIEW_PATH . '/layout/footer.tpl'; ?>
