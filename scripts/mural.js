// Mural da turma: código da turma (copiar, redefinir, tela cheia), "Novo aviso" e excluir publicação.
// Menus ⋮, fundo dos diálogos e avisar() vêm do layout.js.
// ponytail: sem back-end, código e avisos ficam no navegador (localStorage); trocar por API quando existir.
const CHAVE_CODIGO = 'educonnect:codigo';
const CHAVE_AVISOS = 'educonnect:avisos';
const feedMural = document.querySelector('[data-feed]');
const novoAviso = document.querySelector('.novo-aviso');
const formAviso = document.querySelector('[data-form-aviso]');
const dialogoCodigo = document.getElementById('dialogo-codigo');
const dialogoExcluirPublicacao = document.getElementById('dialogo-excluir-publicacao');
let publicacaoParaExcluir = null;

function lerLocal(chave, padrao) {
    try { return JSON.parse(localStorage.getItem(chave)) ?? padrao; } catch (erro) { return padrao; }
}

function gravarLocal(chave, valor) {
    try { localStorage.setItem(chave, JSON.stringify(valor)); } catch (erro) { /* bloqueado: vale só nesta tela */ }
}

// ---------- Código da turma ----------

function mostrarCodigo(codigo) {
    document.querySelectorAll('[data-codigo]').forEach((el) => { el.textContent = codigo; });
    // O "Informações da turma" (botão ⓘ) lê o código deste atributo.
    document.querySelector('edu-modal-configuracoes-turma')?.setAttribute('codigo', codigo);
}

function codigoAtual() {
    return document.querySelector('[data-codigo]').textContent;
}

function novoCodigo() {
    const letras = 'abcdefghjkmnpqrstuvwxyz23456789'; // sem 0/o, 1/l/i, que se confundem
    return Array.from(crypto.getRandomValues(new Uint8Array(8)), (n) => letras[n % letras.length]).join('');
}

mostrarCodigo(lerLocal(CHAVE_CODIGO, codigoAtual()));

// ---------- Avisos ----------

function hojeCurto() {
    return new Date().toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit' });
}

function criarItemAviso(aviso) {
    const item = document.createElement('li');
    item.className = 'item-mural item-mural--aviso';
    item.dataset.titulo = 'Aviso';
    item.dataset.avisoId = aviso.id;
    item.innerHTML = `
        <div class="item-mural__link">
            <span class="item-mural__icone material-symbols-outlined" aria-hidden="true">campaign</span>
            <span class="item-mural__texto">
                <span data-autor></span>
                <time datetime="${aviso.iso}">${aviso.data}</time>
                <span class="item-mural__corpo" data-corpo></span>
            </span>
        </div>
        <details class="menu-suspenso item-mural__menu">
            <summary class="botao-icone" aria-label="Mais opções para o aviso" title="Mais opções">
                <span class="material-symbols-outlined" aria-hidden="true">more_vert</span>
            </summary>
            <ul class="menu-suspenso__lista">
                <li><button class="menu-suspenso__item" type="button" data-excluir-item>Excluir</button></li>
            </ul>
        </details>`;
    // Texto digitado entra com textContent: nunca vira HTML.
    item.querySelector('[data-autor]').textContent = aviso.autor;
    item.querySelector('[data-corpo]').textContent = aviso.texto;
    return item;
}

// Avisos salvos aparecem no topo do mural, do mais novo para o mais antigo.
feedMural.prepend(...lerLocal(CHAVE_AVISOS, []).map(criarItemAviso));

novoAviso.addEventListener('toggle', () => {
    if (novoAviso.open) formAviso.texto.focus();
});

document.querySelector('[data-cancelar-aviso]').addEventListener('click', () => {
    formAviso.reset();
    novoAviso.open = false;
});

formAviso.addEventListener('submit', (evento) => {
    evento.preventDefault();
    const texto = formAviso.texto.value.trim();
    if (!texto) {
        formAviso.texto.value = '';
        formAviso.texto.reportValidity();
        return;
    }
    const aviso = {
        id: String(Date.now()),
        autor: document.querySelector('.mural-cabecalho__professor').textContent,
        texto,
        data: hojeCurto(),
        iso: new Date().toISOString().slice(0, 10)
    };
    gravarLocal(CHAVE_AVISOS, [aviso, ...lerLocal(CHAVE_AVISOS, [])]);
    feedMural.prepend(criarItemAviso(aviso));
    formAviso.reset();
    novoAviso.open = false;
    avisar('Aviso publicado.');
});

// ---------- Cliques: código e excluir ----------

document.addEventListener('click', (evento) => {
    const alvo = evento.target;
    alvo.closest('.menu-suspenso__lista')?.closest('.menu-suspenso')?.removeAttribute('open');

    if (alvo.closest('[data-copiar-codigo]')) {
        const codigo = codigoAtual();
        Promise.resolve()
            .then(() => navigator.clipboard.writeText(codigo))
            .then(() => avisar(`Código ${codigo} copiado.`))
            .catch(() => avisar(`Não foi possível copiar. O código é ${codigo}.`));
    }

    if (alvo.closest('[data-redefinir-codigo]')) {
        const codigo = novoCodigo();
        gravarLocal(CHAVE_CODIGO, codigo);
        mostrarCodigo(codigo);
        avisar(`Novo código da turma: ${codigo}`);
    }

    if (alvo.closest('[data-codigo-tela-cheia]')) dialogoCodigo.showModal();

    if (alvo.closest('[data-excluir-item]')) {
        publicacaoParaExcluir = alvo.closest('.item-mural');
        dialogoExcluirPublicacao.querySelector('[data-excluir-titulo]').textContent = publicacaoParaExcluir.dataset.titulo;
        dialogoExcluirPublicacao.showModal();
    }
});

// ponytail: material/atividade de exemplo saem só desta tela (voltam ao recarregar); avisos saem também do navegador.
dialogoExcluirPublicacao.addEventListener('submit', (evento) => {
    if (evento.submitter.value !== 'excluir') return;
    const { avisoId, titulo } = publicacaoParaExcluir.dataset;
    if (avisoId) gravarLocal(CHAVE_AVISOS, lerLocal(CHAVE_AVISOS, []).filter((aviso) => aviso.id !== avisoId));
    publicacaoParaExcluir.remove();
    publicacaoParaExcluir = null;
    avisar(`"${titulo}" foi excluído do mural.`);
});
