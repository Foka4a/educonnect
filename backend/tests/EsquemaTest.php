<?php
// src/Database/Esquema.php: backend/sql/schema.sql cria todas as tabelas e usuario-padrao.sql a conta padrão, no MySQL e no SQLite.

use EduConnect\Database\Conexao;
use EduConnect\Database\Esquema;
use EduConnect\Services\ContaService;

teste('schema.sql: separa os 9 CREATE TABLE, sem comentários', function () {
    foreach ([true, false] as $mysql) {
        $comandos = Esquema::comandos($mysql);
        igual(9, count($comandos), $mysql ? 'MySQL' : 'SQLite');
        foreach ($comandos as $sql) {
            verdade(str_starts_with($sql, 'CREATE TABLE IF NOT EXISTS '), "comando inesperado: $sql");
            verdade(!str_contains($sql, '--'), "comentário sobrou: $sql");
        }
    }
});

teste('schema.sql: no SQLite vira chave AUTOINCREMENT e perde o ENGINE do MySQL', function () {
    $sqlite = implode("\n", Esquema::comandos(false));
    verdade(!str_contains($sqlite, 'AUTO_INCREMENT') && !str_contains($sqlite, 'ENGINE='), 'sobrou sintaxe do MySQL');
    verdade(str_contains($sqlite, 'INTEGER PRIMARY KEY AUTOINCREMENT'));
    verdade(str_contains(implode("\n", Esquema::comandos(true)), 'ENGINE=InnoDB'));
});

teste('schema.sql: o banco de teste tem todas as tabelas, e rodar de novo não quebra', function () {
    $pdo = Conexao::obter();
    Esquema::criar($pdo);
    $tabelas = $pdo->query("SELECT name FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%' ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);
    igual(['anexos', 'comentarios', 'convites', 'entrega_historico', 'entregas', 'participacoes', 'publicacoes', 'turmas', 'usuarios'], $tabelas);
});

teste('usuario-padrao.sql: a conta padrão existe, entra com a senha documentada e não duplica', function () {
    $pdo = Conexao::obter();
    Esquema::criar($pdo);   // de novo: não pode criar outra
    $contar = fn () => (int) $pdo->query("SELECT COUNT(*) FROM usuarios WHERE email = 'padrao@educonnect.test'")->fetchColumn();
    igual(1, $contar());
    igual('Usuário Padrão', (new ContaService())->autenticar('padrao@educonnect.test', 'educonnect123')->nome);

    $pdo->exec("DELETE FROM usuarios WHERE email = 'padrao@educonnect.test'");
    Esquema::criar($pdo);   // apagada: volta
    igual(1, $contar());
});
