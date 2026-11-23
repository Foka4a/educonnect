<?php
declare(strict_types=1);

namespace EduConnect\Models;

/** Linha da tabela `convites` (o e-mail em si ainda não é enviado). */
final class Convite
{
    public function __construct(
        public ?int $id = null,
        public int $turmaId = 0,
        public string $email = '',
        public int $convidadoPor = 0,
        public ?string $criadoEm = null,
    ) {}

    public static function deLinha(array $l): self
    {
        return new self((int) $l['id'], (int) $l['turma_id'], $l['email'], (int) $l['convidado_por'], $l['criado_em'] ?? null);
    }
}
