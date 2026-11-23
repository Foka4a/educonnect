/* EduConnect · base dos modais de confirmação (excluir, sair, remover).
   Cada modal concreto só diz o título, o texto, a rota do back-end e o campo oculto com o id do item (extras = outros campos ocultos).
   Carregue DEPOIS de edu-modal.js e ANTES dos componentes de confirmação. */
(function () {
  'use strict';
  class EduModalConfirmar extends EduModal {
    /* Os filhos devolvem: {id, rota, titulo, corpo (HTML já escapado), campo, valor, extras, ok, perigo} */
    config() { return {}; }
    render() {
      var c = this.config();
      return `
<div class="modal fade" id="${c.id}" tabindex="-1" role="alertdialog" aria-labelledby="titulo-${c.id}" aria-describedby="descricao-${c.id}" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form class="modal-content" ${this.formAttrs(c.rota)}>
      <input type="hidden" name="${c.campo}" value="${this.esc(c.valor)}">
      ${c.extras || ''}
      <header class="modal-header">
        <h2 class="modal-title" id="titulo-${c.id}">${c.titulo}</h2>
      </header>
      <section class="modal-body" id="descricao-${c.id}">${c.corpo}</section>
      <footer class="modal-footer">
        <button type="button" class="btn btn-link-edu" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-link-edu${c.perigo ? ' btn-perigo' : ''}">${c.ok}</button>
      </footer>
    </form>
  </div>
</div>`;
    }
  }
  window.EduModalConfirmar = EduModalConfirmar;
})();
