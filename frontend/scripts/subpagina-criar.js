// Aba Atividades: subpáginas "Criar atividade" e "Criar material" (<dialog> em tela cheia), que também servem para editar.
// O formulário posta para o back-end (criar-* ou editar-*, definido pelo atividades.php), que volta para Atividades com o aviso.
// Menus, fundo do diálogo e avisar() vêm do layout.js.
const MAX_ARQUIVOS = 10;
const MAX_BYTES = 20 * 1024 * 1024;
const dialogoDescartar = document.getElementById('dialogo-descartar');

const TEXTOS = {
    atividade: { descartar: 'atividade' },
    material: { descartar: 'material' },
};

// ---------- HTML da descrição: só negrito, itálico, sublinhado, lista e quebra de linha ----------
const TAGS_PERMITIDAS = new Set(['B', 'STRONG', 'I', 'EM', 'U', 'UL', 'LI', 'BR', 'DIV', 'P']);

function limparHtml(origem) {
    const saida = document.createDocumentFragment();
    origem.childNodes.forEach((no) => {
        if (no.nodeType === Node.TEXT_NODE) {
            saida.append(no.textContent);
        } else if (no.nodeType === Node.ELEMENT_NODE) {
            if (TAGS_PERMITIDAS.has(no.tagName)) {
                const copia = document.createElement(no.tagName); // sem atributos: nada de style, onclick, href...
                copia.append(limparHtml(no));
                saida.append(copia);
            } else if (!['SCRIPT', 'STYLE'].includes(no.tagName)) {
                saida.append(limparHtml(no)); // tag desconhecida: fica só o texto dentro dela
            }
        }
    });
    return saida;
}

function formatarTamanho(bytes) {
    return bytes >= 1048576 ? `${(bytes / 1048576).toFixed(1).replace('.', ',')} MB` : `${Math.max(1, Math.round(bytes / 1024))} KB`;
}

// ---------- Uma subpágina ----------
function iniciar(dialogo) {
    const tipo = dialogo.dataset.tipo;
    const form = dialogo.querySelector('[data-form]');
    const titulo = form.elements.titulo;
    const erroTitulo = dialogo.querySelector(`#${tipo}-erro-titulo`);
    const editor = dialogo.querySelector('[data-editor]');
    const descricaoHtml = dialogo.querySelector('[data-descricao-html]');
    const botoesEditor = dialogo.querySelectorAll('[data-comando]');
    const entradaArquivos = dialogo.querySelector('[data-arquivos]');
    const listaAnexos = dialogo.querySelector('[data-anexos]');
    const erroUpload = dialogo.querySelector(`#${tipo}-erro-upload`);
    const textoErroUpload = dialogo.querySelector('[data-erro-upload]');
    const prazoTipo = dialogo.querySelector('[data-prazo-tipo]');   // só atividade
    const prazo = dialogo.querySelector('[data-prazo]');
    const prazoData = form.elements.prazo_data;
    const erroPrazo = dialogo.querySelector('#atividade-erro-prazo');
    let anexos = [];

    // título obrigatório
    const erroDoTitulo = (mostrar) => {
        erroTitulo.hidden = !mostrar;
        titulo.setAttribute('aria-invalid', String(mostrar));
    };
    titulo.addEventListener('input', () => erroDoTitulo(false));

    // editor: os botões não roubam o foco (senão a seleção se perde)
    const atualizarBotoes = () => botoesEditor.forEach((botao) => {
        if (botao.hasAttribute('aria-pressed')) botao.setAttribute('aria-pressed', String(document.queryCommandState(botao.dataset.comando)));
    });
    botoesEditor.forEach((botao) => {
        botao.addEventListener('mousedown', (evento) => evento.preventDefault());
        botao.addEventListener('click', () => {
            editor.focus();
            document.execCommand(botao.dataset.comando);
            atualizarBotoes();
        });
    });
    document.addEventListener('selectionchange', () => {
        if (editor.contains(document.getSelection().anchorNode)) atualizarBotoes();
    });
    // ao colar, entra só o texto: HTML de outros sites não vem junto
    editor.addEventListener('paste', (evento) => {
        evento.preventDefault();
        document.execCommand('insertText', false, evento.clipboardData.getData('text/plain'));
    });

    // data de entrega (só atividade)
    if (prazoTipo) {
        prazoTipo.addEventListener('change', () => {
            prazo.hidden = prazoTipo.value !== 'definir';
            if (!prazo.hidden) prazoData.focus();
            erroPrazo.hidden = true;
        });
        prazoData.addEventListener('input', () => { erroPrazo.hidden = true; prazoData.removeAttribute('aria-invalid'); });
    }

    // anexos (só upload de arquivos)
    const mostrarErroUpload = (texto) => {
        textoErroUpload.textContent = texto;
        erroUpload.hidden = !texto;
    };
    const sincronizarArquivos = () => {
        const lista = new DataTransfer();
        anexos.forEach((arquivo) => lista.items.add(arquivo));
        entradaArquivos.files = lista.files; // o formulário envia exatamente o que está na lista
    };
    const desenharAnexos = () => {
        listaAnexos.replaceChildren(...anexos.map((arquivo, posicao) => {
            const item = document.createElement('li');
            item.innerHTML = `<span class="material-symbols-outlined" aria-hidden="true">attach_file</span>
                <span class="anexos__nome"></span>
                <button class="botao-icone" type="button"><span class="material-symbols-outlined" aria-hidden="true">close</span></button>`;
            item.querySelector('.anexos__nome').textContent = `${arquivo.name} · ${formatarTamanho(arquivo.size)}`; // texto, nunca HTML
            const remover = item.querySelector('button');
            remover.setAttribute('aria-label', `Remover ${arquivo.name}`);
            remover.addEventListener('click', () => {
                anexos.splice(posicao, 1);
                sincronizarArquivos();
                desenharAnexos();
                mostrarErroUpload('');
            });
            return item;
        }));
    };
    dialogo.querySelector('[data-escolher-arquivo]').addEventListener('click', () => entradaArquivos.click());
    entradaArquivos.addEventListener('change', () => {
        let aviso = '';
        [...entradaArquivos.files].forEach((arquivo) => {
            if (arquivo.size > MAX_BYTES) aviso = `“${arquivo.name}” passa de 20 MB e não foi anexado.`;
            else if (anexos.length >= MAX_ARQUIVOS) aviso = `Máximo de ${MAX_ARQUIVOS} arquivos por envio.`;
            else if (!anexos.some((a) => a.name === arquivo.name && a.size === arquivo.size)) anexos.push(arquivo);
        });
        sincronizarArquivos();
        desenharAnexos();
        mostrarErroUpload(aviso);
    });

    // cancelar (×, Esc): pergunta antes de perder o que foi digitado
    const temConteudo = () => titulo.value.trim() !== '' || editor.textContent.trim() !== '' || anexos.length > 0
        || (prazoTipo && prazoTipo.value === 'definir' && prazoData.value !== '');
    const cancelar = () => {
        if (!temConteudo()) { dialogo.close(); return; }
        dialogoDescartar.querySelector('[data-descartar-tipo]').textContent = TEXTOS[tipo].descartar;
        dialogoDescartar.dataset.origem = tipo;
        dialogoDescartar.showModal();
    };
    dialogo.querySelector('[data-cancelar]').addEventListener('click', cancelar);
    dialogo.addEventListener('cancel', (evento) => { evento.preventDefault(); cancelar(); }); // Esc

    // limpa tudo quando fecha (cancelou ou descartou). Editando, volta para a lista sem o ?editar= na URL.
    dialogo.addEventListener('close', () => {
        if (dialogo.hasAttribute('data-editar')) { location.replace(dialogo.dataset.voltar); return; }
        form.reset();
        editor.replaceChildren();
        anexos = [];
        sincronizarArquivos();
        desenharAnexos();
        mostrarErroUpload('');
        erroDoTitulo(false);
        if (prazoTipo) { prazo.hidden = true; erroPrazo.hidden = true; prazoData.removeAttribute('aria-invalid'); }
        atualizarBotoes();
    });

    // postar / atribuir
    form.addEventListener('submit', (evento) => {
        if (!titulo.value.trim()) { evento.preventDefault(); erroDoTitulo(true); titulo.focus(); return; }
        const comPrazo = prazoTipo && prazoTipo.value === 'definir';
        if (comPrazo && !prazoData.value) {
            evento.preventDefault();
            erroPrazo.hidden = false;
            prazoData.setAttribute('aria-invalid', 'true');
            prazoData.focus();
            return;
        }
        const pacote = document.createElement('div');
        pacote.append(limparHtml(editor));
        descricaoHtml.value = pacote.innerHTML; // o back-end limpa de novo (lista de tags permitidas)
        // segue o envio normal do formulário (com os arquivos da lista)
    });
}

// ---------- Abrir ----------
const dialogos = {};
document.querySelectorAll('.subpagina').forEach((dialogo) => {
    dialogos[dialogo.dataset.tipo] = dialogo;
    iniciar(dialogo);
});

document.addEventListener('click', (evento) => {
    const botao = evento.target.closest('[data-abrir-subpagina]');
    if (!botao) return;
    botao.closest('.menu-suspenso').open = false;
    dialogos[botao.dataset.abrirSubpagina].showModal();
});

// Descartar fecha a subpágina; "Continuar editando" só fecha a pergunta.
dialogoDescartar.addEventListener('submit', (evento) => {
    if (evento.submitter.value === 'descartar') dialogos[dialogoDescartar.dataset.origem].close();
});

// ?criar=atividade|material abre a subpágina direto; ?editar=<id> já vem com a subpágina preenchida (data-editar).
const pedido = new URLSearchParams(location.search).get('criar');
const editando = document.querySelector('.subpagina[data-editar]');
if (editando) {
    editando.showModal();
} else if (dialogos[pedido]) {
    const params = new URLSearchParams(location.search);
    params.delete('criar');
    history.replaceState(null, '', location.pathname + '?' + params);
    dialogos[pedido].showModal();
}
