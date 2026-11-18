/* <edu-modal-criar-material> · Criar material ou aviso (funcionalidade 8; "Cadastrar material" e "Criar publicação" do mural).
   Abrir: data-bs-target="#modal-criar-material"
   Atributos: veja publicacao-base.js */
(function () {
  'use strict';
  class EduModalCriarMaterial extends EduModalPublicacao {
    get tipo() { return 'material'; }
    get modo() { return 'criar'; }
  }
  customElements.define('edu-modal-criar-material', EduModalCriarMaterial);
})();
