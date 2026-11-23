<?php
declare(strict_types=1);

namespace EduConnect\Models;

/** Linha da tabela `usuarios`. */
final class Usuario
{
    public function __construct(
        public ?int $id = null,
        public string $nome = '',
        public string $email = '',
        public string $senhaHash = '',
        public ?string $criadoEm = null,
    ) {}

    public static function deLinha(array $l): self
    {
        return new self((int) $l['id'], $l['nome'], $l['email'], $l['senha_hash'] ?? '', $l['criado_em'] ?? null);
    }

    public function confereSenha(string $senha): bool
    {
        return password_verify($senha, $this->senhaHash);
    }

    /** Dados públicos (nunca inclui o hash da senha). */
    public function paraArray(): array
    {
        return ['id' => $this->id, 'nome' => $this->nome, 'email' => $this->email];
    }
}
