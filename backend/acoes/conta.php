<?php
declare(strict_types=1);

function acao_cadastro(): never {
    $nome = texto('nome', 120, true, 'O nome');
    $email = mb_strtolower(texto('email', 190, true, 'O e-mail'));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) throw new Falha('E-mail inválido.');
    $senha = (string) ($_POST['senha'] ?? '');
    if (mb_strlen($senha) < config('senha_minima')) throw new Falha('A senha precisa ter pelo menos ' . config('senha_minima') . ' caracteres.');
    if ($senha !== (string) ($_POST['confirmar-senha'] ?? '')) throw new Falha('As senhas não conferem.');
    if (um('SELECT id FROM usuarios WHERE email = ?', [$email])) throw new Falha('Já existe uma conta com esse e-mail.', 409);

    $id = executar('INSERT INTO usuarios (nome, email, senha_hash, criado_em) VALUES (?,?,?,?)',
        [$nome, $email, password_hash($senha, PASSWORD_DEFAULT), agora()]);
    entrar_na_sessao($id);
    sucesso('Conta criada.', pagina('inicio.html'), ['usuario' => ['id' => $id, 'nome' => $nome]]);
}

function acao_login(): never {
    $email = mb_strtolower(trim((string) ($_POST['email'] ?? '')));
    $u = um('SELECT id, nome, senha_hash FROM usuarios WHERE email = ?', [$email]);
    // mesma resposta para e-mail inexistente e senha errada (não revela quais e-mails têm conta)
    if (!$u || !password_verify((string) ($_POST['senha'] ?? ''), $u['senha_hash'])) throw new Falha('E-mail ou senha incorretos', 401);
    entrar_na_sessao((int) $u['id']);
    sucesso('Bem-vindo(a), ' . $u['nome'] . '.', pagina('inicio.html'), ['usuario' => ['id' => (int) $u['id'], 'nome' => $u['nome']]]);
}

function acao_sair(): never {
    $_SESSION = [];
    session_destroy();
    sucesso('Você saiu da conta.', pagina('login.html'));
}

/** GET: quem está logado (para as páginas mostrarem o nome e as iniciais). */
function acao_eu(): never {
    $u = usuario();
    json_resposta($u ? ['ok' => true, 'usuario' => $u] : ['ok' => false, 'erro' => 'Não autenticado.'], $u ? 200 : 401);
}
