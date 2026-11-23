/* <edu-modal-configuracoes-turma> · "Configurações da turma" (mapa de telas, tela 4 · engrenagem das abas). Somente professor.
   Independente do edu-modal.js e do Bootstrap: usa <dialog> nativo e as classes do style/layout.css
   (.dialogo, .dialogo__campo, .botao-icone, .aviso) + modais/css/configuracoes-turma.css.

   Uso na página:
     <link rel="stylesheet" href="../modais/css/configuracoes-turma.css">
     <script src="../modais/components/configuracoes-turma.js" defer></script>
     <button type="button" data-abrir="modal-configuracoes-turma">…</button>
     <edu-modal-configuracoes-turma nome="Matemática" materia="Matemática"></edu-modal-configuracoes-turma>

   Atributos: nome, descricao, secao, niveis, materia, sala (valores atuais da turma) e codigo (código de convite).
   Também traz "Informações da turma": <button data-abrir="modal-informacoes-turma"> mostra os mesmos dados só para leitura.
   Salvar só habilita quando algo mudou, grava no navegador (vale em todas as páginas da turma) e mostra "Alterações salvas";
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

  // ponytail: sem back-end, a turma fica salva no navegador (localStorage) e vale para todas as páginas;
  // trocar por GET/POST no servidor quando ele existir.
  var CHAVE = 'educonnect:turma';

  function lerSalvos() {
    try { return JSON.parse(localStorage.getItem(CHAVE)) || {}; } catch (erro) { return {}; }
  }

  function gravar(dados) {
    try { localStorage.setItem(CHAVE, JSON.stringify(dados)); } catch (erro) { /* navegador bloqueou: vale só nesta tela */ }
  }

  // Nome da turma aparece na trilha do topo, no menu lateral e no título do Mural.
  function aplicarNaPagina(dados) {
    if (!dados.nome) return;
    document.querySelectorAll('.trilha__nome, #titulo-disciplina, .menu-grupo__itens .menu-link--ativo span:last-child')
      .forEach(function (el) { el.textContent = dados.nome; });
  }

  class EduModalConfiguracoesTurma extends HTMLElement {
    connectedCallback() {
      if (this.dialogo) return;
      var self = this;
      var dadosSalvos = lerSalvos();
      this.style.display = 'contents';
      this.innerHTML = `
<dialog class="dialogo config-turma" id="modal-configuracoes-turma" aria-labelledby="titulo-config-turma">
  <form class="config-turma__form">
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
        // O que foi salvo vale mais que o valor inicial escrito na tag.
        var valor = esc(campo[0] in dadosSalvos ? dadosSalvos[campo[0]] : (self.getAttribute(campo[0]) || ''));
        var entrada = campo[0] === 'descricao'
          ? `<textarea class="dialogo__campo" id="${id}" name="descricao" rows="3">${valor}</textarea>`
          : `<input class="dialogo__campo" id="${id}" name="${campo[0]}" value="${valor}" autocomplete="off"${campo[0] === 'nome' ? ' required pattern=".*\\S.*"' : ''}>`;
        return `<label class="dialogo__rotulo" for="${id}">${campo[1]}</label>${entrada}`;
      }).join('')}
    </section>
  </form>
  <p class="aviso" role="status" data-aviso-config></p>
</dialog>

<dialog class="dialogo info-turma" id="modal-informacoes-turma" aria-labelledby="titulo-info-turma">
  <form method="dialog">
    <h2 class="dialogo__titulo" id="titulo-info-turma">Informações da turma</h2>
    <dl class="info-turma__lista" data-info></dl>
    <div class="dialogo__acoes">
      <button class="botao-texto" type="button" data-editar-turma>Editar</button>
      <button class="botao-texto" type="submit">Fechar</button>
    </div>
  </form>
</dialog>`;

      this.dialogo = this.querySelector('dialog');
      var form = this.querySelector('form');
      var salvar = this.querySelector('.config-turma__salvar');
      var campos = Array.from(form.elements).filter(function (el) { return el.name; });
      var salvos = campos.map(function (c) { return c.value; });

      function mudou() { return campos.some(function (c, i) { return c.value !== salvos[i]; }); }

      aplicarNaPagina(dadosSalvos);

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

      form.addEventListener('submit', function (evento) {
        evento.preventDefault();
        var dados = {};
        campos.forEach(function (c) { dados[c.name] = c.value.trim(); c.value = dados[c.name]; });
        salvos = campos.map(function (c) { return c.value; });
        salvar.disabled = true;
        gravar(dados);
        aplicarNaPagina(dados);
        // O aviso fica dentro do <dialog>: fora dele ficaria escondido atrás do modal.
        var aviso = self.querySelector('[data-aviso-config]');
        aviso.textContent = 'Alterações salvas';
        aviso.classList.add('aviso--visivel');
        clearTimeout(self.timer);
        self.timer = setTimeout(function () { aviso.classList.remove('aviso--visivel'); }, 3000);
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

      info.querySelector('[data-editar-turma]').addEventListener('click', function () {
        info.close();
        self.dialogo.showModal();
      });

      document.addEventListener('click', function (evento) {
        if (evento.target.closest('[data-abrir="modal-configuracoes-turma"]')) self.dialogo.showModal();
        if (evento.target.closest('[data-abrir="modal-informacoes-turma"]')) mostrarInformacoes();
      });
    }
  }

  customElements.define('edu-modal-configuracoes-turma', EduModalConfiguracoesTurma);
})();
