// Instruções da atividade: comentários da turma e "Excluir" do menu ⋮.
// Menu ⋮, fundo do diálogo e avisar() vêm do layout.js.
const dialogoExcluir = document.getElementById('dialogo-excluir');
const novoComentario = document.querySelector('.comentarios-turma__novo');

document.querySelector('[data-excluir]').addEventListener('click', (evento) => {
    evento.target.closest('.menu-suspenso').open = false;
    dialogoExcluir.showModal();
});

// ponytail: exclusão e comentário só mudam a tela; trocar por envio ao back-end quando ele existir.
dialogoExcluir.addEventListener('submit', (evento) => {
    if (evento.submitter.value === 'excluir') location.href = 'atividades.html';
});

// Abriu "Adicionar comentário" → foco direto no campo.
novoComentario.addEventListener('toggle', () => {
    if (novoComentario.open) novoComentario.querySelector('input').focus();
});

document.querySelector('[data-form-comentario]').addEventListener('submit', (evento) => {
    evento.preventDefault();
    const campo = evento.target.comentario;
    const item = document.createElement('li');
    const hora = new Date().toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
    item.innerHTML = `<span class="comentarios-turma__avatar" aria-hidden="true">G</span><div><strong>Gabriel Pereira</strong> • ${hora}<p></p></div>`;
    item.querySelector('p').textContent = campo.value; // texto digitado nunca vira HTML
    document.querySelector('[data-comentarios]').append(item);
    campo.value = '';
    novoComentario.open = false;
    avisar('Comentário publicado.');
});
