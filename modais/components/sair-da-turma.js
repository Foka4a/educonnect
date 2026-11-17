/* <edu-modal-sair-da-turma> · EXTRA: confirmação de "Sair da turma" (aluno ou professor que não é o administrador).
   Abrir: data-bs-target="#modal-sair-da-turma"
   Atributos: action (destino do POST), turma-id, turma-nome */
(function () {
  'use strict';
  class EduModalSairDaTurma extends EduModalConfirmar {
    config() {
      return {
        id: 'modal-sair-da-turma', titulo: 'Sair da turma?', campo: 'turma_id', valor: this.attr('turma-id', ''),
        ok: 'Sair', perigo: true,
        corpo: '<p class="mb-2"><strong style="color:var(--bs-body-color)">' + this.esc(this.attr('turma-nome', 'Matemática')) + '</strong></p>' +
               '<p class="mb-0">Você deixa de ver o mural, as atividades e as notas desta turma. Para voltar, será preciso usar o código da turma de novo.</p>'
      };
    }
  }
  customElements.define('edu-modal-sair-da-turma', EduModalSairDaTurma);
})();
