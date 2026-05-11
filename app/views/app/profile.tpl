<?php include VIEW_PATH . '/layout/header.tpl'; ?>

<main>
  <section class="page-heading">
    <p class="eyebrow">Contul meu</p>
    <h1>Profil</h1>
  </section>

  <section class="content-grid">

    <div class="panel">
      <div class="section-title"><h2>Editeaza datele</h2></div>

      <?php if (!empty($error)): ?>
        <p class="form-message error" role="alert"><?= htmlspecialchars($error) ?></p>
      <?php endif; ?>
      <?php if (!empty($success)): ?>
        <p class="form-message" role="status"><?= htmlspecialchars($success) ?></p>
      <?php endif; ?>

      <form method="POST" action="<?= $base ?>/profile" class="form-panel">
        <label for="username">Username</label>
        <input id="username" type="text" name="username"
               value="<?= htmlspecialchars($user['username']) ?>" required>

        <label for="email">Email</label>
        <input id="email" type="email" name="email"
               value="<?= htmlspecialchars($user['email']) ?>" required>

        <p class="muted" style="font-size:.85rem;margin:12px 0 4px">
          Schimba parola — lasa gol daca nu vrei sa o modifici.
        </p>

        <label for="current_password">Parola curenta</label>
        <input id="current_password" type="password" name="current_password" autocomplete="current-password">

        <label for="new_password">Parola noua</label>
        <input id="new_password" type="password" name="new_password" autocomplete="new-password" minlength="6">

        <button class="button primary full" type="submit">Salveaza modificarile</button>
      </form>
    </div>

    <div class="panel">
      <div class="section-title"><h2>Sterge contul</h2></div>
      <p class="muted">Aceasta actiune este ireversibila. Tot progresul si sesiunile tale vor fi sterse.</p>

      <form method="POST" action="<?= $base ?>/profile/delete-account" class="form-panel"
            onsubmit="return confirm('Esti sigur? Contul va fi sters permanent.')">
        <label for="confirm_password">Confirma cu parola</label>
        <input id="confirm_password" type="password" name="confirm_password"
               autocomplete="current-password" required>
        <button class="btn-danger full" type="submit">Sterge contul definitiv</button>
      </form>
    </div>

  </section>
</main>

<?php include VIEW_PATH . '/layout/footer.tpl'; ?>
