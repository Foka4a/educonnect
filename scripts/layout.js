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

// ---------- Componentes compartilhados pelas páginas ----------

// Menus suspensos (<details class="menu-suspenso">): só um aberto por vez;
// clicar fora ou apertar Esc fecha.
document.addEventListener('click', (evento) => {
    document.querySelectorAll('.menu-suspenso[open]').forEach((menu) => {
        if (!menu.contains(evento.target)) menu.open = false;
    });
    // Clicar no fundo escuro de um <dialog> fecha, como "Cancelar".
    if (evento.target instanceof HTMLDialogElement) evento.target.close();
});

document.addEventListener('keydown', (evento) => {
    if (evento.key !== 'Escape') return;
    document.querySelectorAll('.menu-suspenso[open]').forEach((menu) => {
        menu.open = false;
        menu.querySelector('summary').focus();
    });
});

// Aviso rápido no rodapé da tela. Precisa de <p class="aviso" role="status" data-aviso></p> na página.
let timerAviso;
function avisar(texto) {
    const aviso = document.querySelector('[data-aviso]');
    aviso.textContent = texto;
    aviso.classList.add('aviso--visivel');
    clearTimeout(timerAviso);
    timerAviso = setTimeout(() => aviso.classList.remove('aviso--visivel'), 3000);
}
