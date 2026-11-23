/* EduConnect · liga os componentes de modal ao back-end (backend/index.php).
   Carregue em qualquer página que use modais ou formulários do back-end (inclusive login e cadastro).

   1. Mostra o resultado de um formulário: o back-end volta para a página com ?msg=… ou ?erro=… na URL.
      Usa avisar() do layout.js quando existe; senão, escreve em <p data-aviso>, em <p id="status"> ou, nas telas de
      acesso (login e cadastro), na mensagem <p class="erro" role="alert"> que já existe na página.
   2. Os modais "Entrega do aluno" e "Convidar alunos" não têm formulário que recarrega a página;
      os eventos deles viram chamadas fetch():
        edu:salvar-nota       → /salvar-nota       {entrega_id, nota}
        edu:novo-comentario   → /comentar          {entrega_id, texto}      (comentário particular)
        edu:redefinir-codigo  → /redefinir-codigo  {turma_id}  → mostra o código novo no modal
   Para outro caminho do back-end: window.EDUCONNECT_BACKEND = '/caminho/backend/index.php' (antes dos componentes). */
(function () {
  'use strict';
  var BASE = window.EDUCONNECT_BACKEND || '../../backend/index.php';

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

  // 2. eventos dos modais → fetch
  function enviar(rota, campos) {
    var dados = new URLSearchParams(campos);
    return fetch(BASE + rota, {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'X-Requested-With': 'fetch', 'Content-Type': 'application/x-www-form-urlencoded' },
      body: dados
    }).then(function (r) { return r.json(); }).catch(function () { return { ok: false, erro: 'Sem conexão com o servidor.' }; });
  }
  function resultado(r) { if (!r.ok) avisar_(r.erro || 'Não foi possível concluir.', true); return r; }

  document.addEventListener('edu:salvar-nota', function (e) {
    enviar('/salvar-nota', { entrega_id: e.detail.entregaId, nota: e.detail.nota === null ? '' : e.detail.nota }).then(resultado);
  });
  document.addEventListener('edu:novo-comentario', function (e) {
    enviar('/comentar', { entrega_id: e.detail.entregaId, texto: e.detail.texto }).then(resultado);
  });
  document.addEventListener('edu:redefinir-codigo', function (e) {
    enviar('/redefinir-codigo', { turma_id: e.detail.turmaId }).then(resultado).then(function (r) {
      var modal = e.target.closest ? e.target.closest('edu-modal-convidar-alunos') : null;
      if (r.ok && modal && modal.definirCodigo) modal.definirCodigo(r.codigo);
    });
  });
})();
