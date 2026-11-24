<?php
declare(strict_types=1);

namespace EduConnect\Services;

use EduConnect\DAO\UsuarioDAO;
use EduConnect\Models\Usuario;
use Falha;

/** Cadastro e login. A sessão (cookie) fica com o controller. */
final class ContaService
{
    public function __construct(private UsuarioDAO $usuarios = new UsuarioDAO()) {}

    public function cadastrar(string $nome, string $email, string $senha, string $confirmacao): Usuario
    {
        $email = mb_strtolower($email);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) throw new Falha('E-mail inválido.');
        if (mb_strlen($senha) < config('senha_minima')) throw new Falha('A senha precisa ter pelo menos ' . config('senha_minima') . ' caracteres.');
        if ($senha !== $confirmacao) throw new Falha('As senhas não conferem.');
        if ($this->usuarios->emailExiste($email)) throw new Falha('Já existe uma conta com esse e-mail.', 409);

        $u = new Usuario(nome: $nome, email: $email, senhaHash: password_hash($senha, PASSWORD_DEFAULT));
        $this->usuarios->inserir($u);
        return $u;
    }

    public function autenticar(string $email, string $senha): Usuario
    {
        $u = $this->usuarios->buscarPorEmail(mb_strtolower(trim($email)));
        // mesma resposta para e-mail inexistente e senha errada (não revela quais e-mails têm conta)
        if (!$u || !$u->confereSenha($senha)) throw new Falha('E-mail ou senha incorretos', 401);
        return $u;
    }
}
