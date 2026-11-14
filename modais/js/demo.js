/* Somente demonstração: tema automático e formulários/eventos que não enviam nada.
/* Somente demonstração: tema automático e formulários que não enviam nada.
   Não carregue este arquivo no site final. */
(function () {
  'use strict';
  var mq = matchMedia('(prefers-color-scheme: dark)');
  function setTheme() { document.documentElement.setAttribute('data-bs-theme', mq.matches ? 'dark' : 'light'); }
  setTheme(); mq.addEventListener('change', setTheme);

  var status = document.getElementById('status');
  function dizer(t) { if (status) status.textContent = t; }

  // formulários dos modais: os componentes já barram o que for inválido (defaultPrevented)
  document.addEventListener('submit', function (e) {
    var modal = e.target.closest && e.target.closest('.modal');
    if (!modal || e.defaultPrevented || e.target.id === 'entrega-form-comentario') return;
    e.preventDefault();
    dizer('Formulário válido (nada foi enviado nesta demonstração).');
    bootstrap.Modal.getOrCreateInstance(modal).hide();
  });

  // eventos da Entrega do aluno
  document.addEventListener('edu:salvar-nota', function (e) {
    dizer('Nota de ' + e.detail.aluno + ': ' + (e.detail.nota === null ? 'sem nota' : e.detail.nota + '/100') + '.');
  });
  document.addEventListener('edu:novo-comentario', function (e) {
    dizer('Comentário particular para ' + e.detail.aluno + ': “' + e.detail.texto + '”.');
  });
  document.addEventListener('submit', function (e) {
    var modal = e.target.closest && e.target.closest('.modal');
    if (!modal || e.defaultPrevented) return;   // formulário inválido já foi barrado em modais.js
    e.preventDefault();
    var status = document.getElementById('status');
    if (status) status.textContent = 'Formulário válido (nada foi enviado nesta demonstração).';
    bootstrap.Modal.getOrCreateInstance(modal).hide();
  });
})();
