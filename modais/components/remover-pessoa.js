/* <edu-modal-remover-pessoa> · EXTRA: "Remover da turma?" (aba Pessoas, menu ⋮ da pessoa; professor remove estudantes).
   Abrir: data-bs-target="#modal-remover-pessoa"
   Atributos: action (destino do POST), pessoa-id, pessoa-nome */
(function () {
  'use strict';
  class EduModalRemoverPessoa extends EduModalConfirmar {
    config() {
      return {
        rota: '/remover-pessoa', id: 'modal-remover-pessoa', titulo: 'Remover da turma?', campo: 'pessoa_id', valor: this.attr('pessoa-id', ''),
        extras: this.campoTurma(), ok: 'Remover', perigo: true,
        corpo: '<p class="mb-2"><strong style="color:var(--bs-body-color)">' + this.esc(this.attr('pessoa-nome', 'João Silva')) + '</strong></p>' +
               '<p class="mb-0">A pessoa deixa de ver o mural, as atividades e as notas desta turma. As entregas já feitas continuam guardadas.</p>'
      };
    }
  }
  customElements.define('edu-modal-remover-pessoa', EduModalRemoverPessoa);
})();
