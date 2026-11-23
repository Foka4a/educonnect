/* <edu-modal-criar-turma> · Modal "Criar turma" (mapa de telas, Início). Abrir: data-bs-target="#modal-criar-turma"
   Campos: Nome da turma (obrigatório), Níveis e Matéria (opcionais). Seção e Sala foram removidas.
   Desktop: título + Cancelar / Criar (botões de texto). Mobile (< 576 px): tela cheia com [×] título [Criar].
   Quem cria vira professor da turma; o back-end gera o código e abre o Mural.
   Atributo: action (destino do POST) */
(function () {
  'use strict';
  class EduModalCriarTurma extends EduModal {
    render() {
      return `
<div class="modal fade" id="modal-criar-turma" tabindex="-1" aria-labelledby="titulo-criar-turma" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-fullscreen-sm-down">
    <form class="modal-content" id="form-criar-turma" ${this.formAttrs('/criar-turma')} novalidate>
      <header class="modal-header cab-criar">
        <button type="button" class="btn btn-redondo d-sm-none" data-bs-dismiss="modal" aria-label="Fechar sem salvar">${this.icone('fechar')}</button>
        <h2 class="modal-title" id="titulo-criar-turma">Criar turma</h2>
        <button type="submit" class="btn btn-primary rounded-pill d-sm-none">Criar</button>
      </header>
      <section class="modal-body">
        <div class="form-floating">
          <input class="form-control" id="criar-turma-nome" name="nome" placeholder="Nome da turma" autocomplete="off" required aria-describedby="criar-turma-ajuda">
          <label for="criar-turma-nome">Nome da turma *</label>
          <div class="invalid-feedback" id="criar-turma-erro" role="alert">Informe o nome da turma.</div>
        </div>
        <p class="form-text mt-1 mb-3" id="criar-turma-ajuda">*Obrigatório</p>
        <div class="form-floating mb-3">
          <input class="form-control" id="criar-turma-niveis" name="niveis" placeholder="Níveis" autocomplete="off">
          <label for="criar-turma-niveis">Níveis</label>
        </div>
        <div class="form-floating">
          <input class="form-control" id="criar-turma-materia" name="materia" placeholder="Matéria" autocomplete="off">
          <label for="criar-turma-materia">Matéria</label>
        </div>
      </section>
      <footer class="modal-footer d-none d-sm-flex">
        <button type="button" class="btn btn-link-edu" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-link-edu">Criar</button>
      </footer>
    </form>
  </div>
</div>`;
    }

    depoisDeRenderizar() {
      var modal = this.querySelector('.modal');
      var form = this.querySelector('form');
      var nome = this.querySelector('#criar-turma-nome');

      // nome é obrigatório: sem ele o formulário não segue
      form.addEventListener('submit', function (e) {
        if (!nome.value.trim()) {
          e.preventDefault();
          nome.classList.add('is-invalid');
          nome.focus();
        }
      });
      nome.addEventListener('input', function () { nome.classList.remove('is-invalid'); });

      // Cancelar / × fecha sem salvar: limpa o que foi digitado
      modal.addEventListener('hidden.bs.modal', function () {
        form.reset();
        nome.classList.remove('is-invalid');
      });
      modal.addEventListener('shown.bs.modal', function () { nome.focus(); });
    }
  }
  customElements.define('edu-modal-criar-turma', EduModalCriarTurma);
})();
