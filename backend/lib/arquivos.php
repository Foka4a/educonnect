<?php
declare(strict_types=1);

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

/** Grava os arquivos em storage/uploads com nome aleatório e registra em `anexos`. */
function guardar_anexos(array $lista, ?int $publicacaoId, ?int $entregaId): void {
    $pasta = config('uploads');
    if (!is_dir($pasta)) mkdir($pasta, 0755, true);
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    foreach ($lista as $a) {
        $nomeInterno = bin2hex(random_bytes(16));
        if (!move_uploaded_file($a['tmp_name'], "$pasta/$nomeInterno")) throw new Falha('Falha ao gravar o arquivo.', 500);
        $mime = $finfo->file("$pasta/$nomeInterno") ?: 'application/octet-stream';
        $nome = mb_substr(preg_replace('/[\x00-\x1F\/\\\\]/', '_', basename($a['name'])), 0, 255);
        executar('INSERT INTO anexos (publicacao_id, entrega_id, nome, caminho, tipo, tamanho, criado_em) VALUES (?,?,?,?,?,?,?)',
            [$publicacaoId, $entregaId, $nome, $nomeInterno, $mime, (int) $a['size'], agora()]);
    }
}

function apagar_arquivo_fisico(string $caminho): void {
    $arq = config('uploads') . '/' . basename($caminho);
    if (is_file($arq)) unlink($arq);
}

/** Apaga do disco os anexos que o banco vai remover em cascata (chame ANTES do DELETE). */
function apagar_anexos_de(string $coluna, int $id): void {
    if (!in_array($coluna, ['publicacao_id', 'entrega_id'], true)) return;
    foreach (consultar("SELECT caminho FROM anexos WHERE $coluna = ?", [$id]) as $a) apagar_arquivo_fisico($a['caminho']);
}

/** Para excluir turma/atividade: apaga do disco tudo que depende dela. */
function apagar_anexos_da_turma(int $turmaId): void {
    $sql = 'SELECT a.caminho FROM anexos a
            LEFT JOIN publicacoes p ON p.id = a.publicacao_id
            LEFT JOIN entregas e ON e.id = a.entrega_id
            LEFT JOIN publicacoes pe ON pe.id = e.atividade_id
            WHERE p.turma_id = ? OR pe.turma_id = ?';
    foreach (consultar($sql, [$turmaId, $turmaId]) as $a) apagar_arquivo_fisico($a['caminho']);
    $t = um('SELECT imagem FROM turmas WHERE id = ?', [$turmaId]);
    if ($t && $t['imagem']) apagar_arquivo_fisico($t['imagem']);
}

function apagar_anexos_da_atividade(int $atividadeId): void {
    apagar_anexos_de('publicacao_id', $atividadeId);
    foreach (consultar('SELECT id FROM entregas WHERE atividade_id = ?', [$atividadeId]) as $e) apagar_anexos_de('entrega_id', (int) $e['id']);
}
