/* EduConnect · classe base dos componentes de modal (Web Components, sem PHP e sem build).
   Carregue este arquivo ANTES dos componentes, no fim do <body>, depois do bootstrap.bundle.js.
   Os componentes usam o DOM normal (sem shadow DOM), então o Bootstrap e o CSS do tema funcionam neles.

   Uso na página:
     <edu-modal-criar-turma></edu-modal-criar-turma>
     <button data-bs-toggle="modal" data-bs-target="#modal-criar-turma">Criar turma</button>

   Atributo aceito pelos componentes com formulário:  action="url"  (destino, método POST) */
(function () {
  'use strict';

  /* ícones SVG de traço (decorativos: sempre com aria-hidden) */
  var ICONES = {
    fechar: '<path d="M6 6l12 12M18 6L6 18"/>',
    enviar: '<path d="M4 12l16-8-6 16-3-7z"/>',
    upload: '<path d="M12 16V5M7 10l5-5 5 5M5 19h14"/>',
    imagem: '<rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="9" cy="10" r="1.5"/><path d="M21 16l-5-5-8 8"/>',
    arquivo: '<path d="M7 3h7l5 5v13H7z"/><path d="M14 3v5h5"/>',
    pessoa: '<circle cx="12" cy="8" r="3.5"/><path d="M5 20c0-4 3-6 7-6s7 2 7 6"/>',
    alerta: '<path d="M12 4l9 16H3z"/><path d="M12 10v4M12 17v.5"/>'
  };

   Atributos aceitos por todos:  action="url"  (destino do formulário, método POST) */
(function () {
  'use strict';

  class EduModal extends HTMLElement {
    connectedCallback() {
      if (this._pronto) return;
      this._pronto = true;
      this.style.display = 'contents';
      this.innerHTML = this.render();
      this.depoisDeRenderizar();
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
    /* <svg> decorativo */
    icone(nome) {
      return '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">' + ICONES[nome] + '</svg>';
    }
    /* iniciais de um nome: "Aluno Exemplo" -> "AE" */
    iniciais(nome) {
      return nome.trim().split(/\s+/).slice(0, 2).map(function (p) { return p.charAt(0).toUpperCase(); }).join('');
    }
    render() { return ''; }
    /* gancho para ligar eventos do próprio componente depois de montar o HTML */
    depoisDeRenderizar() {}
    render() { return ''; }
  }

  window.EduModal = EduModal;
})();
