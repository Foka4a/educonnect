<?php
declare(strict_types=1);

namespace EduConnect\Database;

use PDO;
use RuntimeException;

/** Cria as tabelas (backend/sql/schema.sql) e o usuário padrão (backend/sql/usuario-padrao.sql) se ainda não existem, no SQLite e no MySQL. */
final class Esquema
{
    /** Script com os CREATE TABLE, escrito para o MySQL. */
    public const ARQUIVO = __DIR__ . '/../../sql/schema.sql';

    /** Usuário padrão (padrao@educonnect.test), criado depois das tabelas se ainda não existe. */
    public const USUARIO_PADRAO = __DIR__ . '/../../sql/usuario-padrao.sql';

    public static function criar(PDO $pdo): void
    {
        foreach (self::comandos($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql') as $sql) $pdo->exec($sql);

        // Bancos criados antes destas colunas existirem ganham as que faltam.
        self::adicionarColunas($pdo, 'turmas', [
            'descricao' => "VARCHAR(1000) NOT NULL DEFAULT ''",
            'secao' => "VARCHAR(120) NOT NULL DEFAULT ''",
            'sala' => "VARCHAR(120) NOT NULL DEFAULT ''",
            'cor' => "VARCHAR(7) NOT NULL DEFAULT ''",
        ]);

        foreach (self::comandos($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql', self::USUARIO_PADRAO) as $sql) $pdo->exec($sql);
    }

    /**
     * Lê um script .sql (o schema.sql por padrão) e separa os comandos (cada um termina com `;` no fim da linha),
     * sem os comentários `--`. No SQLite troca a chave autoincremento e tira as opções de tabela que só o MySQL entende.
     * @return list<string>
     */
    public static function comandos(bool $mysql, string $arquivo = self::ARQUIVO): array
    {
        $texto = file_get_contents($arquivo);
        if ($texto === false) throw new RuntimeException('Não foi possível ler ' . $arquivo);
        $texto = preg_replace('/^\s*--.*$/m', '', $texto);
        if (!$mysql) {
            $texto = str_replace('INT AUTO_INCREMENT PRIMARY KEY', 'INTEGER PRIMARY KEY AUTOINCREMENT', $texto);
            $texto = str_replace(' ENGINE=InnoDB DEFAULT CHARSET=utf8mb4', '', $texto);
        }
        return array_values(array_filter(array_map('trim', preg_split('/;\s*$/m', $texto))));
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
