// Início: menu ⋮ dos cards. "Excluir turma" (administrador) e "Sair da turma" (demais) abrem a confirmação
// com o id e o nome da turma do card. Menus ⋮, fundo do diálogo e avisar() vêm do layout.js.
document.addEventListener('click', (evento) => {
    const item = evento.target.closest('[data-confirmar]');
    if (!item) return;
    const card = item.closest('.card');
    const dialogo = document.getElementById(item.dataset.confirmar);
    item.closest('.menu-suspenso').open = false;
    dialogo.querySelector('[data-turma-id]').value = card.dataset.turmaId;
    dialogo.querySelector('[data-turma-nome]').textContent = card.dataset.nome;
    dialogo.showModal();
});
