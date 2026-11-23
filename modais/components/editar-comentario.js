/* <edu-modal-editar-comentario> · EXTRA: editar o próprio comentário (público ou particular).
   Abrir: data-bs-target="#modal-editar-comentario"
   Atributos: action (destino do POST), comentario-id, texto (texto atual), particular (presente = comentário particular)
   Salvar só habilita quando o texto muda e não fica vazio. */
(function () {
  'use strict';
  class EduModalEditarComentario extends EduModal {
    render() {
      var particular = this.hasAttribute('particular');
      return `
<div class="modal fade" id="modal-editar-comentario" tabindex="-1" aria-labelledby="titulo-editar-comentario" aria-hidden="true">
  <div class="modal-dialog dialogo-medio modal-dialog-centered">
    <form class="modal-content" ${this.formAttrs('/editar-comentario')} novalidate>
      <input type="hidden" name="comentario_id" value="${this.esc(this.attr('comentario-id', ''))}">
      <header class="modal-header">
        <h2 class="modal-title" id="titulo-editar-comentario">Editar comentário</h2>
      </header>
      <section class="modal-body">
        ${particular ? '<p class="mb-2">Comentário particular: só você e o professor veem.</p>' : '<p class="mb-2">Comentário público: toda a turma vê.</p>'}
        <div class="form-floating">
          <textarea class="form-control campo-texto" id="editar-comentario-texto" name="texto" placeholder="Comentário" maxlength="1000" required aria-describedby="editar-comentario-erro"></textarea>
          <label for="editar-comentario-texto">Comentário *</label>
          <div class="invalid-feedback" id="editar-comentario-erro" role="alert">O comentário não pode ficar vazio.</div>
        </div>
      </section>
      <footer class="modal-footer">
        <button type="button" class="btn btn-link-edu" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-link-edu" id="editar-comentario-salvar" disabled>Salvar</button>
      </footer>
    </form>
  </div>
</div>`;
    }
    depoisDeRenderizar() {
      var modal = this.querySelector('.modal');
      var campo = this.querySelector('#editar-comentario-texto');
      var salvar = this.querySelector('#editar-comentario-salvar');
      var inicial = this.attr('texto', '');
      campo.value = inicial;
      function atualizar() {
        var v = campo.value.trim();
        campo.classList.toggle('is-invalid', v === '' && campo.value !== inicial);
        salvar.disabled = v === '' || v === inicial.trim();
      }
      campo.addEventListener('input', atualizar);
      modal.querySelector('form').addEventListener('submit', function (e) {
        if (!campo.value.trim()) { e.preventDefault(); campo.classList.add('is-invalid'); campo.focus(); }
      });
      modal.addEventListener('shown.bs.modal', function () { campo.focus(); });
      modal.addEventListener('hidden.bs.modal', function () { campo.value = inicial; campo.classList.remove('is-invalid'); salvar.disabled = true; });
    }
  }
  customElements.define('edu-modal-editar-comentario', EduModalEditarComentario);
})();
