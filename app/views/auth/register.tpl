<?php include VIEW_PATH . '/layout/header.tpl'; ?>

<main class="auth-layout">
  <section class="auth-card">
    <p class="eyebrow">Start rapid</p>
    <h1>Creare cont</h1>
    <p class="muted">Contul iti leaga raspunsurile de progres, scoruri si componente invatate.</p>

    <?php if (!empty($error)): ?>
      <p class="form-message error" role="alert"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST" action="<?= $base ?>/register" class="form-panel">
      <label for="name">Nume utilizator</label>
      <input id="name" type="text" name="name" autocomplete="username" required>

      <label for="email">Email</label>
      <input id="email" type="email" name="email" autocomplete="email" required>

      <label for="newPassword">Parola</label>
      <input id="newPassword" type="password" name="password" autocomplete="new-password" minlength="6" required>

      <button class="button primary full" type="submit">Creeaza cont</button>
    </form>

    <p class="small-link">Ai deja cont? <a href="<?= $base ?>/login">Autentifica-te</a>.</p>
  </section>
</main>

<?php include VIEW_PATH . '/layout/footer.tpl'; ?>
