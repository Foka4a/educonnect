<?php
declare(strict_types=1);

/** Criar atividade/material (tipo = 'atividade' | 'material'). */
function criar_publicacao(string $tipo): never {
    $u = exigir_login();
    $turma = turma_ou_falha(inteiro('turma_id', 'Turma'));
    exigir_membro((int) $turma['id'], $u, true);
    $titulo = texto('titulo', 120, true, $tipo === 'atividade' ? 'O título da atividade' : 'O título do material');
    $descricao = limpar_html(texto('descricao', 20000));
    $entrega = $tipo === 'atividade' ? data_de_entrega() : null;
    $arquivos = arquivos_enviados('anexos');
    validar_arquivos($arquivos, config('max_arquivos_publicacao'));

    $pdo = db();
    $pdo->beginTransaction();
    $id = executar('INSERT INTO publicacoes (turma_id, tipo, titulo, descricao, entrega_em, autor_id, criado_em) VALUES (?,?,?,?,?,?,?)',
        [$turma['id'], $tipo, $titulo, $descricao, $entrega, $u['id'], agora()]);
    guardar_anexos($arquivos, $id, null);
    $pdo->commit();
    sucesso($tipo === 'atividade' ? 'Atividade atribuída.' : 'Material publicado.', pagina('atividades.html', 'turma=' . $turma['id']), ['id' => $id]);
}

function editar_publicacao(string $tipo): never {
    $u = exigir_login();
    $p = publicacao_ou_falha(inteiro($tipo . '_id', 'Item'), $tipo);
    exigir_membro((int) $p['turma_id'], $u, true);
    $titulo = texto('titulo', 120, true, $tipo === 'atividade' ? 'O título da atividade' : 'O título do material');
    $descricao = limpar_html(texto('descricao', 20000));
    $entrega = $tipo === 'atividade' ? data_de_entrega() : null;
    $novos = arquivos_enviados('anexos');
    $remover = array_filter((array) ($_POST['remover_anexos'] ?? []), 'is_string');
    $atuais = consultar('SELECT id, nome, caminho FROM anexos WHERE publicacao_id = ?', [$p['id']]);
    $paraApagar = array_values(array_filter($atuais, fn ($a) => in_array($a['nome'], $remover, true)));
    if (count($atuais) - count($paraApagar) + count($novos) > config('max_arquivos_publicacao')) {
        throw new Falha('Máximo de ' . config('max_arquivos_publicacao') . ' arquivos por item.');
    }
    validar_arquivos($novos, config('max_arquivos_publicacao'));

    $pdo = db();
    $pdo->beginTransaction();
    executar('UPDATE publicacoes SET titulo = ?, descricao = ?, entrega_em = ?, atualizado_em = ? WHERE id = ?', [$titulo, $descricao, $entrega, agora(), $p['id']]);
    foreach ($paraApagar as $a) {
        executar('DELETE FROM anexos WHERE id = ?', [$a['id']]);
        apagar_arquivo_fisico($a['caminho']);
    }
    guardar_anexos($novos, (int) $p['id'], null);
    if ($tipo === 'atividade') {
        // prazo mudou: refaz a marcação de atraso das entregas já enviadas
        executar('UPDATE entregas SET atrasada = CASE WHEN ? IS NOT NULL AND enviado_em > ? THEN 1 ELSE 0 END WHERE atividade_id = ?', [$entrega, $entrega, $p['id']]);
    }
    $pdo->commit();
    sucesso($tipo === 'atividade' ? 'Atividade atualizada.' : 'Material atualizado.');
}

function excluir_publicacao(string $tipo): never {
    $u = exigir_login();
    $p = publicacao_ou_falha(inteiro($tipo . '_id', 'Item'), $tipo);
    exigir_membro((int) $p['turma_id'], $u, true);
    apagar_anexos_da_atividade((int) $p['id']);
    executar('DELETE FROM publicacoes WHERE id = ?', [$p['id']]);
    sucesso($tipo === 'atividade' ? 'Atividade excluída.' : 'Material excluído.', pagina('atividades.html', 'turma=' . $p['turma_id']));
}

function acao_criar_atividade(): never { criar_publicacao('atividade'); }
function acao_editar_atividade(): never { editar_publicacao('atividade'); }
function acao_excluir_atividade(): never { excluir_publicacao('atividade'); }
function acao_criar_material(): never { criar_publicacao('material'); }
function acao_editar_material(): never { editar_publicacao('material'); }
function acao_excluir_material(): never { excluir_publicacao('material'); }

/** GET: baixa um anexo (de atividade/material: qualquer membro; de entrega: o aluno dono ou os professores). */
function acao_arquivo(): never {
    $u = exigir_login();
    $a = um('SELECT * FROM anexos WHERE id = ?', [inteiro('id', 'Arquivo')]) ?? throw new Falha('Arquivo não encontrado.', 404);
    if ($a['publicacao_id']) {
        $p = publicacao_ou_falha((int) $a['publicacao_id']);
        exigir_membro((int) $p['turma_id'], $u);
    } else {
        $e = um('SELECT e.aluno_id, p.turma_id FROM entregas e JOIN publicacoes p ON p.id = e.atividade_id WHERE e.id = ?', [$a['entrega_id']])
            ?? throw new Falha('Arquivo não encontrado.', 404);
        $papel = exigir_membro((int) $e['turma_id'], $u);
        if ($papel !== 'professor' && (int) $e['aluno_id'] !== (int) $u['id']) throw new Falha('Você não pode ver este arquivo.', 403);
    }
    $arq = config('uploads') . '/' . basename($a['caminho']);
    if (!is_file($arq)) throw new Falha('Arquivo não encontrado.', 404);
    header('Content-Type: application/octet-stream');
    header("Content-Disposition: attachment; filename*=UTF-8''" . rawurlencode($a['nome']));
    header('Content-Length: ' . filesize($arq));
    header('X-Content-Type-Options: nosniff');
    readfile($arq);
    exit;
}
