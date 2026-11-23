<?php
declare(strict_types=1);

/** Modal "Enviar trabalho" (aluno): botão `acao` = entregar | cancelar. */
function acao_enviar_trabalho(): never {
    $u = exigir_login();
    $atividade = publicacao_ou_falha(inteiro('atividade_id', 'Atividade'), 'atividade');
    $papel = exigir_membro((int) $atividade['turma_id'], $u);
    if ($papel !== 'aluno') throw new Falha('Só alunos enviam trabalhos.', 403);
    $acao = (string) ($_POST['acao'] ?? 'entregar');
    $entrega = um('SELECT * FROM entregas WHERE atividade_id = ? AND aluno_id = ?', [$atividade['id'], $u['id']]);
    if ($entrega && $entrega['nota'] !== null) throw new Falha('Este trabalho já foi corrigido e não pode ser alterado.', 409);

    if ($acao === 'cancelar') {
        if (!$entrega || $entrega['status'] !== 'entregue') throw new Falha('Não há envio para cancelar.', 409);
        db()->beginTransaction();
        apagar_anexos_de('entrega_id', (int) $entrega['id']);
        executar('DELETE FROM anexos WHERE entrega_id = ?', [$entrega['id']]);
        executar("UPDATE entregas SET status = 'cancelada' WHERE id = ?", [$entrega['id']]);
        executar('INSERT INTO entrega_historico (entrega_id, evento, em) VALUES (?,?,?)', [$entrega['id'], 'cancelado', agora()]);
        db()->commit();
        sucesso('Envio cancelado.');
    }
    if ($acao !== 'entregar') throw new Falha('Ação inválida.');

    $arquivos = arquivos_enviados('arquivos');
    if (!$arquivos) throw new Falha('Escolha ao menos um arquivo.');
    validar_arquivos($arquivos, config('max_arquivos_entrega'));
    $atrasada = $atividade['entrega_em'] !== null && agora() > $atividade['entrega_em'] ? 1 : 0;

    db()->beginTransaction();
    if ($entrega) {
        apagar_anexos_de('entrega_id', (int) $entrega['id']);
        executar('DELETE FROM anexos WHERE entrega_id = ?', [$entrega['id']]);
        executar("UPDATE entregas SET status = 'entregue', atrasada = ?, enviado_em = ? WHERE id = ?", [$atrasada, agora(), $entrega['id']]);
        $id = (int) $entrega['id'];
        $evento = $entrega['status'] === 'entregue' ? 'reenviado' : 'enviado';
    } else {
        $id = executar('INSERT INTO entregas (atividade_id, aluno_id, status, atrasada, enviado_em) VALUES (?,?,?,?,?)',
            [$atividade['id'], $u['id'], 'entregue', $atrasada, agora()]);
        $evento = 'enviado';
    }
    executar('INSERT INTO entrega_historico (entrega_id, evento, em) VALUES (?,?,?)', [$id, $evento, agora()]);
    guardar_anexos($arquivos, null, $id);
    db()->commit();
    sucesso($atrasada ? 'Trabalho entregue com atraso.' : 'Trabalho entregue.', null, ['entrega_id' => $id, 'atrasada' => (bool) $atrasada]);
}

/** Modal "Entrega do aluno" (professor): nota de 0 a 100 ou vazio ("Sem nota"). */
function acao_salvar_nota(): never {
    $u = exigir_login();
    $entrega = um('SELECT e.*, p.turma_id FROM entregas e JOIN publicacoes p ON p.id = e.atividade_id WHERE e.id = ?', [inteiro('entrega_id', 'Entrega')])
        ?? throw new Falha('Entrega não encontrada.', 404);
    exigir_membro((int) $entrega['turma_id'], $u, true);
    if ($entrega['status'] !== 'entregue') throw new Falha('O aluno cancelou esta entrega.', 409);
    $bruta = trim((string) ($_POST['nota'] ?? ''));
    if ($bruta === '') {
        $nota = null;
    } elseif (ctype_digit($bruta) && (int) $bruta <= 100) {
        $nota = (int) $bruta;
    } else {
        throw new Falha('Use um número de 0 a 100.');
    }
    executar('UPDATE entregas SET nota = ? WHERE id = ?', [$nota, $entrega['id']]);
    sucesso($nota === null ? 'Sem nota.' : 'Nota salva.', null, ['nota' => $nota]);
}
