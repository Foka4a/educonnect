// Trabalhos dos estudantes: filtro Entregues/Pendentes e modal "Entrega do aluno".
// A lista vem do servidor (trabalho-estudantes.php). Nota e comentário particular vão por fetch() para a própria página
// (window.educonnect.enviar, do backend.js), que responde JSON, sem recarregar.
// Fundo do diálogo (fechar ao clicar fora) e avisar() vêm do layout.js.
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

// O HTML das listas escondidas foi montado (e escapado) pelo servidor; aqui só é copiado para o modal.
function mostrarComentarios() {
    const comentarios = estudanteAberto.querySelector('[data-comentarios]');
    const total = comentarios.children.length;
    listaComentarios.innerHTML = comentarios.innerHTML;
    dialogoEntrega.querySelector('[data-comentarios-total]').textContent =
        `${total} ${total === 1 ? 'comentário particular' : 'comentários particulares'}`;
}

// Salva a nota (0 a 100 ou vazio = sem nota). Deu certo: a nota da lista e a do modal ficam iguais.
function salvarNota(estudante, campo) {
    if (!campo.checkValidity()) { campo.reportValidity(); return; }
    window.educonnect.enviar('salvar-nota', { entrega_id: estudante.dataset.entregaId, nota: campo.value }).then((r) => {
        avisar(r.ok ? r.mensagem : r.erro);
        if (!r.ok) return;
        estudante.querySelector('[data-nota]').value = campo.value;
        if (estudante === estudanteAberto) notaModal.value = campo.value;
    });
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
        const { nome, entregueEm } = estudanteAberto.dataset;
        dialogoEntrega.querySelector('#entrega-nome').textContent = nome;
        dialogoEntrega.querySelector('[data-entregue-em]').textContent = entregueEm;
        dialogoEntrega.querySelector('[data-arquivos-modal]').innerHTML = estudanteAberto.querySelector('[data-arquivos]').innerHTML;
        dialogoEntrega.querySelector('.entrega__historico').open = false;
        notaModal.value = estudanteAberto.querySelector('[data-nota]').value;
        notaModal.setAttribute('aria-label', `Nota de ${nome}, de 0 a 100`);
        mostrarComentarios();
        dialogoEntrega.showModal();
    }
});

// Nota na lista ou no modal: "salva ao sair do campo", como no mapa.
document.querySelectorAll('.estudante [data-nota]').forEach((campo) => {
    campo.addEventListener('change', () => salvarNota(campo.closest('.estudante'), campo));
});
notaModal.addEventListener('change', () => salvarNota(estudanteAberto, notaModal));

dialogoEntrega.querySelector('[data-form-comentario]').addEventListener('submit', (evento) => {
    evento.preventDefault();
    const campo = evento.target.comentario;
    const texto = campo.value.trim();
    if (!texto) return;
    const estudante = estudanteAberto;
    window.educonnect.enviar('comentar', { entrega_id: estudante.dataset.entregaId, texto }).then((r) => {
        if (!r.ok) { avisar(r.erro); return; }
        const item = document.createElement('li');
        const autor = document.createElement('strong');
        const corpo = document.createElement('p');
        autor.textContent = r.comentario.autor; // textContent: texto digitado nunca vira HTML
        corpo.textContent = r.comentario.texto;
        item.append(autor, ` • ${r.comentario.hora}`, corpo);
        estudante.querySelector('[data-comentarios]').append(item);
        estudante.querySelector('[data-previa]').textContent = `"${texto.slice(0, 40)}"`;
        campo.value = '';
        if (estudante === estudanteAberto) mostrarComentarios();
    });
});
