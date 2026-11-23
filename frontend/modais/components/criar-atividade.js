/* <edu-modal-criar-atividade> · Criar atividade (funcionalidade 9; página "Cadastrar atividade").
   Abrir: data-bs-target="#modal-criar-atividade"
   Atributos: veja publicacao-base.js */
(function () {
  'use strict';
  class EduModalCriarAtividade extends EduModalPublicacao {
    get tipo() { return 'atividade'; }
    get modo() { return 'criar'; }
  }
  customElements.define('edu-modal-criar-atividade', EduModalCriarAtividade);
})();
