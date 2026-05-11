<?php include VIEW_PATH . '/layout/header.tpl'; ?>

<main>
  <section class="hero">
    <div class="hero-copy">
      <p class="eyebrow">Learning by Playing Web Games</p>
      <h1>Invata componentele unui computer prin provocari scurte si clare.</h1>
      <p class="hero-text">
        LeG transforma notiunile de hardware in niveluri interactive: descoperi rolul pieselor,
        raspunzi la intrebari si iti urmaresti progresul pana ajungi in clasament.
      </p>
      <div class="hero-actions">
        <a class="button primary" href="<?= $base ?>/register">Incepe gratuit</a>
        <a class="button ghost" href="<?= $base ?>/learn">Exploreaza componentele</a>
      </div>
    </div>

    <div class="device-panel" aria-label="Schema simplificata computer">
      <div class="chip cpu">CPU</div>
      <div class="chip ram">RAM</div>
      <div class="chip gpu">GPU</div>
      <div class="chip ssd">SSD</div>
      <div class="chip board">Motherboard</div>
      <div class="chip psu">PSU</div>
    </div>
  </section>

  <section class="feature-grid" aria-label="Functii LeG">
    <article class="feature-card">
      <span class="badge">01</span>
      <h2>Invata progresiv</h2>
      <p>Parcurge componentele una cate una, de la CPU pana la Cooling System, cu informatii clare si vizuale.</p>
    </article>
    <article class="feature-card">
      <span class="badge">02</span>
      <h2>Joaca si testeaza</h2>
      <p>Plaseaza piesele pe diagrama, potriveste descrierile si rezolva quiz-ul final pentru a trece nivelul.</p>
    </article>
    <article class="feature-card">
      <span class="badge">03</span>
      <h2>Urmareste-ti progresul</h2>
      <p>Scorurile, componentele invatate si incercarile recente sunt salvate in dashboard-ul personal.</p>
    </article>
    <article class="feature-card">
      <span class="badge">04</span>
      <h2>Clasament live</h2>
      <p>Compara-te cu alti utilizatori in clasamentul global, disponibil si ca flux RSS in timp real.</p>
    </article>
  </section>
</main>

<?php include VIEW_PATH . '/layout/footer.tpl'; ?>
