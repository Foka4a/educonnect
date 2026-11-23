<?php
declare(strict_types=1);

function dados_do_comentario(array $c): array {
    return ['id' => (int) $c['id'], 'autor' => $c['autor'] ?? '', 'texto' => $c['texto'], 'particular' => (bool) $c['particular'],
            'hora' => date('H:i', strtotime($c['criado_em']))];
}

/**
 * Comentário público (material ou atividade) ou particular (atividade, entre o aluno e os professores).
 * Campos: publicacao_id (ou entrega_id, que o modal "Entrega do aluno" envia), texto, particular, aluno_id (professor, no particular).
 */
function acao_comentar(): never {
    $u = exigir_login();
    $texto = texto('texto', 1000, true, 'O comentário');
    $alunoId = null;
    if (($_POST['entrega_id'] ?? '') !== '') {
        $e = um('SELECT atividade_id, aluno_id FROM entregas WHERE id = ?', [inteiro('entrega_id', 'Entrega')]) ?? throw new Falha('Entrega não encontrada.', 404);
        $publicacaoId = (int) $e['atividade_id'];
        $alunoId = (int) $e['aluno_id'];
        $_POST['particular'] = '1';
    } else {
        $publicacaoId = inteiro('publicacao_id', 'Item');
    }
    $p = publicacao_ou_falha($publicacaoId);
    $papel = exigir_membro((int) $p['turma_id'], $u);
    $particular = in_array($_POST['particular'] ?? '0', ['1', 'true', 'on'], true);

    if ($particular) {
        if ($p['tipo'] !== 'atividade') throw new Falha('Comentário particular só existe em atividades.');
        if ($papel === 'aluno') {
            $alunoId = (int) $u['id'];                                   // aluno só conversa em particular consigo mesmo
        } else {
            $alunoId ??= inteiro('aluno_id', 'Aluno');
            if (papel_na_turma((int) $p['turma_id'], $alunoId) !== 'aluno') throw new Falha('Aluno não encontrado nesta turma.', 404);
        }
    }
    $id = executar('INSERT INTO comentarios (publicacao_id, autor_id, aluno_id, texto, particular, criado_em) VALUES (?,?,?,?,?,?)',
        [$p['id'], $u['id'], $alunoId, $texto, $particular ? 1 : 0, agora()]);
    $c = um('SELECT c.*, u.nome AS autor FROM comentarios c JOIN usuarios u ON u.id = c.autor_id WHERE c.id = ?', [$id]);
    sucesso($particular ? 'Comentário particular enviado.' : 'Comentário publicado.', null, ['comentario' => dados_do_comentario($c)]);
}

function comentario_ou_falha(int $id): array {
    return um('SELECT c.*, p.turma_id FROM comentarios c JOIN publicacoes p ON p.id = c.publicacao_id WHERE c.id = ?', [$id])
        ?? throw new Falha('Comentário não encontrado.', 404);
}

function acao_editar_comentario(): never {
    $u = exigir_login();
    $c = comentario_ou_falha(inteiro('comentario_id', 'Comentário'));
    if ((int) $c['autor_id'] !== (int) $u['id']) throw new Falha('Só o autor pode editar o comentário.', 403);
    exigir_membro((int) $c['turma_id'], $u);
    executar('UPDATE comentarios SET texto = ?, editado_em = ? WHERE id = ?', [texto('texto', 1000, true, 'O comentário'), agora(), $c['id']]);
    sucesso('Comentário atualizado.');
}

function acao_excluir_comentario(): never {
    $u = exigir_login();
    $c = comentario_ou_falha(inteiro('comentario_id', 'Comentário'));
    $papel = exigir_membro((int) $c['turma_id'], $u);
    if ((int) $c['autor_id'] !== (int) $u['id'] && $papel !== 'professor') throw new Falha('Você não pode excluir este comentário.', 403);
    executar('DELETE FROM comentarios WHERE id = ?', [$c['id']]);
    sucesso('Comentário excluído.');
}
