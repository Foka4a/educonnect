/* <edu-modal-convidar-alunos> · EXTRA (não está nos wireframes): convites e código da turma (menu ⋮ do código: copiar / redefinir).
   Abrir: data-bs-target="#modal-convidar-alunos"
   Atributos: action (destino do POST), turma-id, codigo
   Campo oculto "emails" recebe os e-mails separados por vírgula.
   Evento: "edu:redefinir-codigo" detail {turmaId}. Depois de redefinir no servidor, chame
           document.querySelector('edu-modal-convidar-alunos').definirCodigo('novo-codigo'). */
(function () {
  'use strict';
  var EMAIL = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

  class EduModalConvidarAlunos extends EduModal {
    render() {
      return `
<div class="modal fade" id="modal-convidar-alunos" tabindex="-1" aria-labelledby="titulo-convidar-alunos" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
    <form class="modal-content" id="form-convidar-alunos" ${this.formAttrs()} novalidate>
      <input type="hidden" name="turma_id" value="${this.esc(this.attr('turma-id', ''))}">
      <input type="hidden" name="emails" id="convidar-emails-valor">
      <header class="modal-header">
        <h2 class="modal-title" id="titulo-convidar-alunos">Convidar alunos</h2>
      </header>
      <section class="modal-body">
        <div class="painel-codigo mb-3">
          <h3 id="convidar-codigo-rotulo">Código da turma</h3>
          <p class="mb-2">Quem tiver o código entra como aluno.</p>
          <div class="codigo-linha">
            <output class="codigo-valor" id="convidar-codigo" aria-labelledby="convidar-codigo-rotulo">${this.esc(this.attr('codigo', 't7hz2z4m'))}</output>
            <button type="button" class="btn btn-contorno" id="convidar-copiar">Copiar código</button>
            <button type="button" class="btn btn-link-edu" id="convidar-redefinir">Redefinir</button>
          </div>
          <p class="form-text mb-0 mt-2" id="convidar-codigo-msg" role="status"></p>
        </div>
        <label class="form-label fw-bold" for="convidar-email">Convidar por e-mail</label>
        <input class="form-control" id="convidar-email" type="email" placeholder="nome@escola.com" autocomplete="off" aria-describedby="convidar-email-ajuda convidar-email-erro">
        <p class="form-text mb-0 mt-1" id="convidar-email-ajuda">Pressione Enter para adicionar.</p>
        <p class="msg-erro" id="convidar-email-erro" role="alert">${this.icone('alerta')}<span id="convidar-email-erro-texto"></span></p>
        <ul class="chips-email" id="convidar-chips" aria-label="E-mails a convidar"></ul>
      </section>
      <footer class="modal-footer">
        <button type="button" class="btn btn-link-edu" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-link-edu">Convidar</button>
      </footer>
    </form>
  </div>
</div>`;
    }

    definirCodigo(novo) { this.querySelector('#convidar-codigo').textContent = novo; }

    depoisDeRenderizar() {
      var self = this;
      var modal = this.querySelector('.modal');
      var form = this.querySelector('form');
      var campo = this.querySelector('#convidar-email');
      var chips = this.querySelector('#convidar-chips');
      var valor = this.querySelector('#convidar-emails-valor');
      var erro = this.querySelector('#convidar-email-erro');
      var erroTexto = this.querySelector('#convidar-email-erro-texto');
      var msgCodigo = this.querySelector('#convidar-codigo-msg');
      var emails = [];

      function mostrarErro(t) { erroTexto.textContent = t || ''; erro.classList.toggle('visivel', !!t); }
      function sincronizar() { valor.value = emails.join(','); }
      function desenhar() {
        chips.innerHTML = '';
        emails.forEach(function (e) {
          var li = document.createElement('li');
          li.className = 'chip-email';
          li.append(document.createTextNode(e));
          var b = document.createElement('button');
          b.type = 'button';
          b.setAttribute('aria-label', 'Remover ' + e);
          b.textContent = '×';
          b.addEventListener('click', function () { emails = emails.filter(function (x) { return x !== e; }); sincronizar(); desenhar(); });
          li.append(b);
          chips.append(li);
        });
      }
      // devolve true se o campo ficou vazio ou o e-mail foi aceito
      function adicionar() {
        var v = campo.value.trim().toLowerCase();
        if (!v) return true;
        if (!EMAIL.test(v)) { mostrarErro('E-mail inválido. Confira e tente de novo.'); return false; }
        mostrarErro('');
        if (emails.indexOf(v) === -1) { emails.push(v); sincronizar(); desenhar(); }
        campo.value = '';
        return true;
      }

      campo.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' || e.key === ',') { e.preventDefault(); adicionar(); }
      });
      campo.addEventListener('input', function () { mostrarErro(''); });

      form.addEventListener('submit', function (e) {
        if (!adicionar()) { e.preventDefault(); campo.focus(); return; }
        if (!emails.length) { e.preventDefault(); mostrarErro('Adicione ao menos um e-mail.'); campo.focus(); }
      });

      // copiar o código
      this.querySelector('#convidar-copiar').addEventListener('click', function () {
        var codigo = self.querySelector('#convidar-codigo').textContent;
        var ok = function () { msgCodigo.textContent = 'Código copiado.'; };
        var falha = function () { msgCodigo.textContent = 'Não foi possível copiar. Selecione o código e copie manualmente.'; };
        if (navigator.clipboard && navigator.clipboard.writeText) navigator.clipboard.writeText(codigo).then(ok, falha);
        else falha();
      });
      // redefinir: avisa o back-end por evento
      this.querySelector('#convidar-redefinir').addEventListener('click', function () {
        self.dispatchEvent(new CustomEvent('edu:redefinir-codigo', { bubbles: true, detail: { turmaId: self.attr('turma-id', '') } }));
        msgCodigo.textContent = 'Pedido de novo código enviado.';
      });

      modal.addEventListener('hidden.bs.modal', function () {
        emails = []; sincronizar(); desenhar(); campo.value = ''; mostrarErro(''); msgCodigo.textContent = '';
      });
    }
  }
  customElements.define('edu-modal-convidar-alunos', EduModalConvidarAlunos);
})();
