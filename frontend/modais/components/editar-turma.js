/* <edu-modal-editar-turma> · "Alterar detalhes da turma" (funcionalidade 6; página Detalhes da turma).
   Abrir: data-bs-target="#modal-editar-turma"
   Mesmos campos de Criar turma: Nome (obrigatório), Níveis e Matéria. Desktop: Cancelar / Salvar; mobile (< 576 px): tela cheia com [×] título [Salvar].
   Atributos: action (destino do POST), turma-id, nome, niveis, materia
   Salvar só habilita quando algo mudou. */
(function () {
  'use strict';
  class EduModalEditarTurma extends EduModal {
    render() {
      return `
<div class="modal fade" id="modal-editar-turma" tabindex="-1" aria-labelledby="titulo-editar-turma" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-fullscreen-sm-down">
    <form class="modal-content" ${this.formAttrs()} novalidate>
      <input type="hidden" name="turma_id" value="${this.esc(this.attr('turma-id', ''))}">
      <header class="modal-header cab-criar">
        <button type="button" class="btn btn-redondo d-sm-none" data-bs-dismiss="modal" aria-label="Fechar sem salvar">${this.icone('fechar')}</button>
        <h2 class="modal-title" id="titulo-editar-turma">Editar turma</h2>
        <button type="submit" class="btn btn-primary rounded-pill d-sm-none" data-salvar disabled>Salvar</button>
      </header>
      <section class="modal-body">
        <div class="form-floating">
          <input class="form-control" id="editar-turma-nome" name="nome" placeholder="Nome da turma" value="${this.esc(this.attr('nome', ''))}" autocomplete="off" required aria-describedby="editar-turma-ajuda">
          <label for="editar-turma-nome">Nome da turma *</label>
          <div class="invalid-feedback" role="alert">Informe o nome da turma.</div>
        </div>
        <p class="form-text mt-1 mb-3" id="editar-turma-ajuda">*Obrigatório</p>
        <div class="form-floating mb-3">
          <input class="form-control" id="editar-turma-niveis" name="niveis" placeholder="Níveis" value="${this.esc(this.attr('niveis', ''))}" autocomplete="off">
          <label for="editar-turma-niveis">Níveis</label>
        </div>
        <div class="form-floating">
          <input class="form-control" id="editar-turma-materia" name="materia" placeholder="Matéria" value="${this.esc(this.attr('materia', ''))}" autocomplete="off">
          <label for="editar-turma-materia">Matéria</label>
        </div>
      </section>
      <footer class="modal-footer d-none d-sm-flex">
        <button type="button" class="btn btn-link-edu" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-link-edu" data-salvar disabled>Salvar</button>
      </footer>
    </form>
  </div>
</div>`;
    }
    depoisDeRenderizar() {
      var modal = this.querySelector('.modal');
      var form = this.querySelector('form');
      var nome = this.querySelector('#editar-turma-nome');
      var botoes = this.querySelectorAll('[data-salvar]');
      var campos = Array.prototype.slice.call(form.querySelectorAll('input[type="text"], input:not([type])'));
      var inicial = campos.map(function (c) { return c.value; });
      function mudou() { return campos.some(function (c, i) { return c.value !== inicial[i]; }); }
      function atualizar() { botoes.forEach(function (b) { b.disabled = !mudou(); }); }
      form.addEventListener('input', function () { nome.classList.remove('is-invalid'); atualizar(); });
      form.addEventListener('submit', function (e) {
        if (!nome.value.trim()) { e.preventDefault(); nome.classList.add('is-invalid'); nome.focus(); }
      });
      modal.addEventListener('hidden.bs.modal', function () {
        campos.forEach(function (c, i) { c.value = inicial[i]; });
        nome.classList.remove('is-invalid'); atualizar();
      });
      modal.addEventListener('shown.bs.modal', function () { nome.focus(); });
    }
  }
  customElements.define('edu-modal-editar-turma', EduModalEditarTurma);
})();
