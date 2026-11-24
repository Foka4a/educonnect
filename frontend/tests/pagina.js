// Monta uma página falsa (jsdom) com o HTML dado e roda os scripts do front-end nela, na ordem.
const { JSDOM } = require('jsdom');
const fs = require('node:fs');
const path = require('node:path');

function pagina(html = '', { url = 'http://localhost/frontend/pages/mural.php', scripts = [], antes } = {}) {
  const dom = new JSDOM(`<!doctype html><html><body>${html}</body></html>`, { url, runScripts: 'outside-only' });
  const w = dom.window;
  if (antes) antes(w);   // stubs (fetch, avisar, matchMedia...) antes dos scripts
  for (const s of scripts) w.eval(fs.readFileSync(path.join(__dirname, '..', s), 'utf8'));
  return w;
}

/** Dispara um evento cancelável e diz se a página o cancelou (preventDefault). */
function cancelado(w, alvo, tipo, extra = {}) {
  const ev = tipo === 'keydown' ? new w.KeyboardEvent(tipo, { cancelable: true, bubbles: true, ...extra })
    : new w.Event(tipo, { cancelable: true, bubbles: true });
  Object.assign(ev, tipo === 'keydown' ? {} : extra);
  alvo.dispatchEvent(ev);
  return ev.defaultPrevented;
}

function digitar(w, campo, valor) {
  campo.value = valor;
  campo.dispatchEvent(new w.Event('input', { bubbles: true }));
}

/** Espera o DOMContentLoaded (scripts que só agem com a página pronta). */
function pronta(w) {
  return w.document.readyState === 'loading'
    ? new Promise((r) => w.document.addEventListener('DOMContentLoaded', () => setTimeout(r, 0)))
    : Promise.resolve();
}

module.exports = { pagina, cancelado, digitar, pronta };
