// Aba Atividades: menus suspensos ("+ Criar" e ⋮), excluir com confirmação e copiar link.
// A lista expansível usa <details name="..."> nativo: não precisa de JS.
const menus = document.querySelectorAll('.menu-suspenso');
const dialogoExcluir = document.getElementById('dialogo-excluir');
const aviso = document.querySelector('[data-aviso]');
let itemParaExcluir = null;
let timerAviso;

function avisar(texto) {
    aviso.textContent = texto;
    aviso.classList.add('aviso--visivel');
    clearTimeout(timerAviso);
    timerAviso = setTimeout(() => aviso.classList.remove('aviso--visivel'), 3000);
}

// Só um menu aberto por vez: clicar fora fecha (inclusive ao abrir outro menu).
document.addEventListener('click', (evento) => {
    menus.forEach((menu) => {
        if (menu.open && !menu.contains(evento.target)) menu.open = false;
    });
});

document.addEventListener('keydown', (evento) => {
    if (evento.key !== 'Escape') return;
    menus.forEach((menu) => {
        if (!menu.open) return;
        menu.open = false;
        menu.querySelector('summary').focus();
    });
});

// Ações do menu ⋮
document.addEventListener('click', (evento) => {
    const botao = evento.target.closest('[data-acao]');
    if (!botao) return;
    const item = botao.closest('.item-atividade');
    botao.closest('.menu-suspenso').open = false;

    if (botao.dataset.acao === 'excluir') {
        itemParaExcluir = item;
        dialogoExcluir.querySelector('[data-excluir-tipo]').textContent = item.dataset.tipo;
        dialogoExcluir.querySelector('[data-excluir-titulo]').textContent = item.dataset.titulo;
        dialogoExcluir.returnValue = '';
        dialogoExcluir.showModal();
    }

    if (botao.dataset.acao === 'copiar-link') {
        const link = location.href.split('#')[0] + '#' + item.id;
        Promise.resolve()
            .then(() => navigator.clipboard.writeText(link))
            .then(() => avisar('Link copiado.'), () => avisar('Não foi possível copiar o link.'));
    }
});

// Clicar no fundo escuro fecha a confirmação, como "Cancelar".
dialogoExcluir.addEventListener('click', (evento) => {
    if (evento.target === dialogoExcluir) dialogoExcluir.close();
});

dialogoExcluir.addEventListener('close', () => {
    if (dialogoExcluir.returnValue !== 'excluir' || !itemParaExcluir) return;
    // ponytail: só remove da tela; trocar por envio ao back-end quando ele existir.
    itemParaExcluir.remove();
    avisar(`"${itemParaExcluir.dataset.titulo}" foi excluído.`);
    itemParaExcluir = null;
});
