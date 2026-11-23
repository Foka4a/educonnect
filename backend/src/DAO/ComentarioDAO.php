<?php
declare(strict_types=1);

namespace EduConnect\DAO;

use EduConnect\Models\Comentario;

final class ComentarioDAO extends DAO
{
    /** Já traz o nome do autor e a turma da publicação. */
    public function buscarPorId(int $id): ?Comentario
    {
        $l = $this->linha('SELECT c.*, u.nome AS autor, p.turma_id FROM comentarios c
                           JOIN usuarios u ON u.id = c.autor_id
                           JOIN publicacoes p ON p.id = c.publicacao_id
                           WHERE c.id = ?', [$id]);
        return $l ? Comentario::deLinha($l) : null;
    }

    /** Grava o comentário e preenche $c->id. */
    public function inserir(Comentario $c): int
    {
        $c->criadoEm ??= self::agora();
        return $c->id = $this->inserirLinha(
            'INSERT INTO comentarios (publicacao_id, autor_id, aluno_id, texto, particular, criado_em) VALUES (?,?,?,?,?,?)',
            [$c->publicacaoId, $c->autorId, $c->alunoId, $c->texto, (int) $c->particular, $c->criadoEm]);
    }

    /** Salva o texto e marca editado_em. */
    public function atualizarTexto(Comentario $c): void
    {
        $c->editadoEm = self::agora();
        $this->executar('UPDATE comentarios SET texto = ?, editado_em = ? WHERE id = ?', [$c->texto, $c->editadoEm, $c->id]);
    }

    public function excluir(int $id): void
    {
        $this->executar('DELETE FROM comentarios WHERE id = ?', [$id]);
    }
}
