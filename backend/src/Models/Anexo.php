<?php
declare(strict_types=1);

namespace EduConnect\Models;

/** Linha da tabela `anexos`: arquivo de uma publicação OU de uma entrega. `caminho` é o nome aleatório no disco. */
final class Anexo
{
    public function __construct(
        public ?int $id = null,
        public ?int $publicacaoId = null,
        public ?int $entregaId = null,
        public string $nome = '',
        public string $caminho = '',
        public string $tipo = 'application/octet-stream',
        public int $tamanho = 0,
        public ?string $criadoEm = null,
    ) {}

    public static function deLinha(array $l): self
    {
        return new self((int) $l['id'],
            $l['publicacao_id'] === null ? null : (int) $l['publicacao_id'],
            $l['entrega_id'] === null ? null : (int) $l['entrega_id'],
            $l['nome'], $l['caminho'], $l['tipo'], (int) $l['tamanho'], $l['criado_em'] ?? null);
    }
}
