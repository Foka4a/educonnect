// Instruções da atividade: "Excluir" do menu ⋮ (só professor) e foco no campo de comentário.
// Os dados vêm do servidor (instrucoes.php); comentar, entregar e excluir são formulários que postam para o back-end.
// Menu ⋮, fundo do diálogo e avisar() vêm do layout.js.
const dialogoExcluir = document.getElementById('dialogo-excluir');
const novoComentario = document.querySelector('.comentarios-turma__novo');

document.querySelector('[data-excluir]')?.addEventListener('click', (evento) => {
    evento.target.closest('.menu-suspenso').open = false;
    dialogoExcluir.showModal();
});

// Abriu "Adicionar comentário" → foco direto no campo.
novoComentario.addEventListener('toggle', () => {
    if (novoComentario.open) novoComentario.querySelector('input:not([type="hidden"])').focus();
});
