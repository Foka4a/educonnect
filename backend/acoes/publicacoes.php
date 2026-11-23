<?php
declare(strict_types=1);

use EduConnect\Database\Conexao;
use EduConnect\DAO\AnexoDAO;
use EduConnect\DAO\EntregaDAO;
use EduConnect\DAO\PublicacaoDAO;
use EduConnect\Models\Publicacao;

/** Criar atividade/material (tipo = Publicacao::ATIVIDADE | Publicacao::MATERIAL). */
function criar_publicacao(string $tipo): never {
    $u = exigir_login();
    $turma = turma_ou_falha(inteiro('turma_id', 'Turma'));
    exigir_membro($turma->id, $u, true);
    $p = new Publicacao(
        turmaId: $turma->id,
        tipo: $tipo,
        titulo: texto('titulo', 120, true, $tipo === Publicacao::ATIVIDADE ? 'O título da atividade' : 'O título do material'),
        descricao: limpar_html(texto('descricao', 20000)),
        entregaEm: $tipo === Publicacao::ATIVIDADE ? data_de_entrega() : null,
        autorId: $u->id,
    );
    $arquivos = arquivos_enviados('anexos');
    validar_arquivos($arquivos, config('max_arquivos_publicacao'));

    Conexao::transacao(function () use ($p, $arquivos) {
        (new PublicacaoDAO())->inserir($p);
        guardar_anexos($arquivos, $p->id, null);
    });
    sucesso($p->ehAtividade() ? 'Atividade atribuída.' : 'Material publicado.', pagina('atividades.html', 'turma=' . $turma->id), ['id' => $p->id]);
}

function editar_publicacao(string $tipo): never {
    $u = exigir_login();
    $p = publicacao_ou_falha(inteiro($tipo . '_id', 'Item'), $tipo);
    exigir_membro($p->turmaId, $u, true);
    $p->titulo = texto('titulo', 120, true, $p->ehAtividade() ? 'O título da atividade' : 'O título do material');
    $p->descricao = limpar_html(texto('descricao', 20000));
    $p->entregaEm = $p->ehAtividade() ? data_de_entrega() : null;
    $novos = arquivos_enviados('anexos');
    $remover = array_filter((array) ($_POST['remover_anexos'] ?? []), 'is_string');
    $anexos = new AnexoDAO();
    $atuais = $anexos->listarDaPublicacao($p->id);
    $paraApagar = array_values(array_filter($atuais, fn ($a) => in_array($a->nome, $remover, true)));
    if (count($atuais) - count($paraApagar) + count($novos) > config('max_arquivos_publicacao')) {
        throw new Falha('Máximo de ' . config('max_arquivos_publicacao') . ' arquivos por item.');
    }
    validar_arquivos($novos, config('max_arquivos_publicacao'));

    Conexao::transacao(function () use ($p, $anexos, $paraApagar, $novos) {
        (new PublicacaoDAO())->atualizar($p);
        foreach ($paraApagar as $a) {
            $anexos->excluir($a->id);
            apagar_arquivo_fisico($a->caminho);
        }
        guardar_anexos($novos, $p->id, null);
        if ($p->ehAtividade()) (new EntregaDAO())->recalcularAtrasos($p->id, $p->entregaEm); // o prazo pode ter mudado
    });
    sucesso($p->ehAtividade() ? 'Atividade atualizada.' : 'Material atualizado.');
}

function excluir_publicacao(string $tipo): never {
    $u = exigir_login();
    $p = publicacao_ou_falha(inteiro($tipo . '_id', 'Item'), $tipo);
    exigir_membro($p->turmaId, $u, true);
    apagar_arquivos_da_publicacao($p->id);
    (new PublicacaoDAO())->excluir($p->id);
    sucesso($p->ehAtividade() ? 'Atividade excluída.' : 'Material excluído.', pagina('atividades.html', 'turma=' . $p->turmaId));
}

function acao_criar_atividade(): never { criar_publicacao(Publicacao::ATIVIDADE); }
function acao_editar_atividade(): never { editar_publicacao(Publicacao::ATIVIDADE); }
function acao_excluir_atividade(): never { excluir_publicacao(Publicacao::ATIVIDADE); }
function acao_criar_material(): never { criar_publicacao(Publicacao::MATERIAL); }
function acao_editar_material(): never { editar_publicacao(Publicacao::MATERIAL); }
function acao_excluir_material(): never { excluir_publicacao(Publicacao::MATERIAL); }

/** GET: baixa um anexo (de atividade/material: qualquer membro; de entrega: o aluno dono ou os professores). */
function acao_arquivo(): never {
    $u = exigir_login();
    $a = (new AnexoDAO())->buscarPorId(inteiro('id', 'Arquivo')) ?? throw new Falha('Arquivo não encontrado.', 404);
    if ($a->publicacaoId) {
        $p = publicacao_ou_falha($a->publicacaoId);
        exigir_membro($p->turmaId, $u);
    } else {
        $e = (new EntregaDAO())->buscarPorId((int) $a->entregaId) ?? throw new Falha('Arquivo não encontrado.', 404);
        $papel = exigir_membro($e->turmaId, $u);
        if ($papel !== 'professor' && $e->alunoId !== $u->id) throw new Falha('Você não pode ver este arquivo.', 403);
    }
    $arq = config('uploads') . '/' . basename($a->caminho);
    if (!is_file($arq)) throw new Falha('Arquivo não encontrado.', 404);
    header('Content-Type: application/octet-stream');
    header("Content-Disposition: attachment; filename*=UTF-8''" . rawurlencode($a->nome));
    header('Content-Length: ' . filesize($arq));
    header('X-Content-Type-Options: nosniff');
    readfile($arq);
    exit;
}
