/* <edu-modal-excluir-comentario> · EXTRA: excluir um comentário (público em material/atividade ou particular em atividade).
   Abrir: data-bs-target="#modal-excluir-comentario"
   Atributos: action (destino do POST), comentario-id, trecho (começo do texto, para o usuário confirmar qual é) */
(function () {
  'use strict';
  class EduModalExcluirComentario extends EduModalConfirmar {
    config() {
      var trecho = this.attr('trecho', '');
      return {
        id: 'modal-excluir-comentario', titulo: 'Excluir comentário?', campo: 'comentario_id', valor: this.attr('comentario-id', ''),
        ok: 'Excluir', perigo: true,
        corpo: (trecho ? '<blockquote class="trecho-comentario">' + this.esc(trecho) + '</blockquote>' : '') +
               '<p class="mb-0">Esta ação não pode ser desfeita.</p>'
      };
    }
  }
  customElements.define('edu-modal-excluir-comentario', EduModalExcluirComentario);
})();
