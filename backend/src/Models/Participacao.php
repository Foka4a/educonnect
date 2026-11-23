<?php
declare(strict_types=1);

namespace EduConnect\Models;

/** Linha da tabela `participacoes`: o papel de um usuário numa turma. */
final class Participacao
{
    public const ALUNO = 'aluno';
    public const PROFESSOR = 'professor';

    public function __construct(
        public int $turmaId = 0,
        public int $usuarioId = 0,
        public string $papel = self::ALUNO,
        public ?string $criadoEm = null,
    ) {}

    public static function deLinha(array $l): self
    {
        return new self((int) $l['turma_id'], (int) $l['usuario_id'], $l['papel'], $l['criado_em'] ?? null);
    }
}
