<?php include VIEW_PATH . '/layout/header.tpl'; ?>

<main class="auth-layout">
  <section class="auth-card">
    <p class="eyebrow">Bine ai revenit</p>
    <h1>Autentificare</h1>
    <p class="muted">Intra in cont pentru a salva scorurile si progresul pe niveluri.</p>

    <?php if (!empty($error)): ?>
      <p class="form-message error" role="alert"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST" action="<?= $base ?>/login" class="form-panel">
      <label for="identifier">Username sau email</label>
      <input id="identifier" type="text" name="identifier" autocomplete="username" required>

      <label for="password">Parola</label>
      <input id="password" type="password" name="password" autocomplete="current-password" required>

      <button class="button primary full" type="submit">Autentificare</button>
    </form>

    <p class="small-link">Nu ai cont? <a href="<?= $base ?>/register">Creeaza unul</a>.</p>
  </section>
</main>

<?php include VIEW_PATH . '/layout/footer.tpl'; ?>
