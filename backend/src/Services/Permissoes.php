<?php
declare(strict_types=1);

namespace EduConnect\Services;

use EduConnect\DAO\ParticipacaoDAO;
use EduConnect\DAO\PublicacaoDAO;
use EduConnect\DAO\TurmaDAO;
use EduConnect\Models\Participacao;
use EduConnect\Models\Publicacao;
use EduConnect\Models\Turma;
use EduConnect\Models\Usuario;
use Falha;

/** Quem pode o quê numa turma: busca o recurso (ou 404) e confere o papel do usuário (ou 403). */
final class Permissoes
{
    public static function turma(int $id): Turma
    {
        return (new TurmaDAO())->buscarPorId($id) ?? throw new Falha('Turma não encontrada.', 404);
    }

    public static function publicacao(int $id, ?string $tipo = null): Publicacao
    {
        $p = (new PublicacaoDAO())->buscarPorId($id);
        if (!$p || ($tipo !== null && $p->tipo !== $tipo)) throw new Falha('Item não encontrado.', 404);
        return $p;
    }

    public static function papel(int $turmaId, int $usuarioId): ?string
    {
        return (new ParticipacaoDAO())->papel($turmaId, $usuarioId);
    }

    /** Exige que o usuário seja membro da turma (e, opcionalmente, professor). Devolve o papel. */
    public static function exigirMembro(int $turmaId, Usuario $u, bool $soProfessor = false): string
    {
        $papel = self::papel($turmaId, $u->id);
        if ($papel === null) throw new Falha('Você não participa desta turma.', 403);
        if ($soProfessor && $papel !== Participacao::PROFESSOR) throw new Falha('Só professores podem fazer isso.', 403);
        return $papel;
    }

    /** O administrador é quem criou a turma. */
    public static function exigirAdministrador(Turma $turma, Usuario $u): void
    {
        if (!$turma->ehAdministrador($u)) throw new Falha('Só o administrador da turma pode fazer isso.', 403);
    }
}
