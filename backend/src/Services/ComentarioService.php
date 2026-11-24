<?php
declare(strict_types=1);

namespace EduConnect\Services;

use EduConnect\DAO\ComentarioDAO;
use EduConnect\DAO\EntregaDAO;
use EduConnect\Models\Comentario;
use EduConnect\Models\Participacao;
use EduConnect\Models\Publicacao;
use EduConnect\Models\Usuario;
use Falha;

/**
 * Comentário público (material ou atividade) ou particular (atividade, entre um aluno e os professores).
 * Editar: só o autor. Excluir: o autor ou um professor.
 */
final class ComentarioService
{
    public function __construct(private ComentarioDAO $comentarios = new ComentarioDAO()) {}

    /**
     * Pelo modal "Entrega do aluno" chega $entregaId (sempre particular, com o aluno dono da entrega);
     * senão, $publicacaoId. No particular, o aluno só conversa consigo mesmo e o professor informa $alunoId.
     */
    public function comentar(Usuario $u, string $texto, ?int $publicacaoId, ?int $entregaId, bool $particular, ?int $alunoId): Comentario
    {
        if ($entregaId !== null) {
            $e = (new EntregaDAO())->buscarPorId($entregaId) ?? throw new Falha('Entrega não encontrada.', 404);
            $publicacaoId = $e->atividadeId;
            $alunoId = $e->alunoId;
            $particular = true;
        }
        $p = Permissoes::publicacao($publicacaoId ?? throw new Falha('Item inválido.'));
        $papel = Permissoes::exigirMembro($p->turmaId, $u);

        if (!$particular) {
            $alunoId = null;
        } else {
            if ($p->tipo !== Publicacao::ATIVIDADE) throw new Falha('Comentário particular só existe em atividades.');
            if ($papel === Participacao::ALUNO) {
                $alunoId = $u->id;
            } else {
                $alunoId ?? throw new Falha('Aluno inválido.');
                if (Permissoes::papel($p->turmaId, $alunoId) !== Participacao::ALUNO) throw new Falha('Aluno não encontrado nesta turma.', 404);
            }
        }
        $c = new Comentario(publicacaoId: $p->id, autorId: $u->id, alunoId: $alunoId, texto: $texto, particular: $particular);
        $this->comentarios->inserir($c);
        $c->autor = $u->nome;
        return $c;
    }

    /** Comentários públicos da publicação, para os membros da turma. @return list<Comentario> */
    public function publicos(Usuario $u, int $publicacaoId): array
    {
        $p = Permissoes::publicacao($publicacaoId);
        Permissoes::exigirMembro($p->turmaId, $u);
        return $this->comentarios->listarDaPublicacao($p->id);
    }

    /** A conversa particular do próprio aluno com os professores numa atividade. @return list<Comentario> */
    public function meusParticulares(Usuario $u, int $atividadeId): array
    {
        $p = Permissoes::publicacao($atividadeId, Publicacao::ATIVIDADE);
        Permissoes::exigirMembro($p->turmaId, $u);
        return $this->comentarios->listarDaPublicacao($p->id, $u->id);
    }

    public function editar(Usuario $u, int $comentarioId, string $texto): Comentario
    {
        $c = $this->buscar($comentarioId);
        if ($c->autorId !== $u->id) throw new Falha('Só o autor pode editar o comentário.', 403);
        Permissoes::exigirMembro($c->turmaId, $u);
        $c->texto = $texto;
        $this->comentarios->atualizarTexto($c);
        return $c;
    }

    public function excluir(Usuario $u, int $comentarioId): void
    {
        $c = $this->buscar($comentarioId);
        $papel = Permissoes::exigirMembro($c->turmaId, $u);
        if ($c->autorId !== $u->id && $papel !== Participacao::PROFESSOR) throw new Falha('Você não pode excluir este comentário.', 403);
        $this->comentarios->excluir($c->id);
    }

    private function buscar(int $id): Comentario
    {
        return $this->comentarios->buscarPorId($id) ?? throw new Falha('Comentário não encontrado.', 404);
    }
}
