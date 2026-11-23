/* <edu-modal-editar-material> · Editar material (menu ⋮ do material, "Editar").
   Abrir: data-bs-target="#modal-editar-material"
   Atributos: veja publicacao-base.js */
(function () {
  'use strict';
  class EduModalEditarMaterial extends EduModalPublicacao {
    get tipo() { return 'material'; }
    get modo() { return 'editar'; }
  }
  customElements.define('edu-modal-editar-material', EduModalEditarMaterial);
})();
