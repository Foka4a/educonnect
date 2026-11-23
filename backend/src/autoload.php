<?php
declare(strict_types=1);
/*
 * Autoload das classes do back-end (padrão PSR-4): EduConnect\DAO\TurmaDAO  →  src/DAO/TurmaDAO.php
 *   EduConnect\Database  conexão PDO e criação das tabelas
 *   EduConnect\Models    uma classe por tabela (os dados de uma linha)
 *   EduConnect\DAO       uma classe por tabela com o SQL (buscar, inserir, atualizar, excluir)
 */
spl_autoload_register(function (string $classe): void {
    $prefixo = 'EduConnect\\';
    if (!str_starts_with($classe, $prefixo)) return;
    $arquivo = __DIR__ . '/' . str_replace('\\', '/', substr($classe, strlen($prefixo))) . '.php';
    if (is_file($arquivo)) require $arquivo;
});
