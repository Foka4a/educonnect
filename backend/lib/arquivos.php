<?php
declare(strict_types=1);

use EduConnect\DAO\AnexoDAO;
use EduConnect\DAO\EntregaDAO;
use EduConnect\Models\Anexo;
use EduConnect\Models\Turma;

const EXTENSOES_PROIBIDAS = ['php', 'php3', 'php4', 'php5', 'php7', 'php8', 'phtml', 'phar', 'phps', 'pht', 'exe', 'bat', 'cmd', 'com', 'msi', 'sh', 'js', 'html', 'htm', 'svg'];

/** Normaliza $_FILES['campo'] (um ou vários) numa lista simples, ignorando campos vazios. */
function arquivos_enviados(string $campo): array {
    if (empty($_FILES[$campo])) return [];
    $f = $_FILES[$campo];
    $lista = [];
    if (is_array($f['name'])) {
        foreach (array_keys($f['name']) as $i) {
            $lista[] = ['name' => $f['name'][$i], 'tmp_name' => $f['tmp_name'][$i], 'size' => $f['size'][$i], 'error' => $f['error'][$i]];
        }
    } else {
        $lista[] = $f;
    }
    return array_values(array_filter($lista, fn ($a) => $a['error'] !== UPLOAD_ERR_NO_FILE));
}

/** Valida (sem gravar). Lança Falha se algum arquivo não serve. */
function validar_arquivos(array $lista, int $maxArquivos): void {
    if (count($lista) > $maxArquivos) throw new Falha("Máximo de $maxArquivos arquivos por envio.");
    foreach ($lista as $a) {
        if ($a['error'] === UPLOAD_ERR_INI_SIZE || $a['error'] === UPLOAD_ERR_FORM_SIZE) throw new Falha("“{$a['name']}” é grande demais.");
        if ($a['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($a['tmp_name'])) throw new Falha("Não foi possível receber “{$a['name']}”.");
        if ($a['size'] > config('max_bytes_arquivo')) throw new Falha("“{$a['name']}” passa de 20 MB.");
        $ext = strtolower(pathinfo($a['name'], PATHINFO_EXTENSION));
        if (in_array($ext, EXTENSOES_PROIBIDAS, true)) throw new Falha("Tipo de arquivo não permitido: “{$a['name']}”.");
    }
}

/** Grava os arquivos em storage/uploads com nome aleatório e registra cada um em `anexos`. */
function guardar_anexos(array $lista, ?int $publicacaoId, ?int $entregaId): void {
    $pasta = config('uploads');
    if (!is_dir($pasta)) mkdir($pasta, 0755, true);
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $dao = new AnexoDAO();
    foreach ($lista as $a) {
        $nomeInterno = bin2hex(random_bytes(16));
        if (!move_uploaded_file($a['tmp_name'], "$pasta/$nomeInterno")) throw new Falha('Falha ao gravar o arquivo.', 500);
        $dao->inserir(new Anexo(
            publicacaoId: $publicacaoId,
            entregaId: $entregaId,
            nome: mb_substr(preg_replace('/[\x00-\x1F\/\\\\]/', '_', basename($a['name'])), 0, 255),
            caminho: $nomeInterno,
            tipo: $finfo->file("$pasta/$nomeInterno") ?: 'application/octet-stream',
            tamanho: (int) $a['size'],
        ));
    }
}

function apagar_arquivo_fisico(string $caminho): void {
    $arq = config('uploads') . '/' . basename($caminho);
    if (is_file($arq)) unlink($arq);
}

/** Remove os anexos de uma entrega: arquivos do disco e registros do banco. */
function remover_anexos_da_entrega(int $entregaId): void {
    $dao = new AnexoDAO();
    foreach ($dao->listarDaEntrega($entregaId) as $a) apagar_arquivo_fisico($a->caminho);
    $dao->excluirDaEntrega($entregaId);
}

/** Para excluir turma: apaga do disco tudo que depende dela (as linhas saem em cascata). Chame ANTES do DELETE. */
function apagar_arquivos_da_turma(Turma $turma): void {
    foreach ((new AnexoDAO())->caminhosDaTurma($turma->id) as $caminho) apagar_arquivo_fisico($caminho);
    if ($turma->imagem) apagar_arquivo_fisico($turma->imagem);
}

/** Para excluir atividade/material: apaga do disco os anexos dela e das entregas. Chame ANTES do DELETE. */
function apagar_arquivos_da_publicacao(int $publicacaoId): void {
    $dao = new AnexoDAO();
    foreach ($dao->listarDaPublicacao($publicacaoId) as $a) apagar_arquivo_fisico($a->caminho);
    foreach ((new EntregaDAO())->idsDaAtividade($publicacaoId) as $entregaId) {
        foreach ($dao->listarDaEntrega($entregaId) as $a) apagar_arquivo_fisico($a->caminho);
    }
}
