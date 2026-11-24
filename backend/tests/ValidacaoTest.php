<?php
// lib/validacao.php e lib/sessao.php: leitura dos campos, limpeza do HTML, data de entrega e checagem de origem (CSRF).

use EduConnect\Models\Publicacao;

teste('texto(): apara espaços e remove caracteres de controle', function () {
    $_POST['nome'] = "  Ana\x01\x07 ";
    igual('Ana', texto('nome', 120));
});

teste('texto(): obrigatório vazio e acima do limite são recusados', function () {
    $_POST['nome'] = '   ';
    recusa(400, fn () => texto('nome', 120, true, 'O nome'), 'O nome é obrigatório');
    $_POST['nome'] = str_repeat('á', 121);
    recusa(400, fn () => texto('nome', 120), 'no máximo 120');
    $_POST['nome'] = str_repeat('á', 120);   // conta caracteres, não bytes
    igual(120, mb_strlen(texto('nome', 120)));
});

teste('inteiro(): só aceita dígitos', function () {
    $_POST['id'] = '42';
    igual(42, inteiro('id'));
    foreach (['', '-1', '1.5', '1 OR 1=1', 'abc'] as $ruim) {
        $_POST['id'] = $ruim;
        recusa(400, fn () => inteiro('id'));
    }
    $_POST['id'] = ['1'];
    recusa(400, fn () => inteiro('id'));
});

teste('inteiro_opcional(): ausente vira null', function () {
    igual(null, inteiro_opcional('id'));
    $_GET['id'] = '7';
    igual(7, inteiro_opcional('id'));
});

teste('limpar_html(): mantém só as tags permitidas, sem atributos', function () {
    igual('<b>oi</b> <i>tudo</i><ul><li>a</li></ul>', limpar_html('<b>oi</b> <i>tudo</i><ul><li>a</li></ul>'));
    igual('<b>x</b>', limpar_html('<b onclick="alert(1)" style="color:red">x</b>'));
    igual('', limpar_html('<script>alert(1)</script>'));
    igual('', limpar_html('<style>body{}</style>'));
    igual('clique', limpar_html('<a href="javascript:alert(1)">clique</a>'));
    igual('', limpar_html('<img src=x onerror="alert(1)">'));
    igual('&lt;b&gt;', limpar_html('&lt;b&gt;'));   // texto escapado continua escapado
    igual('', limpar_html('   '));
});

teste('data_de_entrega(): modal (entrega) e subpágina (prazo_tipo)', function () {
    igual(null, data_de_entrega());
    $_POST['entrega'] = '2026-11-20T14:30';
    igual('2026-11-20 14:30:00', data_de_entrega());
    $_POST = ['prazo_tipo' => 'definir', 'prazo_data' => '2026-12-01', 'prazo_hora' => ''];
    igual('2026-12-01 23:59:00', data_de_entrega(), 'sem hora vale o fim do dia');
    $_POST = ['prazo_tipo' => 'sem', 'entrega' => ''];
    igual(null, data_de_entrega());
});

teste('data_de_entrega(): data impossível ou mal formatada é recusada', function () {
    foreach (['2026-02-30T10:00', '20/11/2026', '2026-11-20', '2026-11-20T25:00'] as $ruim) {
        $_POST = ['entrega' => $ruim];
        recusa(400, fn () => data_de_entrega(), 'Data de entrega inválida');
    }
});

teste('conferir_origem(): aceita o próprio site e recusa outro (CSRF)', function () {
    $antes = $_SERVER;
    try {
        $_SERVER['HTTP_HOST'] = 'localhost:8000';
        $_SERVER['HTTP_ORIGIN'] = 'http://localhost:8000';
        conferir_origem();
        $_SERVER['HTTP_ORIGIN'] = 'http://malicioso.com';
        recusa(403, fn () => conferir_origem());
        $_SERVER['HTTP_ORIGIN'] = 'http://localhost:9999';
        recusa(403, fn () => conferir_origem());
        unset($_SERVER['HTTP_ORIGIN']);
        $_SERVER['HTTP_SEC_FETCH_SITE'] = 'cross-site';
        recusa(403, fn () => conferir_origem());
        $_SERVER['HTTP_SEC_FETCH_SITE'] = 'same-origin';
        conferir_origem();
    } finally {
        $_SERVER = $antes;
    }
});

teste('Publicacao::prazoVencido(): sem prazo nunca atrasa', function () {
    $p = new Publicacao(entregaEm: '2026-11-20 23:59:00');
    verdade(!$p->prazoVencido('2026-11-20 23:59:00'), 'no minuto do prazo ainda vale');
    verdade($p->prazoVencido('2026-11-21 00:00:00'));
    verdade(!(new Publicacao())->prazoVencido('2099-01-01 00:00:00'));
});
