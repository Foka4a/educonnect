/* <edu-modal-entrega-aluno> · Modal "Entrega do aluno" (mapa de telas, Trabalhos dos estudantes, somente professor).
   Abrir: data-bs-target="#modal-entrega-aluno" (ao clicar no aluno da lista).
   Desktop: caixa larga. Mobile (< 576 px): tela cheia com [×] nome, status, nota e comentários no rodapé.

   Elementos: × fecha e volta para a lista · "(Ver histórico)": datas de entrega / cancelamento do envio ·
   Nota de 0 a 100, salva ao sair do campo · arquivo entregue (abre/baixa) · comentário particular entre professor e aluno.

   Atributos: aluno-nome, professor-nome, entrega-id, nota, arquivo-nome, arquivo-tipo, arquivo-url, arquivo-miniatura,
              historico (itens separados por |), comentarios (JSON: [{"autor":"…","hora":"22:06","texto":"…"}])

   Eventos (bubbles, disparados no elemento): "edu:salvar-nota"  detail {aluno, entregaId, nota}   (nota null = "Sem nota")
                                              "edu:novo-comentario"  detail {aluno, entregaId, texto}
   Ligue esses eventos ao back-end (fetch). O conteúdo padrão é só exemplo do wireframe. */
(function () {
  'use strict';

  class EduModalEntregaAluno extends EduModal {
    comentariosIniciais() {
      var bruto = this.getAttribute('comentarios');
      if (bruto === null) return [{ autor: 'Aluno Exemplo', hora: '22:06', texto: 'segue a lista' }];
      try { var l = JSON.parse(bruto); return Array.isArray(l) ? l : []; } catch (e) { return []; }
    }

    itemComentario(autor, hora, texto) {
      return '<li><span class="av" aria-hidden="true">' + this.esc(this.iniciais(autor)) + '</span><div>' +
        '<p class="quem">' + this.esc(autor) + ' <span>• ' + this.esc(hora) + '</span></p>' +
        '<p>' + this.esc(texto) + '</p></div></li>';
    }

    render() {
      var aluno = this.attr('aluno-nome', 'Aluno Exemplo');
      var professor = this.attr('professor-nome', 'Carla Mendes');
      var nota = this.esc(this.attr('nota', ''));
      var arquivoNome = this.esc(this.attr('arquivo-nome', 'resposta.pdf'));
      var arquivoTipo = this.esc(this.attr('arquivo-tipo', 'PDF'));
      var arquivoUrl = this.esc(this.attr('arquivo-url', '#'));
      var miniatura = this.getAttribute('arquivo-miniatura');
      var historico = this.attr('historico', '13 nov., 22:06 · Entregue').split('|');
      var self = this;
      var comentarios = this.comentariosIniciais().map(function (c) { return self.itemComentario(c.autor || '', c.hora || '', c.texto || ''); }).join('');

      return `
<div class="modal fade" id="modal-entrega-aluno" tabindex="-1" aria-labelledby="titulo-entrega-aluno" aria-hidden="true">
  <div class="modal-dialog dialogo-largo modal-dialog-centered modal-dialog-scrollable modal-fullscreen-sm-down">
    <div class="modal-content">
      <header class="modal-header entrega-cab">
        <button type="button" class="btn btn-redondo" data-bs-dismiss="modal" aria-label="Fechar e voltar para a lista">${this.icone('fechar')}</button>
        <h2 class="modal-title" id="titulo-entrega-aluno">${this.esc(aluno)}</h2>
        <p class="entrega-status">Entregue <button type="button" class="btn-historico" id="entrega-historico-btn" aria-expanded="false" aria-controls="entrega-historico">(Ver histórico)</button></p>
        <div class="entrega-nota-caixa">
          <label class="entrega-nota" id="entrega-nota-rotulo">Nota: <input id="entrega-nota" type="number" min="0" max="100" step="1" inputmode="numeric" placeholder="__" value="${nota}" aria-describedby="entrega-nota-msg"> /100</label>
          <span class="entrega-nota-msg" id="entrega-nota-msg" role="status"></span>
        </div>
      </header>
      <section class="modal-body">
        <ul class="historico" id="entrega-historico" hidden>${historico.map(function (h) { return '<li>' + self.esc(h.trim()) + '</li>'; }).join('')}</ul>
        <a class="arquivo-cartao" href="${arquivoUrl}" download>
          <span class="arquivo-info"><strong>${arquivoNome}</strong><span>${arquivoTipo}</span></span>
          <span class="arquivo-miniatura">${miniatura ? '<img src="' + this.esc(miniatura) + '" alt="Miniatura de ' + arquivoNome + '">' : this.icone('arquivo')}</span>
        </a>
      </section>
      <footer class="modal-footer entrega-rodape">
        <section class="entrega-comentarios" aria-labelledby="entrega-comentarios-titulo">
          <h3 id="entrega-comentarios-titulo">${this.icone('pessoa')}<span id="entrega-comentarios-total"></span></h3>
          <ul class="lista-comentarios" id="entrega-lista">${comentarios}</ul>
          <form class="novo-comentario" id="entrega-form-comentario">
            <span class="av" aria-hidden="true">${this.esc(this.iniciais(professor))}</span>
            <input class="form-control" id="entrega-comentario" type="text" placeholder="Adicionar comentário particular..." aria-label="Adicionar comentário particular" autocomplete="off">
            <button type="submit" class="btn btn-redondo" aria-label="Enviar comentário">${this.icone('enviar')}</button>
          </form>
        </section>
      </footer>
    </div>
  </div>
</div>`;
    }

    depoisDeRenderizar() {
      var self = this;
      var aluno = this.attr('aluno-nome', 'Aluno Exemplo');
      var professor = this.attr('professor-nome', 'Carla Mendes');
      var entregaId = this.attr('entrega-id', '');
      var modal = this.querySelector('.modal');
      var lista = this.querySelector('#entrega-lista');
      var total = this.querySelector('#entrega-comentarios-total');
      var campoNota = this.querySelector('#entrega-nota');
      var caixaNota = this.querySelector('#entrega-nota-rotulo');
      var msgNota = this.querySelector('#entrega-nota-msg');
      var btnHist = this.querySelector('#entrega-historico-btn');
      var historicoLista = this.querySelector('#entrega-historico');
      var aviso;

      function emitir(nome, detalhe) {
        self.dispatchEvent(new CustomEvent(nome, { bubbles: true, detail: detalhe }));
      }
      function atualizarTotal() {
        var n = lista.children.length;
        total.textContent = n === 0 ? 'Nenhum comentário particular' : n === 1 ? '1 comentário particular' : n + ' comentários particulares';
      }
      function avisoNota(texto, erro) {
        clearTimeout(aviso);
        msgNota.textContent = texto;
        msgNota.classList.toggle('erro', !!erro);
        caixaNota.classList.toggle('invalida', !!erro);
        if (!erro && texto) aviso = setTimeout(function () { msgNota.textContent = ''; }, 2500);
      }
      atualizarTotal();

      // (Ver histórico): mostra/oculta as datas de entrega e cancelamento
      btnHist.addEventListener('click', function () {
        var abrir = historicoLista.hasAttribute('hidden');
        historicoLista.toggleAttribute('hidden', !abrir);
        btnHist.setAttribute('aria-expanded', String(abrir));
      });

      // Nota de 0 a 100, salva ao sair do campo
      campoNota.addEventListener('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); campoNota.blur(); } });
      campoNota.addEventListener('change', function () {
        var v = campoNota.value.trim();
        if (v === '') { avisoNota('Sem nota.'); emitir('edu:salvar-nota', { aluno: aluno, entregaId: entregaId, nota: null }); return; }
        var n = Number(v);
        if (!Number.isInteger(n) || n < 0 || n > 100) { avisoNota('Use um número de 0 a 100.', true); return; }
        avisoNota('Nota salva.');
        emitir('edu:salvar-nota', { aluno: aluno, entregaId: entregaId, nota: n });
      });
      campoNota.addEventListener('input', function () { if (caixaNota.classList.contains('invalida')) avisoNota(''); });

      // Comentário particular entre professor e aluno
      this.querySelector('#entrega-form-comentario').addEventListener('submit', function (e) {
        e.preventDefault();
        var campo = self.querySelector('#entrega-comentario');
        var texto = campo.value.trim();
        if (!texto) return;
        var agora = new Date();
        var hora = ('0' + agora.getHours()).slice(-2) + ':' + ('0' + agora.getMinutes()).slice(-2);
        lista.insertAdjacentHTML('beforeend', self.itemComentario(professor, hora, texto));
        lista.scrollTop = lista.scrollHeight;
        campo.value = '';
        atualizarTotal();
        emitir('edu:novo-comentario', { aluno: aluno, entregaId: entregaId, texto: texto });
      });

      // ao fechar, volta para a lista e recolhe o historicoLista
      modal.addEventListener('hidden.bs.modal', function () {
        historicoLista.setAttribute('hidden', '');
        btnHist.setAttribute('aria-expanded', 'false');
      });
    }
  }
  customElements.define('edu-modal-entrega-aluno', EduModalEntregaAluno);
})();
