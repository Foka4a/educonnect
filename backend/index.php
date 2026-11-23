<?php
declare(strict_types=1);
/*
 * EduConnect · ponto de entrada do back-end.
 * Cada modal/formulário do front-end envia para  backend/index.php/<rota>  (POST) e volta para a página de origem
 * com ?msg=… (deu certo) ou ?erro=… (falhou). Chamadas feitas por fetch() (cabeçalho X-Requested-With: fetch)
 * recebem JSON no lugar do redirecionamento. Veja backend/README.md.
 */
require __DIR__ . '/lib/db.php';
require __DIR__ . '/lib/resposta.php';
require __DIR__ . '/lib/sessao.php';
require __DIR__ . '/lib/validacao.php';
require __DIR__ . '/lib/permissoes.php';
require __DIR__ . '/lib/arquivos.php';
foreach (['conta', 'turmas', 'pessoas', 'publicacoes', 'entregas', 'comentarios'] as $modulo) require __DIR__ . "/acoes/$modulo.php";

date_default_timezone_set(config('fuso'));
header('X-Content-Type-Options: nosniff');

// rota => [método, função, exige login?]. O login é conferido dentro de cada ação (exigir_login).
$rotas = [
    'cadastro' => ['POST', 'acao_cadastro'], 'login' => ['POST', 'acao_login'], 'sair' => ['POST', 'acao_sair'], 'eu' => ['GET', 'acao_eu'],
    'criar-turma' => ['POST', 'acao_criar_turma'], 'editar-turma' => ['POST', 'acao_editar_turma'], 'excluir-turma' => ['POST', 'acao_excluir_turma'],
    'sair-da-turma' => ['POST', 'acao_sair_da_turma'], 'participar-turma' => ['POST', 'acao_participar_turma'],
    'personalizar-aparencia' => ['POST', 'acao_personalizar_aparencia'], 'imagem-turma' => ['GET', 'acao_imagem_turma'],
    'convidar-alunos' => ['POST', 'acao_convidar_alunos'], 'redefinir-codigo' => ['POST', 'acao_redefinir_codigo'],
    'promover-professor' => ['POST', 'acao_promover_professor'], 'remover-pessoa' => ['POST', 'acao_remover_pessoa'],
    'criar-atividade' => ['POST', 'acao_criar_atividade'], 'editar-atividade' => ['POST', 'acao_editar_atividade'], 'excluir-atividade' => ['POST', 'acao_excluir_atividade'],
    'criar-material' => ['POST', 'acao_criar_material'], 'editar-material' => ['POST', 'acao_editar_material'], 'excluir-material' => ['POST', 'acao_excluir_material'],
    'enviar-trabalho' => ['POST', 'acao_enviar_trabalho'], 'salvar-nota' => ['POST', 'acao_salvar_nota'],
    'comentar' => ['POST', 'acao_comentar'], 'editar-comentario' => ['POST', 'acao_editar_comentario'], 'excluir-comentario' => ['POST', 'acao_excluir_comentario'],
    'arquivo' => ['GET', 'acao_arquivo'],
];

try {
    iniciar_sessao();
    $rota = trim($_SERVER['PATH_INFO'] ?? '', '/');
    if ($rota === '' && isset($_SERVER['REQUEST_URI'])) {   // servidores sem PATH_INFO: /backend/index.php/rota
        $caminho = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '';
        $rota = trim(substr($caminho, (int) strpos($caminho, 'index.php') + 9), '/');
    }
    [$metodo, $funcao] = $rotas[$rota] ?? throw new Falha('Rota não encontrada.', 404);
    if ($_SERVER['REQUEST_METHOD'] !== $metodo) {
        header('Allow: ' . $metodo);
        throw new Falha('Método não permitido.', 405);
    }
    if ($metodo === 'POST') conferir_origem();
    $funcao();
} catch (Falha $f) {
    if (db()->inTransaction()) db()->rollBack();
    falhou($f->getMessage(), $f->status);
} catch (Throwable $e) {
    if (isset($pdo) || function_exists('db')) { try { if (db()->inTransaction()) db()->rollBack(); } catch (Throwable) {} }
    error_log('[educonnect] ' . $e);
    falhou('Algo deu errado no servidor. Tente de novo.', 500);
}
