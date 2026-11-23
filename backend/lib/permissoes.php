<?php
declare(strict_types=1);

use EduConnect\DAO\ParticipacaoDAO;
use EduConnect\DAO\PublicacaoDAO;
use EduConnect\DAO\TurmaDAO;
use EduConnect\Models\Participacao;
use EduConnect\Models\Publicacao;
use EduConnect\Models\Turma;
use EduConnect\Models\Usuario;

function turma_ou_falha(int $id): Turma {
    return (new TurmaDAO())->buscarPorId($id) ?? throw new Falha('Turma não encontrada.', 404);
}

function papel_na_turma(int $turmaId, int $usuarioId): ?string {
    return (new ParticipacaoDAO())->papel($turmaId, $usuarioId);
}

/** Exige que o usuário seja membro da turma (e, opcionalmente, professor). Devolve o papel. */
function exigir_membro(int $turmaId, Usuario $u, bool $soProfessor = false): string {
    $papel = papel_na_turma($turmaId, $u->id);
    if ($papel === null) throw new Falha('Você não participa desta turma.', 403);
    if ($soProfessor && $papel !== Participacao::PROFESSOR) throw new Falha('Só professores podem fazer isso.', 403);
    return $papel;
}

function exigir_criador(Turma $turma, Usuario $u): void {
    if (!$turma->ehAdministrador($u)) throw new Falha('Só o administrador da turma pode fazer isso.', 403);
}

function publicacao_ou_falha(int $id, ?string $tipo = null): Publicacao {
    $p = (new PublicacaoDAO())->buscarPorId($id);
    if (!$p || ($tipo !== null && $p->tipo !== $tipo)) throw new Falha('Item não encontrado.', 404);
    return $p;
}
