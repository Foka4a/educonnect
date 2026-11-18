/* <edu-modal-enviar-trabalho> · Entrega do trabalho pelo ALUNO (funcionalidade 12: anexos de entregas, apenas upload de arquivos).
   Abrir: data-bs-target="#modal-enviar-trabalho"
   Atributos: action (destino do POST, multipart), atividade-id, atividade-titulo,
     arquivos-atuais (nomes separados por |; presente = trabalho já entregue), atrasada (presente = prazo vencido)
   Entregar só habilita com ao menos um arquivo (ou, se já entregou, com alteração).
   Já entregue: o aluno pode enviar de novo (substitui) ou "Cancelar envio" (botão name="acao" value="cancelar").
   Até 5 arquivos de 20 MB. Valide tamanho e tipo também no servidor. */
(function () {
  'use strict';
  var MAX_ARQUIVOS = 5;
  var MAX_BYTES = 20 * 1024 * 1024;
  function tamanho(b) { return b >= 1048576 ? (b / 1048576).toFixed(1).replace('.', ',') + ' MB' : Math.max(1, Math.round(b / 1024)) + ' KB'; }

  class EduModalEnviarTrabalho extends EduModal {
    render() {
      var atuais = this.attr('arquivos-atuais', '').split('|').filter(Boolean);
      var entregue = atuais.length > 0;
      var atrasada = this.hasAttribute('atrasada');
      return `
<div class="modal fade" id="modal-enviar-trabalho" tabindex="-1" aria-labelledby="titulo-enviar-trabalho" aria-hidden="true">
  <div class="modal-dialog dialogo-medio modal-dialog-centered modal-dialog-scrollable">
    <form class="modal-content" ${this.formAttrs()} enctype="multipart/form-data" novalidate>
      <input type="hidden" name="atividade_id" value="${this.esc(this.attr('atividade-id', ''))}">
      <header class="modal-header">
        <h2 class="modal-title" id="titulo-enviar-trabalho">${entregue ? 'Seu trabalho' : 'Enviar trabalho'}</h2>
      </header>
      <section class="modal-body">
        <p class="mb-2"><strong style="color:var(--bs-body-color)">${this.esc(this.attr('atividade-titulo', 'Lista de exercícios 1'))}</strong></p>
        ${atrasada ? `<p class="aviso-atraso" role="note">${this.icone('alerta')}<span>O prazo desta atividade já passou. O envio será marcado como atrasado.</span></p>` : ''}
        ${entregue ? `<p class="rotulo-forte">Arquivos entregues</p>
        <ul class="anexos-lista" aria-label="Arquivos entregues">${atuais.map(n => `<li><span class="anexo-nome">${this.icone('arquivo')}<span>${this.esc(n)}</span></span></li>`).join('')}</ul>` : ''}
        <p class="rotulo-forte">${entregue ? 'Enviar de novo (substitui os arquivos acima)' : 'Seus arquivos'}</p>
        <button type="button" class="btn btn-contorno" id="enviar-escolher" aria-describedby="enviar-ajuda enviar-erro">${this.icone('upload')}Adicionar arquivos</button>
        <input type="file" name="arquivos[]" id="enviar-arquivos" multiple hidden>
        <p class="form-text mt-2 mb-0" id="enviar-ajuda">Até ${MAX_ARQUIVOS} arquivos de 20 MB cada.</p>
        <p class="msg-erro" id="enviar-erro" role="alert">${this.icone('alerta')}<span></span></p>
        <ul class="anexos-lista" id="enviar-novos" aria-label="Arquivos a enviar"></ul>
      </section>
      <footer class="modal-footer">
        ${entregue ? '<button type="submit" name="acao" value="cancelar" class="btn btn-link-edu btn-perigo me-auto" id="enviar-cancelar-envio" formnovalidate>Cancelar envio</button>' : ''}
        <button type="button" class="btn btn-link-edu" data-bs-dismiss="modal">Fechar</button>
        <button type="submit" name="acao" value="entregar" class="btn btn-link-edu" id="enviar-entregar" disabled>${entregue ? 'Reenviar' : 'Entregar'}</button>
      </footer>
    </form>
  </div>
</div>`;
    }

    depoisDeRenderizar() {
      var modal = this.querySelector('.modal');
      var escolher = this.querySelector('#enviar-escolher');
      var input = this.querySelector('#enviar-arquivos');
      var lista = this.querySelector('#enviar-novos');
      var entregar = this.querySelector('#enviar-entregar');
      var erro = this.querySelector('#enviar-erro');
      var erroTexto = erro.querySelector('span');
      var escolhidos = [];

      function mostrarErro(t) { erroTexto.textContent = t || ''; erro.classList.toggle('visivel', !!t); }
      function sincronizar() {
        try {
          var dt = new DataTransfer();
          escolhidos.forEach(function (f) { dt.items.add(f); });
          input.files = dt.files;
        } catch (e) { /* sem DataTransfer */ }
        entregar.disabled = escolhidos.length === 0;
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
        var msg = '';
        Array.prototype.slice.call(input.files).forEach(function (f) {
          if (f.size > MAX_BYTES) msg = '“' + f.name + '” passa de 20 MB e não foi adicionado.';
          else if (escolhidos.length >= MAX_ARQUIVOS) msg = 'Máximo de ' + MAX_ARQUIVOS + ' arquivos por envio.';
          else if (!escolhidos.some(function (e) { return e.name === f.name && e.size === f.size; })) escolhidos.push(f);
        });
        sincronizar(); desenhar(); mostrarErro(msg);
      });

      modal.querySelector('form').addEventListener('submit', function (e) {
        var cancelando = e.submitter && e.submitter.value === 'cancelar';
        if (!cancelando && !escolhidos.length) { e.preventDefault(); mostrarErro('Escolha ao menos um arquivo.'); }
      });
      modal.addEventListener('hidden.bs.modal', function () { escolhidos = []; sincronizar(); desenhar(); mostrarErro(''); });
    }
  }
  customElements.define('edu-modal-enviar-trabalho', EduModalEnviarTrabalho);
})();
