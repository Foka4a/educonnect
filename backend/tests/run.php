<?php
declare(strict_types=1);
/*
 * Testes do back-end, sem PHPUnit: roda todos os backend/tests/*Test.php num banco SQLite em memória (some ao terminar).
 *   php backend/tests/run.php
 *   docker run --rm -v "$PWD":/app -w /app php:8.3-cli php backend/tests/run.php   (sem PHP instalado)
 * Saída 0 = tudo passou.
 */
putenv('DB_DSN=sqlite::memory:');
require __DIR__ . '/../bootstrap.php';

use EduConnect\Models\Turma;
use EduConnect\Models\Usuario;
use EduConnect\Services\ContaService;
use EduConnect\Services\TurmaService;

$total = 0;
$falhas = 0;

function teste(string $nome, callable $corpo): void {
    global $total, $falhas;
    $total++;
    $_POST = $_GET = $_FILES = [];
    try {
        $corpo();
        echo "  ok  $nome\n";
    } catch (Throwable $e) {
        $falhas++;
        echo "FALHOU $nome\n       " . get_class($e) . ': ' . $e->getMessage() . ' (' . basename($e->getFile()) . ':' . $e->getLine() . ")\n";
    }
}

function igual(mixed $esperado, mixed $obtido, string $contexto = ''): void {
    if ($esperado !== $obtido) {
        throw new Exception(($contexto ? "$contexto: " : '') . 'esperado ' . var_export($esperado, true) . ', veio ' . var_export($obtido, true));
    }
}

function verdade(bool $condicao, string $contexto = 'condição falsa'): void {
    if (!$condicao) throw new Exception($contexto);
}

/** A regra de negócio tem que recusar com este status HTTP (e, se dado, um trecho da mensagem). */
function recusa(int $status, callable $corpo, string $trecho = ''): void {
    try {
        $corpo();
    } catch (Falha $f) {
        igual($status, $f->status, 'status da Falha "' . $f->getMessage() . '"');
        if ($trecho !== '' && !str_contains($f->getMessage(), $trecho)) throw new Exception("mensagem \"{$f->getMessage()}\" sem \"$trecho\"");
        return;
    }
    throw new Exception("esperava Falha $status, mas passou");
}

function novo_usuario(string $nome = 'Pessoa Teste'): Usuario {
    static $n = 0;
    $n++;
    return (new ContaService())->cadastrar($nome, "pessoa$n@teste.com", 'senha1234', 'senha1234');
}

/** Turma nova: administrador (professor) e um aluno que entrou pelo código. @return array{0: Usuario, 1: Usuario, 2: Turma} */
function turma_com_aluno(): array {
    $prof = novo_usuario('Professora Ana');
    $aluno = novo_usuario('Aluno Bruno');
    $turma = (new TurmaService())->criar($prof, 'Matemática', '1º ano', 'Matemática');
    (new TurmaService())->participar($aluno, $turma->codigo);
    return [$prof, $aluno, $turma];
}

foreach (glob(__DIR__ . '/*Test.php') as $arquivo) {
    echo "\n" . basename($arquivo) . "\n";
    require $arquivo;
}

echo "\n$total testes, $falhas " . ($falhas === 1 ? 'falha' : 'falhas') . "\n";
exit($falhas ? 1 : 0);
