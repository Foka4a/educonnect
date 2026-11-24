// Aba Pessoas: confirmação do "Remover da turma". A lista vem do servidor (pessoas.php) e o formulário posta para o back-end.
// "Promover a professor" é o modal modais/components/promover-professor.js.
// Menus ⋮, fundo dos diálogos e avisar() vêm do layout.js.
const dialogoRemover = document.getElementById('dialogo-remover');

document.addEventListener('click', (evento) => {
    const remover = evento.target.closest('[data-remover]');
    if (!remover) return;
    const pessoa = remover.closest('.pessoa');
    remover.closest('.menu-suspenso').open = false;
    dialogoRemover.querySelector('[data-remover-id]').value = pessoa.dataset.pessoaId;
    dialogoRemover.querySelector('[data-remover-nome]').textContent = pessoa.dataset.nome;
    dialogoRemover.showModal();
});
