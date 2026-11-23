/* <edu-modal-promover-professor> · EXTRA (não está nos wireframes): "Promover a professor", aba Pessoas, somente o administrador.
   Regra de negócio: quem entra na turma é aluno; o administrador (criador) pode promovê-lo a professor.
   Abrir: data-bs-target="#modal-promover-professor"
   Atributos: action (destino do POST), pessoa-id, pessoa-nome, pessoa-email */
(function () {
  'use strict';
  class EduModalPromoverProfessor extends EduModal {
    render() {
      var nome = this.attr('pessoa-nome', 'Aluno Exemplo');
      var email = this.attr('pessoa-email', 'aluno@escola.com');
      return `
<div class="modal fade" id="modal-promover-professor" tabindex="-1" aria-labelledby="titulo-promover-professor" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form class="modal-content" ${this.formAttrs('/promover-professor')}>
      ${this.campoTurma()}
      <input type="hidden" name="pessoa_id" value="${this.esc(this.attr('pessoa-id', ''))}">
      <header class="modal-header">
        <h2 class="modal-title" id="titulo-promover-professor">Promover a professor</h2>
      </header>
      <section class="modal-body">
        <p>Professores podem criar atividades, corrigir entregas e convidar alunos.</p>
        <div class="pessoa">
          <span class="av" aria-hidden="true">${this.esc(this.iniciais(nome))}</span>
          <span><strong>${this.esc(nome)}</strong><br><span class="pessoa-email">${this.esc(email)}</span></span>
        </div>
      </section>
      <footer class="modal-footer">
        <button type="button" class="btn btn-link-edu" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-link-edu">Promover</button>
      </footer>
    </form>
  </div>
</div>`;
    }
  }
  customElements.define('edu-modal-promover-professor', EduModalPromoverProfessor);
})();
