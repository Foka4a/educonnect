// Aba Atividades: subpáginas "Criar atividade" e "Criar material" (<dialog> em tela cheia).
// Menus, fundo do diálogo e avisar() vêm do layout.js.
const MAX_ARQUIVOS = 10;
const MAX_BYTES = 20 * 1024 * 1024;
const dialogoDescartar = document.getElementById('dialogo-descartar');
const listaAtividades = document.querySelector('.lista-atividades');

const TEXTOS = {
    atividade: { descartar: 'atividade', aviso: 'Atividade atribuída.' },
    material: { descartar: 'material', aviso: 'Material publicado.' },
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

function dataBr(iso) { // 2026-11-20 → 20/11/2026
    const [ano, mes, dia] = iso.split('-');
    return `${dia}/${mes}/${ano}`;
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

    // limpa tudo quando fecha (publicou, cancelou ou descartou)
    dialogo.addEventListener('close', () => {
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
        evento.preventDefault();
        if (!titulo.value.trim()) { erroDoTitulo(true); titulo.focus(); return; }
        const comPrazo = prazoTipo && prazoTipo.value === 'definir';
        if (comPrazo && !prazoData.value) {
            erroPrazo.hidden = false;
            prazoData.setAttribute('aria-invalid', 'true');
            prazoData.focus();
            return;
        }
        const descricao = limparHtml(editor);
        const pacote = document.createElement('div');
        pacote.append(descricao.cloneNode(true));
        descricaoHtml.value = pacote.innerHTML; // para o envio ao back-end (que também deve sanitizar)
        // ponytail: publicar só muda a tela; trocar por envio ao back-end quando ele existir.
        adicionarNaLista({ tipo, titulo: titulo.value.trim(), descricao, data: comPrazo ? prazoData.value : '' });
        dialogo.close();
        avisar(TEXTOS[tipo].aviso);
    });
}

// ---------- Mostra o item novo no topo da lista (mesmo HTML dos itens existentes) ----------
function adicionarNaLista({ tipo, titulo, descricao, data }) {
    const ehAtividade = tipo === 'atividade';
    const hoje = new Date().toLocaleDateString('pt-BR');
    const rotuloPrazo = data ? `Entrega: ${dataBr(data).slice(0, 5)}` : 'Sem data de entrega';
    const linhaData = ehAtividade ? (data ? `Data de entrega: ${dataBr(data)}` : 'Sem data de entrega') : `Publicado em ${hoje}`;
    const item = document.createElement('li');
    item.className = 'item-atividade';
    item.id = `item-${Date.now()}`;
    item.dataset.tipo = tipo;
    item.dataset.titulo = titulo;
    item.innerHTML = `
        <details class="item-atividade__detalhes" name="item-atividade" open>
            <summary class="item-atividade__resumo">
                <span class="item-atividade__icone material-symbols-outlined" aria-hidden="true">${ehAtividade ? 'assignment' : 'book'}</span>
                <span class="item-atividade__titulo"></span>
                ${ehAtividade ? '<span class="item-atividade__prazo"></span>' : ''}
            </summary>
            <div class="item-atividade__conteudo">
                <div class="item-atividade__texto">
                    <p class="item-atividade__data"></p>
                    <div data-descricao></div>
                </div>
                ${ehAtividade ? `<dl class="item-atividade__contadores">
                    <div><dt>Entregues</dt><dd>0</dd></div>
                    <div><dt>Pendentes</dt><dd>0</dd></div>
                </dl>` : ''}
                <a class="item-atividade__link" href="${ehAtividade ? 'instrucoes.html' : '#'}">${ehAtividade ? 'Ver instruções' : 'Ver material'}</a>
            </div>
        </details>
        <details class="menu-suspenso item-atividade__menu">
            <summary class="botao-icone" title="Mais opções">
                <span class="material-symbols-outlined" aria-hidden="true">more_vert</span>
            </summary>
            <ul class="menu-suspenso__lista">
                <li><a class="menu-suspenso__item" href="criar-${tipo}.html">Editar</a></li>
                <li><button class="menu-suspenso__item" type="button" data-acao="excluir">Excluir</button></li>
                <li><button class="menu-suspenso__item" type="button" data-acao="copiar-link">Copiar link</button></li>
            </ul>
        </details>`;
    // textos digitados entram como texto; a descrição passou pelo limparHtml
    item.querySelector('.item-atividade__titulo').textContent = titulo;
    if (ehAtividade) item.querySelector('.item-atividade__prazo').textContent = rotuloPrazo;
    item.querySelector('.item-atividade__data').textContent = linhaData;
    item.querySelector('[data-descricao]').append(descricao);
    item.querySelector('.item-atividade__menu summary').setAttribute('aria-label', `Mais opções para ${titulo}`);
    listaAtividades.prepend(item);
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

// criar-atividade.html e criar-material.html redirecionam para cá (?criar=atividade|material).
const pedido = new URLSearchParams(location.search).get('criar');
if (dialogos[pedido]) {
    history.replaceState(null, '', location.pathname);
    dialogos[pedido].showModal();
}
