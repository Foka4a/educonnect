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

    /** Da mais nova para a mais antiga, com o nome do autor. @param list<string> $tipos @return list<Publicacao> */
    public function listarDaTurma(int $turmaId, array $tipos): array
    {
        $marcas = implode(',', array_fill(0, count($tipos), '?'));
        return array_map([Publicacao::class, 'deLinha'], $this->linhas(
            "SELECT p.*, u.nome AS autor FROM publicacoes p JOIN usuarios u ON u.id = p.autor_id
             WHERE p.turma_id = ? AND p.tipo IN ($marcas) ORDER BY p.criado_em DESC, p.id DESC", [$turmaId, ...$tipos]));
    }

    /** Atividades com prazo a partir de $agora, da mais próxima para a mais distante. @return list<Publicacao> */
    public function proximasAtividades(int $turmaId, string $agora, int $limite): array
    {
        return array_map([Publicacao::class, 'deLinha'], $this->linhas(
            "SELECT * FROM publicacoes WHERE turma_id = ? AND tipo = 'atividade' AND entrega_em >= ?
             ORDER BY entrega_em LIMIT " . max(1, $limite), [$turmaId, $agora]));
    }

    public function buscarComAutor(int $id): ?Publicacao
    {
        $l = $this->linha('SELECT p.*, u.nome AS autor FROM publicacoes p JOIN usuarios u ON u.id = p.autor_id WHERE p.id = ?', [$id]);
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
