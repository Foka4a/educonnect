<?php
declare(strict_types=1);

namespace EduConnect\Models;

/** Linha da tabela `entregas`: o trabalho de um aluno numa atividade. */
final class Entrega
{
    public const ENTREGUE = 'entregue';
    public const CANCELADA = 'cancelada';

    public function __construct(
        public ?int $id = null,
        public int $atividadeId = 0,
        public int $alunoId = 0,
        public string $status = self::ENTREGUE,
        public bool $atrasada = false,
        public ?int $nota = null,
        public ?string $enviadoEm = null,
        public ?int $turmaId = null,      // vem do JOIN com publicacoes (não é coluna de entregas)
    ) {}

    public static function deLinha(array $l): self
    {
        return new self((int) $l['id'], (int) $l['atividade_id'], (int) $l['aluno_id'], $l['status'], (bool) $l['atrasada'],
            $l['nota'] === null ? null : (int) $l['nota'], $l['enviado_em'] ?? null,
            isset($l['turma_id']) ? (int) $l['turma_id'] : null);
    }

    public function foiCorrigida(): bool
    {
        return $this->nota !== null;
    }

    public function estaEntregue(): bool
    {
        return $this->status === self::ENTREGUE;
    }
}
