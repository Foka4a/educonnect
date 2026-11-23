/* <edu-modal-excluir-atividade> · EXTRA (não está nos wireframes): confirmação de "editar/excluir" do menu ⋮ da atividade.
   Abrir: data-bs-target="#modal-excluir-atividade"
   Atributos: action (destino do POST), atividade-id, atividade-titulo */
/* <edu-modal-excluir-atividade> · Excluir atividade (confirmação irreversível). Abrir: data-bs-target="#modal-excluir-atividade"
   Atributos: action */
(function () {
  'use strict';
  class EduModalExcluirAtividade extends EduModal {
    render() {
      var titulo = this.attr('atividade-titulo', 'Lista de exercícios 1');
      return `
<div class="modal fade" id="modal-excluir-atividade" tabindex="-1" role="alertdialog" aria-labelledby="titulo-excluir-atividade" aria-describedby="descricao-excluir-atividade" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form class="modal-content" ${this.formAttrs()}>
      <input type="hidden" name="atividade_id" value="${this.esc(this.attr('atividade-id', ''))}">
      <header class="modal-header">
        <h2 class="modal-title" id="titulo-excluir-atividade">Excluir atividade?</h2>
      </header>
      <section class="modal-body" id="descricao-excluir-atividade">
        <p class="mb-2"><strong style="color:var(--bs-body-color)">${this.esc(titulo)}</strong></p>
        <p class="mb-0">As entregas, as notas e os comentários desta atividade também serão excluídos. Esta ação não pode ser desfeita.</p>
      </section>
      <footer class="modal-footer">
        <button type="button" class="btn btn-link-edu" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-link-edu btn-perigo">Excluir</button>
      return `
<div class="modal fade" id="modal-excluir-atividade" tabindex="-1" role="alertdialog" aria-labelledby="titulo-excluir-atividade" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form class="modal-content" ${this.formAttrs()}>
      <header class="modal-header">
        <h2 class="modal-title" id="titulo-excluir-atividade">Excluir atividade?</h2>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </header>
      <section class="modal-body">
        <div class="alert mb-3" role="alert">Esta ação não pode ser desfeita.</div>
        <p class="mb-0">As entregas, as notas e os comentários desta atividade também serão excluídos.</p>
      </section>
      <footer class="modal-footer">
        <button type="button" class="btn btn-link-edu" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-warning">Excluir</button>
      </footer>
    </form>
  </div>
</div>`;
    }
  }
  customElements.define('edu-modal-excluir-atividade', EduModalExcluirAtividade);
})();
