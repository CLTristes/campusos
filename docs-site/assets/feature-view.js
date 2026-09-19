/**
 * Alternância "regra de negócio" × "código real" — convenção padrão de TODA
 * página em docs-site/features/*.html.
 *
 * Por que existe: as duas plateias deste documento são diferentes e leem no
 * mesmo dia. O jurado do hackathon e a dupla discutindo produto leem a
 * regra de negócio; quem abre a IDE às 3 da manhã lê o código. Servir os
 * dois na mesma página sem alternância dá uma página que nenhum dos dois
 * termina de ler.
 *
 * Uma página nova que queira esse padrão só precisa:
 *
 *   1. incluir este script (defer) + <div class="view-switch" role="group">
 *      com dois <button data-view="business">/<button data-view="code">;
 *   2. marcar cada bloco de conteúdo com a classe .view-business OU
 *      .view-code — o HTML contém as duas versões SEMPRE, é a visibilidade
 *      que alterna (ver a regra [data-view] em assets/site.css);
 *   3. escrever o texto de negócio em prosa comum, sem nome de classe/
 *      endpoint/JSON — se precisa de "código" pra fazer sentido, pertence
 *      à visão de código, não à de negócio.
 */
(function () {
  'use strict';

  var STORAGE_KEY = 'campusos-docs-view';

  function initViewSwitch() {
    var switchEl = document.querySelector('.view-switch');
    if (!switchEl) return;

    var buttons = Array.prototype.slice.call(switchEl.querySelectorAll('button[data-view]'));
    if (!buttons.length) return;

    function setView(view) {
      document.body.setAttribute('data-view', view);
      buttons.forEach(function (btn) {
        var isActive = btn.getAttribute('data-view') === view;
        btn.classList.toggle('active', isActive);
        btn.setAttribute('aria-pressed', isActive ? 'true' : 'false');
      });
      try {
        localStorage.setItem(STORAGE_KEY, view);
      } catch (e) {
        // localStorage indisponível (modo privado etc.) — a escolha só não
        // sobrevive entre páginas; a alternância em si continua funcionando.
      }
    }

    buttons.forEach(function (btn) {
      btn.addEventListener('click', function () {
        setView(btn.getAttribute('data-view'));
      });
    });

    var saved = null;
    try {
      saved = localStorage.getItem(STORAGE_KEY);
    } catch (e) {
      saved = null;
    }
    setView(saved === 'code' ? 'code' : 'business');
  }

  document.addEventListener('DOMContentLoaded', initViewSwitch);
})();
