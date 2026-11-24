<?php
require __DIR__ . '/../partes/pagina.php';

use EduConnect\Services\TurmaService;

$usuario = usuario_da_pagina();
formularios([
    // menu ⋮ do card: o administrador exclui a turma; os outros participantes saem dela
    'excluir-turma' => function () use ($usuario) {
        (new TurmaService())->excluir($usuario, inteiro('turma_id', 'Turma'));
        sucesso('Turma excluída.');
    },
    'sair-da-turma' => function () use ($usuario) {
        (new TurmaService())->sair($usuario, inteiro('turma_id', 'Turma'));
        sucesso('Você saiu da turma.');
    },
]);
$turmas = (new TurmaService())->listarDoUsuario($usuario);
$titulo = 'EduConnect | Sala de aula';

include __DIR__ . '/../partes/cabeca.php';
?>
    <link rel="stylesheet" href="../style/inicio.css">
    <script src="../scripts/inicio.js" defer></script>
</head>
<?php include __DIR__ . '/../partes/topo.php'; ?>

        <main class="conteudo-principal inicio">
            <h1 id="titulo-turmas">Turmas</h1>
            <?php if (!$turmas): ?>
            <div class="estado-vazio">
                <span class="material-symbols-outlined" aria-hidden="true">school</span>
                <p>Você ainda não participa de nenhuma turma. Use o “+” no topo para criar uma turma ou entrar com um código.</p>
            </div>
            <?php else: ?>
            <section class="cards-grid" aria-labelledby="titulo-turmas">
                <?php foreach ($turmas as $t): $fundo = fundo_da_turma($t); ?>
                <article class="card" data-turma-id="<?= $t->id ?>" data-nome="<?= e($t->nome) ?>">
                    <header class="card-banner" style="background: <?= e($fundo ?: "url('../img/placeholder.jpg') center / cover no-repeat") ?>;">
                        <h3 class="card-text"><a href="mural.php?turma=<?= $t->id ?>"><?= e($t->nome) ?></a></h3>
                        <p class="card-text"><?= e($t->criadorNome) ?></p>
                    </header>
                    <div class="perfil perfil-card" aria-hidden="true"><?= e(iniciais($t->criadorNome)) ?></div>
                    <div class="card-body">
                        <details class="menu-suspenso card-menu">
                            <summary class="botao-icone" aria-label="Opções da turma <?= e($t->nome) ?>" title="Opções da turma">
                                <span class="material-symbols-outlined" aria-hidden="true">more_vert</span>
                            </summary>
                            <ul class="menu-suspenso__lista">
                                <?php if ($t->ehAdministrador($usuario)): ?>
                                <li><button class="menu-suspenso__item" type="button" data-confirmar="dialogo-excluir-turma">Excluir turma</button></li>
                                <?php else: ?>
                                <li><button class="menu-suspenso__item" type="button" data-confirmar="dialogo-sair-turma">Sair da turma</button></li>
                                <?php endif; ?>
                            </ul>
                        </details>
                    </div>
                </article>
                <?php endforeach; ?>
            </section>
            <?php endif; ?>
        </main>

    <!-- Confirmações do menu ⋮ dos cards: o inicio.js põe o id e o nome da turma escolhida -->
    <dialog class="dialogo" id="dialogo-excluir-turma" aria-labelledby="titulo-excluir-turma" aria-describedby="descricao-excluir-turma">
        <form method="post"><?= acao('excluir-turma') ?>
            <input type="hidden" name="turma_id" data-turma-id>
            <h2 class="dialogo__titulo" id="titulo-excluir-turma">Excluir turma?</h2>
            <div id="descricao-excluir-turma">
                <p><strong data-turma-nome></strong></p>
                <p>Mural, atividades, materiais, entregas, notas e comentários serão excluídos para todos. Esta ação não pode ser desfeita.</p>
            </div>
            <div class="dialogo__acoes">
                <button class="botao-texto" type="submit" formmethod="dialog" formnovalidate>Cancelar</button>
                <button class="botao-texto botao-texto--perigo" type="submit">Excluir turma</button>
            </div>
        </form>
    </dialog>
    <dialog class="dialogo" id="dialogo-sair-turma" aria-labelledby="titulo-sair-turma" aria-describedby="descricao-sair-turma">
        <form method="post"><?= acao('sair-da-turma') ?>
            <input type="hidden" name="turma_id" data-turma-id>
            <h2 class="dialogo__titulo" id="titulo-sair-turma">Sair da turma?</h2>
            <div id="descricao-sair-turma">
                <p><strong data-turma-nome></strong></p>
                <p>Você deixa de ver o mural, as atividades e as notas desta turma. Para voltar, será preciso usar o código da turma de novo.</p>
            </div>
            <div class="dialogo__acoes">
                <button class="botao-texto" type="submit" formmethod="dialog" formnovalidate>Cancelar</button>
                <button class="botao-texto botao-texto--perigo" type="submit">Sair</button>
            </div>
        </form>
    </dialog>
<?php include __DIR__ . '/../partes/rodape.php'; ?>
