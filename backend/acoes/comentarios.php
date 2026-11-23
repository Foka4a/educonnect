<?php
declare(strict_types=1);

use EduConnect\DAO\ComentarioDAO;
use EduConnect\DAO\EntregaDAO;
use EduConnect\Models\Comentario;
use EduConnect\Models\Participacao;
use EduConnect\Models\Publicacao;

/**
 * Comentário público (material ou atividade) ou particular (atividade, entre o aluno e os professores).
 * Campos: publicacao_id (ou entrega_id, que o modal "Entrega do aluno" envia), texto, particular, aluno_id (professor, no particular).
 */
function acao_comentar(): never {
    $u = exigir_login();
    $texto = texto('texto', 1000, true, 'O comentário');
    $alunoId = null;
    if (($_POST['entrega_id'] ?? '') !== '') {
        $e = (new EntregaDAO())->buscarPorId(inteiro('entrega_id', 'Entrega')) ?? throw new Falha('Entrega não encontrada.', 404);
        $publicacaoId = $e->atividadeId;
        $alunoId = $e->alunoId;
        $_POST['particular'] = '1';
    } else {
        $publicacaoId = inteiro('publicacao_id', 'Item');
    }
    $p = publicacao_ou_falha($publicacaoId);
    $papel = exigir_membro($p->turmaId, $u);
    $particular = in_array($_POST['particular'] ?? '0', ['1', 'true', 'on'], true);

    if ($particular) {
        if ($p->tipo !== Publicacao::ATIVIDADE) throw new Falha('Comentário particular só existe em atividades.');
        if ($papel === Participacao::ALUNO) {
            $alunoId = $u->id;                                           // aluno só conversa em particular consigo mesmo
        } else {
            $alunoId ??= inteiro('aluno_id', 'Aluno');
            if (papel_na_turma($p->turmaId, $alunoId) !== Participacao::ALUNO) throw new Falha('Aluno não encontrado nesta turma.', 404);
        }
    }
    $comentarios = new ComentarioDAO();
    $c = new Comentario(publicacaoId: $p->id, autorId: $u->id, alunoId: $alunoId, texto: $texto, particular: $particular);
    $comentarios->inserir($c);
    $c->autor = $u->nome;
    sucesso($particular ? 'Comentário particular enviado.' : 'Comentário publicado.', null, ['comentario' => $c->paraArray()]);
}

function comentario_ou_falha(int $id): Comentario {
    return (new ComentarioDAO())->buscarPorId($id) ?? throw new Falha('Comentário não encontrado.', 404);
}

function acao_editar_comentario(): never {
    $u = exigir_login();
    $c = comentario_ou_falha(inteiro('comentario_id', 'Comentário'));
    if ($c->autorId !== $u->id) throw new Falha('Só o autor pode editar o comentário.', 403);
    exigir_membro($c->turmaId, $u);
    $c->texto = texto('texto', 1000, true, 'O comentário');
    (new ComentarioDAO())->atualizarTexto($c);
    sucesso('Comentário atualizado.');
}

function acao_excluir_comentario(): never {
    $u = exigir_login();
    $c = comentario_ou_falha(inteiro('comentario_id', 'Comentário'));
    $papel = exigir_membro($c->turmaId, $u);
    if ($c->autorId !== $u->id && $papel !== Participacao::PROFESSOR) throw new Falha('Você não pode excluir este comentário.', 403);
    (new ComentarioDAO())->excluir($c->id);
    sucesso('Comentário excluído.');
}
