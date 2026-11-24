<?php
require __DIR__ . '/../partes/pagina.php';

use EduConnect\Services\ContaService;

formularios([
    'login' => function () {
        $u = (new ContaService())->autenticar((string) ($_POST['email'] ?? ''), (string) ($_POST['senha'] ?? ''));
        entrar_na_sessao($u->id);
        sucesso('Bem-vindo(a), ' . $u->nome . '.', 'index.php');
    },
]);
if (usuario()) { header('Location: index.php', true, 303); exit; } // já entrou: vai para as turmas
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entrar | EduConnect</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap">
    <link rel="stylesheet" href="../style/acesso.css">
    <script src="../scripts/backend.js" defer></script>
</head>
<body>
    <main>
        <section class="card-acesso" aria-labelledby="titulo-acesso">
            <header>
                <img src="../img/icon.svg" alt="">
                <h1 id="titulo-acesso">EduConnect</h1>
                <p>Fazer login para continuar</p>
            </header>

            <form method="post" novalidate>
                <?= acao('login') ?>
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" autocomplete="email" required>

                <label for="senha">Senha</label>
                <input type="password" id="senha" name="senha" autocomplete="current-password" required>

                <p id="erro-login" class="erro" role="alert" hidden>E-mail ou senha incorretos</p>

                <button type="submit">Entrar</button>
            </form>

            <p>Não tem conta? <a href="cadastro.php">Criar conta</a></p>
        </section>
    </main>
</body>
</html>
