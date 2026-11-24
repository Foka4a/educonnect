// Aba Atividades: ações do menu ⋮ (excluir com confirmação e copiar link).
// A lista vem do servidor (atividades.php); "Excluir" posta para a própria página com a ação que o item informa.
// A lista expansível usa <details name="..."> nativo, e os menus/avisos vêm do layout.js.
const dialogoExcluir = document.getElementById('dialogo-excluir');

document.addEventListener('click', (evento) => {
    const botao = evento.target.closest('[data-acao]');
    if (!botao) return;
    const item = botao.closest('.item-atividade');
    botao.closest('.menu-suspenso').open = false;

    if (botao.dataset.acao === 'excluir') {
        const form = dialogoExcluir.querySelector('form');
        const campo = form.querySelector('[data-excluir-id]');
        form.querySelector('[data-excluir-acao]').value = item.dataset.acaoExcluir;
        campo.name = item.dataset.campo;
        campo.value = item.dataset.id;
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

// Link com #atividade-3 / #material-5 (Copiar link, mural): abre o item.
const alvo = location.hash ? document.getElementById(location.hash.slice(1)) : null;
const detalhesDoAlvo = alvo?.querySelector('.item-atividade__detalhes');
if (detalhesDoAlvo) detalhesDoAlvo.open = true;
