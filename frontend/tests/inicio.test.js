// scripts/inicio.js: menu ⋮ dos cards da página inicial abre a confirmação da turma certa.
const test = require('node:test');
const assert = require('node:assert/strict');
const { pagina } = require('./pagina');

const dialogo = (id) => `
  <dialog id="${id}"><form method="post">
    <input type="hidden" name="turma_id" data-turma-id><strong data-turma-nome></strong>
  </form></dialog>`;

const card = (id, nome, alvo) => `
  <article class="card" data-turma-id="${id}" data-nome="${nome}">
    <details class="menu-suspenso card-menu" open>
      <summary>⋮</summary>
      <ul class="menu-suspenso__lista"><li><button class="menu-suspenso__item" type="button" data-confirmar="${alvo}">Ação</button></li></ul>
    </details>
  </article>`;

function inicio() {
  const html = card(7, 'Matemática', 'dialogo-excluir-turma') + card(9, 'História <b>', 'dialogo-sair-turma')
    + dialogo('dialogo-excluir-turma') + dialogo('dialogo-sair-turma');
  return pagina(html, {
    url: 'http://localhost/frontend/pages/index.php',
    scripts: ['scripts/inicio.js'],
    antes: (w) => { w.HTMLDialogElement.prototype.showModal = function () { this.open = true; }; },
  });
}

test('"Excluir turma" (administrador) abre a confirmação com o id e o nome do card e fecha o menu', () => {
  const w = inicio();
  const [c] = w.document.querySelectorAll('.card');
  c.querySelector('[data-confirmar]').click();
  const d = w.document.getElementById('dialogo-excluir-turma');
  assert.equal(d.open, true);
  assert.equal(d.querySelector('[data-turma-id]').value, '7');
  assert.equal(d.querySelector('[data-turma-nome]').textContent, 'Matemática');
  assert.equal(c.querySelector('details').open, false);
  assert.equal(w.document.getElementById('dialogo-sair-turma').open, false);
});

test('"Sair da turma" usa o card clicado; o nome entra como texto, não como HTML', () => {
  const w = inicio();
  w.document.querySelectorAll('.card')[1].querySelector('[data-confirmar]').click();
  const d = w.document.getElementById('dialogo-sair-turma');
  assert.equal(d.open, true);
  assert.equal(d.querySelector('[data-turma-id]').value, '9');
  assert.equal(d.querySelector('[data-turma-nome]').textContent, 'História <b>');
  assert.equal(d.querySelector('b'), null);
});
