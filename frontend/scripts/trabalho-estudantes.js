// Trabalhos dos estudantes: filtro Entregues/Pendentes e modal "Entrega do aluno".
// Fundo do diálogo (fechar ao clicar fora) vem do layout.js.
const filtro = document.getElementById('filtro-entrega');
const dialogoEntrega = document.getElementById('dialogo-entrega');
const notaModal = dialogoEntrega.querySelector('[data-nota-modal]');
const listaComentarios = dialogoEntrega.querySelector('[data-comentarios-modal]');
let estudanteAberto = null;

function filtrar() {
    document.querySelectorAll('[data-grupo]').forEach((grupo) => {
        grupo.hidden = filtro.value !== 'todos' && filtro.value !== grupo.dataset.grupo;
    });
}

function mostrarComentarios() {
    const comentarios = estudanteAberto.querySelector('[data-comentarios]');
    const total = comentarios.children.length;
    listaComentarios.innerHTML = comentarios.innerHTML;
    dialogoEntrega.querySelector('[data-comentarios-total]').textContent =
        `${total} ${total === 1 ? 'comentário particular' : 'comentários particulares'}`;
}

filtro.addEventListener('change', filtrar);

document.addEventListener('click', (evento) => {
    // Contadores Entregue/Pendentes aplicam o filtro correspondente.
    const contador = evento.target.closest('[data-filtrar]');
    if (contador) {
        filtro.value = contador.dataset.filtrar;
        filtrar();
    }

    const abrir = evento.target.closest('[data-abrir-entrega]');
    if (abrir) {
        estudanteAberto = abrir.closest('.estudante');
        const { nome, arquivo, entregueEm } = estudanteAberto.dataset;
        dialogoEntrega.querySelector('#entrega-nome').textContent = nome;
        dialogoEntrega.querySelector('[data-entregue-em]').textContent = entregueEm;
        dialogoEntrega.querySelector('[data-arquivo-nome]').textContent = arquivo;
        dialogoEntrega.querySelector('[data-arquivo]').setAttribute('aria-label', `Abrir ${arquivo}, entregue por ${nome}`);
        dialogoEntrega.querySelector('.entrega__historico').open = false;
        notaModal.value = estudanteAberto.querySelector('[data-nota]').value;
        notaModal.setAttribute('aria-label', `Nota de ${nome}, de 0 a 100`);
        mostrarComentarios();
        dialogoEntrega.showModal();
    }
});

// ponytail: nota e comentário só mudam a tela; trocar por envio ao back-end quando ele existir.
// Nota do modal e da lista ficam iguais ("salva ao sair do campo", como no mapa).
notaModal.addEventListener('change', () => {
    if (notaModal.checkValidity()) estudanteAberto.querySelector('[data-nota]').value = notaModal.value;
    else notaModal.reportValidity();
});

dialogoEntrega.querySelector('[data-form-comentario]').addEventListener('submit', (evento) => {
    evento.preventDefault();
    const campo = evento.target.comentario;
    const item = document.createElement('li');
    const hora = new Date().toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
    item.innerHTML = `<strong>Gabriel Pereira</strong> • ${hora}<p></p>`;
    item.querySelector('p').textContent = campo.value; // textContent: texto digitado nunca vira HTML
    estudanteAberto.querySelector('[data-comentarios]').append(item);
    campo.value = '';
    mostrarComentarios();
});
