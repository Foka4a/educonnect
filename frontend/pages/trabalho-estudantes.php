<?php
require __DIR__ . '/../partes/pagina.php';

use EduConnect\Models\Publicacao;
use EduConnect\Services\ComentarioService;
use EduConnect\Services\EntregaService;
use EduConnect\Services\PublicacaoService;
use EduConnect\Services\TurmaService;

$usuario = usuario_da_pagina();
// Nota e comentário particular chegam por fetch() (trabalho-estudantes.js) e respondem JSON, sem recarregar a página.
formularios([
    'salvar-nota' => function () use ($usuario) {
        $entrega = (new EntregaService())->salvarNota($usuario, inteiro('entrega_id', 'Entrega'), (string) ($_POST['nota'] ?? ''));
        sucesso($entrega->nota === null ? 'Sem nota.' : 'Nota salva.', null, ['nota' => $entrega->nota]);
    },
    'comentar' => function () use ($usuario) {
        $c = (new ComentarioService())->comentar($usuario, texto('texto', 1000, true, 'O comentário'), null,
            inteiro('entrega_id', 'Entrega'), true, null);
        sucesso('Comentário particular enviado.', null, ['comentario' => $c->paraArray()]);
    },
]);
// Só professores (o EntregaService::daAtividade confere); aluno que abrir esta página volta para o início com o aviso.
[$atividade, $turma, $turmas, $alunos] = carregar(function () use ($usuario) {
    $turmas = new TurmaService();
    $atividade = (new PublicacaoService())->abrir($usuario, (int) ($_GET['atividade'] ?? 0), Publicacao::ATIVIDADE);
    $alunos = (new EntregaService())->daAtividade($usuario, $atividade->id);
    return [$atividade, $turmas->abrir($usuario, $atividade->turmaId), $turmas->listarDoUsuario($usuario), $alunos];
});
$entregues = array_values(array_filter($alunos, fn ($a) => $a['entrega']?->estaEntregue()));
$pendentes = array_values(array_filter($alunos, fn ($a) => !$a['entrega']?->estaEntregue()));
$titulo = 'EduConnect | Trabalhos dos estudantes';

include __DIR__ . '/../partes/cabeca.php';
?>
    <link rel="stylesheet" href="../modais/css/configuracoes-turma.css">
    <link rel="stylesheet" href="../style/trabalho-estudantes.css">
    <script src="../modais/components/configuracoes-turma.js" defer></script>
    <script src="../scripts/trabalho-estudantes.js" defer></script>
</head>
<?php include __DIR__ . '/../partes/topo.php'; ?>

        <!-- Abas da atividade (mapa, tela "Trabalhos dos estudantes") -->
        <nav class="abas-mural" aria-label="Seções da atividade">
            <a class="aba" href="instrucoes.php?atividade=<?= $atividade->id ?>">Instruções</a>
            <a class="aba aba--ativa" href="trabalho-estudantes.php?atividade=<?= $atividade->id ?>" aria-current="page">Trabalhos dos estudantes</a>
            <button class="botao-icone abas-mural__config" type="button" data-abrir="modal-configuracoes-turma" aria-label="Configurações da turma" title="Configurações da turma">
                <span class="material-symbols-outlined" aria-hidden="true">settings</span>
            </button>
        </nav>

        <main class="conteudo-principal notas">
            <section class="notas__resumo" aria-labelledby="titulo-atividade">
                <h1 id="titulo-atividade"><?= e($atividade->titulo) ?></h1>
                <div class="notas__contadores">
                    <button class="contador" type="button" data-filtrar="entregues">
                        <span class="contador__numero"><?= count($entregues) ?></span>
                        <span class="contador__rotulo"><?= count($entregues) === 1 ? 'Entregue' : 'Entregues' ?></span>
                    </button>
                    <button class="contador" type="button" data-filtrar="pendentes">
                        <span class="contador__numero"><?= count($pendentes) ?></span>
                        <span class="contador__rotulo">Pendentes</span>
                    </button>
                </div>
                <p class="notas__dica">Clique em um estudante da lista para ver o trabalho entregue, dar nota e comentar. A nota é salva ao sair do campo.</p>
            </section>

            <section class="notas__lista" aria-label="Estudantes">
                <label class="visualmente-oculto" for="filtro-entrega">Filtrar estudantes</label>
                <select class="notas__filtro" id="filtro-entrega">
                    <option value="todos">Filtrar: Todos</option>
                    <option value="entregues">Filtrar: Entregues</option>
                    <option value="pendentes">Filtrar: Pendentes</option>
                </select>

                <section class="grupo-estudantes" data-grupo="entregues" aria-labelledby="titulo-entregues">
                    <h2 id="titulo-entregues">Entregues (<?= count($entregues) ?>)</h2>
                    <ul class="lista-estudantes">
                        <?php foreach ($entregues as ['aluno' => $aluno, 'entrega' => $entrega, 'anexos' => $anexos, 'comentarios' => $comentarios]):
                            $ultimo = $comentarios ? end($comentarios) : null; ?>
                        <li class="estudante" data-nome="<?= e($aluno->nome) ?>" data-entrega-id="<?= $entrega->id ?>"
                            data-entregue-em="<?= data_br($entrega->enviadoEm, 'hora') ?><?= $entrega->atrasada ? ' (com atraso)' : '' ?>">
                            <input class="estudante__selecao" type="checkbox" aria-label="Selecionar <?= e($aluno->nome) ?>">
                            <button class="estudante__abrir" type="button" data-abrir-entrega>
                                <span class="estudante__avatar" aria-hidden="true"><?= e(mb_substr($aluno->nome, 0, 1)) ?></span>
                                <span class="estudante__texto">
                                    <span class="estudante__nome"><?= e($aluno->nome) ?></span>
                                    <span class="estudante__previa" data-previa><?= $ultimo ? '"' . e(mb_strimwidth($ultimo->texto, 0, 40, '…')) . '"' : ($entrega->atrasada ? 'Entregue com atraso' : 'Entregue') ?></span>
                                </span>
                            </button>
                            <span class="estudante__nota">
                                <input class="campo-nota" type="number" min="0" max="100" inputmode="numeric" placeholder="__" value="<?= $entrega->nota ?? '' ?>" aria-label="Nota de <?= e($aluno->nome) ?>, de 0 a 100" data-nota>
                                <span aria-hidden="true">/100</span>
                            </span>
                            <ul hidden data-arquivos>
                                <?php foreach ($anexos as $a): ?>
                                <li><a href="arquivo.php?id=<?= $a->id ?>"><span class="material-symbols-outlined" aria-hidden="true">description</span><?= e($a->nome) ?></a></li>
                                <?php endforeach; ?>
                            </ul>
                            <ul hidden data-comentarios>
                                <?php foreach ($comentarios as $c): ?>
                                <li><strong><?= e($c->autor) ?></strong> • <?= data_br($c->criadoEm, 'hora') ?><p><?= e($c->texto) ?></p></li>
                                <?php endforeach; ?>
                            </ul>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </section>

                <section class="grupo-estudantes" data-grupo="pendentes" aria-labelledby="titulo-pendentes">
                    <h2 id="titulo-pendentes">Pendentes (<?= count($pendentes) ?>)</h2>
                    <ul class="lista-estudantes">
                        <?php foreach ($pendentes as ['aluno' => $aluno, 'entrega' => $entrega]): ?>
                        <li class="estudante">
                            <input class="estudante__selecao" type="checkbox" aria-label="Selecionar <?= e($aluno->nome) ?>">
                            <span class="estudante__abrir">
                                <span class="estudante__avatar" aria-hidden="true"><?= e(mb_substr($aluno->nome, 0, 1)) ?></span>
                                <span class="estudante__nome"><?= e($aluno->nome) ?></span>
                            </span>
                            <span class="estudante__status"><?= $entrega ? 'Envio cancelado' : 'Pendente' ?></span>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </section>
            </section>
        </main>

    <!-- Modal "Entrega do aluno": abre ao clicar num estudante que entregou. Nota e comentário vão por fetch() ao back-end. -->
    <dialog class="dialogo dialogo--entrega" id="dialogo-entrega" aria-labelledby="entrega-nome">
        <div class="entrega">
            <header class="entrega__cabecalho">
                <div>
                    <h2 class="dialogo__titulo" id="entrega-nome"></h2>
                    <details class="entrega__historico">
                        <summary>Entregue <span>(Ver histórico)</span></summary>
                        <p>Entregue em <span data-entregue-em></span></p>
                    </details>
                </div>
                <label class="entrega__nota">
                    Nota:
                    <input class="campo-nota" type="number" min="0" max="100" inputmode="numeric" placeholder="__" data-nota-modal>
                    /100
                </label>
                <form method="dialog">
                    <button class="botao-icone" type="submit" aria-label="Fechar e voltar para a lista">
                        <span class="material-symbols-outlined" aria-hidden="true">close</span>
                    </button>
                </form>
            </header>

            <ul class="lista-arquivos" aria-label="Arquivos entregues" data-arquivos-modal></ul>

            <section class="comentarios" aria-labelledby="comentarios-titulo">
                <h3 id="comentarios-titulo">
                    <span class="material-symbols-outlined" aria-hidden="true">person</span>
                    <span data-comentarios-total></span>
                </h3>
                <ul class="comentarios__lista" data-comentarios-modal></ul>
                <form class="comentarios__form" data-form-comentario>
                    <label class="visualmente-oculto" for="novo-comentario">Adicionar comentário particular</label>
                    <input class="comentarios__campo" id="novo-comentario" name="comentario" maxlength="1000" placeholder="Adicionar comentário particular..." required autocomplete="off">
                    <button class="botao-icone" type="submit" aria-label="Enviar comentário">
                        <span class="material-symbols-outlined" aria-hidden="true">send</span>
                    </button>
                </form>
            </section>
        </div>
    </dialog>
<?php include __DIR__ . '/../partes/rodape.php'; ?>
