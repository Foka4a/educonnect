/* <edu-modal-promover-professor> · EXTRA (não está nos wireframes): "Promover a professor", aba Pessoas.
   Regra de negócio: quem entra na turma é estudante; o professor pode promovê-lo a professor.
   Independente do edu-modal.js e do Bootstrap: usa <dialog> nativo e as classes do style/layout.css
   (.dialogo, .botao-texto) + modais/css/promover-professor.css.

   Uso na página:
     <link rel="stylesheet" href="../modais/css/promover-professor.css">
     <script src="../modais/components/promover-professor.js" defer></script>
     <li class="pessoa" data-nome="Ana Souza"> … <button type="button" data-promover>Promover a professor</button> … </li>
     <edu-modal-promover-professor></edu-modal-promover-professor>

   O botão [data-promover] abre o modal com o nome do .pessoa[data-nome] mais próximo.
   Atributo: action (destino do POST, envia o campo "pessoa_nome").
   Sem action, não há back-end: ao confirmar, dispara o evento "promover-professor" no document,
   com detail = { pessoa: <li>, nome }, e a página atualiza a tela. */
(function () {
  'use strict';

  class EduModalPromoverProfessor extends HTMLElement {
    connectedCallback() {
      if (this.dialogo) return;
      var action = this.getAttribute('action');
      this.style.display = 'contents';
      this.innerHTML = `
<dialog class="dialogo promover-professor" id="modal-promover-professor" aria-labelledby="titulo-promover-professor" aria-describedby="descricao-promover-professor">
  <form method="post">
    <input type="hidden" name="pessoa_nome">
    <h2 class="dialogo__titulo" id="titulo-promover-professor">Promover a professor?</h2>
    <div id="descricao-promover-professor">
      <p class="promover-professor__pessoa">
        <span class="promover-professor__avatar" aria-hidden="true"></span>
        <strong data-promover-nome></strong>
      </p>
      <p>Professores podem criar atividades, corrigir entregas e convidar estudantes.</p>
    </div>
    <footer class="dialogo__acoes">
      <button class="botao-texto" type="button" data-fechar>Cancelar</button>
      <button class="botao-texto" type="submit">Promover</button>
    </footer>
  </form>
</dialog>`;

      var dialogo = this.dialogo = this.querySelector('dialog');
      var form = dialogo.querySelector('form');
      var pessoa = null;
      if (action) form.action = action;

      document.addEventListener('click', function (evento) {
        var botao = evento.target.closest('[data-promover]');
        if (!botao) return;
        pessoa = botao.closest('.pessoa');
        var nome = pessoa.dataset.nome;
        form.elements.pessoa_nome.value = nome;
        dialogo.querySelector('[data-promover-nome]').textContent = nome;
        dialogo.querySelector('.promover-professor__avatar').textContent = nome.charAt(0).toUpperCase();
        var menu = botao.closest('.menu-suspenso');
        if (menu) menu.open = false;
        dialogo.showModal();
      });

      form.querySelector('[data-fechar]').addEventListener('click', function () { dialogo.close(); });

      // ponytail: sem back-end, só avisa a página; com action, o POST segue normalmente.
      form.addEventListener('submit', function (evento) {
        if (action) return;
        evento.preventDefault();
        var detalhe = { pessoa: pessoa, nome: form.elements.pessoa_nome.value };
        dialogo.close();
        document.dispatchEvent(new CustomEvent('promover-professor', { detail: detalhe }));
      });

      dialogo.addEventListener('close', function () { pessoa = null; });
    }
  }

  customElements.define('edu-modal-promover-professor', EduModalPromoverProfessor);
})();
