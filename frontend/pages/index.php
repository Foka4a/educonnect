<?php
require __DIR__ . '/../partes/pagina.php';

use EduConnect\Services\TurmaService;

$usuario = usuario_da_pagina();
formularios(); // só as ações comuns (sair, criar e participar de turma)
$turmas = (new TurmaService())->listarDoUsuario($usuario);
$titulo = 'EduConnect | Sala de aula';

include __DIR__ . '/../partes/cabeca.php';
?>
    <link rel="stylesheet" href="../style/inicio.css">
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
                <article class="card">
                    <header class="card-banner" style="background: <?= e($fundo ?: "url('../img/placeholder.jpg') center / cover no-repeat") ?>;">
                        <h3 class="card-text"><a href="mural.php?turma=<?= $t->id ?>"><?= e($t->nome) ?></a></h3>
                        <p class="card-text"><?= e($t->criadorNome) ?></p>
                    </header>
                    <div class="perfil perfil-card" aria-hidden="true"><?= e(iniciais($t->criadorNome)) ?></div>
                    <div class="card-body">
                        <button class="classroom-options botao-icone" type="button" aria-label="Opções da turma">
                            <span class="material-symbols-outlined" aria-hidden="true">more_vert</span>
                        </button>
                    </div>
                </article>
                <?php endforeach; ?>
            </section>
            <?php endif; ?>
        </main>
<?php include __DIR__ . '/../partes/rodape.php'; ?>
