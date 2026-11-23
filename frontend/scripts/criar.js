// Telas "Criar atividade" e "Criar material": título obrigatório, editor de texto,
// anexos e (só na atividade) data de entrega. Não usa o layout.js: a tela não tem menu lateral.
const form = document.querySelector('[data-criar]');
const titulo = form.elements.titulo;
const erroTitulo = document.getElementById('erro-titulo');
const texto = form.querySelector('[data-texto]');
const ferramentas = form.querySelectorAll('[data-comando]');
const entradaArquivos = form.elements.anexos;
const listaAnexos = form.querySelector('[data-anexos]');
let anexos = [];

// ---------- Título obrigatório: erro abaixo do campo se vazio ----------

function mostrarErroTitulo(mostrar) {
    erroTitulo.hidden = !mostrar;
    if (mostrar) titulo.setAttribute('aria-invalid', 'true');
    else titulo.removeAttribute('aria-invalid');
}

titulo.addEventListener('input', () => mostrarErroTitulo(false));

// ---------- Editor: negrito, itálico, sublinhado, lista e limpar formatação ----------
// execCommand está obsoleto, mas ainda é a forma mais simples de formatar um contenteditable sem biblioteca.

ferramentas.forEach((botao) => {
    // mousedown sem foco: a seleção de texto continua no editor.
    botao.addEventListener('mousedown', (evento) => evento.preventDefault());
    botao.addEventListener('click', () => {
        texto.focus();
        document.execCommand(botao.dataset.comando);
        atualizarFerramentas();
    });
});

function atualizarFerramentas() {
    ferramentas.forEach((botao) => {
        if (botao.hasAttribute('aria-pressed')) {
            botao.setAttribute('aria-pressed', String(document.queryCommandState(botao.dataset.comando)));
        }
    });
}

document.addEventListener('selectionchange', () => {
    if (texto.contains(document.getSelection().anchorNode)) atualizarFerramentas();
});

// Apagar tudo deixa um <br> que esconderia o placeholder.
texto.addEventListener('input', () => {
    if (!texto.textContent.trim() && !texto.querySelector('li')) texto.innerHTML = '';
});

// ---------- Anexos: lista com remover (×) ----------

function sincronizarArquivos() {
    const transferencia = new DataTransfer();
    anexos.forEach((arquivo) => transferencia.items.add(arquivo));
    entradaArquivos.files = transferencia.files;
}

function mostrarAnexos() {
    listaAnexos.replaceChildren(...anexos.map((arquivo, indice) => {
        const item = document.createElement('li');
        item.className = 'criar__anexo';
        item.innerHTML = `
            <span class="material-symbols-outlined" aria-hidden="true">attach_file</span>
            <span class="criar__anexo-nome"></span>
            <button class="botao-icone" type="button">
                <span class="material-symbols-outlined" aria-hidden="true">close</span>
            </button>`;
        item.querySelector('.criar__anexo-nome').textContent = arquivo.name;
        const remover = item.querySelector('button');
        remover.setAttribute('aria-label', `Remover ${arquivo.name}`);
        remover.addEventListener('click', () => {
            anexos.splice(indice, 1);
            sincronizarArquivos();
            mostrarAnexos();
            entradaArquivos.focus();
        });
        return item;
    }));
}

entradaArquivos.addEventListener('change', () => {
    anexos = anexos.concat([...entradaArquivos.files]);
    sincronizarArquivos();
    mostrarAnexos();
});

// ---------- Data de entrega (só na atividade) ----------

const prazo = form.elements.prazo;
if (prazo) {
    const camposData = form.querySelector('[data-campos-data]');
    prazo.addEventListener('change', () => {
        const comData = prazo.value === 'data';
        camposData.hidden = !comData;
        form.elements.data.required = comData;
        if (comData) form.elements.data.focus();
    });
}

// ---------- Atribuir / Postar ----------

form.addEventListener('submit', (evento) => {
    form.elements.texto.value = texto.innerHTML;
    if (!titulo.value.trim()) {
        evento.preventDefault();
        mostrarErroTitulo(true);
        titulo.focus();
        return;
    }
    if (prazo && prazo.value === 'data' && !form.elements.data.value) {
        evento.preventDefault();
        form.elements.data.reportValidity();
        return;
    }
    // ponytail: sem back-end, publicar só volta para Atividades; com action no form, o POST segue normalmente.
    if (!form.getAttribute('action')) {
        evento.preventDefault();
        location.href = 'atividades.html';
    }
});
