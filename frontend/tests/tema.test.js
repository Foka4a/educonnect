// scripts/tema.js: tema claro/escuro (escolha salva > tema do sistema) e o botão da barra superior.
const test = require('node:test');
const assert = require('node:assert/strict');
const { pagina, pronta } = require('./pagina');

function comTema({ salvo, sistemaEscuro = false, html = '' } = {}) {
  return pagina(html, {
    scripts: ['scripts/tema.js'],
    antes: (w) => {
      w.matchMedia = () => ({ matches: sistemaEscuro });
      if (salvo) w.localStorage.setItem('educonnect-tema', salvo);
    },
  });
}

const tema = (w) => w.document.documentElement.getAttribute('data-bs-theme');

test('sem escolha salva, segue o sistema', () => {
  assert.equal(tema(comTema({ sistemaEscuro: true })), 'dark');
  assert.equal(tema(comTema({ sistemaEscuro: false })), 'light');
});

test('escolha salva vale mais que o sistema; valor estranho é ignorado', () => {
  assert.equal(tema(comTema({ salvo: 'claro', sistemaEscuro: true })), 'light');
  assert.equal(tema(comTema({ salvo: 'escuro' })), 'dark');
  assert.equal(tema(comTema({ salvo: '<script>', sistemaEscuro: true })), 'dark');
});

test('botão alterna o tema, salva a escolha e atualiza o rótulo', async () => {
  const w = comTema({ html: '<div class="barra-superior__acoes"><button id="mais">+</button></div>' });
  await pronta(w);
  const botao = w.document.querySelector('.barra-superior__acoes').firstElementChild;
  assert.notEqual(botao.id, 'mais', 'entra antes do "+"');
  assert.equal(botao.getAttribute('aria-label'), 'Ativar tema escuro');

  botao.click();
  assert.equal(tema(w), 'dark');
  assert.equal(w.localStorage.getItem('educonnect-tema'), 'escuro');
  assert.equal(botao.getAttribute('aria-label'), 'Ativar tema claro');
  assert.equal(botao.textContent, 'light_mode');

  botao.click();
  assert.equal(tema(w), 'light');
  assert.equal(w.localStorage.getItem('educonnect-tema'), 'claro');
});

test('página sem barra superior (login) não ganha botão', async () => {
  const w = comTema({ html: '<main></main>' });
  await pronta(w);
  assert.equal(w.document.querySelector('button'), null);
});
