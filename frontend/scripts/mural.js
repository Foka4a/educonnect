// Mural da turma: copiar o código, código em tela cheia, "Novo aviso" e confirmação do "Excluir".
// Os dados vêm do servidor (mural.php); redefinir código, postar aviso e excluir são formulários que postam para o back-end.
// Menus ⋮, fundo dos diálogos e avisar() vêm do layout.js.
const novoAviso = document.querySelector('.novo-aviso');
const formAviso = document.querySelector('[data-form-aviso]');
const dialogoCodigo = document.getElementById('dialogo-codigo');
const dialogoExcluir = document.getElementById('dialogo-excluir-publicacao');

function codigoAtual() {
    return document.querySelector('[data-codigo]')?.textContent.trim() ?? '';
}

// ---------- Novo aviso (só professor) ----------

if (novoAviso) {
    novoAviso.addEventListener('toggle', () => {
        if (novoAviso.open) formAviso.texto.focus();
    });

    document.querySelector('[data-cancelar-aviso]').addEventListener('click', () => {
        formAviso.reset();
        novoAviso.open = false;
    });

    // Só espaços não conta como aviso.
    formAviso.addEventListener('submit', (evento) => {
        if (formAviso.texto.value.trim()) return;
        evento.preventDefault();
        formAviso.texto.value = '';
        formAviso.texto.reportValidity();
    });
}

// ---------- Cliques: código e excluir ----------

document.addEventListener('click', (evento) => {
    const alvo = evento.target;

    if (alvo.closest('[data-copiar-codigo]')) {
        const codigo = codigoAtual();
        Promise.resolve()
            .then(() => navigator.clipboard.writeText(codigo))
            .then(() => avisar(`Código ${codigo} copiado.`))
            .catch(() => avisar(`Não foi possível copiar. O código é ${codigo}.`));
    }

    if (alvo.closest('[data-codigo-tela-cheia]')) dialogoCodigo.showModal();

    // O item diz qual ação pedir à página (excluir-aviso, excluir-atividade ou excluir-material) e com qual campo de id.
    if (alvo.closest('[data-excluir-item]')) {
        const item = alvo.closest('.item-mural');
        const form = dialogoExcluir.querySelector('form');
        const campo = form.querySelector('[data-excluir-id]');
        form.querySelector('[data-excluir-acao]').value = item.dataset.acaoExcluir;
        campo.name = item.dataset.campo;
        campo.value = item.dataset.id;
        dialogoExcluir.querySelector('[data-excluir-titulo]').textContent = item.dataset.titulo;
        dialogoExcluir.showModal();
    }
});
