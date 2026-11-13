/* <edu-modal-participar-turma> · Participar da turma (aluno). Abrir: data-bs-target="#modal-participar-turma"
   Atributos: action
   A validação de 5 a 8 letras/números está em js/modais.js; valide também no servidor. */
(function () {
  'use strict';
  class EduModalParticiparTurma extends EduModal {
    render() {
      return `
<div class="modal fade" id="modal-participar-turma" tabindex="-1" aria-labelledby="titulo-participar-turma" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form class="modal-content" id="form-participar-turma" ${this.formAttrs()} novalidate>
      <header class="modal-header">
        <h2 class="modal-title" id="titulo-participar-turma">Participar da turma</h2>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </header>
      <section class="modal-body">
        <p>Peça o código ao professor e digite abaixo. Você entra como aluno.</p>
        <label class="form-label" for="participar-turma-codigo">Código da turma</label>
        <input class="form-control" id="participar-turma-codigo" name="codigo" maxlength="8" placeholder="Ex.: x7k2m9q" autocomplete="off" aria-describedby="participar-turma-erro" required>
        <div class="invalid-feedback" id="participar-turma-erro">Código inválido. Use de 5 a 8 letras ou números, sem espaços.</div>
      </section>
      <footer class="modal-footer">
        <button type="button" class="btn btn-link-edu" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-primary">Participar</button>
      </footer>
    </form>
  </div>
</div>`;
    }
  }
  customElements.define('edu-modal-participar-turma', EduModalParticiparTurma);
})();
