/* EduConnect · comportamento dos modais (validação e prévia).
   Usa delegação de eventos no document, então funciona qualquer que seja a ordem dos scripts
   e só age nos modais presentes na página. O envio dos formulários fica a cargo do back-end. */
(function () {
  'use strict';
  var $ = function (s) { return document.querySelector(s); };

  document.addEventListener('input', function (e) {
    // Criar turma: o botão "Criar" só habilita com o nome preenchido
    if (e.target.id === 'criar-turma-nome') {
      var ok = $('#criar-turma-ok');
      if (ok) ok.disabled = !e.target.value.trim();
    }
    // Participar da turma: limpa o erro ao digitar
    if (e.target.id === 'participar-turma-codigo') e.target.classList.remove('is-invalid');
  });

  // Participar da turma: código de 5 a 8 letras ou números
  document.addEventListener('submit', function (e) {
    if (e.target.id !== 'form-participar-turma') return;
    var codigo = $('#participar-turma-codigo');
    var valido = /^[a-z0-9]{5,8}$/i.test(codigo.value.trim());
    codigo.classList.toggle('is-invalid', !valido);
    if (!valido) { e.preventDefault(); codigo.focus(); }
  });

  // Personalizar aparência: prévia da cor do cabeçalho
  document.addEventListener('change', function (e) {
    if (e.target.name !== 'cor' || !e.target.closest('#modal-personalizar-aparencia')) return;
    var banner = $('#aparencia-previa');
    if (!banner) return;
    banner.style.background = e.target.value;
    banner.style.color = (e.target.value === '#d3e3fd' || e.target.value === '#bdc1c6') ? '#1d2433' : '#fff';
  });

  // Criar turma: limpa o formulário ao fechar
  document.addEventListener('hidden.bs.modal', function (e) {
    if (e.target.id !== 'modal-criar-turma') return;
    var form = $('#form-criar-turma'), ok = $('#criar-turma-ok');
    if (form) form.reset();
    if (ok) ok.disabled = true;
  });
})();
