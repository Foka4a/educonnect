<?php
declare(strict_types=1);

namespace EduConnect\Services;

use EduConnect\DAO\AnexoDAO;
use EduConnect\DAO\EntregaDAO;
use EduConnect\Models\Anexo;
use EduConnect\Models\Turma;
use Falha;
use finfo;

/**
 * Arquivos no disco (backend/storage/uploads): validação dos envios, gravação com nome aleatório e limpeza.
 * Cada envio é um item de arquivos_enviados() (lib/validacao.php): ['name', 'tmp_name', 'size', 'error'].
 */
final class ArquivoService
{
    public const EXTENSOES_PROIBIDAS = ['php', 'php3', 'php4', 'php5', 'php7', 'php8', 'phtml', 'phar', 'phps', 'pht',
        'exe', 'bat', 'cmd', 'com', 'msi', 'sh', 'js', 'html', 'htm', 'svg'];

    public function __construct(private AnexoDAO $anexos = new AnexoDAO()) {}

    /** Valida (sem gravar). Lança Falha se algum arquivo não serve. */
    public function validar(array $lista, int $maxArquivos): void
    {
        if (count($lista) > $maxArquivos) throw new Falha("Máximo de $maxArquivos arquivos por envio.");
        foreach ($lista as $a) {
            if ($a['error'] === UPLOAD_ERR_INI_SIZE || $a['error'] === UPLOAD_ERR_FORM_SIZE) throw new Falha("“{$a['name']}” é grande demais.");
            if ($a['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($a['tmp_name'])) throw new Falha("Não foi possível receber “{$a['name']}”.");
            if ($a['size'] > config('max_bytes_arquivo')) throw new Falha("“{$a['name']}” passa de 20 MB.");
            $ext = strtolower(pathinfo($a['name'], PATHINFO_EXTENSION));
            if (in_array($ext, self::EXTENSOES_PROIBIDAS, true)) throw new Falha("Tipo de arquivo não permitido: “{$a['name']}”.");
        }
    }

    /** Grava os arquivos com nome aleatório e registra cada um em `anexos` (de uma publicação OU de uma entrega). */
    public function guardarAnexos(array $lista, ?int $publicacaoId, ?int $entregaId): void
    {
        $pasta = self::pasta();
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        foreach ($lista as $a) {
            $nomeInterno = bin2hex(random_bytes(16));
            if (!move_uploaded_file($a['tmp_name'], "$pasta/$nomeInterno")) throw new Falha('Falha ao gravar o arquivo.', 500);
            $this->anexos->inserir(new Anexo(
                publicacaoId: $publicacaoId,
                entregaId: $entregaId,
                nome: mb_substr(preg_replace('/[\x00-\x1F\/\\\\]/', '_', basename($a['name'])), 0, 255),
                caminho: $nomeInterno,
                tipo: $finfo->file("$pasta/$nomeInterno") ?: 'application/octet-stream',
                tamanho: (int) $a['size'],
            ));
        }
    }

    /** Imagem do cabeçalho da turma: só JPG/PNG de verdade, até 2 MB. Devolve o nome gravado no disco. */
    public function guardarImagemTurma(array $envio): string
    {
        if ($envio['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($envio['tmp_name'])) throw new Falha('Não foi possível receber a imagem.');
        if ($envio['size'] > config('max_bytes_imagem')) throw new Falha('Arquivo inválido. Use JPG ou PNG de até 2 MB.');
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($envio['tmp_name']);
        if (!in_array($mime, ['image/jpeg', 'image/png'], true) || @getimagesize($envio['tmp_name']) === false) {
            throw new Falha('Arquivo inválido. Use JPG ou PNG de até 2 MB.');
        }
        $nome = 'turma-' . bin2hex(random_bytes(12)) . ($mime === 'image/png' ? '.png' : '.jpg');
        if (!move_uploaded_file($envio['tmp_name'], self::pasta() . "/$nome")) throw new Falha('Falha ao gravar a imagem.', 500);
        return $nome;
    }

    /** Caminho completo no disco (o nome guardado no banco nunca sai da pasta de uploads). */
    public static function caminhoNoDisco(string $caminho): string
    {
        return config('uploads') . '/' . basename($caminho);
    }

    public function apagarFisico(string $caminho): void
    {
        $arq = self::caminhoNoDisco($caminho);
        if (is_file($arq)) unlink($arq);
    }

    /** Remove os anexos de uma entrega: arquivos do disco e registros do banco. */
    public function removerAnexosDaEntrega(int $entregaId): void
    {
        foreach ($this->anexos->listarDaEntrega($entregaId) as $a) $this->apagarFisico($a->caminho);
        $this->anexos->excluirDaEntrega($entregaId);
    }

    /** Para excluir turma: apaga do disco tudo que depende dela (as linhas saem em cascata). Chame ANTES do DELETE. */
    public function apagarDaTurma(Turma $turma): void
    {
        foreach ($this->anexos->caminhosDaTurma($turma->id) as $caminho) $this->apagarFisico($caminho);
        if ($turma->imagem) $this->apagarFisico($turma->imagem);
    }

    /** Para excluir atividade/material: apaga do disco os anexos dela e das entregas. Chame ANTES do DELETE. */
    public function apagarDaPublicacao(int $publicacaoId): void
    {
        foreach ($this->anexos->listarDaPublicacao($publicacaoId) as $a) $this->apagarFisico($a->caminho);
        foreach ((new EntregaDAO())->idsDaAtividade($publicacaoId) as $entregaId) {
            foreach ($this->anexos->listarDaEntrega($entregaId) as $a) $this->apagarFisico($a->caminho);
        }
    }

    private static function pasta(): string
    {
        $pasta = config('uploads');
        if (!is_dir($pasta)) mkdir($pasta, 0755, true);
        return $pasta;
    }
}
