<?php
declare(strict_types=1);

namespace EduConnect\Database;

use PDO;

/** Cria as tabelas se ainda não existem (SQLite e MySQL). */
final class Esquema
{
    public static function criar(PDO $pdo): void
    {
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
                descricao VARCHAR(1000) NOT NULL DEFAULT '', secao VARCHAR(120) NOT NULL DEFAULT '', sala VARCHAR(120) NOT NULL DEFAULT '',
                cor VARCHAR(7) NOT NULL DEFAULT '',
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

        // Bancos criados antes destas colunas existirem ganham as que faltam.
        self::adicionarColunas($pdo, 'turmas', [
            'descricao' => "VARCHAR(1000) NOT NULL DEFAULT ''",
            'secao' => "VARCHAR(120) NOT NULL DEFAULT ''",
            'sala' => "VARCHAR(120) NOT NULL DEFAULT ''",
            'cor' => "VARCHAR(7) NOT NULL DEFAULT ''",
        ]);
    }

    /** @param array<string, string> $colunas nome => definição */
    private static function adicionarColunas(PDO $pdo, string $tabela, array $colunas): void
    {
        $st = $pdo->query("SELECT * FROM $tabela LIMIT 0");
        $existentes = [];
        for ($i = 0; $i < $st->columnCount(); $i++) $existentes[] = $st->getColumnMeta($i)['name'];
        $st->closeCursor();
        foreach ($colunas as $nome => $definicao) {
            if (!in_array($nome, $existentes, true)) $pdo->exec("ALTER TABLE $tabela ADD COLUMN $nome $definicao");
        }
    }
}
