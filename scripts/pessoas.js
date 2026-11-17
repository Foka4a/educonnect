// Aba Pessoas: convidar professor/estudantes e remover estudante da turma.
// Menus ⋮, fundo dos diálogos e avisar() vêm do layout.js.
const dialogoConvidar = document.getElementById('dialogo-convidar');
const dialogoRemover = document.getElementById('dialogo-remover');
const listaEstudantes = document.querySelector('[data-lista-estudantes]');
let estudanteParaRemover = null;

function atualizarEstudantes() {
    const total = listaEstudantes.children.length;
    document.querySelector('[data-contador-estudantes]').textContent = `${total} ${total === 1 ? 'estudante' : 'estudantes'}`;
    document.querySelector('[data-vazio-estudantes]').hidden = total > 0;
    listaEstudantes.hidden = total === 0;
}

document.addEventListener('click', (evento) => {
    const convidar = evento.target.closest('[data-convidar]');
    if (convidar) {
        const paraEstudantes = convidar.dataset.convidar === 'estudantes';
        dialogoConvidar.querySelector('[data-convidar-tipo]').textContent = paraEstudantes ? 'estudantes' : 'professor';
        dialogoConvidar.querySelector('[data-codigo-turma]').hidden = !paraEstudantes;
        dialogoConvidar.querySelector('form').reset();
        dialogoConvidar.showModal();
    }

    const remover = evento.target.closest('[data-remover]');
    if (remover) {
        estudanteParaRemover = remover.closest('.pessoa');
        remover.closest('.menu-suspenso').open = false;
        dialogoRemover.querySelector('[data-remover-nome]').textContent = estudanteParaRemover.dataset.nome;
        dialogoRemover.showModal();
    }
});

// ponytail: convite e remoção só mudam a tela; trocar por envio ao back-end quando ele existir.
// "submit" (e não "close") diz na hora qual botão foi usado; Esc e clique no fundo não disparam.
dialogoConvidar.addEventListener('submit', (evento) => {
    if (evento.submitter.value !== 'convidar') return;
    avisar(`Convite enviado para ${dialogoConvidar.querySelector('[name="email"]').value}.`);
});

dialogoRemover.addEventListener('submit', (evento) => {
    if (evento.submitter.value !== 'remover') return;
    estudanteParaRemover.remove();
    avisar(`${estudanteParaRemover.dataset.nome} foi removido da turma.`);
    estudanteParaRemover = null;
    atualizarEstudantes();
});
