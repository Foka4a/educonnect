/* <edu-modal-promover-professor> · Promover a professor (administrador, aba Pessoas). Abrir: data-bs-target="#modal-promover-professor"
   Atributos: action, pessoa-nome, pessoa-email */
(function () {
  'use strict';
  class EduModalPromoverProfessor extends EduModal {
    render() {
      var nomeBruto = this.attr('pessoa-nome', 'Ana Souza');
      var iniciais = nomeBruto.trim().split(/\s+/).slice(0, 2).map(function (p) { return p.charAt(0).toUpperCase(); }).join('');
      var nome = this.esc(nomeBruto);
      var email = this.esc(this.attr('pessoa-email', 'ana@escola.com'));
      return `
<div class="modal fade" id="modal-promover-professor" tabindex="-1" aria-labelledby="titulo-promover-professor" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form class="modal-content" ${this.formAttrs()}>
      <header class="modal-header">
        <h2 class="modal-title" id="titulo-promover-professor">Promover a professor</h2>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </header>
      <section class="modal-body">
        <p>Professores podem criar atividades, corrigir entregas e convidar alunos.</p>
        <div class="d-flex align-items-center gap-2">
          <span class="av" aria-hidden="true">${this.esc(iniciais)}</span>
          <span>${nome}<br><span style="color:var(--edu-muted)">${email}</span></span>
        </div>
      </section>
      <footer class="modal-footer">
        <button type="button" class="btn btn-link-edu" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-primary">Promover</button>
      </footer>
    </form>
  </div>
</div>`;
    }
  }
  customElements.define('edu-modal-promover-professor', EduModalPromoverProfessor);
})();
