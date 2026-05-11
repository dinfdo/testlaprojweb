<!DOCTYPE html>
<html lang="ro">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($title ?? 'LeG') ?></title>
  <link rel="stylesheet" href="<?= $base ?>/css/style.css">
</head>
<body>
<header class="site-header">
  <a class="brand" href="<?= $base ?>/">LeG</a>
  <nav class="nav-links" aria-label="Navigatie principala">
    <?php if (!empty($_SESSION['user_id'])): ?>
      <a href="<?= $base ?>/learn">Invata</a>
      <a href="<?= $base ?>/game">Joc</a>
      <a href="<?= $base ?>/leaderboard">Clasament</a>
      <a href="<?= $base ?>/dashboard">Dashboard</a>
      <a href="<?= $base ?>/profile">Profil</a>
      <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
        <a href="<?= $base ?>/admin">Admin</a>
      <?php endif; ?>
      <form method="POST" action="<?= $base ?>/logout" style="display:inline">
        <button class="nav-button" type="submit">Deconectare</button>
      </form>
    <?php else: ?>
      <a href="<?= $base ?>/learn">Componente</a>
      <a href="<?= $base ?>/leaderboard">Clasament</a>
      <a href="<?= $base ?>/login">Login</a>
      <a class="nav-button" href="<?= $base ?>/register">Cont nou</a>
    <?php endif; ?>
  </nav>
</header>
