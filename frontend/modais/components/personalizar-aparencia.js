/* <edu-modal-personalizar-aparencia> · "Personalizar aparência" (mapa de telas, Mural: lápis do cabeçalho). Somente professor.
   Foto do cabeçalho (JPG/PNG até 2 MB) ou uma cor; a última escolha vale. "Remover foto" volta para a cor escolhida.
   Independente do edu-modal.js e do Bootstrap: usa <dialog> nativo e as classes do style/layout.css
   + modais/css/personalizar-aparencia.css.

   Uso na página:
     <link rel="stylesheet" href="../modais/css/personalizar-aparencia.css">
     <script src="../modais/components/personalizar-aparencia.js" defer></script>
     <button type="button" data-abrir="modal-personalizar-aparencia">…</button>
     <edu-modal-personalizar-aparencia></edu-modal-personalizar-aparencia>

   Aplica em .mural-cabecalho. Salvar só habilita quando algo mudou; Cancelar, Esc ou clique fora descartam. */
(function () {
  'use strict';

  var LIMITE = 2 * 1024 * 1024;
  var CHAVE = 'educonnect:aparencia';
  var CORES = [
    ['', 'Padrão (azul)'],
    ['#3a86eb', 'Azul'],
    ['#e97a1b', 'Laranja'],
    ['#bdc1c6', 'Cinza'],
    ['#d3e3fd', 'Azul-claro']
  ];

  // ponytail: sem back-end, a aparência fica no navegador (localStorage); trocar por upload no servidor quando ele existir.
  function lerSalvo() {
    try { return JSON.parse(localStorage.getItem(CHAVE)) || { cor: '', foto: '' }; } catch (erro) { return { cor: '', foto: '' }; }
  }

  function gravar(estado) {
    try { localStorage.setItem(CHAVE, JSON.stringify(estado)); return true; } catch (erro) { return false; }
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
      var salvo = lerSalvo();
      var rascunho = Object.assign({}, salvo);
      this.style.display = 'contents';
      this.innerHTML = `
<dialog class="dialogo aparencia" id="modal-personalizar-aparencia" aria-labelledby="titulo-aparencia">
  <form method="dialog">
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
        <input class="visualmente-oculto" type="file" name="foto" accept="image/png,image/jpeg" aria-describedby="aparencia-ajuda aparencia-erro">
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
      ${CORES.map(function (cor, i) {
        return `<label class="aparencia__cor" style="--cor: ${cor[0] || 'linear-gradient(135deg, #155b93, #1769aa)'}">
          <input class="visualmente-oculto" type="radio" name="cor" value="${cor[0]}" aria-label="${cor[1]}"${i === 0 ? ' checked' : ''}>
        </label>`;
      }).join('')}
    </fieldset>

    <div class="dialogo__acoes">
      <button class="botao-texto" type="submit" value="cancelar">Cancelar</button>
      <button class="botao-texto" type="submit" value="salvar" disabled data-salvar>Salvar</button>
    </div>
  </form>
</dialog>`;

      this.dialogo = this.querySelector('dialog');
      var form = this.querySelector('form');
      var previa = this.querySelector('[data-previa]');
      var arquivo = form.elements.foto;
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
        // Com foto, nenhuma cor fica marcada (a foto é que vale).
        form.querySelectorAll('[name="cor"]').forEach(function (radio) {
          radio.checked = !rascunho.foto && radio.value === rascunho.cor;
        });
      }

      // Ao carregar a página: aplica o que foi salvo no cabeçalho do Mural.
      var cabecalho = document.querySelector('.mural-cabecalho');
      if (cabecalho) aplicar(cabecalho, salvo);

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

      arquivo.addEventListener('change', function () {
        var foto = arquivo.files[0];
        erro.hidden = true;
        if (!foto) return;
        if (!/^image\/(jpeg|png)$/.test(foto.type) || foto.size > LIMITE) {
          arquivo.value = '';
          mostrarErro('Arquivo inválido. Use JPG ou PNG de até 2 MB.');
          return;
        }
        // Data URL (e não blob:) para a foto continuar valendo depois de recarregar a página.
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
        form.querySelector('[name="cor"]:checked').focus();
      });

      form.addEventListener('submit', function (evento) {
        if (evento.submitter.value !== 'salvar') return;
        if (!gravar(rascunho)) {
          // Navegador sem espaço/bloqueado: não fecha, avisa e deixa tentar outra foto.
          evento.preventDefault();
          mostrarErro('Não foi possível salvar a foto neste navegador. Tente uma imagem menor.');
          return;
        }
        salvo = Object.assign({}, rascunho);
        if (cabecalho) aplicar(cabecalho, salvo);
      });
    }
  }

  customElements.define('edu-modal-personalizar-aparencia', EduModalPersonalizarAparencia);
})();
