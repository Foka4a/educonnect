<?php
declare(strict_types=1);

namespace EduConnect\Database;

use PDO;
use Throwable;

/** Conexão PDO única por requisição (MySQL no Docker, SQLite por padrão — ver backend/config.php). */
final class Conexao
{
    private static ?PDO $pdo = null;

    public static function obter(): PDO
    {
        if (self::$pdo === null) {
            $pdo = new PDO(\config('dsn'), \config('usuario'), \config('senha'), [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
            if ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite') $pdo->exec('PRAGMA foreign_keys = ON');
            Esquema::criar($pdo);
            self::$pdo = $pdo;
        }
        return self::$pdo;
    }

    /** Roda $passos dentro de uma transação: confirma no fim ou desfaz tudo se algo lançar exceção. */
    public static function transacao(callable $passos): mixed
    {
        $pdo = self::obter();
        $pdo->beginTransaction();
        try {
            $resultado = $passos();
            $pdo->commit();
            return $resultado;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            throw $e;
        }
    }

    /** Desfaz uma transação esquecida aberta (usado no tratamento de erro do index.php). */
    public static function desfazerSeAberta(): void
    {
        if (self::$pdo?->inTransaction()) self::$pdo->rollBack();
    }
}
