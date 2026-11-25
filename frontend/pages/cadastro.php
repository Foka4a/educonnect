<?php
require __DIR__ . '/../partes/pagina.php';

use EduConnect\Services\ContaService;

formularios([
    'cadastro' => function () {
        $u = (new ContaService())->cadastrar(texto('nome', 120, true, 'O nome'), texto('email', 190, true, 'O e-mail'),
            (string) ($_POST['senha'] ?? ''), (string) ($_POST['confirmar-senha'] ?? ''));
        entrar_na_sessao($u->id);
        sucesso('Conta criada.', 'index.php');
    },
]);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro | EduConnect</title>
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
                <h1 id="titulo-acesso">Criar conta</h1>
            </header>

            <form method="post" novalidate>
                <?= acao('cadastro') ?>
                <label for="nome">Nome completo</label>
                <input type="text" id="nome" name="nome" autocomplete="name" required>

                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" autocomplete="email" required>

                <label for="senha">Senha</label>
                <input type="password" id="senha" name="senha" autocomplete="new-password" required>

                <label for="confirmar-senha">Confirmar senha</label>
                <input type="password" id="confirmar-senha" name="confirmar-senha" autocomplete="new-password" required>

                <p id="erro-cadastro" class="erro" role="alert" hidden>As senhas não conferem</p>

                <button type="submit">Criar conta</button>
            </form>

            <p>Já tem conta? <a href="login.php">Fazer login</a></p>
        </section>
    </main>

    <footer class="rodape">
        <p>EduConnect é um clone do Google Classroom feito para fins educacionais, sem vínculo com o Google.</p>
    </footer>
</body>
</html>
