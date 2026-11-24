<?php
require __DIR__ . '/../partes/pagina.php';

use EduConnect\Models\Participacao;
use EduConnect\Services\PessoaService;
use EduConnect\Services\TurmaService;

$usuario = usuario_da_pagina();
formularios([
    'promover-professor' => function () use ($usuario) {
        (new PessoaService())->promover($usuario, inteiro('turma_id', 'Turma'), inteiro('pessoa_id', 'Pessoa'));
        sucesso('Pessoa promovida a professor.');
    },
    'remover-pessoa' => function () use ($usuario) {
        (new PessoaService())->remover($usuario, inteiro('turma_id', 'Turma'), inteiro('pessoa_id', 'Pessoa'));
        sucesso('Pessoa removida da turma.');
    },
]);
[$turma, $turmas, $pessoas] = carregar(function () use ($usuario) {
    $turmas = new TurmaService();
    $turma = $turmas->abrir($usuario, (int) ($_GET['turma'] ?? 0));
    return [$turma, $turmas->listarDoUsuario($usuario), (new PessoaService())->listar($usuario, $turma->id)];
});
$professores = array_values(array_filter($pessoas, fn ($p) => $p->papel === Participacao::PROFESSOR));
$estudantes = array_values(array_filter($pessoas, fn ($p) => $p->papel === Participacao::ALUNO));
// Mesmas regras do PessoaService: professor remove estudantes; só o administrador promove e remove professores.
$professor = $turma->ehProfessor();
$administrador = $turma->ehAdministrador($usuario);
$titulo = 'EduConnect | Pessoas';

include __DIR__ . '/../partes/cabeca.php';
?>
    <link rel="stylesheet" href="../modais/css/configuracoes-turma.css">
    <link rel="stylesheet" href="../style/pessoas.css">
    <link rel="stylesheet" href="../modais/css/promover-professor.css">
    <script src="../modais/components/promover-professor.js" defer></script>
    <script src="../modais/components/configuracoes-turma.js" defer></script>
    <script src="../scripts/pessoas.js" defer></script>
</head>
<?php include __DIR__ . '/../partes/topo.php'; ?>

<?php $aba = 'pessoas'; include __DIR__ . '/../partes/abas-turma.php'; ?>

        <main class="conteudo-principal">
            <div class="pessoas">
                <h1 class="visualmente-oculto">Pessoas da turma</h1>

                <section class="pessoas__secao" aria-labelledby="titulo-professores">
                    <header class="pessoas__cabecalho">
                        <h2 id="titulo-professores">Professores</h2>
                    </header>
                    <ul class="lista-pessoas" data-lista-professores>
                        <?php foreach ($professores as $p): ?>
                        <li class="pessoa" data-nome="<?= e($p->nome) ?>" data-pessoa-id="<?= $p->usuarioId ?>">
                            <span class="pessoa__avatar" aria-hidden="true"><?= e(mb_substr($p->nome, 0, 1)) ?></span>
                            <span class="pessoa__nome"><?= e($p->nome) ?><?= $p->usuarioId === $turma->criadorId ? ' (administrador)' : '' ?></span>
                            <?php if ($administrador && $p->usuarioId !== $turma->criadorId): ?>
                            <details class="menu-suspenso pessoa__menu">
                                <summary class="botao-icone" aria-label="Mais opções para <?= e($p->nome) ?>" title="Mais opções">
                                    <span class="material-symbols-outlined" aria-hidden="true">more_vert</span>
                                </summary>
                                <ul class="menu-suspenso__lista">
                                    <li><button class="menu-suspenso__item" type="button" data-remover>Remover da turma</button></li>
                                </ul>
                            </details>
                            <?php endif; ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </section>

                <section class="pessoas__secao" aria-labelledby="titulo-estudantes">
                    <header class="pessoas__cabecalho">
                        <h2 id="titulo-estudantes">Estudantes</h2>
                        <p class="pessoas__contador"><?= count($estudantes) ?> <?= count($estudantes) === 1 ? 'estudante' : 'estudantes' ?></p>
                    </header>

                    <?php if ($estudantes): ?>
                    <ul class="lista-pessoas" data-lista-estudantes>
                        <?php foreach ($estudantes as $p): ?>
                        <li class="pessoa" data-nome="<?= e($p->nome) ?>" data-pessoa-id="<?= $p->usuarioId ?>">
                            <span class="pessoa__avatar" aria-hidden="true"><?= e(mb_substr($p->nome, 0, 1)) ?></span>
                            <span class="pessoa__nome"><?= e($p->nome) ?></span>
                            <?php if ($professor): ?>
                            <details class="menu-suspenso pessoa__menu">
                                <summary class="botao-icone" aria-label="Mais opções para <?= e($p->nome) ?>" title="Mais opções">
                                    <span class="material-symbols-outlined" aria-hidden="true">more_vert</span>
                                </summary>
                                <ul class="menu-suspenso__lista">
                                    <?php if ($administrador): ?>
                                    <li><button class="menu-suspenso__item" type="button" data-promover>Promover a professor</button></li>
                                    <?php endif; ?>
                                    <li><button class="menu-suspenso__item" type="button" data-remover>Remover da turma</button></li>
                                </ul>
                            </details>
                            <?php endif; ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php else: ?>
                    <div class="pessoas__vazio">
                        <span class="material-symbols-outlined" aria-hidden="true">group_add</span>
                        <p>Adicione estudantes a essa turma<?= $professor ? ': passe o código ' . e($turma->codigo) . ' (está no Mural)' : '' ?></p>
                    </div>
                    <?php endif; ?>
                </section>
            </div>
        </main>

    <?php if ($professor): ?>
    <!-- Confirmação do "Remover da turma" do menu ⋮: o pessoas.js põe o id da pessoa escolhida -->
    <dialog class="dialogo" id="dialogo-remover" aria-labelledby="titulo-remover" aria-describedby="descricao-remover">
        <form method="post"><?= acao('remover-pessoa') ?>
            <input type="hidden" name="turma_id" value="<?= $turma->id ?>">
            <input type="hidden" name="pessoa_id" data-remover-id>
            <h2 class="dialogo__titulo" id="titulo-remover">Remover da turma?</h2>
            <div id="descricao-remover">
                <p><strong data-remover-nome></strong></p>
                <p>A pessoa deixa de ver o mural, as atividades e as notas desta turma.</p>
            </div>
            <div class="dialogo__acoes">
                <button class="botao-texto" type="submit" formmethod="dialog" formnovalidate>Cancelar</button>
                <button class="botao-texto botao-texto--perigo" type="submit">Remover</button>
            </div>
        </form>
    </dialog>
    <?php endif; ?>
    <?php if ($administrador): ?>
    <edu-modal-promover-professor turma-id="<?= $turma->id ?>"></edu-modal-promover-professor>
    <?php endif; ?>
<?php include __DIR__ . '/../partes/rodape.php'; ?>
