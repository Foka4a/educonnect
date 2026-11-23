<?php
declare(strict_types=1);
// A conexão com o banco (PDO) fica em src/Database/Conexao.php e o SQL nos DAOs (src/DAO).

function config(string $chave) {
    static $c;
    $c ??= require __DIR__ . '/../config.php';
    return $c[$chave];
}

function agora(): string {
    return date('Y-m-d H:i:s');
}
