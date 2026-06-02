
(function (global) {
  'use strict';
  const registry = {};
  function register(slug, fn) { registry[slug] = fn; }
  function launch(slug, gameId, difficulty) {
    const fn = registry[slug];
    if (!fn) { LeG.wm.toast('Joc indisponibil: ' + slug, true); return; }
    fn(gameId, difficulty);
  }
  global.LeG = global.LeG || {};
  global.LeG.games = { register, launch };
})(window);
