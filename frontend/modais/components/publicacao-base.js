/* EduConnect · base dos formulários de Atividade e Material (criar e editar).
   Carregue DEPOIS de edu-modal.js e ANTES de criar/editar-atividade e criar/editar-material.

   Os quatro modais são o mesmo formulário com poucas diferenças:
     atividade: Título*, Instruções, Anexos, Data de entrega (opcional)
     material : Título*, Descrição, Anexos
   Anexos: só upload de arquivos (até 10 arquivos de 20 MB). Ao editar, os anexos atuais aparecem
   com uma caixa "Remover" (campo remover_anexos[]). Valide tamanho e tipo também no servidor.

   Atributos dos componentes: action, titulo, descricao, anexos-atuais (nomes separados por |),
     entrega (só atividade; formato 2026-11-20T23:59), atividade-id / material-id (só editar).
   Mobile (< 576 px): tela cheia com [×] título [Publicar/Salvar]. */
(function () {
  'use strict';
  var MAX_ARQUIVOS = 10;
  var MAX_BYTES = 20 * 1024 * 1024;

  function tamanho(b) { return b >= 1048576 ? (b / 1048576).toFixed(1).replace('.', ',') + ' MB' : Math.max(1, Math.round(b / 1024)) + ' KB'; }

  class EduModalPublicacao extends EduModal {
    /* os filhos definem: tipo ('atividade'|'material') e modo ('criar'|'editar') */
    get tipo() { return 'atividade'; }
    get modo() { return 'criar'; }

    render() {
      var tipo = this.tipo, modo = this.modo, ativ = tipo === 'atividade', edit = modo === 'editar';
      var id = 'modal-' + modo + '-' + tipo;
      var p = id;                                   // prefixo dos ids internos
      var nomeTipo = ativ ? 'atividade' : 'material';
      var titulo = (edit ? 'Editar ' : 'Criar ') + nomeTipo;
      var ok = edit ? 'Salvar' : (ativ ? 'Criar' : 'Publicar');
      var atuais = this.attr('anexos-atuais', '').split('|').filter(Boolean);
      var atuaisHtml = atuais.length ? `
        <ul class="anexos-lista" aria-label="Anexos atuais">${atuais.map((n, i) => `
          <li><span class="anexo-nome">${this.icone('arquivo')}<span>${this.esc(n)}</span></span>
            <span class="form-check m-0"><input class="form-check-input" type="checkbox" name="remover_anexos[]" value="${this.esc(n)}" id="${p}-rem-${i}"><label class="form-check-label" for="${p}-rem-${i}">Remover</label></span></li>`).join('')}
        </ul>` : '';
      return `
<div class="modal fade" id="${id}" tabindex="-1" aria-labelledby="titulo-${id}" aria-hidden="true">
  <div class="modal-dialog dialogo-largo modal-dialog-centered modal-dialog-scrollable modal-fullscreen-sm-down">
    <form class="modal-content" ${this.formAttrs()} enctype="multipart/form-data" novalidate>
      ${edit ? `<input type="hidden" name="${nomeTipo}_id" value="${this.esc(this.attr(nomeTipo + '-id', ''))}">` : ''}
      <header class="modal-header cab-criar">
        <button type="button" class="btn btn-redondo d-sm-none" data-bs-dismiss="modal" aria-label="Fechar sem salvar">${this.icone('fechar')}</button>
        <h2 class="modal-title" id="titulo-${id}">${titulo}</h2>
        <button type="submit" class="btn btn-primary rounded-pill d-sm-none">${ok}</button>
      </header>
      <section class="modal-body">
        <div class="form-floating">
          <input class="form-control" id="${p}-titulo" name="titulo" placeholder="Título" value="${this.esc(this.attr('titulo', ''))}" maxlength="120" autocomplete="off" required aria-describedby="${p}-ajuda">
          <label for="${p}-titulo">Título *</label>
          <div class="invalid-feedback" role="alert">Informe o título ${ativ ? 'da atividade' : 'do material'}.</div>
        </div>
        <p class="form-text mt-1 mb-3" id="${p}-ajuda">*Obrigatório</p>
        <div class="form-floating mb-3">
          <textarea class="form-control campo-texto" id="${p}-descricao" name="descricao" placeholder="${ativ ? 'Instruções' : 'Descrição'}" maxlength="5000"></textarea>
          <label for="${p}-descricao">${ativ ? 'Instruções (opcional)' : 'Descrição (opcional)'}</label>
        </div>
        ${ativ ? `
        <div class="form-floating mb-1">
          <input class="form-control" type="datetime-local" id="${p}-entrega" name="entrega" value="${this.esc(this.attr('entrega', ''))}" aria-describedby="${p}-entrega-ajuda">
          <label for="${p}-entrega">Data de entrega</label>
        </div>
        <p class="form-text mb-3" id="${p}-entrega-ajuda">Deixe em branco para uma atividade sem data de entrega. Entregas depois dessa data ficam como atrasadas.</p>` : ''}
        <p class="rotulo-forte" id="${p}-anexos-rotulo">Anexos</p>
        ${atuaisHtml}
        <button type="button" class="btn btn-contorno" id="${p}-escolher" aria-describedby="${p}-anexos-ajuda ${p}-anexos-erro">${this.icone('upload')}Adicionar arquivos</button>
        <input type="file" name="anexos[]" id="${p}-arquivos" multiple hidden>
        <p class="form-text mt-2 mb-0" id="${p}-anexos-ajuda">Até ${MAX_ARQUIVOS} arquivos de 20 MB cada.</p>
        <p class="msg-erro" id="${p}-anexos-erro" role="alert">${this.icone('alerta')}<span></span></p>
        <ul class="anexos-lista" id="${p}-novos" aria-label="Arquivos a enviar"></ul>
      </section>
      <footer class="modal-footer d-none d-sm-flex">
        <button type="button" class="btn btn-link-edu" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-link-edu">${ok}</button>
      </footer>
    </form>
  </div>
</div>`;
    }

    depoisDeRenderizar() {
      var p = 'modal-' + this.modo + '-' + this.tipo;
      var modal = this.querySelector('.modal');
      var form = this.querySelector('form');
      var titulo = this.querySelector('#' + p + '-titulo');
      var descricao = this.querySelector('#' + p + '-descricao');
      var escolher = this.querySelector('#' + p + '-escolher');
      var input = this.querySelector('#' + p + '-arquivos');
      var lista = this.querySelector('#' + p + '-novos');
      var erro = this.querySelector('#' + p + '-anexos-erro');
      var erroTexto = erro.querySelector('span');
      var escolhidos = [];
      var textoInicial = this.attr('descricao', '');
      descricao.value = textoInicial;

      function mostrarErro(t) { erroTexto.textContent = t || ''; erro.classList.toggle('visivel', !!t); }
      function sincronizar() {
        try {
          var dt = new DataTransfer();
          escolhidos.forEach(function (f) { dt.items.add(f); });
          input.files = dt.files;
        } catch (e) { /* navegador sem DataTransfer: a lista só serve de aviso */ }
      }
      function desenhar() {
        lista.innerHTML = '';
        escolhidos.forEach(function (f, i) {
          var li = document.createElement('li');
          var nome = document.createElement('span');
          nome.className = 'anexo-nome';
          nome.textContent = f.name + ' · ' + tamanho(f.size);
          var b = document.createElement('button');
          b.type = 'button';
          b.className = 'btn btn-link-edu btn-sm';
          b.textContent = 'Remover';
          b.setAttribute('aria-label', 'Remover ' + f.name);
          b.addEventListener('click', function () { escolhidos.splice(i, 1); sincronizar(); desenhar(); mostrarErro(''); });
          li.append(nome, b);
          lista.append(li);
        });
      }

      escolher.addEventListener('click', function () { input.click(); });
      input.addEventListener('change', function () {
        var novos = Array.prototype.slice.call(input.files);
        var msg = '';
        novos.forEach(function (f) {
          if (f.size > MAX_BYTES) msg = '“' + f.name + '” passa de 20 MB e não foi adicionado.';
          else if (escolhidos.length >= MAX_ARQUIVOS) msg = 'Máximo de ' + MAX_ARQUIVOS + ' arquivos por envio.';
          else if (!escolhidos.some(function (e) { return e.name === f.name && e.size === f.size; })) escolhidos.push(f);
        });
        sincronizar(); desenhar(); mostrarErro(msg);
      });

      titulo.addEventListener('input', function () { titulo.classList.remove('is-invalid'); });
      form.addEventListener('submit', function (e) {
        if (!titulo.value.trim()) { e.preventDefault(); titulo.classList.add('is-invalid'); titulo.focus(); }
      });
      modal.addEventListener('shown.bs.modal', function () { titulo.focus(); });
      modal.addEventListener('hidden.bs.modal', function () {
        form.reset();                      // volta aos valores iniciais (vazios no criar; os atuais no editar)
        descricao.value = textoInicial;
        escolhidos = []; sincronizar(); desenhar(); mostrarErro('');
        titulo.classList.remove('is-invalid');
      });
    }
  }
  window.EduModalPublicacao = EduModalPublicacao;
})();
