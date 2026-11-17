/* <edu-modal-editar-atividade> · Editar atividade (menu ⋮ da atividade, "Editar").
   Abrir: data-bs-target="#modal-editar-atividade"
   Atributos: veja publicacao-base.js */
(function () {
  'use strict';
  class EduModalEditarAtividade extends EduModalPublicacao {
    get tipo() { return 'atividade'; }
    get modo() { return 'editar'; }
  }
  customElements.define('edu-modal-editar-atividade', EduModalEditarAtividade);
})();
