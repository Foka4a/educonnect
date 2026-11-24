<?php
declare(strict_types=1);

namespace EduConnect\DAO;

use EduConnect\Models\Anexo;

/** Só os registros dos anexos. Gravar/apagar o arquivo no disco fica em src/Services/ArquivoService.php. */
final class AnexoDAO extends DAO
{
    public function buscarPorId(int $id): ?Anexo
    {
        $l = $this->linha('SELECT * FROM anexos WHERE id = ?', [$id]);
        return $l ? Anexo::deLinha($l) : null;
    }

    /** @return list<Anexo> */
    public function listarDaPublicacao(int $publicacaoId): array
    {
        return array_map([Anexo::class, 'deLinha'], $this->linhas('SELECT * FROM anexos WHERE publicacao_id = ?', [$publicacaoId]));
    }

    /** @return list<Anexo> */
    public function listarDaEntrega(int $entregaId): array
    {
        return array_map([Anexo::class, 'deLinha'], $this->linhas('SELECT * FROM anexos WHERE entrega_id = ?', [$entregaId]));
    }

    /**
     * Nomes no disco de todos os anexos da turma (das publicações e das entregas).
     * @return list<string>
     */
    public function caminhosDaTurma(int $turmaId): array
    {
        $sql = 'SELECT a.caminho FROM anexos a
                LEFT JOIN publicacoes p ON p.id = a.publicacao_id
                LEFT JOIN entregas e ON e.id = a.entrega_id
                LEFT JOIN publicacoes pe ON pe.id = e.atividade_id
                WHERE p.turma_id = ? OR pe.turma_id = ?';
        return array_column($this->linhas($sql, [$turmaId, $turmaId]), 'caminho');
    }

    /** Grava o registro e preenche $a->id. */
    public function inserir(Anexo $a): int
    {
        $a->criadoEm ??= self::agora();
        return $a->id = $this->inserirLinha(
            'INSERT INTO anexos (publicacao_id, entrega_id, nome, caminho, tipo, tamanho, criado_em) VALUES (?,?,?,?,?,?,?)',
            [$a->publicacaoId, $a->entregaId, $a->nome, $a->caminho, $a->tipo, $a->tamanho, $a->criadoEm]);
    }

    public function excluir(int $id): void
    {
        $this->executar('DELETE FROM anexos WHERE id = ?', [$id]);
    }

    public function excluirDaEntrega(int $entregaId): void
    {
        $this->executar('DELETE FROM anexos WHERE entrega_id = ?', [$entregaId]);
    }
}
