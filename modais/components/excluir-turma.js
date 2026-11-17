/* <edu-modal-excluir-turma> · EXTRA: confirmação de "Excluir turma" (Detalhes da turma, somente o administrador).
   Abrir: data-bs-target="#modal-excluir-turma"
   Atributos: action (destino do POST), turma-id, turma-nome */
(function () {
  'use strict';
  class EduModalExcluirTurma extends EduModalConfirmar {
    config() {
      return {
        id: 'modal-excluir-turma', titulo: 'Excluir turma?', campo: 'turma_id', valor: this.attr('turma-id', ''),
        ok: 'Excluir turma', perigo: true,
        corpo: '<p class="mb-2"><strong style="color:var(--bs-body-color)">' + this.esc(this.attr('turma-nome', 'Matemática')) + '</strong></p>' +
               '<p class="mb-0">Mural, atividades, materiais, entregas, notas e comentários serão excluídos para todos. Esta ação não pode ser desfeita.</p>'
      };
    }
  }
  customElements.define('edu-modal-excluir-turma', EduModalExcluirTurma);
})();
