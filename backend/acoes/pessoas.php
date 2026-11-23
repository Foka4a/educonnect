<?php
declare(strict_types=1);

function acao_promover_professor(): never {
    $u = exigir_login();
    $turma = turma_ou_falha(inteiro('turma_id', 'Turma'));
    exigir_criador($turma, $u);
    $alvo = inteiro('pessoa_id', 'Pessoa');
    $papel = papel_na_turma((int) $turma['id'], $alvo) ?? throw new Falha('Essa pessoa não participa da turma.', 404);
    if ($papel === 'professor') throw new Falha('Essa pessoa já é professora.', 409);
    executar('UPDATE participacoes SET papel = ? WHERE turma_id = ? AND usuario_id = ?', ['professor', $turma['id'], $alvo]);
    sucesso('Pessoa promovida a professor.');
}

function acao_remover_pessoa(): never {
    $u = exigir_login();
    $turma = turma_ou_falha(inteiro('turma_id', 'Turma'));
    exigir_membro((int) $turma['id'], $u, true);
    $alvo = inteiro('pessoa_id', 'Pessoa');
    $papel = papel_na_turma((int) $turma['id'], $alvo) ?? throw new Falha('Essa pessoa não participa da turma.', 404);
    if ($alvo === (int) $turma['criador_id']) throw new Falha('O administrador não pode ser removido.', 403);
    if ($alvo === (int) $u['id']) throw new Falha('Para você sair, use “Sair da turma”.', 409);
    if ($papel === 'professor') exigir_criador($turma, $u); // só o administrador remove professores
    executar('DELETE FROM participacoes WHERE turma_id = ? AND usuario_id = ?', [$turma['id'], $alvo]);
    sucesso('Pessoa removida da turma.');
}
