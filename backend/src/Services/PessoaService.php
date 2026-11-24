<?php
declare(strict_types=1);

namespace EduConnect\Services;

use EduConnect\DAO\ParticipacaoDAO;
use EduConnect\Models\Participacao;
use EduConnect\Models\Usuario;
use Falha;

/** Pessoas da turma: promover a professor e remover. */
final class PessoaService
{
    public function __construct(private ParticipacaoDAO $participacoes = new ParticipacaoDAO()) {}

    /** Pessoas da turma (professores primeiro), para os membros. @return list<Participacao> */
    public function listar(Usuario $u, int $turmaId): array
    {
        Permissoes::exigirMembro($turmaId, $u);
        return $this->participacoes->listarDaTurma($turmaId);
    }

    /** Só o administrador promove. */
    public function promover(Usuario $u, int $turmaId, int $pessoaId): void
    {
        $turma = Permissoes::turma($turmaId);
        Permissoes::exigirAdministrador($turma, $u);
        $papel = $this->participacoes->papel($turma->id, $pessoaId) ?? throw new Falha('Essa pessoa não participa da turma.', 404);
        if ($papel === Participacao::PROFESSOR) throw new Falha('Essa pessoa já é professora.', 409);
        $this->participacoes->alterarPapel($turma->id, $pessoaId, Participacao::PROFESSOR);
    }

    /** Professores removem alunos; professores só o administrador remove; o administrador ninguém remove. */
    public function remover(Usuario $u, int $turmaId, int $pessoaId): void
    {
        $turma = Permissoes::turma($turmaId);
        Permissoes::exigirMembro($turma->id, $u, true);
        $papel = $this->participacoes->papel($turma->id, $pessoaId) ?? throw new Falha('Essa pessoa não participa da turma.', 404);
        if ($pessoaId === $turma->criadorId) throw new Falha('O administrador não pode ser removido.', 403);
        if ($pessoaId === $u->id) throw new Falha('Para você sair, use “Sair da turma”.', 409);
        if ($papel === Participacao::PROFESSOR) Permissoes::exigirAdministrador($turma, $u);
        $this->participacoes->excluir($turma->id, $pessoaId);
    }
}
