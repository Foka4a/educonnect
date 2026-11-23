<?php
declare(strict_types=1);

namespace EduConnect\DAO;

use EduConnect\Models\Convite;

final class ConviteDAO extends DAO
{
    public function existe(int $turmaId, string $email): bool
    {
        return $this->linha('SELECT 1 FROM convites WHERE turma_id = ? AND email = ?', [$turmaId, $email]) !== null;
    }

    /** Grava o convite e preenche $c->id. */
    public function inserir(Convite $c): int
    {
        $c->criadoEm ??= self::agora();
        return $c->id = $this->inserirLinha('INSERT INTO convites (turma_id, email, convidado_por, criado_em) VALUES (?,?,?,?)',
            [$c->turmaId, $c->email, $c->convidadoPor, $c->criadoEm]);
    }
}
