/* EduConnect · classe base dos componentes de modal (Web Components, sem PHP e sem build).
   Carregue este arquivo ANTES dos componentes, no fim do <body>, depois do bootstrap.bundle.js.
   Os componentes usam o DOM normal (sem shadow DOM), então o Bootstrap e o CSS do tema funcionam neles.

   Uso na página:
     <edu-modal-criar-turma></edu-modal-criar-turma>
     <button data-bs-toggle="modal" data-bs-target="#modal-criar-turma">Criar turma</button>

   Atributos aceitos por todos:  action="url"  (destino do formulário, método POST) */
(function () {
  'use strict';

  class EduModal extends HTMLElement {
    connectedCallback() {
      if (this._pronto) return;
      this._pronto = true;
      this.style.display = 'contents';
      this.innerHTML = this.render();
    }
    /* lê um atributo, com valor padrão */
    attr(nome, padrao) {
      var v = this.getAttribute(nome);
      return v === null ? (padrao === undefined ? '' : padrao) : v;
    }
    /* escapa texto vindo de atributos antes de colocar no HTML */
    esc(valor) {
      return String(valor).replace(/[&<>"']/g, function (c) {
        return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
      });
    }
    /* atributos do <form>: method="post" e, se houver, o action */
    formAttrs() {
      var a = this.getAttribute('action');
      return 'method="post"' + (a ? ' action="' + this.esc(a) + '"' : '');
    }
    render() { return ''; }
  }

  window.EduModal = EduModal;
})();
