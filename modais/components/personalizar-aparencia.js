/* <edu-modal-personalizar-aparencia> · Personalizar aparência (professor). Abrir: data-bs-target="#modal-personalizar-aparencia"
   Atributos: action, turma-nome, turma-secao */
(function () {
  'use strict';
  class EduModalPersonalizarAparencia extends EduModal {
    render() {
      var nome = this.esc(this.attr('turma-nome', 'Desenvolvimento Web I'));
      var secao = this.esc(this.attr('turma-secao', 'Turma A'));
      return `
<div class="modal fade" id="modal-personalizar-aparencia" tabindex="-1" aria-labelledby="titulo-personalizar-aparencia" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form class="modal-content" ${this.formAttrs()}>
      <header class="modal-header">
        <h2 class="modal-title" id="titulo-personalizar-aparencia">Personalizar aparência</h2>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </header>
      <section class="modal-body">
        <div class="banner mb-3" id="aparencia-previa">
          <strong>${nome}</strong>
          <span>${secao}</span>
        </div>
        <p class="form-label mb-2" id="aparencia-rotulo-cor">Cor do cabeçalho</p>
        <div class="d-flex gap-2" role="radiogroup" aria-labelledby="aparencia-rotulo-cor">
          <input type="radio" class="btn-check" name="cor" id="aparencia-cor-azul" value="#3a86eb" checked>
          <label class="btn swatch" for="aparencia-cor-azul" style="background:#3a86eb" aria-label="Azul"></label>
          <input type="radio" class="btn-check" name="cor" id="aparencia-cor-laranja" value="#e97a1b">
          <label class="btn swatch" for="aparencia-cor-laranja" style="background:#e97a1b" aria-label="Laranja"></label>
          <input type="radio" class="btn-check" name="cor" id="aparencia-cor-cinza" value="#bdc1c6">
          <label class="btn swatch" for="aparencia-cor-cinza" style="background:#bdc1c6" aria-label="Cinza"></label>
          <input type="radio" class="btn-check" name="cor" id="aparencia-cor-azul-claro" value="#d3e3fd">
          <label class="btn swatch" for="aparencia-cor-azul-claro" style="background:#d3e3fd" aria-label="Azul-claro"></label>
        </div>
      </section>
      <footer class="modal-footer">
        <button type="button" class="btn btn-link-edu" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-primary">Salvar</button>
      </footer>
    </form>
  </div>
</div>`;
    }
  }
  customElements.define('edu-modal-personalizar-aparencia', EduModalPersonalizarAparencia);
})();
