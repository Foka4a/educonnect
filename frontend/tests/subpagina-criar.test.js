// scripts/subpagina-criar.js: data de entrega no formato BR (dd/mm/aaaa e hh:mm, 24 h).
const test = require('node:test');
const assert = require('node:assert/strict');
const { pagina } = require('./pagina');

const { problemaNoPrazo } = pagina('<dialog id="dialogo-descartar"></dialog>', { scripts: ['scripts/subpagina-criar.js'] });

test('prazo certo: dd/mm/aaaa e hh:mm (hora vazia vale 23:59 no servidor)', () => {
  assert.equal(problemaNoPrazo('05/03/2027', '08:15'), null);
  assert.equal(problemaNoPrazo('29/02/2028', '23:59'), null, 'ano bissexto');
  assert.equal(problemaNoPrazo('31/12/2026', ''), null);
});

test('sem data pede a data', () => {
  assert.deepEqual({ ...problemaNoPrazo('', '10:00') }, { campo: 'data', texto: 'Informe a data de entrega.' });
});

test('data impossível ou em formato americano é recusada', () => {
  for (const ruim of ['30/02/2026', '29/02/2027', '12/31/2026', '1/2/2026', '2026-12-01', '00/01/2026']) {
    assert.equal(problemaNoPrazo(ruim, '10:00')?.campo, 'data', ruim);
  }
});

test('hora fora de 00:00 a 23:59 é recusada', () => {
  for (const ruim of ['24:00', '9:00', '12:60', '11:59 PM']) {
    assert.equal(problemaNoPrazo('01/12/2026', ruim)?.campo, 'hora', ruim);
  }
});
