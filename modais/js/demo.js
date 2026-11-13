/* Somente demonstração: tema automático e formulários que não enviam nada.
   Não carregue este arquivo no site final. */
(function () {
  'use strict';
  var mq = matchMedia('(prefers-color-scheme: dark)');
  function setTheme() { document.documentElement.setAttribute('data-bs-theme', mq.matches ? 'dark' : 'light'); }
  setTheme(); mq.addEventListener('change', setTheme);

  document.addEventListener('submit', function (e) {
    var modal = e.target.closest && e.target.closest('.modal');
    if (!modal || e.defaultPrevented) return;   // formulário inválido já foi barrado em modais.js
    e.preventDefault();
    var status = document.getElementById('status');
    if (status) status.textContent = 'Formulário válido (nada foi enviado nesta demonstração).';
    bootstrap.Modal.getOrCreateInstance(modal).hide();
  });
})();
