<?php
declare(strict_types=1);

use EduConnect\DAO\UsuarioDAO;
use EduConnect\Models\Usuario;

function acao_cadastro(): never {
    $nome = texto('nome', 120, true, 'O nome');
    $email = mb_strtolower(texto('email', 190, true, 'O e-mail'));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) throw new Falha('E-mail inválido.');
    $senha = (string) ($_POST['senha'] ?? '');
    if (mb_strlen($senha) < config('senha_minima')) throw new Falha('A senha precisa ter pelo menos ' . config('senha_minima') . ' caracteres.');
    if ($senha !== (string) ($_POST['confirmar-senha'] ?? '')) throw new Falha('As senhas não conferem.');
    $usuarios = new UsuarioDAO();
    if ($usuarios->emailExiste($email)) throw new Falha('Já existe uma conta com esse e-mail.', 409);

    $u = new Usuario(nome: $nome, email: $email, senhaHash: password_hash($senha, PASSWORD_DEFAULT));
    $usuarios->inserir($u);
    entrar_na_sessao($u->id);
    sucesso('Conta criada.', pagina('index.html'), ['usuario' => ['id' => $u->id, 'nome' => $u->nome]]);
}

function acao_login(): never {
    $email = mb_strtolower(trim((string) ($_POST['email'] ?? '')));
    $u = (new UsuarioDAO())->buscarPorEmail($email);
    // mesma resposta para e-mail inexistente e senha errada (não revela quais e-mails têm conta)
    if (!$u || !$u->confereSenha((string) ($_POST['senha'] ?? ''))) throw new Falha('E-mail ou senha incorretos', 401);
    entrar_na_sessao($u->id);
    sucesso('Bem-vindo(a), ' . $u->nome . '.', pagina('index.html'), ['usuario' => ['id' => $u->id, 'nome' => $u->nome]]);
}

function acao_sair(): never {
    $_SESSION = [];
    session_destroy();
    sucesso('Você saiu da conta.', pagina('login.html'));
}

/** GET: quem está logado (para as páginas mostrarem o nome e as iniciais). */
function acao_eu(): never {
    $u = usuario();
    json_resposta($u ? ['ok' => true, 'usuario' => $u->paraArray()] : ['ok' => false, 'erro' => 'Não autenticado.'], $u ? 200 : 401);
}
