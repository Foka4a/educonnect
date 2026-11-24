/* EduConnect · liga as páginas ao back-end.
   As páginas .php já chegam com os dados do servidor; os formulários postam para a própria página, com um campo
   oculto `acao` (ex.: acao=criar-aviso), e a página trata o pedido antes de mostrar o HTML.
   Carregue em qualquer página que use modais ou formulários (inclusive login e cadastro).

   1. Mostra o resultado de um formulário: a página volta com ?msg=… ou ?erro=… na URL.
      Usa avisar() do layout.js quando existe; senão, escreve em <p data-aviso>, em <p id="status"> ou, nas telas de
      acesso (login e cadastro), na mensagem <p class="erro" role="alert"> que já existe na página.
   2. window.educonnect.enviar(acao, campos) → Promise com o JSON da resposta ({ok, mensagem, …} ou {ok: false, erro}),
      para salvar sem recarregar (nota e comentário em "Trabalhos dos estudantes"). Vai para a própria página,
      com o cabeçalho X-Requested-With: fetch.
   3. Os eventos dos modais de exemplo (modais/index.html) viram a mesma chamada:
        edu:salvar-nota → salvar-nota,  edu:novo-comentario → comentar,  edu:redefinir-codigo → redefinir-codigo */
(function () {
  'use strict';
  function avisar_(texto, erro) {
    if (!texto) return;
    if (typeof window.avisar === 'function') return window.avisar(texto);
    var acesso = erro && document.querySelector('p.erro[role="alert"]');
    if (acesso) { acesso.textContent = texto; acesso.hidden = false; return; }
    var el = document.querySelector('[data-aviso]') || document.getElementById('status');
    if (el) el.textContent = (erro ? 'Erro: ' : '') + texto;
  }

  // 1. avisos vindos do redirecionamento
  var params = new URLSearchParams(location.search);
  if (params.has('msg') || params.has('erro')) {
    var msg = params.get('erro') || params.get('msg');
    var erro = params.has('erro');
    params.delete('msg'); params.delete('erro');
    history.replaceState(null, '', location.pathname + (params.toString() ? '?' + params : '') + location.hash);
    var mostrar = function () { avisar_(msg, erro); };
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', mostrar); else mostrar();
  }

  // 2. fetch para a própria página
  function enviar(acao, campos) {
    var dados = new URLSearchParams(campos);
    dados.set('acao', acao);
    return fetch(location.pathname + location.search, {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'X-Requested-With': 'fetch', 'Content-Type': 'application/x-www-form-urlencoded' },
      body: dados
    }).then(function (r) { return r.json(); }).catch(function () { return { ok: false, erro: 'Sem conexão com o servidor.' }; });
  }
  window.educonnect = { enviar: enviar };

  // 3. eventos dos modais de exemplo
  function resultado(r) { if (!r.ok) avisar_(r.erro || 'Não foi possível concluir.', true); return r; }

  document.addEventListener('edu:salvar-nota', function (e) {
    enviar('salvar-nota', { entrega_id: e.detail.entregaId, nota: e.detail.nota === null ? '' : e.detail.nota }).then(resultado);
  });
  document.addEventListener('edu:novo-comentario', function (e) {
    enviar('comentar', { entrega_id: e.detail.entregaId, texto: e.detail.texto }).then(resultado);
  });
  document.addEventListener('edu:redefinir-codigo', function (e) {
    enviar('redefinir-codigo', { turma_id: e.detail.turmaId }).then(resultado).then(function (r) {
      var modal = e.target.closest ? e.target.closest('edu-modal-convidar-alunos') : null;
      if (r.ok && modal && modal.definirCodigo) modal.definirCodigo(r.codigo);
    });
  });
})();
