<?php
/*
 * Barra superior + abertura da .estrutura-aplicacao + menu lateral.
 * Antes do include: $usuario, $turmas (turmas do usuário, para o menu) e, nas páginas de turma, $turma.
 * Feche com partes/rodape.php.
 */
$turma ??= null;
?>
<body>
    <header class="barra-superior">
        <div class="barra-superior__marca">
            <button class="botao-icone" type="button" aria-label="Abrir menu de navegação" aria-controls="menu-lateral"
                aria-expanded="true" data-menu-toggle>
                <span class="material-symbols-outlined" aria-hidden="true">menu</span>
            </button>
            <a class="marca" href="index.php" aria-label="EduConnect, página inicial">
                <img src="../img/icon.svg" alt="Logo do EduConnect">
                <span>EduConnect</span>
            </a>
            <?php if ($turma): ?>
            <p class="trilha"><span class="trilha__separador" aria-hidden="true">›</span><span class="trilha__nome"><?= e($turma->nome) ?></span></p>
            <?php endif; ?>
        </div>

        <div class="barra-superior__acoes">
            <details class="menu-suspenso barra-superior__criar">
                <summary class="botao-icone" aria-label="Criar ou participar de turma" title="Criar ou participar de turma">
                    <span class="material-symbols-outlined" aria-hidden="true">add</span>
                </summary>
                <ul class="menu-suspenso__lista">
                    <li>
                        <button class="menu-suspenso__item" type="button" data-abrir="modal-participar-turma">
                            <span class="material-symbols-outlined" aria-hidden="true">group_add</span>
                            <span>Participar da turma</span>
                        </button>
                    </li>
                    <li>
                        <button class="menu-suspenso__item" type="button" data-abrir="modal-criar-turma">
                            <span class="material-symbols-outlined" aria-hidden="true">add</span>
                            <span>Criar turma</span>
                        </button>
                    </li>
                </ul>
            </details>
            <details class="menu-suspenso barra-superior__perfil">
                <summary class="perfil" aria-label="Conta de <?= e($usuario->nome) ?>" title="<?= e($usuario->nome) ?>"><?= e(iniciais($usuario->nome)) ?></summary>
                <ul class="menu-suspenso__lista">
                    <li><p class="menu-suspenso__item"><?= e($usuario->nome) ?></p></li>
                    <li>
                        <form method="post"><?= acao('sair') ?>
                            <button class="menu-suspenso__item" type="submit">
                                <span class="material-symbols-outlined" aria-hidden="true">logout</span>
                                <span>Sair da conta</span>
                            </button>
                        </form>
                    </li>
                </ul>
            </details>
        </div>
    </header>

    <div class="estrutura-aplicacao">
        <aside class="menu-lateral" id="menu-lateral" aria-label="Navegação principal">
            <nav>
                <a class="menu-link<?= $turma ? '' : ' menu-link--ativo' ?>" href="index.php"<?= $turma ? '' : ' aria-current="page"' ?>>
                    <span class="material-symbols-outlined" aria-hidden="true">home</span>
                    <span>Início</span>
                </a>
                <details class="menu-grupo" open>
                    <summary class="menu-link menu-grupo__titulo">
                        <span class="material-symbols-outlined" aria-hidden="true">school</span>
                        <span>Turmas</span>
                        <span class="material-symbols-outlined menu-grupo__seta" aria-hidden="true">expand_more</span>
                    </summary>
                    <div class="menu-grupo__itens">
                        <?php foreach ($turmas as $t): $atual = $turma && $t->id === $turma->id; ?>
                        <a class="menu-link menu-link--subitem<?= $atual ? ' menu-link--ativo' : '' ?>" href="mural.php?turma=<?= $t->id ?>"<?= $atual ? ' aria-current="true"' : '' ?>>
                            <span class="material-symbols-outlined" aria-hidden="true">class</span>
                            <span><?= e($t->nome) ?></span>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </details>
            </nav>
        </aside>
