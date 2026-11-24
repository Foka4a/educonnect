/* <edu-modal-personalizar-aparencia> · "Personalizar aparência" (mapa de telas, Mural: lápis do cabeçalho). Somente professor.
   Foto do cabeçalho (JPG/PNG até 2 MB) ou uma cor; a última escolha vale. "Remover foto" volta para a cor escolhida.
   Independente do edu-modal.js e do Bootstrap: usa <dialog> nativo e as classes do style/layout.css
   + modais/css/personalizar-aparencia.css.

   Uso na página:
     <link rel="stylesheet" href="../modais/css/personalizar-aparencia.css">
     <script src="../modais/components/personalizar-aparencia.js" defer></script>
     <button type="button" data-abrir="modal-personalizar-aparencia">…</button>
     <edu-modal-personalizar-aparencia turma-id="3" cor="#e97a1b" foto="…/imagem-turma?id=3"></edu-modal-personalizar-aparencia>

   Atributos: turma-id, cor e foto (aparência atual, vinda do servidor) e action (sem ele, a própria página, com acao=personalizar-aparencia).
   Salvar posta para a própria página (campos imagem, cor e remover_foto), que confere o arquivo e volta para o Mural já com a
   aparência nova. Salvar só habilita quando algo mudou; Cancelar, Esc ou clique fora descartam. */
(function () {
  'use strict';

  var LIMITE = 2 * 1024 * 1024;
  var CORES = [
    ['', 'Padrão (azul)'],
    ['#3a86eb', 'Azul'],
    ['#e97a1b', 'Laranja'],
    ['#bdc1c6', 'Cinza'],
    ['#d3e3fd', 'Azul-claro']
  ];

  function esc(valor) {
    return String(valor).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  }

  // Foto ou cor viram o fundo; a classe aplica a sombra escura que mantém o texto branco legível.
  function aplicar(elemento, estado) {
    var fundo = estado.foto ? 'url("' + estado.foto + '") center / cover no-repeat' : estado.cor;
    elemento.classList.toggle('cabecalho-personalizado', Boolean(fundo));
    elemento.style.setProperty('--cabecalho-fundo', fundo || '');
  }

  class EduModalPersonalizarAparencia extends HTMLElement {
    connectedCallback() {
      if (this.dialogo) return;
      var self = this;
      var action = this.getAttribute('action') || ''; // sem action: a própria página
      var salvo = { cor: this.getAttribute('cor') || '', foto: this.getAttribute('foto') || '' };
      var rascunho = Object.assign({}, salvo);
      this.style.display = 'contents';
      this.innerHTML = `
<dialog class="dialogo aparencia" id="modal-personalizar-aparencia" aria-labelledby="titulo-aparencia">
  <form method="post" action="${esc(action)}" enctype="multipart/form-data">
    <input type="hidden" name="acao" value="personalizar-aparencia">
    <input type="hidden" name="turma_id" value="${esc(this.getAttribute('turma-id') || '')}">
    <input type="hidden" name="remover_foto" value="0">
    <h2 class="dialogo__titulo" id="titulo-aparencia">Personalizar aparência</h2>
    <div class="aparencia__previa" data-previa>
      <strong data-previa-nome></strong>
      <span>Prévia do cabeçalho</span>
    </div>

    <p class="aparencia__rotulo">Selecione a imagem do cabeçalho do mural</p>
    <div class="aparencia__fotos">
      <label class="aparencia__upload">
        <span class="material-symbols-outlined" aria-hidden="true">upload</span>
        Fazer upload da foto
        <input class="visualmente-oculto" type="file" name="imagem" accept="image/png,image/jpeg" aria-describedby="aparencia-ajuda aparencia-erro">
      </label>
      <button class="botao-texto" type="button" data-remover-foto hidden>Remover foto</button>
    </div>
    <p class="aparencia__ajuda" id="aparencia-ajuda">JPG ou PNG, até 2 MB</p>
    <p class="aparencia__erro" id="aparencia-erro" role="alert" data-erro hidden>
      <span class="material-symbols-outlined" aria-hidden="true">warning</span>
      <span data-erro-texto>Arquivo inválido. Use JPG ou PNG de até 2 MB.</span>
    </p>

    <fieldset class="aparencia__cores">
      <legend class="aparencia__rotulo">Cor do cabeçalho</legend>
      ${CORES.map(function (cor) {
        return `<label class="aparencia__cor" style="--cor: ${cor[0] || 'linear-gradient(135deg, #155b93, #1769aa)'}">
          <input class="visualmente-oculto" type="radio" name="cor" value="${cor[0]}" aria-label="${cor[1]}">
        </label>`;
      }).join('')}
    </fieldset>

    <div class="dialogo__acoes">
      <button class="botao-texto" type="button" data-cancelar>Cancelar</button>
      <button class="botao-texto" type="submit" disabled data-salvar>Salvar</button>
    </div>
  </form>
</dialog>`;

      this.dialogo = this.querySelector('dialog');
      var form = this.querySelector('form');
      var previa = this.querySelector('[data-previa]');
      var arquivo = form.elements.imagem;
      var removerFoto = this.querySelector('[data-remover-foto]');
      var erro = this.querySelector('[data-erro]');
      var salvar = this.querySelector('[data-salvar]');

      function mostrarErro(texto) {
        self.querySelector('[data-erro-texto]').textContent = texto;
        erro.hidden = false;
      }

      function atualizar() {
        aplicar(previa, rascunho);
        removerFoto.hidden = !rascunho.foto;
        salvar.disabled = rascunho.cor === salvo.cor && rascunho.foto === salvo.foto;
        // Com foto, nenhuma cor fica marcada (a foto é que vale) e a cor não é enviada.
        form.querySelectorAll('[name="cor"]').forEach(function (radio) {
          radio.checked = !rascunho.foto && radio.value === rascunho.cor;
        });
      }

      document.addEventListener('click', function (evento) {
        if (!evento.target.closest('[data-abrir="modal-personalizar-aparencia"]')) return;
        rascunho = Object.assign({}, salvo);
        arquivo.value = '';
        erro.hidden = true;
        var titulo = document.querySelector('#titulo-disciplina');
        self.querySelector('[data-previa-nome]').textContent = titulo ? titulo.textContent : '';
        atualizar();
        self.dialogo.showModal();
      });

      this.querySelector('[data-cancelar]').addEventListener('click', function () { self.dialogo.close(); });

      arquivo.addEventListener('change', function () {
        var foto = arquivo.files[0];
        erro.hidden = true;
        if (!foto) return;
        if (!/^image\/(jpeg|png)$/.test(foto.type) || foto.size > LIMITE) {
          arquivo.value = '';
          mostrarErro('Arquivo inválido. Use JPG ou PNG de até 2 MB.');
          return;
        }
        // Só para a prévia; o arquivo vai no próprio formulário.
        var leitor = new FileReader();
        leitor.addEventListener('load', function () {
          rascunho.foto = leitor.result;
          atualizar();
        });
        leitor.readAsDataURL(foto);
      });

      form.addEventListener('change', function (evento) {
        if (evento.target.name !== 'cor') return;
        rascunho = { cor: evento.target.value, foto: '' };
        arquivo.value = '';
        atualizar();
      });

      removerFoto.addEventListener('click', function () {
        rascunho.foto = '';
        arquivo.value = '';
        atualizar();
        var cor = form.querySelector('[name="cor"]:checked') || form.querySelector('[name="cor"]');
        cor.checked = true;
        cor.focus();
      });

      // Tinha foto e ela saiu (removida ou trocada por cor): o servidor apaga a foto.
      form.addEventListener('submit', function () {
        form.elements.remover_foto.value = salvo.foto && !rascunho.foto ? '1' : '0';
      });
    }
  }

  customElements.define('edu-modal-personalizar-aparencia', EduModalPersonalizarAparencia);
})();
