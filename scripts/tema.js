// Tema claro/escuro. Carregado sem "defer" no <head> para aplicar o tema antes de pintar a tela (sem piscar).
// Sem escolha salva, segue o tema do sistema.
const CHAVE_TEMA = 'educonnect-tema';

function lerTema() {
    try {
        const salvo = localStorage.getItem(CHAVE_TEMA);
        if (salvo === 'claro' || salvo === 'escuro') return salvo;
    } catch (erro) { /* navegador bloqueou: usa o do sistema */ }
    return matchMedia('(prefers-color-scheme: dark)').matches ? 'escuro' : 'claro';
}

function aplicarTema(tema) {
    document.documentElement.setAttribute('data-bs-theme', tema === 'escuro' ? 'dark' : 'light');
}

aplicarTema(lerTema());

// Botão na barra superior, ao lado do "+".
document.addEventListener('DOMContentLoaded', () => {
    const acoes = document.querySelector('.barra-superior__acoes');
    if (!acoes) return;

    const botao = document.createElement('button');
    botao.type = 'button';
    botao.className = 'botao-icone';
    botao.innerHTML = '<span class="material-symbols-outlined" aria-hidden="true"></span>';

    // Lê o tema da tela (não do localStorage), para funcionar mesmo se o navegador bloquear o salvamento.
    const temaNaTela = () => (document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'escuro' : 'claro');

    function atualizarBotao() {
        const escuro = temaNaTela() === 'escuro';
        const rotulo = escuro ? 'Ativar tema claro' : 'Ativar tema escuro';
        botao.setAttribute('aria-label', rotulo);
        botao.title = rotulo;
        botao.firstChild.textContent = escuro ? 'light_mode' : 'dark_mode';
    }

    botao.addEventListener('click', () => {
        const novo = temaNaTela() === 'escuro' ? 'claro' : 'escuro';
        try { localStorage.setItem(CHAVE_TEMA, novo); } catch (erro) { /* bloqueado: vale só nesta tela */ }
        aplicarTema(novo);
        atualizarBotao();
    });

    atualizarBotao();
    acoes.prepend(botao);
});
