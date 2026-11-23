<?php
declare(strict_types=1);

namespace EduConnect\DAO;

use EduConnect\Models\Participacao;

final class ParticipacaoDAO extends DAO
{
    public function buscar(int $turmaId, int $usuarioId): ?Participacao
    {
        $l = $this->linha('SELECT * FROM participacoes WHERE turma_id = ? AND usuario_id = ?', [$turmaId, $usuarioId]);
        return $l ? Participacao::deLinha($l) : null;
    }

    /** 'aluno', 'professor' ou null (não participa). */
    public function papel(int $turmaId, int $usuarioId): ?string
    {
        return $this->buscar($turmaId, $usuarioId)?->papel;
    }

    public function inserir(Participacao $p): void
    {
        $p->criadoEm ??= self::agora();
        $this->executar('INSERT INTO participacoes (turma_id, usuario_id, papel, criado_em) VALUES (?,?,?,?)',
            [$p->turmaId, $p->usuarioId, $p->papel, $p->criadoEm]);
    }

    public function alterarPapel(int $turmaId, int $usuarioId, string $papel): void
    {
        $this->executar('UPDATE participacoes SET papel = ? WHERE turma_id = ? AND usuario_id = ?', [$papel, $turmaId, $usuarioId]);
    }

    public function excluir(int $turmaId, int $usuarioId): void
    {
        $this->executar('DELETE FROM participacoes WHERE turma_id = ? AND usuario_id = ?', [$turmaId, $usuarioId]);
    }
}
