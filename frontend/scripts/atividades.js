// Aba Atividades: ações do menu ⋮ (excluir com confirmação e copiar link).
// A lista expansível usa <details name="..."> nativo, e os menus/avisos vêm do layout.js.
const dialogoExcluir = document.getElementById('dialogo-excluir');
let itemParaExcluir = null;

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
        dialogoExcluir.showModal();
    }

    if (botao.dataset.acao === 'copiar-link') {
        const link = location.href.split('#')[0] + '#' + item.id;
        Promise.resolve()
            .then(() => navigator.clipboard.writeText(link))
            .then(() => avisar('Link copiado.'), () => avisar('Não foi possível copiar o link.'));
    }
});

// "submit" (e não "close") diz na hora qual botão foi usado; Esc e clique no fundo não disparam.
dialogoExcluir.addEventListener('submit', (evento) => {
    if (evento.submitter.value !== 'excluir') return;
    // ponytail: só remove da tela; trocar por envio ao back-end quando ele existir.
    itemParaExcluir.remove();
    avisar(`"${itemParaExcluir.dataset.titulo}" foi excluído.`);
    itemParaExcluir = null;
});
