/* <edu-modal-entrega-aluno> · Entrega do aluno (professor corrige). Abrir: data-bs-target="#modal-entrega-aluno"
   Atributos: action, aluno-nome, atividade-titulo, entrega-data, arquivo-nome, arquivo-url, nota (0 a 100) */
(function () {
  'use strict';
  class EduModalEntregaAluno extends EduModal {
    render() {
      var aluno = this.esc(this.attr('aluno-nome', 'Ana Souza'));
      var atividade = this.esc(this.attr('atividade-titulo', 'Redação sobre o meio ambiente'));
      var data = this.esc(this.attr('entrega-data', '10 nov.'));
      var arquivo = this.esc(this.attr('arquivo-nome', 'redacao-ana.pdf'));
      var url = this.esc(this.attr('arquivo-url', '#'));
      var nota = this.esc(this.attr('nota', ''));
      return `
<div class="modal fade" id="modal-entrega-aluno" tabindex="-1" aria-labelledby="titulo-entrega-aluno" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
    <form class="modal-content" ${this.formAttrs()}>
      <header class="modal-header">
        <h2 class="modal-title" id="titulo-entrega-aluno">Entrega de ${aluno}</h2>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </header>
      <section class="modal-body">
        <p>${atividade} · entregue em ${data}</p>
        <p class="form-label mb-1">Arquivo enviado</p>
        <p class="mb-3"><a href="${url}" class="badge chip text-decoration-none">${arquivo}</a></p>
        <label class="form-label" for="entrega-nota">Nota</label>
        <div class="input-group mb-3" style="max-width:140px">
          <input class="form-control" id="entrega-nota" name="nota" type="number" min="0" max="100" value="${nota}">
          <span class="input-group-text" style="font-size:12px">/ 100</span>
        </div>
        <label class="form-label" for="entrega-comentario">Comentário particular</label>
        <textarea class="form-control" id="entrega-comentario" name="comentario" rows="3" placeholder="Só você e o aluno veem este comentário"></textarea>
      </section>
      <footer class="modal-footer">
        <button type="button" class="btn btn-link-edu" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-primary">Devolver</button>
      </footer>
    </form>
  </div>
</div>`;
    }
  }
  customElements.define('edu-modal-entrega-aluno', EduModalEntregaAluno);
})();
