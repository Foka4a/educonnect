<?php
declare(strict_types=1);

namespace EduConnect\Models;

/** Linha da tabela `publicacoes`: uma atividade (tem prazo e entregas) ou um material. */
final class Publicacao
{
    public const ATIVIDADE = 'atividade';
    public const MATERIAL = 'material';

    public function __construct(
        public ?int $id = null,
        public int $turmaId = 0,
        public string $tipo = self::ATIVIDADE,
        public string $titulo = '',
        public string $descricao = '',
        public ?string $entregaEm = null,
        public int $autorId = 0,
        public ?string $criadoEm = null,
        public ?string $atualizadoEm = null,
    ) {}

    public static function deLinha(array $l): self
    {
        return new self((int) $l['id'], (int) $l['turma_id'], $l['tipo'], $l['titulo'], $l['descricao'],
            $l['entrega_em'] ?? null, (int) $l['autor_id'], $l['criado_em'] ?? null, $l['atualizado_em'] ?? null);
    }

    public function ehAtividade(): bool
    {
        return $this->tipo === self::ATIVIDADE;
    }

    /** Entregar neste momento ($agora, 'Y-m-d H:i:s') conta como atraso? */
    public function prazoVencido(string $agora): bool
    {
        return $this->entregaEm !== null && $agora > $this->entregaEm;
    }
}
