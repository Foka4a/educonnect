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

    /** Turmas de que o usuário participa, com o papel dele e o nome do administrador. @return list<Turma> */
    public function listarDoUsuario(int $usuarioId): array
    {
        return array_map([Turma::class, 'deLinha'], $this->linhas(
            'SELECT t.*, p.papel, u.nome AS criador_nome FROM turmas t
             JOIN participacoes p ON p.turma_id = t.id AND p.usuario_id = ?
             JOIN usuarios u ON u.id = t.criador_id
             ORDER BY t.nome', [$usuarioId]));
    }

    /** A turma com o papel do usuário (null se ele não participa) e o nome do administrador. */
    public function buscarParaUsuario(int $id, int $usuarioId): ?Turma
    {
        $l = $this->linha('SELECT t.*, p.papel, u.nome AS criador_nome FROM turmas t
                           LEFT JOIN participacoes p ON p.turma_id = t.id AND p.usuario_id = ?
                           JOIN usuarios u ON u.id = t.criador_id
                           WHERE t.id = ?', [$usuarioId, $id]);
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
            'INSERT INTO turmas (nome, niveis, materia, codigo, imagem, criador_id, criado_em, descricao, secao, sala, cor)
             VALUES (?,?,?,?,?,?,?,?,?,?,?)',
            [$t->nome, $t->niveis, $t->materia, $t->codigo, $t->imagem, $t->criadorId, $t->criadoEm,
             $t->descricao, $t->secao, $t->sala, $t->cor]);
    }

    /** Salva os detalhes, o código e a aparência (imagem e cor). */
    public function atualizar(Turma $t): void
    {
        $this->executar('UPDATE turmas SET nome = ?, niveis = ?, materia = ?, codigo = ?, imagem = ?,
                         descricao = ?, secao = ?, sala = ?, cor = ? WHERE id = ?',
            [$t->nome, $t->niveis, $t->materia, $t->codigo, $t->imagem, $t->descricao, $t->secao, $t->sala, $t->cor, $t->id]);
    }

    /** Participações, publicações, entregas, anexos (linhas), comentários e convites saem em cascata. */
    public function excluir(int $id): void
    {
        $this->executar('DELETE FROM turmas WHERE id = ?', [$id]);
    }
}
