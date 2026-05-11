<?php include VIEW_PATH . '/layout/header.tpl'; ?>

<main>
  <section class="page-heading split-heading">
    <div>
      <p class="eyebrow">Mini-joc quiz</p>
      <h1>Testeaza ce ai invatat</h1>
      <p class="muted">Alege un nivel, raspunde la provocari si trimite scorul pentru progres.</p>
    </div>
    <label class="select-control" for="levelSelect">
      Nivel
      <select id="levelSelect"></select>
    </label>
  </section>

  <section class="quiz-shell">
    <div id="questionContainer" class="quiz-list" aria-live="polite"></div>
    <div class="quiz-actions">
      <button id="submitQuiz" class="button primary" type="button">Trimite raspunsurile</button>
      <p id="quizResult" class="form-message" aria-live="polite"></p>
    </div>
  </section>
</main>

<?php $scripts = ['game-quiz']; include VIEW_PATH . '/layout/footer.tpl'; ?>
