<?php
declare(strict_types=1);

namespace EduConnect\DAO;

use EduConnect\Models\Publicacao;

final class PublicacaoDAO extends DAO
{
    public function buscarPorId(int $id): ?Publicacao
    {
        $l = $this->linha('SELECT * FROM publicacoes WHERE id = ?', [$id]);
        return $l ? Publicacao::deLinha($l) : null;
    }

    /** Grava a atividade/material e preenche $p->id. */
    public function inserir(Publicacao $p): int
    {
        $p->criadoEm ??= self::agora();
        return $p->id = $this->inserirLinha(
            'INSERT INTO publicacoes (turma_id, tipo, titulo, descricao, entrega_em, autor_id, criado_em) VALUES (?,?,?,?,?,?,?)',
            [$p->turmaId, $p->tipo, $p->titulo, $p->descricao, $p->entregaEm, $p->autorId, $p->criadoEm]);
    }

    /** Salva título, descrição e prazo; marca atualizado_em. */
    public function atualizar(Publicacao $p): void
    {
        $p->atualizadoEm = self::agora();
        $this->executar('UPDATE publicacoes SET titulo = ?, descricao = ?, entrega_em = ?, atualizado_em = ? WHERE id = ?',
            [$p->titulo, $p->descricao, $p->entregaEm, $p->atualizadoEm, $p->id]);
    }

    /** Entregas, anexos (linhas) e comentários saem em cascata. */
    public function excluir(int $id): void
    {
        $this->executar('DELETE FROM publicacoes WHERE id = ?', [$id]);
    }
}
