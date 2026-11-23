<?php
declare(strict_types=1);

namespace EduConnect\DAO;

use EduConnect\Models\Turma;

final class TurmaDAO extends DAO
{
    public function buscarPorId(int $id): ?Turma
    {
        $l = $this->linha('SELECT * FROM turmas WHERE id = ?', [$id]);
        return $l ? Turma::deLinha($l) : null;
    }

    public function buscarPorCodigo(string $codigo): ?Turma
    {
        $l = $this->linha('SELECT * FROM turmas WHERE codigo = ?', [$codigo]);
        return $l ? Turma::deLinha($l) : null;
    }

    public function codigoExiste(string $codigo): bool
    {
        return $this->linha('SELECT 1 FROM turmas WHERE codigo = ?', [$codigo]) !== null;
    }

    /** Grava a turma e preenche $t->id. */
    public function inserir(Turma $t): int
    {
        $t->criadoEm ??= self::agora();
        return $t->id = $this->inserirLinha(
            'INSERT INTO turmas (nome, niveis, materia, codigo, imagem, criador_id, criado_em) VALUES (?,?,?,?,?,?,?)',
            [$t->nome, $t->niveis, $t->materia, $t->codigo, $t->imagem, $t->criadorId, $t->criadoEm]);
    }

    /** Salva nome, níveis, matéria, código e imagem. */
    public function atualizar(Turma $t): void
    {
        $this->executar('UPDATE turmas SET nome = ?, niveis = ?, materia = ?, codigo = ?, imagem = ? WHERE id = ?',
            [$t->nome, $t->niveis, $t->materia, $t->codigo, $t->imagem, $t->id]);
    }

    /** Participações, publicações, entregas, anexos (linhas), comentários e convites saem em cascata. */
    public function excluir(int $id): void
    {
        $this->executar('DELETE FROM turmas WHERE id = ?', [$id]);
    }
}
