/* <edu-modal-excluir-material> · EXTRA: confirmação do menu ⋮ do material ("Excluir").
   Abrir: data-bs-target="#modal-excluir-material"
   Atributos: action (destino do POST), material-id, material-titulo */
(function () {
  'use strict';
  class EduModalExcluirMaterial extends EduModalConfirmar {
    config() {
      return {
        id: 'modal-excluir-material', titulo: 'Excluir material?', campo: 'material_id', valor: this.attr('material-id', ''),
        ok: 'Excluir', perigo: true,
        corpo: '<p class="mb-2"><strong style="color:var(--bs-body-color)">' + this.esc(this.attr('material-titulo', 'Aviso de prova')) + '</strong></p>' +
               '<p class="mb-0">Os anexos e os comentários deste material também serão excluídos. Esta ação não pode ser desfeita.</p>'
      };
    }
  }
  customElements.define('edu-modal-excluir-material', EduModalExcluirMaterial);
})();
