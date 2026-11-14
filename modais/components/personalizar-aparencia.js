/* <edu-modal-personalizar-aparencia> · Modal "Personalizar aparência" (mapa de telas, Mural). Abrir: data-bs-target="#modal-personalizar-aparencia"
   Só upload de foto (a galeria de temas e cores foi removida). O envio usa input type="file" accept="image/*".
   A prévia é atualizada ao escolher; Salvar só habilita depois de escolher a foto e aplica no banner do Mural.
   Cancelar fecha sem alterar. Aceita JPG ou PNG de até 2 MB (valide também no servidor).
   Atributos: action (destino do POST, multipart), imagem-atual (URL da imagem do cabeçalho atual) */
(function () {
  'use strict';
  var LIMITE = 2 * 1024 * 1024;

  class EduModalPersonalizarAparencia extends EduModal {
    render() {
      return `
<div class="modal fade" id="modal-personalizar-aparencia" tabindex="-1" aria-labelledby="titulo-personalizar-aparencia" aria-hidden="true">
  <div class="modal-dialog dialogo-medio modal-dialog-centered modal-dialog-scrollable">
    <form class="modal-content" id="form-personalizar-aparencia" ${this.formAttrs()} enctype="multipart/form-data" novalidate>
      <header class="modal-header">
        <h2 class="modal-title" id="titulo-personalizar-aparencia">Personalizar aparência</h2>
      </header>
      <section class="modal-body">
        <div class="previa-cabecalho" id="aparencia-previa">
          <div class="vazio">${this.icone('imagem')}<span>Prévia do cabeçalho</span></div>
        </div>
        <p class="rotulo-forte" id="aparencia-rotulo">Selecione a imagem do cabeçalho do mural</p>
        <button type="button" class="btn btn-contorno" id="aparencia-escolher" aria-describedby="aparencia-ajuda aparencia-erro">${this.icone('upload')}Fazer upload da foto</button>
        <input type="file" name="imagem" id="aparencia-arquivo" accept="image/*" hidden>
        <p class="form-text mt-2 mb-0" id="aparencia-ajuda">JPG ou PNG, até 2 MB</p>
        <p class="msg-erro" id="aparencia-erro" role="alert">${this.icone('alerta')}<span>Arquivo inválido. Use JPG ou PNG de até 2 MB.</span></p>
      </section>
      <footer class="modal-footer">
        <button type="button" class="btn btn-link-edu" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-link-edu" id="aparencia-salvar" disabled>Salvar</button>
      </footer>
    </form>
  </div>
</div>`;
    }

    depoisDeRenderizar() {
      var modal = this.querySelector('.modal');
      var previa = this.querySelector('#aparencia-previa');
      var escolher = this.querySelector('#aparencia-escolher');
      var arquivo = this.querySelector('#aparencia-arquivo');
      var erro = this.querySelector('#aparencia-erro');
      var salvar = this.querySelector('#aparencia-salvar');
      var atual = this.getAttribute('imagem-atual');
      var urlTemporaria = null;
      var self = this;

      function mostrarImagem(src) {
        var img = previa.querySelector('img');
        if (!img) {
          img = document.createElement('img');
          img.alt = 'Prévia do cabeçalho do mural';
          previa.appendChild(img);
        }
        img.src = src;
        previa.classList.add('com-imagem');
      }
      function limparPrevia() {
        if (urlTemporaria) { URL.revokeObjectURL(urlTemporaria); urlTemporaria = null; }
        var img = previa.querySelector('img');
        if (img) img.remove();
        previa.classList.remove('com-imagem');
        if (atual) mostrarImagem(atual);
      }
      function falhar() {
        arquivo.value = '';
        salvar.disabled = true;
        erro.classList.add('visivel');
      }

      limparPrevia();
      escolher.addEventListener('click', function () { arquivo.click(); });

      arquivo.addEventListener('change', function () {
        var f = arquivo.files[0];
        erro.classList.remove('visivel');
        if (!f) { salvar.disabled = true; limparPrevia(); return; }
        if (!/^image\/(jpeg|png)$/.test(f.type) || f.size > LIMITE) { limparPrevia(); falhar(); return; }
        if (urlTemporaria) URL.revokeObjectURL(urlTemporaria);
        urlTemporaria = URL.createObjectURL(f);
        mostrarImagem(urlTemporaria);
        salvar.disabled = false;
      });

      // Cancelar fecha sem alterar nada
      modal.addEventListener('hidden.bs.modal', function () {
        arquivo.value = '';
        salvar.disabled = true;
        erro.classList.remove('visivel');
        limparPrevia();
      });
    }
  }
  customElements.define('edu-modal-personalizar-aparencia', EduModalPersonalizarAparencia);
})();
