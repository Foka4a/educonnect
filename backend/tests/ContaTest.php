<?php
// ContaService: cadastro e login.

use EduConnect\Services\ContaService;

teste('cadastro: grava e-mail em minúsculas e a senha só como hash', function () {
    $u = (new ContaService())->cadastrar('Carla', 'Carla@Escola.COM', 'segredo123', 'segredo123');
    verdade($u->id > 0, 'ganhou id');
    igual('carla@escola.com', $u->email);
    verdade($u->senhaHash !== 'segredo123' && $u->confereSenha('segredo123'), 'senha com hash');
    verdade(!array_key_exists('senhaHash', $u->paraArray()) && !in_array($u->senhaHash, $u->paraArray(), true), 'paraArray sem hash');
});

teste('cadastro: e-mail inválido, senha curta, senhas diferentes', function () {
    $c = new ContaService();
    recusa(400, fn () => $c->cadastrar('X', 'nao-e-email', 'segredo123', 'segredo123'), 'E-mail inválido');
    recusa(400, fn () => $c->cadastrar('X', 'x1@teste.com', 'curta', 'curta'), 'pelo menos 8');
    recusa(400, fn () => $c->cadastrar('X', 'x2@teste.com', 'segredo123', 'segredo124'), 'não conferem');
});

teste('cadastro: e-mail repetido (mesmo com outras maiúsculas) é 409', function () {
    $c = new ContaService();
    $c->cadastrar('Davi', 'davi@teste.com', 'segredo123', 'segredo123');
    recusa(409, fn () => $c->cadastrar('Davi 2', 'DAVI@teste.com', 'segredo123', 'segredo123'));
});

teste('login: certo entra; senha errada e e-mail inexistente dão a mesma resposta', function () {
    $c = new ContaService();
    $c->cadastrar('Eva', 'eva@teste.com', 'segredo123', 'segredo123');
    igual('Eva', $c->autenticar('  EVA@teste.com ', 'segredo123')->nome);
    recusa(401, fn () => $c->autenticar('eva@teste.com', 'errada123'), 'E-mail ou senha incorretos');
    recusa(401, fn () => $c->autenticar('ninguem@teste.com', 'segredo123'), 'E-mail ou senha incorretos');
});
