/* <edu-modal-configuracoes-turma> · "Configurações da turma" (mapa de telas, tela 4 · engrenagem das abas). Somente professor.
   Independente do edu-modal.js e do Bootstrap: usa <dialog> nativo e as classes do style/layout.css
   (.dialogo, .dialogo__campo, .botao-icone, .aviso) + modais/css/configuracoes-turma.css.

   Uso na página:
     <link rel="stylesheet" href="../modais/css/configuracoes-turma.css">
     <script src="../modais/components/configuracoes-turma.js" defer></script>
     <button type="button" data-abrir="modal-configuracoes-turma">…</button>
     <edu-modal-configuracoes-turma turma-id="3" nome="Matemática" materia="Matemática" pode-editar></edu-modal-configuracoes-turma>

   Atributos: turma-id, nome, descricao, secao, niveis, materia, sala (valores atuais da turma, vindos do servidor),
   codigo (código de convite), pode-editar (professor) e action (sem ele, a própria página, com acao=editar-turma).
   Também traz "Informações da turma": <button data-abrir="modal-informacoes-turma"> mostra os mesmos dados só para leitura.
   Salvar só habilita quando algo mudou e posta para a própria página, que volta para a página com "Turma atualizada.";
   × com alterações pergunta antes de descartar. */
(function () {
  'use strict';

  var CAMPOS = [
    ['nome', 'Nome da turma *'],
    ['descricao', 'Descrição da turma'],
    ['secao', 'Seção'],
    ['niveis', 'Níveis'],
    ['materia', 'Matéria'],
    ['sala', 'Sala']
  ];

  function esc(valor) {
    return String(valor).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  }

  class EduModalConfiguracoesTurma extends HTMLElement {
    connectedCallback() {
      if (this.dialogo) return;
      var self = this;
      var action = this.getAttribute('action') || ''; // sem action: a própria página
      var podeEditar = this.hasAttribute('pode-editar');
      this.style.display = 'contents';
      this.innerHTML = `
<dialog class="dialogo config-turma" id="modal-configuracoes-turma" aria-labelledby="titulo-config-turma">
  <form class="config-turma__form" method="post" action="${esc(action)}">
    <input type="hidden" name="acao" value="editar-turma">
    <input type="hidden" name="turma_id" value="${esc(this.getAttribute('turma-id') || '')}">
    <header class="config-turma__cabecalho">
      <button class="botao-icone" type="button" data-fechar aria-label="Fechar e voltar">
        <span class="material-symbols-outlined" aria-hidden="true">close</span>
      </button>
      <h2 id="titulo-config-turma">Configurações<span class="config-turma__longo"> da turma</span></h2>
      <button class="config-turma__salvar" type="submit" disabled>Salvar</button>
    </header>
    <section class="config-turma__cartao" aria-labelledby="titulo-detalhes-turma">
      <h3 id="titulo-detalhes-turma">Detalhes da turma</h3>
      ${CAMPOS.map(function (campo) {
        var id = 'config-turma-' + campo[0];
        var valor = esc(self.getAttribute(campo[0]) || '');
        var entrada = campo[0] === 'descricao'
          ? `<textarea class="dialogo__campo" id="${id}" name="descricao" rows="3" maxlength="1000">${valor}</textarea>`
          : `<input class="dialogo__campo" id="${id}" name="${campo[0]}" value="${valor}" maxlength="120" autocomplete="off"${campo[0] === 'nome' ? ' required pattern=".*\\S.*"' : ''}>`;
        return `<label class="dialogo__rotulo" for="${id}">${campo[1]}</label>${entrada}`;
      }).join('')}
    </section>
  </form>
</dialog>

<dialog class="dialogo info-turma" id="modal-informacoes-turma" aria-labelledby="titulo-info-turma">
  <form method="dialog">
    <h2 class="dialogo__titulo" id="titulo-info-turma">Informações da turma</h2>
    <dl class="info-turma__lista" data-info></dl>
    <div class="dialogo__acoes">
      ${podeEditar ? '<button class="botao-texto" type="button" data-editar-turma>Editar</button>' : ''}
      <button class="botao-texto" type="submit">Fechar</button>
    </div>
  </form>
</dialog>`;

      this.dialogo = this.querySelector('dialog');
      var form = this.querySelector('form');
      var salvar = this.querySelector('.config-turma__salvar');
      var campos = Array.from(form.elements).filter(function (el) { return el.name && el.type !== 'hidden'; });
      var salvos = campos.map(function (c) { return c.value; });

      function mudou() { return campos.some(function (c, i) { return c.value !== salvos[i]; }); }

      // × e Esc: com alterações não salvas, pergunta antes de descartar.
      function fechar(evento) {
        if (mudou() && !confirm('Descartar as alterações não salvas?')) {
          if (evento) evento.preventDefault();
          return;
        }
        campos.forEach(function (c, i) { c.value = salvos[i]; });
        salvar.disabled = true;
        self.dialogo.close();
      }

      form.addEventListener('input', function () { salvar.disabled = !mudou(); });
      this.querySelector('[data-fechar]').addEventListener('click', function () { fechar(); });
      this.dialogo.addEventListener('cancel', function (evento) { evento.preventDefault(); fechar(evento); });

      // Tira espaços das pontas e segue o envio normal do formulário.
      form.addEventListener('submit', function () {
        campos.forEach(function (c) { c.value = c.value.trim(); });
      });

      // "Informações da turma" (botão ⓘ do Mural): os mesmos dados salvos, só para leitura.
      var info = this.querySelector('#modal-informacoes-turma');
      var lista = info.querySelector('[data-info]');

      function mostrarInformacoes() {
        var linhas = CAMPOS.map(function (campo, i) { return [campo[1].replace(' *', ''), salvos[i]]; });
        if (self.getAttribute('codigo')) linhas.push(['Código da turma', self.getAttribute('codigo')]);
        lista.replaceChildren();
        linhas.forEach(function (linha) {
          var item = document.createElement('div');
          var termo = document.createElement('dt');
          var valor = document.createElement('dd');
          termo.textContent = linha[0];
          valor.textContent = linha[1] || 'Não informado';
          valor.classList.toggle('info-turma__vazio', !linha[1]);
          item.append(termo, valor);
          lista.append(item);
        });
        info.showModal();
      }

      var editar = info.querySelector('[data-editar-turma]');
      if (editar) editar.addEventListener('click', function () {
        info.close();
        self.dialogo.showModal();
      });

      document.addEventListener('click', function (evento) {
        if (podeEditar && evento.target.closest('[data-abrir="modal-configuracoes-turma"]')) self.dialogo.showModal();
        if (evento.target.closest('[data-abrir="modal-informacoes-turma"]')) mostrarInformacoes();
      });
    }
  }

  customElements.define('edu-modal-configuracoes-turma', EduModalConfiguracoesTurma);
})();
