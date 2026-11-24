<?php
declare(strict_types=1);
/*
 * Primeira linha de toda página .php: carrega o back-end (Services, DAOs, sessão) e os ajudantes dos templates.
 * Ler: a página chama os Services dentro de carregar(). Gravar: os formulários postam para a própria página com um
 * campo oculto `acao` (<?= acao('criar-aviso') ?>), e a página trata no topo com formularios([...]).
 */
require_once __DIR__ . '/../../backend/bootstrap.php';

use EduConnect\Database\Conexao;
use EduConnect\Models\Publicacao;
use EduConnect\Models\Usuario;
use EduConnect\Services\PublicacaoService;
use EduConnect\Services\TurmaService;

iniciar_sessao();

/** Escapa texto para HTML (conteúdo e atributos). Use em tudo que vem do banco ou do usuário. */
function e(?string $texto): string {
    return htmlspecialchars((string) $texto, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Campo oculto que diz à página qual ação o formulário pede. */
function acao(string $nome): string {
    return '<input type="hidden" name="acao" value="' . e($nome) . '">';
}

/**
 * Trata o POST da página: acha a ação pelo campo `acao`, confere a origem e roda. Cada ação termina com sucesso()
 * (volta para a página com ?msg=, ou JSON se veio de fetch). Erro de regra de negócio (Falha) volta com ?erro=.
 * Ações comuns a todas as páginas (sair, criar/participar de turma, configurações da turma) já vêm incluídas.
 * GET: não faz nada e a página segue para a leitura.
 */
function formularios(array $acoes = []): void {
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') return;
    $acoes += acoes_comuns();
    try {
        conferir_origem();
        if (!$_POST && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) throw new Falha('O envio passou do tamanho máximo permitido.', 413);
        $rodar = $acoes[(string) ($_POST['acao'] ?? '')] ?? throw new Falha('Ação inválida.');
        $rodar();
        sucesso('Pronto.');   // toda ação responde antes; isto é só segurança
    } catch (Falha $f) {
        Conexao::desfazerSeAberta();
        falhou($f->getMessage(), $f->status);
    } catch (Throwable $e) {
        try { Conexao::desfazerSeAberta(); } catch (Throwable) {}
        error_log('[educonnect] ' . $e);
        falhou('Algo deu errado no servidor. Tente de novo.', 500);
    }
}

/** Formulários que aparecem em todas as páginas: menu da conta, menu "+" e Configurações da turma (rodapé). */
function acoes_comuns(): array {
    return [
        'sair' => function () {
            $_SESSION = [];
            session_destroy();
            sucesso('Você saiu da conta.', 'login.php');
        },
        'criar-turma' => function () {
            $turma = (new TurmaService())->criar(exigir_login(), texto('nome', 120, true, 'O nome da turma'), texto('niveis', 120), texto('materia', 120));
            sucesso('Turma criada.', "mural.php?turma=$turma->id", ['turma_id' => $turma->id]);
        },
        'participar-turma' => function () {
            $turma = (new TurmaService())->participar(exigir_login(), (string) ($_POST['codigo'] ?? ''));
            sucesso('Você entrou na turma ' . $turma->nome . '.', "mural.php?turma=$turma->id", ['turma_id' => $turma->id]);
        },
        'editar-turma' => function () {
            // descricao, secao e sala: ausentes no formulário = ficam como estão
            $opcional = fn (string $campo, int $max) => array_key_exists($campo, $_POST) ? texto($campo, $max) : null;
            (new TurmaService())->editar(exigir_login(), inteiro('turma_id', 'Turma'), texto('nome', 120, true, 'O nome da turma'),
                texto('niveis', 120), texto('materia', 120), $opcional('descricao', 1000), $opcional('secao', 120), $opcional('sala', 120));
            sucesso('Turma atualizada.');
        },
    ];
}

/** Excluir atividade, material ou aviso (menu ⋮ do Mural, de Atividades e de Instruções). */
function excluir_publicacao(string $tipo): never {
    $p = (new PublicacaoService())->excluir(exigir_login(), $tipo, inteiro($tipo . '_id', 'Item'));
    $mensagem = ['atividade' => 'Atividade excluída.', 'material' => 'Material excluído.', 'aviso' => 'Aviso excluído.'][$tipo];
    sucesso($mensagem, $tipo === Publicacao::AVISO ? null : 'atividades.php?turma=' . $p->turmaId);
}

/** "Gabriel Somariva" → "GS"; "Ana" → "A". */
function iniciais(?string $nome): string {
    $partes = preg_split('/\s+/', trim((string) $nome)) ?: [];
    $primeira = mb_substr($partes[0] ?? '', 0, 1);
    $ultima = count($partes) > 1 ? mb_substr(end($partes), 0, 1) : '';
    return mb_strtoupper($primeira . $ultima);
}

/** 'Y-m-d H:i:s' → '20/11' (curta), '20/11/2026' (longa) ou '17/11, 22:06' (com hora). */
function data_br(?string $data, string $formato = 'curta'): string {
    if (!$data) return '';
    $t = strtotime($data);
    return date(['curta' => 'd/m', 'longa' => 'd/m/Y', 'hora' => 'd/m, H:i', 'iso' => 'Y-m-d'][$formato], $t);
}

/** Quem está logado; sem login, vai para a tela de login. */
function usuario_da_pagina(): Usuario {
    $u = usuario();
    if ($u) return $u;
    if (eh_fetch()) json_resposta(['ok' => false, 'erro' => 'Entre na sua conta para continuar.'], 401);
    header('Location: login.php', true, 303);
    exit;
}

/**
 * Roda a leitura da página. Se a regra de negócio recusar (turma que não existe, aluno abrindo página de
 * professor...), volta para o início com o aviso de erro, do mesmo jeito que os formulários.
 */
function carregar(callable $leitura): mixed {
    try {
        return $leitura();
    } catch (Falha $f) {
        header('Location: index.php?erro=' . rawurlencode($f->getMessage()), true, 303);
        exit;
    }
}

/** Fundo do cabeçalho do mural/card: foto da turma, cor escolhida ou nada (padrão do CSS). */
function fundo_da_turma(EduConnect\Models\Turma $t): string {
    if ($t->imagem) return "url('imagem-turma.php?id=" . $t->id . "') center / cover no-repeat";
    return $t->cor;
}
