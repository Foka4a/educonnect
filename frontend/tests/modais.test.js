// Modais (Web Components): base EduModal e as validações feitas no navegador antes de postar.
const test = require('node:test');
const assert = require('node:assert/strict');
const { pagina, cancelado, digitar } = require('./pagina');

const BASE = 'modais/components/edu-modal.js';

test('EduModal: escapa atributos, monta o form e põe o campo oculto acao', () => {
  const w = pagina(`<x-teste turma-id='7"><script>x</script>' action="/destino?a=1&b=2"></x-teste>`, { scripts: [BASE] });
  w.eval(`customElements.define('x-teste', class extends EduModal {
    render() { return '<form ' + this.formAttrs('/criar-aviso') + '>' + this.campoTurma() + '</form>'; }
  })`);
  const comp = w.document.querySelector('x-teste');
  const form = comp.querySelector('form');
  assert.equal(form.getAttribute('method'), 'post');
  assert.equal(form.getAttribute('action'), '/destino?a=1&b=2');
  assert.equal(form.firstElementChild.name, 'acao');
  assert.equal(form.firstElementChild.value, 'criar-aviso');
  assert.equal(form.elements.turma_id.value, '7"><script>x</script>', 'valor intacto, sem virar HTML');
  assert.equal(comp.querySelector('script'), null);
  assert.equal(comp.esc(`<a href="x">'&'</a>`), '&lt;a href=&quot;x&quot;&gt;&#39;&amp;&#39;&lt;/a&gt;');
  assert.equal(comp.iniciais('  ana   maria souza '), 'AM');
});

test('EduModal: sem atributo action o form posta para a própria página', () => {
  const w = pagina('<x-sem-action></x-sem-action>', { scripts: [BASE] });
  w.eval(`customElements.define('x-sem-action', class extends EduModal { render() { return '<form ' + this.formAttrs('x') + '></form>'; } })`);
  assert.equal(w.document.querySelector('form').hasAttribute('action'), false);
});

test('Participar da turma: código de 5 a 8 letras/números', () => {
  const w = pagina('<edu-modal-participar-turma></edu-modal-participar-turma>', { scripts: ['modais/components/participar-turma.js'] });
  const form = w.document.querySelector('#modal-participar-turma form');
  const campo = form.elements.codigo;
  const botao = form.querySelector('[type="submit"]');
  const erro = form.querySelector('#participar-turma-erro');

  assert.equal(form.elements.acao.value, 'participar-turma');
  assert.equal(botao.disabled, true, 'desabilitado sem código');
  digitar(w, campo, 'ab1');
  assert.equal(botao.disabled, false);
  campo.dispatchEvent(new w.Event('blur'));
  assert.equal(erro.hidden, false, 'avisa ao sair do campo');
  assert.equal(campo.getAttribute('aria-invalid'), 'true');

  for (const ruim of ['ab1', 'abc 12', 'abc-12', '123456789']) {
    campo.value = ruim;
    assert.equal(cancelado(w, form, 'submit'), true, `"${ruim}" não pode ser enviado`);
  }
  digitar(w, campo, 'X7k2m9q');
  assert.equal(erro.hidden, true, 'erro some ao corrigir');
  assert.equal(cancelado(w, form, 'submit'), false);

  form.closest('dialog').dispatchEvent(new w.Event('close'));
  assert.equal(campo.value, '');
  assert.equal(botao.disabled, true, 'fechar limpa o modal');
});

test('Criar turma: nome obrigatório (espaços não contam)', () => {
  const w = pagina('<edu-modal-criar-turma></edu-modal-criar-turma>', { scripts: ['modais/components/criar-turma.js'] });
  const form = w.document.querySelector('#modal-criar-turma form');
  const erro = form.querySelector('#criar-turma-erro');
  assert.equal(form.elements.acao.value, 'criar-turma');
  form.elements.nome.value = '   ';
  assert.equal(cancelado(w, form, 'submit'), true);
  assert.equal(erro.hidden, false);
  digitar(w, form.elements.nome, 'Matemática');
  assert.equal(erro.hidden, true);
  assert.equal(cancelado(w, form, 'submit'), false);
});

test('Convidar alunos: e-mails viram chips, sem repetidos, e vão juntos no campo oculto', () => {
  const w = pagina('<edu-modal-convidar-alunos turma-id="3" codigo="abc12345"></edu-modal-convidar-alunos>',
    { scripts: [BASE, 'modais/components/convidar-alunos.js'] });
  const comp = w.document.querySelector('edu-modal-convidar-alunos');
  const form = comp.querySelector('form');
  const campo = comp.querySelector('#convidar-email');
  const oculto = comp.querySelector('#convidar-emails-valor');
  const erro = comp.querySelector('#convidar-email-erro');
  const enter = (v) => { campo.value = v; return cancelado(w, campo, 'keydown', { key: 'Enter' }); };

  assert.equal(cancelado(w, form, 'submit'), true, 'sem e-mail não envia');
  assert.match(erro.textContent, /ao menos um e-mail/);

  assert.equal(enter('Ana@Escola.com'), true, 'Enter não envia o form');
  enter('ana@escola.com');
  enter('bia@escola.com');
  assert.equal(oculto.value, 'ana@escola.com,bia@escola.com');
  assert.equal(comp.querySelectorAll('.chip-email').length, 2);

  enter('sem-arroba');
  assert.ok(erro.classList.contains('visivel'));
  assert.equal(oculto.value, 'ana@escola.com,bia@escola.com', 'inválido não entra');
  digitar(w, campo, '');
  assert.ok(!erro.classList.contains('visivel'), 'digitar apaga o erro');

  comp.querySelector('[aria-label="Remover ana@escola.com"]').click();
  assert.equal(oculto.value, 'bia@escola.com');

  campo.value = 'caio@escola.com';   // digitado sem Enter: entra ao enviar
  assert.equal(cancelado(w, form, 'submit'), false);
  assert.equal(oculto.value, 'bia@escola.com,caio@escola.com');
  assert.equal(form.elements.turma_id.value, '3');
});

test('Convidar alunos: Redefinir avisa o back-end e definirCodigo troca o código na tela', () => {
  const w = pagina('<edu-modal-convidar-alunos turma-id="9" codigo="velho123"></edu-modal-convidar-alunos>',
    { scripts: [BASE, 'modais/components/convidar-alunos.js'] });
  const comp = w.document.querySelector('edu-modal-convidar-alunos');
  let pedido = null;
  w.document.addEventListener('edu:redefinir-codigo', (e) => { pedido = e.detail; });
  comp.querySelector('#convidar-redefinir').click();
  assert.deepEqual({ ...pedido }, { turmaId: '9' });
  comp.definirCodigo('novo5678');
  assert.equal(comp.querySelector('#convidar-codigo').textContent, 'novo5678');
});

test('Enviar trabalho: até 5 arquivos de 20 MB, sem repetidos; precisa de arquivo para entregar', () => {
  const w = pagina('<edu-modal-enviar-trabalho atividade-id="4"></edu-modal-enviar-trabalho>',
    { scripts: [BASE, 'modais/components/enviar-trabalho.js'] });
  const comp = w.document.querySelector('edu-modal-enviar-trabalho');
  const form = comp.querySelector('form');
  const input = comp.querySelector('#enviar-arquivos');
  const entregar = comp.querySelector('#enviar-entregar');
  const erro = comp.querySelector('#enviar-erro');
  const escolher = (arquivos) => {
    Object.defineProperty(input, 'files', { value: arquivos, configurable: true });
    input.dispatchEvent(new w.Event('change'));
  };
  const MB = 1024 * 1024;

  assert.equal(entregar.disabled, true);
  assert.equal(cancelado(w, form, 'submit', { submitter: entregar }), true);
  assert.match(erro.textContent, /ao menos um arquivo/);

  escolher([{ name: 'enorme.zip', size: 21 * MB }]);
  assert.match(erro.textContent, /passa de 20 MB/);
  assert.equal(entregar.disabled, true);

  escolher([{ name: 'a.pdf', size: 2048 }, { name: 'a.pdf', size: 2048 }]);
  assert.equal(comp.querySelectorAll('#enviar-novos li').length, 1, 'repetido entra uma vez');
  assert.match(comp.querySelector('#enviar-novos li').textContent, /a\.pdf · 2 KB/);
  assert.equal(entregar.disabled, false);

  escolher(['b', 'c', 'd', 'e', 'f'].map((n) => ({ name: n + '.pdf', size: 1.5 * MB })));
  assert.equal(comp.querySelectorAll('#enviar-novos li').length, 5);
  assert.match(erro.textContent, /Máximo de 5 arquivos/);
  assert.equal(cancelado(w, form, 'submit', { submitter: entregar }), false);

  comp.querySelector('[aria-label="Remover a.pdf"]').click();
  assert.equal(comp.querySelectorAll('#enviar-novos li').length, 4);
});

test('Enviar trabalho: já entregue mostra os arquivos e permite cancelar sem escolher arquivo', () => {
  const w = pagina('<edu-modal-enviar-trabalho arquivos-atuais="lista.pdf|<b>x</b>.doc" atrasada></edu-modal-enviar-trabalho>',
    { scripts: [BASE, 'modais/components/enviar-trabalho.js'] });
  const comp = w.document.querySelector('edu-modal-enviar-trabalho');
  const cancelar = comp.querySelector('#enviar-cancelar-envio');
  assert.ok(cancelar);
  assert.equal(comp.querySelector('#enviar-entregar').textContent, 'Reenviar');
  assert.ok(comp.querySelector('.aviso-atraso'), 'avisa o atraso');
  assert.deepEqual([...comp.querySelectorAll('[aria-label="Arquivos entregues"] li')].map((li) => li.textContent.trim()),
    ['lista.pdf', '<b>x</b>.doc'], 'nome do arquivo aparece como texto');
  assert.equal(cancelado(w, comp.querySelector('form'), 'submit', { submitter: cancelar }), false);
});
