<?php
require __DIR__ . '/../partes/pagina.php';

use EduConnect\Models\Participacao;
use EduConnect\Models\Publicacao;
use EduConnect\Services\EntregaService;
use EduConnect\Services\PessoaService;
use EduConnect\Services\PublicacaoService;
use EduConnect\Services\TurmaService;

$usuario = usuario_da_pagina();
// Campos da subpágina "Atividade"/"Material" (os mesmos para criar e editar): título, descrição, prazo e anexos novos.
$campos = fn (string $tipo) => [
    texto('titulo', 120, true, $tipo === Publicacao::ATIVIDADE ? 'O título da atividade' : 'O título do material'),
    texto('descricao', 20000),
    $tipo === Publicacao::ATIVIDADE ? data_de_entrega() : null,
    arquivos_enviados('anexos'),
];
$criar = fn (string $tipo) => function () use ($usuario, $tipo, $campos) {
    $p = (new PublicacaoService())->criar($usuario, $tipo, inteiro('turma_id', 'Turma'), ...$campos($tipo));
    sucesso($p->ehAtividade() ? 'Atividade atribuída.' : 'Material publicado.', 'atividades.php?turma=' . $p->turmaId);
};
$editar = fn (string $tipo) => function () use ($usuario, $tipo, $campos) {
    $remover = array_values(array_filter((array) ($_POST['remover_anexos'] ?? []), 'is_string'));
    $p = (new PublicacaoService())->editar($usuario, $tipo, inteiro($tipo . '_id', 'Item'), ...[...$campos($tipo), $remover]);
    sucesso($p->ehAtividade() ? 'Atividade atualizada.' : 'Material atualizado.', 'atividades.php?turma=' . $p->turmaId);
};
formularios([
    'criar-atividade' => $criar(Publicacao::ATIVIDADE),
    'criar-material' => $criar(Publicacao::MATERIAL),
    'editar-atividade' => $editar(Publicacao::ATIVIDADE),
    'editar-material' => $editar(Publicacao::MATERIAL),
    'excluir-atividade' => fn () => excluir_publicacao(Publicacao::ATIVIDADE),
    'excluir-material' => fn () => excluir_publicacao(Publicacao::MATERIAL),
]);
[$turma, $turmas, $itens, $anexos, $entregues, $totalAlunos, $editando] = carregar(function () use ($usuario) {
    $turmas = new TurmaService();
    $publicacoes = new PublicacaoService();
    $turma = $turmas->abrir($usuario, (int) ($_GET['turma'] ?? 0));
    $itens = $publicacoes->listar($usuario, $turma->id, [Publicacao::ATIVIDADE, Publicacao::MATERIAL]);
    $alunos = array_filter((new PessoaService())->listar($usuario, $turma->id), fn ($p) => $p->papel === Participacao::ALUNO);
    // ?editar=<id>: abre a subpágina já preenchida (menu ⋮ → Editar). Só professor e só item desta turma.
    $editando = null;
    if (isset($_GET['editar']) && $turma->ehProfessor()) {
        $editando = $publicacoes->abrir($usuario, (int) $_GET['editar']);
        if ($editando->turmaId !== $turma->id || $editando->tipo === Publicacao::AVISO) throw new Falha('Item não encontrado.', 404);
    }
    return [$turma, $turmas->listarDoUsuario($usuario), $itens, $publicacoes->anexosDe($editando ? [...$itens, $editando] : $itens),
            (new EntregaService())->contarEntregues($usuario, $turma->id), count($alunos), $editando];
});
$professor = $turma->ehProfessor();
$titulo = 'EduConnect | Atividades';

/** Subpágina "Atividade" (tela 10) ou "Material" (tela 11): cria, ou edita quando $editando é desse tipo. */
function subpagina(string $tipo, $turma, ?Publicacao $editando, array $anexos): void {
    $atividade = $tipo === Publicacao::ATIVIDADE;
    $item = $editando && $editando->tipo === $tipo ? $editando : null;
    $nome = $atividade ? 'Atividade' : 'Material';
    $prazo = $item?->entregaEm ? strtotime($item->entregaEm) : null;
    $voltar = 'atividades.php?turma=' . $turma->id;
    ?>
    <dialog class="subpagina" id="subpagina-<?= $tipo ?>" aria-labelledby="titulo-subpagina-<?= $tipo ?>" data-tipo="<?= $tipo ?>"
        <?php if ($item): ?>data-editar data-voltar="<?= e($voltar) ?>"<?php endif; ?>>
        <header class="barra-superior">
            <div class="barra-superior__marca">
                <button class="botao-icone botao-fechar" type="button" aria-label="Cancelar e voltar para Atividades" title="Cancelar" data-cancelar>
                    <span class="material-symbols-outlined" aria-hidden="true">close</span>
                </button>
                <span class="formulario__icone material-symbols-outlined" aria-hidden="true"><?= $atividade ? 'assignment' : 'book' ?></span>
                <h2 class="formulario__titulo" id="titulo-subpagina-<?= $tipo ?>"><?= $item ? "Editar $nome" : $nome ?></h2>
            </div>
            <div class="barra-superior__acoes">
                <button class="botao-postar" type="submit" form="form-<?= $tipo ?>"><?= $item ? 'Salvar' : ($atividade ? 'Atribuir' : 'Postar') ?></button>
            </div>
        </header>

        <div class="subpagina__corpo">
            <div class="formulario<?= $atividade ? ' formulario--atividade' : '' ?>">
                <form id="form-<?= $tipo ?>" method="post" enctype="multipart/form-data" novalidate data-form>
                    <?= acao(($item ? 'editar-' : 'criar-') . $tipo) ?>
                    <?php if ($item): ?>
                    <input type="hidden" name="<?= $tipo ?>_id" value="<?= $item->id ?>">
                    <?php else: ?>
                    <input type="hidden" name="turma_id" value="<?= $turma->id ?>">
                    <?php endif; ?>
                    <section class="cartao cartao--dados" aria-label="Dados <?= $atividade ? 'da atividade' : 'do material' ?>">
                        <div class="campo">
                            <label class="campo__rotulo" for="<?= $tipo ?>-titulo">Título <span aria-hidden="true">*</span><span class="visualmente-oculto">(obrigatório)</span></label>
                            <input class="campo__entrada" id="<?= $tipo ?>-titulo" name="titulo" type="text" maxlength="120" required autocomplete="off" aria-describedby="<?= $tipo ?>-erro-titulo" value="<?= e($item?->titulo) ?>">
                            <p class="campo__erro" id="<?= $tipo ?>-erro-titulo" role="alert" hidden>
                                <span class="material-symbols-outlined" aria-hidden="true">error</span>
                                <span>Informe o título <?= $atividade ? 'da atividade' : 'do material' ?>.</span>
                            </p>
                        </div>
                    <div class="campo">
                        <span class="campo__rotulo" id="<?= $tipo ?>-rotulo-descricao"><?= $atividade ? 'Instruções' : 'Descrição' ?> (opcional)</span>
                        <div class="editor">
                            <!-- A descrição gravada já passou pela lista de tags permitidas do back-end (limpar_html). -->
                            <div class="editor__texto" id="<?= $tipo ?>-descricao" contenteditable="true" role="textbox" aria-multiline="true" aria-labelledby="<?= $tipo ?>-rotulo-descricao" data-editor><?= $item?->descricao ?? '' ?></div>
                            <div class="editor__barra" role="toolbar" aria-label="Formatação do texto" aria-controls="<?= $tipo ?>-descricao">
                            <button class="editor__botao" type="button" data-comando="bold" aria-pressed="false" aria-label="Negrito" title="Negrito">
                                <span class="material-symbols-outlined" aria-hidden="true">format_bold</span>
                            </button>
                            <button class="editor__botao" type="button" data-comando="italic" aria-pressed="false" aria-label="Itálico" title="Itálico">
                                <span class="material-symbols-outlined" aria-hidden="true">format_italic</span>
                            </button>
                            <button class="editor__botao" type="button" data-comando="underline" aria-pressed="false" aria-label="Sublinhado" title="Sublinhado">
                                <span class="material-symbols-outlined" aria-hidden="true">format_underlined</span>
                            </button>
                            <button class="editor__botao" type="button" data-comando="insertUnorderedList" aria-pressed="false" aria-label="Lista com marcadores" title="Lista com marcadores">
                                <span class="material-symbols-outlined" aria-hidden="true">format_list_bulleted</span>
                            </button>
                            <button class="editor__botao" type="button" data-comando="removeFormat" aria-label="Limpar formatação" title="Limpar formatação">
                                <span class="material-symbols-outlined" aria-hidden="true">format_clear</span>
                            </button>
                            </div>
                        </div>
                        <input type="hidden" name="descricao" data-descricao-html>
                    </div>
                    </section>
                    <?php if ($atividade): ?>
                    <aside class="lateral" aria-labelledby="atividade-titulo-prazo">
                        <h3 class="lateral__titulo" id="atividade-titulo-prazo">Data de entrega</h3>
                        <select class="campo__selecao" name="prazo_tipo" aria-label="Data de entrega" data-prazo-tipo>
                            <option value="sem"<?= $prazo ? '' : ' selected' ?>>Sem data de entrega</option>
                            <option value="definir"<?= $prazo ? ' selected' : '' ?>>Definir data e hora</option>
                        </select>
                        <div class="prazo" data-prazo<?= $prazo ? '' : ' hidden' ?>>
                            <div class="campo">
                                <label class="campo__rotulo" for="atividade-prazo-data">Data</label>
                                <?php /* texto, não type="date": o campo nativo segue o idioma do navegador (mm/dd/aaaa em inglês) */ ?>
                                <input class="campo__entrada" id="atividade-prazo-data" name="prazo_data" type="text" inputmode="numeric" maxlength="10"
                                       placeholder="dd/mm/aaaa" autocomplete="off" aria-describedby="atividade-erro-prazo" value="<?= $prazo ? date('d/m/Y', $prazo) : '' ?>">
                                <p class="campo__erro" id="atividade-erro-prazo" role="alert" hidden>
                                    <span class="material-symbols-outlined" aria-hidden="true">error</span>
                                    <span data-erro-prazo>Informe a data de entrega.</span>
                                </p>
                            </div>
                            <div class="campo">
                                <label class="campo__rotulo" for="atividade-prazo-hora">Hora</label>
                                <input class="campo__entrada" id="atividade-prazo-hora" name="prazo_hora" type="text" inputmode="numeric" maxlength="5"
                                       placeholder="hh:mm" autocomplete="off" aria-describedby="atividade-erro-prazo" value="<?= $prazo ? date('H:i', $prazo) : '23:59' ?>">
                            </div>
                            <p class="prazo__ajuda">Entregas depois desse horário ficam como atrasadas.</p>
                        </div>
                    </aside>
                    <?php endif; ?>
                    <section class="cartao cartao--anexar" aria-labelledby="<?= $tipo ?>-titulo-anexar">
                        <h3 class="cartao__titulo" id="<?= $tipo ?>-titulo-anexar">Anexar</h3>
                        <?php if ($item && $anexos[$item->id]): ?>
                        <p class="upload__ajuda">Arquivos já anexados (marque para remover):</p>
                        <ul class="lista-arquivos">
                            <?php foreach ($anexos[$item->id] as $a): ?>
                            <li><label><input type="checkbox" name="remover_anexos[]" value="<?= e($a->nome) ?>"> <?= e($a->nome) ?></label></li>
                            <?php endforeach; ?>
                        </ul>
                        <?php endif; ?>
                        <div class="upload">
                            <button class="upload__botao" type="button" data-escolher-arquivo aria-describedby="<?= $tipo ?>-ajuda-upload <?= $tipo ?>-erro-upload">
                                <span class="material-symbols-outlined" aria-hidden="true">upload</span>
                                <span class="visualmente-oculto">Fazer upload de arquivo</span>
                            </button>
                            <p class="upload__legenda" aria-hidden="true">Fazer upload</p>
                            <p class="upload__ajuda" id="<?= $tipo ?>-ajuda-upload">Arquivos do computador, até 10 arquivos de 20 MB.</p>
                            <input type="file" name="anexos[]" multiple hidden data-arquivos>
                        </div>
                        <p class="campo__erro" id="<?= $tipo ?>-erro-upload" role="alert" hidden>
                            <span class="material-symbols-outlined" aria-hidden="true">error</span>
                            <span data-erro-upload></span>
                        </p>
                        <ul class="anexos" aria-label="Arquivos anexados" data-anexos></ul>
                    </section>
                </form>
            </div>
        </div>

        <footer class="rodape">
            <p>EduConnect é um clone do Google Classroom feito para fins educacionais, sem vínculo com o Google.</p>
        </footer>
    </dialog>
    <?php
}

include __DIR__ . '/../partes/cabeca.php';
?>
    <link rel="stylesheet" href="../modais/css/configuracoes-turma.css">
    <link rel="stylesheet" href="../style/atividades.css">
    <link rel="stylesheet" href="../style/subpagina-criar.css">
    <script src="../modais/components/configuracoes-turma.js" defer></script>
    <script src="../scripts/atividades.js" defer></script>
    <script src="../scripts/subpagina-criar.js" defer></script>
</head>
<?php include __DIR__ . '/../partes/topo.php'; ?>

<?php $aba = 'atividades'; include __DIR__ . '/../partes/abas-turma.php'; ?>

        <main class="conteudo-principal">
            <div class="atividades">
                <h1 class="visualmente-oculto">Atividades e materiais</h1>

                <?php if ($professor): ?>
                <!-- "+ Criar" abre a subpágina de Atividade (tela 10) ou de Material (tela 11) -->
                <details class="menu-suspenso atividades__criar">
                    <summary class="botao-criar">
                        <span class="material-symbols-outlined" aria-hidden="true">add</span>
                        <span>Criar</span>
                    </summary>
                    <ul class="menu-suspenso__lista">
                        <li>
                            <button class="menu-suspenso__item" type="button" data-abrir-subpagina="atividade">
                                <span class="material-symbols-outlined" aria-hidden="true">assignment</span>
                                <span>Atividade</span>
                            </button>
                        </li>
                        <li>
                            <button class="menu-suspenso__item" type="button" data-abrir-subpagina="material">
                                <span class="material-symbols-outlined" aria-hidden="true">book</span>
                                <span>Material</span>
                            </button>
                        </li>
                    </ul>
                </details>
                <?php endif; ?>

                <?php if (!$itens): ?>
                <div class="estado-vazio">
                    <span class="material-symbols-outlined" aria-hidden="true">assignment</span>
                    <p>Nenhuma atividade ou material publicado ainda.</p>
                </div>
                <?php endif; ?>

                <ul class="lista-atividades">
                    <?php foreach ($itens as $p):
                        $atividade = $p->ehAtividade();
                        $prazo = $p->entregaEm ? 'Entrega: ' . data_br($p->entregaEm) : 'Sem data de entrega';
                        $feitas = $entregues[$p->id] ?? 0; ?>
                    <li class="item-atividade" id="<?= $p->tipo ?>-<?= $p->id ?>" data-tipo="<?= $p->tipo ?>" data-titulo="<?= e($p->titulo) ?>"
                        data-acao-excluir="excluir-<?= $p->tipo ?>" data-campo="<?= $p->tipo ?>_id" data-id="<?= $p->id ?>">
                        <details class="item-atividade__detalhes" name="item-atividade">
                            <summary class="item-atividade__resumo">
                                <span class="item-atividade__icone material-symbols-outlined" aria-hidden="true"><?= $atividade ? 'assignment' : 'book' ?></span>
                                <span class="item-atividade__titulo"><?= e($p->titulo) ?></span>
                                <?php if ($atividade): ?><span class="item-atividade__prazo"><?= $prazo ?></span><?php endif; ?>
                            </summary>
                            <div class="item-atividade__conteudo">
                                <div class="item-atividade__texto">
                                    <p class="item-atividade__data"><?= $atividade ? ($p->entregaEm ? 'Data de entrega: ' . data_br($p->entregaEm, 'longa') : 'Sem data de entrega') : 'Publicado em ' . data_br($p->criadoEm, 'longa') ?></p>
                                    <div><?= $p->descricao /* já limpa pelo back-end */ ?></div>
                                    <?php if ($anexos[$p->id]): ?>
                                    <ul class="lista-arquivos" aria-label="Anexos">
                                        <?php foreach ($anexos[$p->id] as $a): ?>
                                        <li><a href="arquivo.php?id=<?= $a->id ?>"><span class="material-symbols-outlined" aria-hidden="true">attach_file</span><?= e($a->nome) ?></a></li>
                                        <?php endforeach; ?>
                                    </ul>
                                    <?php endif; ?>
                                </div>
                                <?php if ($atividade && $professor): ?>
                                <dl class="item-atividade__contadores">
                                    <div>
                                        <dt>Entregues</dt>
                                        <dd><?= $feitas ?></dd>
                                    </div>
                                    <div>
                                        <dt>Pendentes</dt>
                                        <dd><?= max(0, $totalAlunos - $feitas) ?></dd>
                                    </div>
                                </dl>
                                <?php endif; ?>
                                <?php if ($atividade): ?><a class="item-atividade__link" href="instrucoes.php?atividade=<?= $p->id ?>">Ver instruções</a><?php endif; ?>
                            </div>
                        </details>
                        <details class="menu-suspenso item-atividade__menu">
                            <summary class="botao-icone" aria-label="Mais opções para <?= e($p->titulo) ?>" title="Mais opções">
                                <span class="material-symbols-outlined" aria-hidden="true">more_vert</span>
                            </summary>
                            <ul class="menu-suspenso__lista">
                                <?php if ($professor): ?>
                                <li><a class="menu-suspenso__item" href="atividades.php?turma=<?= $turma->id ?>&amp;editar=<?= $p->id ?>">Editar</a></li>
                                <li><button class="menu-suspenso__item" type="button" data-acao="excluir">Excluir</button></li>
                                <?php endif; ?>
                                <li><button class="menu-suspenso__item" type="button" data-acao="copiar-link">Copiar link</button></li>
                            </ul>
                        </details>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </main>

    <?php if ($professor): ?>
    <?php subpagina(Publicacao::ATIVIDADE, $turma, $editando, $anexos); ?>
    <?php subpagina(Publicacao::MATERIAL, $turma, $editando, $anexos); ?>

    <!-- Cancelar uma subpágina com conteúdo digitado -->
    <dialog class="dialogo" id="dialogo-descartar" aria-labelledby="titulo-descartar">
        <form method="dialog">
            <h2 class="dialogo__titulo" id="titulo-descartar">Descartar <span data-descartar-tipo>atividade</span>?</h2>
            <p>O que você escreveu e os arquivos anexados serão perdidos.</p>
            <div class="dialogo__acoes">
                <button class="botao-texto" type="submit" value="continuar">Continuar editando</button>
                <button class="botao-texto botao-texto--perigo" type="submit" value="descartar">Descartar</button>
            </div>
        </form>
    </dialog>

    <!-- Confirmação para excluir: o atividades.js põe a rota e o id do item escolhido -->
    <dialog class="dialogo" id="dialogo-excluir" aria-labelledby="titulo-excluir" aria-describedby="descricao-excluir">
        <form method="post">
            <input type="hidden" name="acao" data-excluir-acao>
            <input type="hidden" data-excluir-id>
            <h2 class="dialogo__titulo" id="titulo-excluir">Excluir <span data-excluir-tipo>atividade</span>?</h2>
            <div id="descricao-excluir">
                <p><strong data-excluir-titulo></strong></p>
                <p>As entregas, as notas e os comentários também serão excluídos. Esta ação não pode ser desfeita.</p>
            </div>
            <div class="dialogo__acoes">
                <button class="botao-texto" type="submit" formmethod="dialog" formnovalidate>Cancelar</button>
                <button class="botao-texto botao-texto--perigo" type="submit">Excluir</button>
            </div>
        </form>
    </dialog>
    <?php endif; ?>
<?php include __DIR__ . '/../partes/rodape.php'; ?>
