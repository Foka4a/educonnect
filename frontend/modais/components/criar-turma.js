/* <edu-modal-criar-turma> · Modal "Criar turma" (mapa de telas, Início).
   Independente do edu-modal.js e do Bootstrap: usa <dialog> nativo e as classes do style/layout.css
   (.dialogo, .dialogo__campo, .botao-texto, .botao-icone) + modais/css/criar-turma.css.

   Uso na página:
     <link rel="stylesheet" href="../modais/css/criar-turma.css">
     <script src="../modais/components/criar-turma.js" defer></script>
     <button type="button" data-abrir="modal-criar-turma">…</button>
     <edu-modal-criar-turma></edu-modal-criar-turma>

   Campos: Nome da turma (obrigatório), Níveis e Matéria (opcionais).
   Desktop: título + Cancelar / Criar. Celular (< 576 px): tela cheia com [×] título [Criar].
   Atributo: action (destino do POST; sem ele, a própria página, com acao=criar-turma; ela abre o Mural da turma nova). */
(function () {
  'use strict';

  class EduModalCriarTurma extends HTMLElement {
    connectedCallback() {
      if (this.dialogo) return;
      var action = this.getAttribute('action') || ''; // sem action: a própria página
      this.style.display = 'contents';
      this.innerHTML = `
<dialog class="dialogo criar-turma" id="modal-criar-turma" aria-labelledby="titulo-criar-turma">
  <form method="post" novalidate>
    <input type="hidden" name="acao" value="criar-turma">
    <header class="criar-turma__cabecalho">
      <button class="botao-icone criar-turma__so-celular" type="button" data-fechar aria-label="Fechar sem salvar">
        <span class="material-symbols-outlined" aria-hidden="true">close</span>
      </button>
      <h2 class="dialogo__titulo" id="titulo-criar-turma">Criar turma</h2>
      <button class="botao-texto criar-turma__so-celular" type="submit">Criar</button>
    </header>
    <label class="dialogo__rotulo" for="criar-turma-nome">Nome da turma *</label>
    <input class="dialogo__campo" id="criar-turma-nome" name="nome" autocomplete="off" required aria-describedby="criar-turma-ajuda criar-turma-erro">
    <p class="criar-turma__erro" id="criar-turma-erro" role="alert" hidden>Informe o nome da turma.</p>
    <p class="criar-turma__ajuda" id="criar-turma-ajuda">*Obrigatório</p>
    <label class="dialogo__rotulo" for="criar-turma-niveis">Níveis</label>
    <input class="dialogo__campo" id="criar-turma-niveis" name="niveis" autocomplete="off">
    <label class="dialogo__rotulo" for="criar-turma-materia">Matéria</label>
    <input class="dialogo__campo" id="criar-turma-materia" name="materia" autocomplete="off">
    <footer class="dialogo__acoes criar-turma__so-desktop">
      <button class="botao-texto" type="button" data-fechar>Cancelar</button>
      <button class="botao-texto" type="submit">Criar</button>
    </footer>
  </form>
</dialog>`;

      var dialogo = this.dialogo = this.querySelector('dialog');
      var form = dialogo.querySelector('form');
      var nome = form.elements.nome;
      var erro = form.querySelector('#criar-turma-erro');
      form.action = action;

      function limparErro() {
        nome.removeAttribute('aria-invalid');
        erro.hidden = true;
      }

      // Nome é obrigatório: sem ele o formulário não segue.
      form.addEventListener('submit', function (evento) {
        if (!nome.value.trim()) {
          evento.preventDefault();
          nome.setAttribute('aria-invalid', 'true');
          erro.hidden = false;
          nome.focus();
          return;
        }
      });
      nome.addEventListener('input', limparErro);

      // × e Cancelar fecham sem salvar; ao fechar (inclusive Esc ou clique no fundo), limpa o que foi digitado.
      form.querySelectorAll('[data-fechar]').forEach(function (botao) {
        botao.addEventListener('click', function () { dialogo.close(); });
      });
      dialogo.addEventListener('close', function () {
        form.reset();
        limparErro();
      });

      document.addEventListener('click', function (evento) {
        if (evento.target.closest('[data-abrir="modal-criar-turma"]')) {
          dialogo.showModal();
          nome.focus();
        }
      });
    }
  }

  customElements.define('edu-modal-criar-turma', EduModalCriarTurma);
})();
