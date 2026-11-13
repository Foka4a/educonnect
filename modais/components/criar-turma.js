/* <edu-modal-criar-turma> · Criar turma (professor). Abrir: data-bs-target="#modal-criar-turma"
   Atributos: action */
(function () {
  'use strict';
  class EduModalCriarTurma extends EduModal {
    render() {
      return `
<div class="modal fade" id="modal-criar-turma" tabindex="-1" aria-labelledby="titulo-criar-turma" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
    <form class="modal-content" id="form-criar-turma" ${this.formAttrs()} novalidate>
      <header class="modal-header">
        <h2 class="modal-title" id="titulo-criar-turma">Criar turma</h2>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </header>
      <section class="modal-body">
        <label class="form-label" for="criar-turma-nome">Nome da turma (obrigatório)</label>
        <input class="form-control mb-3" id="criar-turma-nome" name="nome" autocomplete="off" required>
        <label class="form-label" for="criar-turma-secao">Seção</label>
        <input class="form-control mb-3" id="criar-turma-secao" name="secao" autocomplete="off">
        <div class="row g-3">
          <div class="col-12 col-sm-6">
            <label class="form-label" for="criar-turma-disciplina">Disciplina</label>
            <input class="form-control" id="criar-turma-disciplina" name="disciplina" autocomplete="off">
          </div>
          <div class="col-12 col-sm-6">
            <label class="form-label" for="criar-turma-sala">Sala</label>
            <input class="form-control" id="criar-turma-sala" name="sala" autocomplete="off">
          </div>
        </div>
        <p class="form-text mt-3 mb-0">O código da turma é gerado ao criar, e você vai direto para o Mural.</p>
      </section>
      <footer class="modal-footer">
        <button type="button" class="btn btn-link-edu" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-primary" id="criar-turma-ok" disabled>Criar</button>
      </footer>
    </form>
  </div>
</div>`;
    }
  }
  customElements.define('edu-modal-criar-turma', EduModalCriarTurma);
})();
