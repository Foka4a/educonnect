<?php
declare(strict_types=1);

namespace EduConnect\DAO;

use EduConnect\Database\Conexao;
use PDO;

/**
 * Base dos DAOs: guarda o PDO e executa SQL sempre com parâmetros (prepared statements).
 * Cada DAO filho cuida de uma tabela e devolve Models (nunca arrays crus) para as ações.
 */
abstract class DAO
{
    protected PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Conexao::obter();
    }

    /** @return list<array<string, mixed>> */
    protected function linhas(string $sql, array $params = []): array
    {
        $st = $this->pdo->prepare($sql);
        $st->execute($params);
        return $st->fetchAll();
    }

    protected function linha(string $sql, array $params = []): ?array
    {
        return $this->linhas($sql, $params)[0] ?? null;
    }

    /** INSERT: devolve o id gerado. */
    protected function inserirLinha(string $sql, array $params): int
    {
        $this->pdo->prepare($sql)->execute($params);
        return (int) $this->pdo->lastInsertId();
    }

    /** UPDATE/DELETE: devolve quantas linhas mudaram. */
    protected function executar(string $sql, array $params = []): int
    {
        $st = $this->pdo->prepare($sql);
        $st->execute($params);
        return $st->rowCount();
    }

    protected static function agora(): string
    {
        return date('Y-m-d H:i:s');
    }
}
