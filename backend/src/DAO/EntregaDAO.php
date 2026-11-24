<?php
declare(strict_types=1);

namespace EduConnect\DAO;

use EduConnect\Models\Entrega;

/** Entregas dos alunos e o histórico delas (tabela `entrega_historico`). */
final class EntregaDAO extends DAO
{
    /** Já traz a turma da atividade (turmaId), usada na conferência de permissão. */
    public function buscarPorId(int $id): ?Entrega
    {
        $l = $this->linha('SELECT e.*, p.turma_id FROM entregas e JOIN publicacoes p ON p.id = e.atividade_id WHERE e.id = ?', [$id]);
        return $l ? Entrega::deLinha($l) : null;
    }

    public function buscarDoAluno(int $atividadeId, int $alunoId): ?Entrega
    {
        $l = $this->linha('SELECT * FROM entregas WHERE atividade_id = ? AND aluno_id = ?', [$atividadeId, $alunoId]);
        return $l ? Entrega::deLinha($l) : null;
    }

    /** @return list<Entrega> */
    public function listarDaAtividade(int $atividadeId): array
    {
        return array_map([Entrega::class, 'deLinha'], $this->linhas('SELECT * FROM entregas WHERE atividade_id = ?', [$atividadeId]));
    }

    /** Quantas entregas válidas (não canceladas) cada atividade da turma tem. @return array<int, int> atividade_id => total */
    public function contarEntreguesDaTurma(int $turmaId): array
    {
        $total = [];
        foreach ($this->linhas("SELECT e.atividade_id, COUNT(*) AS total FROM entregas e JOIN publicacoes p ON p.id = e.atividade_id
                                WHERE p.turma_id = ? AND e.status = 'entregue' GROUP BY e.atividade_id", [$turmaId]) as $l) {
            $total[(int) $l['atividade_id']] = (int) $l['total'];
        }
        return $total;
    }

    /** @return list<int> */
    public function idsDaAtividade(int $atividadeId): array
    {
        return array_map(fn ($l) => (int) $l['id'], $this->linhas('SELECT id FROM entregas WHERE atividade_id = ?', [$atividadeId]));
    }

    /** Grava a entrega e preenche $e->id. */
    public function inserir(Entrega $e): int
    {
        $e->enviadoEm ??= self::agora();
        return $e->id = $this->inserirLinha(
            'INSERT INTO entregas (atividade_id, aluno_id, status, atrasada, nota, enviado_em) VALUES (?,?,?,?,?,?)',
            [$e->atividadeId, $e->alunoId, $e->status, (int) $e->atrasada, $e->nota, $e->enviadoEm]);
    }

    /** Salva status, atraso, nota e data de envio. */
    public function atualizar(Entrega $e): void
    {
        $this->executar('UPDATE entregas SET status = ?, atrasada = ?, nota = ?, enviado_em = ? WHERE id = ?',
            [$e->status, (int) $e->atrasada, $e->nota, $e->enviadoEm, $e->id]);
    }

    /** O prazo da atividade mudou: refaz a marcação de atraso das entregas já enviadas. */
    public function recalcularAtrasos(int $atividadeId, ?string $entregaEm): void
    {
        $this->executar('UPDATE entregas SET atrasada = CASE WHEN ? IS NOT NULL AND enviado_em > ? THEN 1 ELSE 0 END WHERE atividade_id = ?',
            [$entregaEm, $entregaEm, $atividadeId]);
    }

    /** Evento: 'enviado', 'reenviado' ou 'cancelado'. */
    public function registrarEvento(int $entregaId, string $evento): void
    {
        $this->inserirLinha('INSERT INTO entrega_historico (entrega_id, evento, em) VALUES (?,?,?)', [$entregaId, $evento, self::agora()]);
    }
}
