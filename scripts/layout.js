// Menu lateral: no desktop recolhe para só ícones; no celular abre como gaveta.
const botaoMenu = document.querySelector('[data-menu-toggle]');
const menuLateral = document.getElementById('menu-lateral');
const celular = matchMedia('(max-width: 640px)');
const classes = document.body.classList;

function atualizarAria() {
    const aberto = celular.matches ? classes.contains('menu-aberto') : !classes.contains('menu-recolhido');
    botaoMenu.setAttribute('aria-expanded', String(aberto));
}

function fecharGaveta() {
    classes.remove('menu-aberto');
    atualizarAria();
}

botaoMenu.addEventListener('click', () => {
    classes.toggle(celular.matches ? 'menu-aberto' : 'menu-recolhido');
    atualizarAria();
});

// Fecha a gaveta ao clicar fora dela ou apertar Esc.
document.addEventListener('click', (evento) => {
    if (classes.contains('menu-aberto') && !menuLateral.contains(evento.target) && !botaoMenu.contains(evento.target)) {
        fecharGaveta();
    }
});
document.addEventListener('keydown', (evento) => {
    if (evento.key === 'Escape') fecharGaveta();
});
celular.addEventListener('change', fecharGaveta);

atualizarAria();
