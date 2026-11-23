<?php
declare(strict_types=1);

function turma_ou_falha(int $id): array {
    return um('SELECT * FROM turmas WHERE id = ?', [$id]) ?? throw new Falha('Turma não encontrada.', 404);
}

function papel_na_turma(int $turmaId, int $usuarioId): ?string {
    return um('SELECT papel FROM participacoes WHERE turma_id = ? AND usuario_id = ?', [$turmaId, $usuarioId])['papel'] ?? null;
}

/** Exige que o usuário seja membro da turma (e, opcionalmente, professor). Devolve o papel. */
function exigir_membro(int $turmaId, array $u, bool $soProfessor = false): string {
    $papel = papel_na_turma($turmaId, (int) $u['id']);
    if ($papel === null) throw new Falha('Você não participa desta turma.', 403);
    if ($soProfessor && $papel !== 'professor') throw new Falha('Só professores podem fazer isso.', 403);
    return $papel;
}

function exigir_criador(array $turma, array $u): void {
    if ((int) $turma['criador_id'] !== (int) $u['id']) throw new Falha('Só o administrador da turma pode fazer isso.', 403);
}

function publicacao_ou_falha(int $id, ?string $tipo = null): array {
    $p = um('SELECT * FROM publicacoes WHERE id = ?', [$id]);
    if (!$p || ($tipo !== null && $p['tipo'] !== $tipo)) throw new Falha('Item não encontrado.', 404);
    return $p;
}
