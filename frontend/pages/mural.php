<?php
require __DIR__ . '/../partes/pagina.php';

use EduConnect\Models\Publicacao;
use EduConnect\Services\PublicacaoService;
use EduConnect\Services\TurmaService;

$usuario = usuario_da_pagina();
formularios([
    'redefinir-codigo' => function () use ($usuario) {
        $codigo = (new TurmaService())->redefinirCodigo($usuario, inteiro('turma_id', 'Turma'));
        sucesso('Código redefinido: ' . $codigo, null, ['codigo' => $codigo]);
    },
    'personalizar-aparencia' => function () use ($usuario) {
        (new TurmaService())->personalizarAparencia($usuario, inteiro('turma_id', 'Turma'), arquivos_enviados('imagem')[0] ?? null,
            isset($_POST['cor']) ? (string) $_POST['cor'] : null, ($_POST['remover_foto'] ?? '') === '1');
        sucesso('Aparência atualizada.');
    },
    'criar-aviso' => function () use ($usuario) {
        (new PublicacaoService())->criarAviso($usuario, inteiro('turma_id', 'Turma'), texto('texto', 2000, true, 'O aviso'));
        sucesso('Aviso publicado.');
    },
    'excluir-aviso' => fn () => excluir_publicacao(Publicacao::AVISO),
    'excluir-atividade' => fn () => excluir_publicacao(Publicacao::ATIVIDADE),
    'excluir-material' => fn () => excluir_publicacao(Publicacao::MATERIAL),
]);
[$turma, $turmas, $proximas, $feed] = carregar(function () use ($usuario) {
    $turmas = new TurmaService();
    $publicacoes = new PublicacaoService();
    $turma = $turmas->abrir($usuario, (int) ($_GET['turma'] ?? 0));
    return [
        $turma,
        $turmas->listarDoUsuario($usuario),
        $publicacoes->proximasAtividades($usuario, $turma->id),
        $publicacoes->listar($usuario, $turma->id, [Publicacao::AVISO, Publicacao::ATIVIDADE, Publicacao::MATERIAL]),
    ];
});
$professor = $turma->ehProfessor();
$fundo = fundo_da_turma($turma);
$titulo = 'Mural | ' . $turma->nome;

include __DIR__ . '/../partes/cabeca.php';
?>
    <link rel="stylesheet" href="../modais/css/configuracoes-turma.css">
    <link rel="stylesheet" href="../modais/css/personalizar-aparencia.css">
    <link rel="stylesheet" href="../style/mural.css">
    <script src="../modais/components/configuracoes-turma.js" defer></script>
    <script src="../modais/components/personalizar-aparencia.js" defer></script>
    <script src="../scripts/mural.js" defer></script>
</head>
<?php include __DIR__ . '/../partes/topo.php'; ?>

<?php $aba = 'mural'; include __DIR__ . '/../partes/abas-turma.php'; ?>

        <main class="conteudo-principal">
            <div class="mural">
                <section class="mural-cabecalho<?= $fundo ? ' cabecalho-personalizado' : '' ?>"<?= $fundo ? ' style="--cabecalho-fundo: ' . e($fundo) . '"' : '' ?> aria-labelledby="titulo-disciplina">
                    <div class="mural-cabecalho__conteudo">
                        <h1 id="titulo-disciplina"><?= e($turma->nome) ?></h1>
                        <p class="mural-cabecalho__professor">Prof. <?= e($turma->criadorNome) ?></p>
                    </div>

                    <div class="mural-cabecalho__acoes" aria-label="Ações da turma">
                        <?php if ($professor): ?>
                        <button class="botao-icone" type="button" data-abrir="modal-personalizar-aparencia" aria-label="Personalizar aparência" title="Personalizar aparência">
                            <span class="material-symbols-outlined" aria-hidden="true">edit</span>
                        </button>
                        <?php endif; ?>
                        <button class="botao-icone" type="button" data-abrir="modal-informacoes-turma" aria-label="Ver informações da turma" title="Informações da turma">
                            <span class="material-symbols-outlined" aria-hidden="true">info</span>
                        </button>
                    </div>
                </section>

                <div class="mural-corpo">
                    <aside class="mural-lateral" aria-label="Resumo da turma">
                        <?php if ($professor): ?>
                        <section class="cartao-mural cartao-codigo" aria-labelledby="titulo-codigo">
                            <header class="cartao-mural__cabecalho">
                                <h2 id="titulo-codigo">Código da turma</h2>
                                <details class="menu-suspenso cartao-codigo__menu">
                                    <summary class="botao-icone" aria-label="Opções do código da turma" title="Mais opções">
                                        <span class="material-symbols-outlined" aria-hidden="true">more_vert</span>
                                    </summary>
                                    <ul class="menu-suspenso__lista">
                                        <li><button class="menu-suspenso__item" type="button" data-copiar-codigo>Copiar código</button></li>
                                        <li>
                                            <form method="post"><?= acao('redefinir-codigo') ?>
                                                <input type="hidden" name="turma_id" value="<?= $turma->id ?>">
                                                <button class="menu-suspenso__item" type="submit">Redefinir código</button>
                                            </form>
                                        </li>
                                    </ul>
                                </details>
                            </header>
                            <div class="cartao-codigo__linha">
                                <p class="cartao-codigo__valor" data-codigo><?= e($turma->codigo) ?></p>
                                <button class="botao-icone" type="button" data-codigo-tela-cheia aria-label="Mostrar código em tela cheia" title="Mostrar em tela cheia">
                                    <span class="material-symbols-outlined" aria-hidden="true">fullscreen</span>
                                </button>
                            </div>
                        </section>
                        <?php endif; ?>

                        <section class="cartao-mural cartao-proximas" aria-labelledby="titulo-proximas">
                            <h2 id="titulo-proximas">Próximas atividades</h2>
                            <ul class="cartao-proximas__lista">
                                <?php foreach ($proximas as $a): ?>
                                <li>
                                    <a href="instrucoes.php?atividade=<?= $a->id ?>"><?= e($a->titulo) ?></a>
                                    <span>Entrega: <?= data_br($a->entregaEm) ?></span>
                                </li>
                                <?php endforeach; ?>
                                <?php if (!$proximas): ?><li><span>Nenhuma atividade com prazo.</span></li><?php endif; ?>
                            </ul>
                            <a class="cartao-proximas__ver-tudo" href="atividades.php?turma=<?= $turma->id ?>">Ver tudo</a>
                        </section>
                    </aside>

                    <section class="mural-feed" aria-labelledby="titulo-feed">
                        <h2 class="visualmente-oculto" id="titulo-feed">Publicações da turma</h2>

                        <?php if ($professor): ?>
                        <div class="mural-feed__acoes">
                            <details class="novo-aviso">
                                <summary class="novo-aviso__botao">
                                    <span class="material-symbols-outlined" aria-hidden="true">edit</span>
                                    Novo aviso
                                </summary>
                                <form class="novo-aviso__form" method="post" data-form-aviso><?= acao('criar-aviso') ?>
                                    <input type="hidden" name="turma_id" value="<?= $turma->id ?>">
                                    <label class="visualmente-oculto" for="texto-aviso">Texto do aviso</label>
                                    <textarea class="novo-aviso__campo" id="texto-aviso" name="texto" rows="4" maxlength="2000" placeholder="Escreva um aviso para a turma" required></textarea>
                                    <div class="dialogo__acoes">
                                        <button class="botao-texto" type="button" data-cancelar-aviso>Cancelar</button>
                                        <button class="botao-texto" type="submit">Postar</button>
                                    </div>
                                </form>
                            </details>
                            <button class="mural-feed__config" type="button" data-abrir="modal-configuracoes-turma">
                                <span class="material-symbols-outlined" aria-hidden="true">settings</span>
                                Configurações do mural
                            </button>
                        </div>
                        <?php endif; ?>

                        <?php if (!$feed): ?>
                        <div class="estado-vazio">
                            <span class="material-symbols-outlined" aria-hidden="true">forum</span>
                            <p>Nada publicado ainda.</p>
                        </div>
                        <?php endif; ?>

                        <ul class="lista-mural" data-feed>
                            <?php foreach ($feed as $p):
                                $podeExcluir = $professor;
                                if ($p->tipo === Publicacao::AVISO): ?>
                            <li class="item-mural item-mural--aviso" data-titulo="Aviso" data-acao-excluir="excluir-aviso" data-campo="aviso_id" data-id="<?= $p->id ?>">
                                <div class="item-mural__link">
                                    <span class="item-mural__icone material-symbols-outlined" aria-hidden="true">campaign</span>
                                    <span class="item-mural__texto">
                                        <span><?= e($p->autor) ?></span>
                                        <time datetime="<?= data_br($p->criadoEm, 'iso') ?>"><?= data_br($p->criadoEm) ?></time>
                                        <span class="item-mural__corpo"><?= nl2br(e($p->descricao)) ?></span>
                                    </span>
                                </div>
                                <?php else:
                                    $atividade = $p->tipo === Publicacao::ATIVIDADE;
                                    $link = $atividade ? 'instrucoes.php?atividade=' . $p->id : 'atividades.php?turma=' . $turma->id . '#material-' . $p->id; ?>
                            <li class="item-mural" data-titulo="<?= e($p->titulo) ?>" data-acao-excluir="excluir-<?= $p->tipo ?>" data-campo="<?= $p->tipo ?>_id" data-id="<?= $p->id ?>">
                                <a class="item-mural__link" href="<?= e($link) ?>">
                                    <span class="item-mural__icone material-symbols-outlined" aria-hidden="true"><?= $atividade ? 'assignment' : 'book' ?></span>
                                    <span class="item-mural__texto">
                                        <span><?= e($p->autor) ?> postou <?= $atividade ? 'uma nova atividade' : 'um novo material' ?>: <?= e($p->titulo) ?></span>
                                        <time datetime="<?= data_br($p->criadoEm, 'iso') ?>"><?= data_br($p->criadoEm) ?></time>
                                    </span>
                                </a>
                                <?php endif; ?>
                                <?php if ($podeExcluir): ?>
                                <details class="menu-suspenso item-mural__menu">
                                    <summary class="botao-icone" aria-label="Mais opções para <?= e($p->titulo ?: 'o aviso') ?>" title="Mais opções">
                                        <span class="material-symbols-outlined" aria-hidden="true">more_vert</span>
                                    </summary>
                                    <ul class="menu-suspenso__lista">
                                        <?php if ($p->tipo !== Publicacao::AVISO): ?>
                                        <li><a class="menu-suspenso__item" href="atividades.php?turma=<?= $turma->id ?>&amp;editar=<?= $p->id ?>">Editar</a></li>
                                        <?php endif; ?>
                                        <li><button class="menu-suspenso__item" type="button" data-excluir-item>Excluir</button></li>
                                    </ul>
                                </details>
                                <?php endif; ?>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </section>
                </div>
            </div>
        </main>

    <?php if ($professor): ?>
    <!-- Código da turma em tela cheia (botão do cartão "Código da turma") -->
    <dialog class="dialogo dialogo-codigo" id="dialogo-codigo" aria-labelledby="titulo-dialogo-codigo">
        <form method="dialog">
            <h2 class="dialogo__titulo" id="titulo-dialogo-codigo">Código da turma</h2>
            <p class="dialogo-codigo__valor" data-codigo><?= e($turma->codigo) ?></p>
            <div class="dialogo__acoes">
                <button class="botao-texto" type="button" data-copiar-codigo>Copiar</button>
                <button class="botao-texto" type="submit">Fechar</button>
            </div>
        </form>
    </dialog>

    <!-- Confirmação do "Excluir" do menu ⋮ das publicações: o mural.js põe a rota e o id do item escolhido -->
    <dialog class="dialogo" id="dialogo-excluir-publicacao" aria-labelledby="titulo-excluir-publicacao">
        <form method="post">
            <input type="hidden" name="acao" data-excluir-acao>
            <input type="hidden" data-excluir-id>
            <h2 class="dialogo__titulo" id="titulo-excluir-publicacao">Excluir publicação?</h2>
            <p><strong data-excluir-titulo></strong></p>
            <p>A publicação sai do mural da turma.</p>
            <div class="dialogo__acoes">
                <button class="botao-texto" type="submit" formmethod="dialog" formnovalidate>Cancelar</button>
                <button class="botao-texto botao-texto--perigo" type="submit">Excluir</button>
            </div>
        </form>
    </dialog>

    <!-- Personalizar aparência (lápis do cabeçalho do Mural) -->
    <edu-modal-personalizar-aparencia turma-id="<?= $turma->id ?>" cor="<?= e($turma->cor) ?>"
        <?php if ($turma->imagem): ?>foto="imagem-turma.php?id=<?= $turma->id ?>"<?php endif; ?>></edu-modal-personalizar-aparencia>
    <?php endif; ?>
<?php include __DIR__ . '/../partes/rodape.php'; ?>
