// scripts/backend.js: avisos de ?msg=/?erro= e o fetch para a própria página (nota e comentário).
const test = require('node:test');
const assert = require('node:assert/strict');
const { pagina, pronta } = require('./pagina');

const SCRIPT = 'scripts/backend.js';

/** Página com fetch falso: guarda as chamadas e responde `resposta` (ou rejeita, se for um Error). */
function comFetch(resposta, opcoes = {}) {
  const chamadas = [];
  const avisos = [];
  const w = pagina(opcoes.html || '', {
    url: opcoes.url,
    scripts: [SCRIPT],
    antes: (w) => {
      w.fetch = (url, init) => {
        chamadas.push({ url, init, campos: Object.fromEntries(new w.URLSearchParams(init.body.toString())) });
        return resposta instanceof Error ? Promise.reject(resposta) : Promise.resolve({ json: () => Promise.resolve(resposta) });
      };
      if (!opcoes.semAvisar) w.avisar = (t) => avisos.push(t);
    },
  });
  return { w, chamadas, avisos };
}

test('?msg= mostra o aviso e some da URL, mantendo os outros parâmetros', async () => {
  const { w, avisos } = comFetch({}, { url: 'http://localhost/frontend/pages/mural.php?turma=3&msg=Turma%20criada.#topo' });
  await pronta(w);
  assert.deepEqual(avisos, ['Turma criada.']);
  assert.equal(w.location.search, '?turma=3');
  assert.equal(w.location.hash, '#topo');
});

test('?erro= na tela de login aparece na mensagem de erro que já existe', async () => {
  const { w } = comFetch({}, {
    url: 'http://localhost/frontend/pages/login.php?erro=E-mail%20ou%20senha%20incorretos',
    html: '<p class="erro" role="alert" hidden></p>',
    semAvisar: true,
  });
  await pronta(w);
  const p = w.document.querySelector('p.erro');
  assert.equal(p.textContent, 'E-mail ou senha incorretos');
  assert.equal(p.hidden, false);
  assert.equal(w.location.search, '');
});

test('?erro= sem avisar() nem tela de acesso vai para [data-aviso] com "Erro:"', async () => {
  const { w } = comFetch({}, { url: 'http://localhost/x.php?erro=Falhou', html: '<p data-aviso></p>', semAvisar: true });
  await pronta(w);
  assert.equal(w.document.querySelector('[data-aviso]').textContent, 'Erro: Falhou');
});

test('enviar(): POST para a própria página com acao e cabeçalho de fetch', async () => {
  const { w, chamadas } = comFetch({ ok: true, mensagem: 'Nota salva.' }, { url: 'http://localhost/frontend/pages/trabalho-estudantes.php?atividade=8' });
  const r = await w.educonnect.enviar('salvar-nota', { entrega_id: 5, nota: '90' });
  assert.deepEqual(r, { ok: true, mensagem: 'Nota salva.' });
  const [c] = chamadas;
  assert.equal(c.url, '/frontend/pages/trabalho-estudantes.php?atividade=8');
  assert.equal(c.init.method, 'POST');
  assert.equal(c.init.credentials, 'same-origin');
  assert.equal(c.init.headers['X-Requested-With'], 'fetch');
  assert.deepEqual(c.campos, { entrega_id: '5', nota: '90', acao: 'salvar-nota' });
});

test('enviar(): sem conexão devolve {ok: false} em vez de quebrar', async () => {
  const { w } = comFetch(new Error('offline'));
  assert.deepEqual({ ...(await w.educonnect.enviar('comentar', {})) }, { ok: false, erro: 'Sem conexão com o servidor.' });
});

test('eventos dos modais viram ações do back-end; nota null vai vazia; erro é avisado', async () => {
  const { w, chamadas, avisos } = comFetch({ ok: false, erro: 'Use um número de 0 a 100.' });
  w.document.dispatchEvent(new w.CustomEvent('edu:salvar-nota', { detail: { entregaId: 2, nota: null } }));
  w.document.dispatchEvent(new w.CustomEvent('edu:novo-comentario', { detail: { entregaId: 2, texto: 'Bom' } }));
  await new Promise((r) => setTimeout(r, 0));
  assert.deepEqual(chamadas.map((c) => c.campos), [
    { entrega_id: '2', nota: '', acao: 'salvar-nota' },
    { entrega_id: '2', texto: 'Bom', acao: 'comentar' },
  ]);
  assert.deepEqual(avisos, ['Use um número de 0 a 100.', 'Use um número de 0 a 100.']);
});

test('edu:redefinir-codigo: código novo do servidor aparece no modal', async () => {
  const { w, chamadas } = comFetch({ ok: true, codigo: 'novo1234' }, { html: '<edu-modal-convidar-alunos><button id="b"></button></edu-modal-convidar-alunos>' });
  const modal = w.document.querySelector('edu-modal-convidar-alunos');
  modal.definirCodigo = (c) => { modal.dataset.codigo = c; };
  w.document.getElementById('b').dispatchEvent(new w.CustomEvent('edu:redefinir-codigo', { bubbles: true, detail: { turmaId: '4' } }));
  await new Promise((r) => setTimeout(r, 0));
  assert.deepEqual(chamadas[0].campos, { turma_id: '4', acao: 'redefinir-codigo' });
  assert.equal(modal.dataset.codigo, 'novo1234');
});
