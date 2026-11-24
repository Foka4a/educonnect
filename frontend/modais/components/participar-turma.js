/* <edu-modal-participar-turma> · Modal "Participar da turma" (mapa de telas, Início).
   Independente do edu-modal.js e do Bootstrap: usa <dialog> nativo e as classes do style/layout.css
   (.dialogo, .dialogo__campo, .botao-texto) + modais/css/participar-turma.css.

   Uso na página:
     <link rel="stylesheet" href="../modais/css/participar-turma.css">
     <script src="../modais/components/participar-turma.js" defer></script>
     <button type="button" data-abrir="modal-participar-turma">…</button>
     <edu-modal-participar-turma></edu-modal-participar-turma>

   Código: 5 a 8 letras/números, sem espaços; o erro aparece abaixo do campo.
   Participar fica desabilitado até digitar o código. Valide também no servidor.
   Atributo: action (destino do POST; sem ele, a própria página, com acao=participar-turma; ela abre o Mural da turma). */
(function () {
  'use strict';

  class EduModalParticiparTurma extends HTMLElement {
    connectedCallback() {
      if (this.dialogo) return;
      var action = this.getAttribute('action') || ''; // sem action: a própria página
      this.style.display = 'contents';
      this.innerHTML = `
<dialog class="dialogo participar-turma" id="modal-participar-turma" aria-labelledby="titulo-participar-turma">
  <form method="post" novalidate>
    <input type="hidden" name="acao" value="participar-turma">
    <h2 class="dialogo__titulo" id="titulo-participar-turma">Participar da turma</h2>
    <label class="dialogo__rotulo" for="participar-turma-codigo">Código da turma</label>
    <p id="participar-turma-instrucao">Peça o código ao professor e digite-o aqui.</p>
    <input class="dialogo__campo" id="participar-turma-codigo" name="codigo" maxlength="8" placeholder="Ex.: x7k2m9q"
           autocomplete="off" autocapitalize="none" spellcheck="false" required
           aria-describedby="participar-turma-instrucao participar-turma-erro">
    <p class="participar-turma__erro" id="participar-turma-erro" role="alert" hidden>Código inválido (5 a 8 letras ou números)</p>
    <footer class="dialogo__acoes">
      <button class="botao-texto" type="button" data-fechar>Cancelar</button>
      <button class="botao-texto" type="submit" disabled>Participar</button>
    </footer>
  </form>
</dialog>`;

      var dialogo = this.dialogo = this.querySelector('dialog');
      var form = dialogo.querySelector('form');
      var campo = form.elements.codigo;
      var erro = form.querySelector('#participar-turma-erro');
      var participar = form.querySelector('[type="submit"]');
      form.action = action;

      function valido() { return /^[A-Za-z0-9]{5,8}$/.test(campo.value); }
      function mostrarErro(sim) {
        erro.hidden = !sim;
        if (sim) campo.setAttribute('aria-invalid', 'true');
        else campo.removeAttribute('aria-invalid');
      }

      // Participar só habilita depois de digitar algo.
      campo.addEventListener('input', function () {
        participar.disabled = !campo.value.length;
        if (valido()) mostrarErro(false);
      });
      // Ao sair do campo, avisa se o formato está errado.
      campo.addEventListener('blur', function () {
        if (campo.value.length && !valido()) mostrarErro(true);
      });
      form.addEventListener('submit', function (evento) {
        if (!valido()) {
          evento.preventDefault();
          mostrarErro(true);
          campo.focus();
          return;
        }
      });

      // Cancelar fecha sem salvar; ao fechar (inclusive Esc ou clique no fundo), limpa o campo.
      form.querySelector('[data-fechar]').addEventListener('click', function () { dialogo.close(); });
      dialogo.addEventListener('close', function () {
        form.reset();
        participar.disabled = true;
        mostrarErro(false);
      });

      document.addEventListener('click', function (evento) {
        if (evento.target.closest('[data-abrir="modal-participar-turma"]')) {
          dialogo.showModal();
          campo.focus();
        }
      });
    }
  }

  customElements.define('edu-modal-participar-turma', EduModalParticiparTurma);
})();
