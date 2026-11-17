/* <edu-modal-participar-turma> · Modal "Participar da turma" (mapa de telas, Início). Abrir: data-bs-target="#modal-participar-turma"
   Campo código: 5 a 8 letras/números, sem espaços; o erro aparece abaixo do campo.
   Participar fica desabilitado até digitar o código; ao confirmar, o aluno entra na turma e vai para o Mural.
   Valide também no servidor. Atributo: action (destino do POST) */
/* <edu-modal-participar-turma> · Participar da turma (aluno). Abrir: data-bs-target="#modal-participar-turma"
   Atributos: action
   A validação de 5 a 8 letras/números está em js/modais.js; valide também no servidor. */
(function () {
  'use strict';
  class EduModalParticiparTurma extends EduModal {
    render() {
      return `
<div class="modal fade" id="modal-participar-turma" tabindex="-1" aria-labelledby="titulo-participar-turma" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form class="modal-content" id="form-participar-turma" ${this.formAttrs()} novalidate>
      <header class="modal-header">
        <h2 class="modal-title" id="titulo-participar-turma">Participar da turma</h2>
      </header>
      <section class="modal-body">
        <div class="painel-codigo">
          <h3 id="participar-turma-rotulo">Código da turma</h3>
          <p id="participar-turma-instrucao">Peça o código ao professor e digite-o aqui.</p>
          <input class="form-control" id="participar-turma-codigo" name="codigo" placeholder="Código da turma" autocomplete="off" autocapitalize="none" spellcheck="false"
                 aria-labelledby="participar-turma-rotulo" aria-describedby="participar-turma-instrucao participar-turma-erro">
          <p class="msg-erro" id="participar-turma-erro" role="alert">${this.icone('alerta')}<span>Código inválido (5 a 8 letras ou números)</span></p>
        </div>
      </section>
      <footer class="modal-footer">
        <button type="button" class="btn btn-link-edu" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-link-edu" id="participar-turma-ok" disabled>Participar</button>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </header>
      <section class="modal-body">
        <p>Peça o código ao professor e digite abaixo. Você entra como aluno.</p>
        <label class="form-label" for="participar-turma-codigo">Código da turma</label>
        <input class="form-control" id="participar-turma-codigo" name="codigo" maxlength="8" placeholder="Ex.: x7k2m9q" autocomplete="off" aria-describedby="participar-turma-erro" required>
        <div class="invalid-feedback" id="participar-turma-erro">Código inválido. Use de 5 a 8 letras ou números, sem espaços.</div>
      </section>
      <footer class="modal-footer">
        <button type="button" class="btn btn-link-edu" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-primary">Participar</button>
      </footer>
    </form>
  </div>
</div>`;
    }

    depoisDeRenderizar() {
      var modal = this.querySelector('.modal');
      var form = this.querySelector('form');
      var campo = this.querySelector('#participar-turma-codigo');
      var erro = this.querySelector('#participar-turma-erro');
      var ok = this.querySelector('#participar-turma-ok');
      var valido = function () { return /^[A-Za-z0-9]{5,8}$/.test(campo.value); };
      var mostrarErro = function (sim) {
        erro.classList.toggle('visivel', sim);
        campo.setAttribute('aria-invalid', sim ? 'true' : 'false');
      };

      // Participar só habilita depois de digitar algo
      campo.addEventListener('input', function () {
        ok.disabled = !campo.value.length;
        if (valido()) mostrarErro(false);
      });
      // ao sair do campo, avisa se o formato está errado
      campo.addEventListener('blur', function () {
        if (campo.value.length && !valido()) mostrarErro(true);
      });
      form.addEventListener('submit', function (e) {
        if (!valido()) { e.preventDefault(); mostrarErro(true); campo.focus(); }
      });

      modal.addEventListener('hidden.bs.modal', function () {
        form.reset();
        ok.disabled = true;
        mostrarErro(false);
      });
      modal.addEventListener('shown.bs.modal', function () { campo.focus(); });
    }
  }
  customElements.define('edu-modal-participar-turma', EduModalParticiparTurma);
})();
