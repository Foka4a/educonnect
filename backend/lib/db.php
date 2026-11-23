<?php
declare(strict_types=1);

function config(string $chave) {
    static $c;
    $c ??= require __DIR__ . '/../config.php';
    return $c[$chave];
}

function db(): PDO {
    static $pdo;
    if ($pdo) return $pdo;
    $pdo = new PDO(config('dsn'), config('usuario'), config('senha'), [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    if ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite') $pdo->exec('PRAGMA foreign_keys = ON');
    criar_tabelas($pdo);
    return $pdo;
}

function consultar(string $sql, array $params = []): array {
    $st = db()->prepare($sql);
    $st->execute($params);
    return $st->fetchAll();
}

function um(string $sql, array $params = []): ?array {
    return consultar($sql, $params)[0] ?? null;
}

/** Executa INSERT/UPDATE/DELETE. Devolve o id inserido (INSERT) ou o número de linhas afetadas. */
function executar(string $sql, array $params = []): int {
    $st = db()->prepare($sql);
    $st->execute($params);
    return stripos(ltrim($sql), 'INSERT') === 0 ? (int) db()->lastInsertId() : $st->rowCount();
}

function agora(): string {
    return date('Y-m-d H:i:s');
}

/** Cria as tabelas se ainda não existem (SQLite e MySQL). */
function criar_tabelas(PDO $pdo): void {
    static $feito = false;
    if ($feito) return;
    $feito = true;
    $mysql = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql';
    $pk = $mysql ? 'INT AUTO_INCREMENT PRIMARY KEY' : 'INTEGER PRIMARY KEY AUTOINCREMENT';
    $motor = $mysql ? ' ENGINE=InnoDB DEFAULT CHARSET=utf8mb4' : '';
    $tabelas = [
        "CREATE TABLE IF NOT EXISTS usuarios (
            id $pk, nome VARCHAR(120) NOT NULL, email VARCHAR(190) NOT NULL UNIQUE,
            senha_hash VARCHAR(255) NOT NULL, criado_em DATETIME NOT NULL)$motor",
        "CREATE TABLE IF NOT EXISTS turmas (
            id $pk, nome VARCHAR(120) NOT NULL, niveis VARCHAR(120) NOT NULL DEFAULT '', materia VARCHAR(120) NOT NULL DEFAULT '',
            codigo VARCHAR(8) NOT NULL UNIQUE, imagem VARCHAR(255) NULL,
            criador_id INT NOT NULL, criado_em DATETIME NOT NULL,
            FOREIGN KEY (criador_id) REFERENCES usuarios(id))$motor",
        "CREATE TABLE IF NOT EXISTS participacoes (
            turma_id INT NOT NULL, usuario_id INT NOT NULL, papel VARCHAR(10) NOT NULL DEFAULT 'aluno', criado_em DATETIME NOT NULL,
            PRIMARY KEY (turma_id, usuario_id),
            FOREIGN KEY (turma_id) REFERENCES turmas(id) ON DELETE CASCADE,
            FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE)$motor",
        "CREATE TABLE IF NOT EXISTS publicacoes (
            id $pk, turma_id INT NOT NULL, tipo VARCHAR(10) NOT NULL, titulo VARCHAR(120) NOT NULL, descricao TEXT NOT NULL,
            entrega_em DATETIME NULL, autor_id INT NOT NULL, criado_em DATETIME NOT NULL, atualizado_em DATETIME NULL,
            FOREIGN KEY (turma_id) REFERENCES turmas(id) ON DELETE CASCADE,
            FOREIGN KEY (autor_id) REFERENCES usuarios(id))$motor",
        "CREATE TABLE IF NOT EXISTS entregas (
            id $pk, atividade_id INT NOT NULL, aluno_id INT NOT NULL, status VARCHAR(10) NOT NULL DEFAULT 'entregue',
            atrasada TINYINT NOT NULL DEFAULT 0, nota INT NULL, enviado_em DATETIME NOT NULL,
            UNIQUE (atividade_id, aluno_id),
            FOREIGN KEY (atividade_id) REFERENCES publicacoes(id) ON DELETE CASCADE,
            FOREIGN KEY (aluno_id) REFERENCES usuarios(id) ON DELETE CASCADE)$motor",
        "CREATE TABLE IF NOT EXISTS entrega_historico (
            id $pk, entrega_id INT NOT NULL, evento VARCHAR(12) NOT NULL, em DATETIME NOT NULL,
            FOREIGN KEY (entrega_id) REFERENCES entregas(id) ON DELETE CASCADE)$motor",
        "CREATE TABLE IF NOT EXISTS anexos (
            id $pk, publicacao_id INT NULL, entrega_id INT NULL, nome VARCHAR(255) NOT NULL, caminho VARCHAR(80) NOT NULL,
            tipo VARCHAR(100) NOT NULL, tamanho INT NOT NULL, criado_em DATETIME NOT NULL,
            FOREIGN KEY (publicacao_id) REFERENCES publicacoes(id) ON DELETE CASCADE,
            FOREIGN KEY (entrega_id) REFERENCES entregas(id) ON DELETE CASCADE)$motor",
        "CREATE TABLE IF NOT EXISTS comentarios (
            id $pk, publicacao_id INT NOT NULL, autor_id INT NOT NULL, aluno_id INT NULL, texto VARCHAR(1000) NOT NULL,
            particular TINYINT NOT NULL DEFAULT 0, criado_em DATETIME NOT NULL, editado_em DATETIME NULL,
            FOREIGN KEY (publicacao_id) REFERENCES publicacoes(id) ON DELETE CASCADE,
            FOREIGN KEY (autor_id) REFERENCES usuarios(id) ON DELETE CASCADE)$motor",
        "CREATE TABLE IF NOT EXISTS convites (
            id $pk, turma_id INT NOT NULL, email VARCHAR(190) NOT NULL, convidado_por INT NOT NULL, criado_em DATETIME NOT NULL,
            UNIQUE (turma_id, email),
            FOREIGN KEY (turma_id) REFERENCES turmas(id) ON DELETE CASCADE)$motor",
    ];
    foreach ($tabelas as $sql) $pdo->exec($sql);
}
