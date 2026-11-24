<?php
require __DIR__ . '/../partes/pagina.php';

use EduConnect\Models\Publicacao;
use EduConnect\Services\ComentarioService;
use EduConnect\Services\EntregaService;
use EduConnect\Services\PublicacaoService;
use EduConnect\Services\TurmaService;

$usuario = usuario_da_pagina();
formularios([
    // "Seu trabalho" (aluno): Entregar/Reenviar ou Cancelar envio
    'entregar-trabalho' => function () use ($usuario) {
        $entrega = (new EntregaService())->entregar($usuario, inteiro('atividade_id', 'Atividade'), arquivos_enviados('arquivos'));
        sucesso($entrega->atrasada ? 'Trabalho entregue com atraso.' : 'Trabalho entregue.');
    },
    'cancelar-envio' => function () use ($usuario) {
        (new EntregaService())->cancelar($usuario, inteiro('atividade_id', 'Atividade'));
        sucesso('Envio cancelado.');
    },
    // comentário da turma, ou particular (aluno → professores)
    'comentar' => function () use ($usuario) {
        $c = (new ComentarioService())->comentar($usuario, texto('texto', 1000, true, 'O comentário'), inteiro('publicacao_id', 'Item'),
            null, ($_POST['particular'] ?? '0') === '1', null);
        sucesso($c->particular ? 'Comentário particular enviado.' : 'Comentário publicado.');
    },
    'excluir-atividade' => fn () => excluir_publicacao(Publicacao::ATIVIDADE),
]);
[$atividade, $turma, $turmas, $anexos, $comentarios, $trabalho, $particulares] = carregar(function () use ($usuario) {
    $publicacoes = new PublicacaoService();
    $turmas = new TurmaService();
    $comentarios = new ComentarioService();
    $atividade = $publicacoes->abrir($usuario, (int) ($_GET['atividade'] ?? 0), Publicacao::ATIVIDADE);
    $turma = $turmas->abrir($usuario, $atividade->turmaId);
    $aluno = !$turma->ehProfessor();
    return [
        $atividade, $turma, $turmas->listarDoUsuario($usuario),
        $publicacoes->anexosDe([$atividade])[$atividade->id],
        $comentarios->publicos($usuario, $atividade->id),
        $aluno ? (new EntregaService())->doAluno($usuario, $atividade->id) : null,
        $aluno ? $comentarios->meusParticulares($usuario, $atividade->id) : [],
    ];
});
$professor = $turma->ehProfessor();
$entrega = $trabalho['entrega'] ?? null;
$entregue = $entrega?->estaEntregue() ?? false;
$titulo = 'EduConnect | ' . $atividade->titulo;

/** Uma lista de comentários (públicos ou particulares). */
function lista_comentarios(array $comentarios, string $classe): void {
    echo '<ul class="' . $classe . '">';
    foreach ($comentarios as $c) {
        echo '<li><span class="comentarios-turma__avatar" aria-hidden="true">' . e(iniciais($c->autor)) . '</span><div><strong>'
            . e($c->autor) . '</strong> • ' . data_br($c->criadoEm, 'hora') . '<p>' . nl2br(e($c->texto)) . '</p></div></li>';
    }
    echo '</ul>';
}

include __DIR__ . '/../partes/cabeca.php';
?>
    <link rel="stylesheet" href="../modais/css/configuracoes-turma.css">
    <link rel="stylesheet" href="../style/instrucoes.css">
    <script src="../modais/components/configuracoes-turma.js" defer></script>
    <script src="../scripts/instrucoes.js" defer></script>
</head>
<?php include __DIR__ . '/../partes/topo.php'; ?>

        <!-- Abas da atividade -->
        <nav class="abas-mural" aria-label="Seções da atividade">
            <a class="aba aba--ativa" href="instrucoes.php?atividade=<?= $atividade->id ?>" aria-current="page">Instruções</a>
            <?php if ($professor): ?>
            <a class="aba" href="trabalho-estudantes.php?atividade=<?= $atividade->id ?>">Trabalhos dos estudantes</a>
            <button class="botao-icone abas-mural__config" type="button" data-abrir="modal-configuracoes-turma" aria-label="Configurações da turma" title="Configurações da turma">
                <span class="material-symbols-outlined" aria-hidden="true">settings</span>
            </button>
            <?php endif; ?>
        </nav>

        <main class="conteudo-principal">
            <article class="instrucoes" aria-labelledby="titulo-atividade">
                <header class="instrucoes__cabecalho">
                    <span class="instrucoes__icone material-symbols-outlined" aria-hidden="true">assignment</span>
                    <h1 id="titulo-atividade"><?= e($atividade->titulo) ?></h1>
                    <?php if ($professor): ?>
                    <details class="menu-suspenso instrucoes__menu">
                        <summary class="botao-icone" aria-label="Mais opções da atividade" title="Mais opções">
                            <span class="material-symbols-outlined" aria-hidden="true">more_vert</span>
                        </summary>
                        <ul class="menu-suspenso__lista">
                            <li><a class="menu-suspenso__item" href="atividades.php?turma=<?= $turma->id ?>&amp;editar=<?= $atividade->id ?>">Editar</a></li>
                            <li><button class="menu-suspenso__item" type="button" data-excluir>Excluir</button></li>
                        </ul>
                    </details>
                    <?php endif; ?>
                </header>

                <p class="instrucoes__meta"><?= e($atividade->autor) ?> <span aria-hidden="true">•</span> <time datetime="<?= data_br($atividade->criadoEm, 'iso') ?>"><?= data_br($atividade->criadoEm, 'longa') ?></time></p>
                <p class="instrucoes__pontos"><?= $atividade->entregaEm ? 'Data de entrega: ' . data_br($atividade->entregaEm, 'hora') : 'Sem data de entrega' ?> · 100 pontos</p>

                <div class="instrucoes__descricao">
                    <?= $atividade->descricao /* já limpa pelo back-end */ ?>
                    <?php if ($anexos): ?>
                    <ul class="lista-arquivos" aria-label="Anexos">
                        <?php foreach ($anexos as $a): ?>
                        <li><a href="arquivo.php?id=<?= $a->id ?>"><span class="material-symbols-outlined" aria-hidden="true">attach_file</span><?= e($a->nome) ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>
                </div>

                <?php if (!$professor): ?>
                <!-- Só aluno: o trabalho dele (enviar, reenviar ou cancelar) e a conversa particular com os professores -->
                <section class="seu-trabalho" aria-labelledby="titulo-seu-trabalho">
                    <header class="seu-trabalho__cabecalho">
                        <h2 id="titulo-seu-trabalho">Seu trabalho</h2>
                        <p class="seu-trabalho__status">
                            <?php if ($entrega?->foiCorrigida()): ?>Nota: <?= $entrega->nota ?>/100
                            <?php elseif ($entregue): ?>Entregue em <?= data_br($entrega->enviadoEm, 'hora') ?><?= $entrega->atrasada ? ' (com atraso)' : '' ?>
                            <?php else: ?>Pendente<?php endif; ?>
                        </p>
                    </header>
                    <?php if ($trabalho['anexos']): ?>
                    <ul class="lista-arquivos" aria-label="Arquivos entregues">
                        <?php foreach ($trabalho['anexos'] as $a): ?>
                        <li><a href="arquivo.php?id=<?= $a->id ?>"><span class="material-symbols-outlined" aria-hidden="true">description</span><?= e($a->nome) ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>
                    <?php if (!$entrega?->foiCorrigida()): ?>
                    <form class="seu-trabalho__form" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="atividade_id" value="<?= $atividade->id ?>">
                        <label class="seu-trabalho__arquivos">
                            <span><?= $entregue ? 'Enviar de novo (substitui os arquivos)' : 'Arquivos do trabalho' ?> · até 5 arquivos de 20 MB</span>
                            <input type="file" name="arquivos[]" multiple>
                        </label>
                        <?php if ($atividade->prazoVencido(agora())): ?><p class="seu-trabalho__atraso">O prazo já passou: o envio fica marcado como atrasado.</p><?php endif; ?>
                        <div class="dialogo__acoes">
                            <?php if ($entregue): ?>
                            <button class="botao-texto botao-texto--perigo" type="submit" name="acao" value="cancelar-envio" formnovalidate>Cancelar envio</button>
                            <?php endif; ?>
                            <button class="seu-trabalho__entregar" type="submit" name="acao" value="entregar-trabalho"><?= $entregue ? 'Reenviar' : 'Entregar' ?></button>
                        </div>
                    </form>
                    <?php endif; ?>

                    <h3 class="seu-trabalho__subtitulo">
                        <span class="material-symbols-outlined" aria-hidden="true">person</span>
                        <span>Comentários particulares</span>
                    </h3>
                    <?php lista_comentarios($particulares, 'comentarios-turma__lista'); ?>
                    <form class="comentarios-turma__form" method="post"><?= acao('comentar') ?>
                        <input type="hidden" name="publicacao_id" value="<?= $atividade->id ?>">
                        <input type="hidden" name="particular" value="1">
                        <label class="visualmente-oculto" for="comentario-particular">Comentário particular para os professores</label>
                        <input class="comentarios-turma__campo" id="comentario-particular" name="texto" maxlength="1000" placeholder="Adicionar comentário particular..." required autocomplete="off">
                        <button class="botao-icone" type="submit" aria-label="Enviar comentário particular">
                            <span class="material-symbols-outlined" aria-hidden="true">send</span>
                        </button>
                    </form>
                </section>
                <?php endif; ?>

                <section class="comentarios-turma" aria-labelledby="titulo-comentarios">
                    <h2 id="titulo-comentarios">
                        <span class="material-symbols-outlined" aria-hidden="true">group</span>
                        <span>Comentários da turma</span>
                    </h2>
                    <?php lista_comentarios($comentarios, 'comentarios-turma__lista'); ?>
                    <details class="comentarios-turma__novo">
                        <summary>
                            <span class="material-symbols-outlined" aria-hidden="true">comment</span>
                            Adicionar comentário
                        </summary>
                        <form class="comentarios-turma__form" method="post"><?= acao('comentar') ?>
                            <input type="hidden" name="publicacao_id" value="<?= $atividade->id ?>">
                            <label class="visualmente-oculto" for="comentario-turma">Comentário da turma</label>
                            <input class="comentarios-turma__campo" id="comentario-turma" name="texto" maxlength="1000" placeholder="Adicionar comentário para a turma..." required autocomplete="off">
                            <button class="botao-icone" type="submit" aria-label="Enviar comentário">
                                <span class="material-symbols-outlined" aria-hidden="true">send</span>
                            </button>
                        </form>
                    </details>
                </section>
            </article>
        </main>

    <?php if ($professor): ?>
    <!-- Confirmação do "Excluir" do menu ⋮ -->
    <dialog class="dialogo" id="dialogo-excluir" aria-labelledby="titulo-excluir">
        <form method="post"><?= acao('excluir-atividade') ?>
            <input type="hidden" name="atividade_id" value="<?= $atividade->id ?>">
            <h2 class="dialogo__titulo" id="titulo-excluir">Excluir atividade?</h2>
            <p>As entregas, as notas e os comentários de <strong><?= e($atividade->titulo) ?></strong> também serão excluídos.</p>
            <div class="dialogo__acoes">
                <button class="botao-texto" type="submit" formmethod="dialog" formnovalidate>Cancelar</button>
                <button class="botao-texto botao-texto--perigo" type="submit">Excluir</button>
            </div>
        </form>
    </dialog>
    <?php endif; ?>
<?php include __DIR__ . '/../partes/rodape.php'; ?>
