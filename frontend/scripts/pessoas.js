// Aba Pessoas: remover estudante da turma e promover estudante a professor.
// Menus ⋮, fundo dos diálogos e avisar() vêm do layout.js.
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
    const remover = evento.target.closest('[data-remover]');
    if (remover) {
        estudanteParaRemover = remover.closest('.pessoa');
        remover.closest('.menu-suspenso').open = false;
        dialogoRemover.querySelector('[data-remover-nome]').textContent = estudanteParaRemover.dataset.nome;
        dialogoRemover.showModal();
    }
});

// ponytail: a remoção só muda a tela; trocar por envio ao back-end quando ele existir.
// "submit" (e não "close") diz na hora qual botão foi usado; Esc e clique no fundo não disparam.
dialogoRemover.addEventListener('submit', (evento) => {
    if (evento.submitter.value !== 'remover') return;
    estudanteParaRemover.remove();
    avisar(`${estudanteParaRemover.dataset.nome} foi removido da turma.`);
    estudanteParaRemover = null;
    atualizarEstudantes();
});

// Promover a professor: o modal (modais/components/promover-professor.js) confirma e avisa por evento.
// ponytail: só muda a tela; trocar por envio ao back-end quando ele existir.
document.addEventListener('promover-professor', (evento) => {
    const { pessoa, nome } = evento.detail;
    pessoa.querySelector('.pessoa__menu').remove();
    document.querySelector('[data-lista-professores]').append(pessoa);
    avisar(`${nome} agora está entre os professores da turma.`);
    atualizarEstudantes();
});
