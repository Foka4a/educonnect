<?php
declare(strict_types=1);

use EduConnect\DAO\ParticipacaoDAO;
use EduConnect\Models\Participacao;

function acao_promover_professor(): never {
    $u = exigir_login();
    $turma = turma_ou_falha(inteiro('turma_id', 'Turma'));
    exigir_criador($turma, $u);
    $alvo = inteiro('pessoa_id', 'Pessoa');
    $participacoes = new ParticipacaoDAO();
    $papel = $participacoes->papel($turma->id, $alvo) ?? throw new Falha('Essa pessoa não participa da turma.', 404);
    if ($papel === Participacao::PROFESSOR) throw new Falha('Essa pessoa já é professora.', 409);
    $participacoes->alterarPapel($turma->id, $alvo, Participacao::PROFESSOR);
    sucesso('Pessoa promovida a professor.');
}

function acao_remover_pessoa(): never {
    $u = exigir_login();
    $turma = turma_ou_falha(inteiro('turma_id', 'Turma'));
    exigir_membro($turma->id, $u, true);
    $alvo = inteiro('pessoa_id', 'Pessoa');
    $participacoes = new ParticipacaoDAO();
    $papel = $participacoes->papel($turma->id, $alvo) ?? throw new Falha('Essa pessoa não participa da turma.', 404);
    if ($alvo === $turma->criadorId) throw new Falha('O administrador não pode ser removido.', 403);
    if ($alvo === $u->id) throw new Falha('Para você sair, use “Sair da turma”.', 409);
    if ($papel === Participacao::PROFESSOR) exigir_criador($turma, $u); // só o administrador remove professores
    $participacoes->excluir($turma->id, $alvo);
    sucesso('Pessoa removida da turma.');
}
