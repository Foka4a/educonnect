<?php
declare(strict_types=1);

namespace EduConnect\Models;

/** Linha da tabela `turmas`. O criador é o administrador da turma. */
final class Turma
{
    public function __construct(
        public ?int $id = null,
        public string $nome = '',
        public string $niveis = '',
        public string $materia = '',
        public string $codigo = '',
        public ?string $imagem = null,
        public int $criadorId = 0,
        public ?string $criadoEm = null,
    ) {}

    public static function deLinha(array $l): self
    {
        return new self((int) $l['id'], $l['nome'], $l['niveis'], $l['materia'], $l['codigo'],
            $l['imagem'] ?? null, (int) $l['criador_id'], $l['criado_em'] ?? null);
    }

    public function ehAdministrador(Usuario $u): bool
    {
        return $this->criadorId === $u->id;
    }
}
