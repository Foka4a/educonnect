<?php
declare(strict_types=1);

namespace EduConnect\DAO;

use EduConnect\Models\Usuario;

final class UsuarioDAO extends DAO
{
    public function buscarPorId(int $id): ?Usuario
    {
        $l = $this->linha('SELECT * FROM usuarios WHERE id = ?', [$id]);
        return $l ? Usuario::deLinha($l) : null;
    }

    public function buscarPorEmail(string $email): ?Usuario
    {
        $l = $this->linha('SELECT * FROM usuarios WHERE email = ?', [$email]);
        return $l ? Usuario::deLinha($l) : null;
    }

    public function emailExiste(string $email): bool
    {
        return $this->linha('SELECT 1 FROM usuarios WHERE email = ?', [$email]) !== null;
    }

    /** Grava o usuário e preenche $u->id. */
    public function inserir(Usuario $u): int
    {
        $u->criadoEm ??= self::agora();
        return $u->id = $this->inserirLinha('INSERT INTO usuarios (nome, email, senha_hash, criado_em) VALUES (?,?,?,?)',
            [$u->nome, $u->email, $u->senhaHash, $u->criadoEm]);
    }
}
