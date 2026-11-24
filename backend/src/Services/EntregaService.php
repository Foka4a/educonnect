<?php
declare(strict_types=1);

namespace EduConnect\Services;

use EduConnect\Database\Conexao;
use EduConnect\DAO\AnexoDAO;
use EduConnect\DAO\ComentarioDAO;
use EduConnect\DAO\EntregaDAO;
use EduConnect\DAO\ParticipacaoDAO;
use EduConnect\Models\Anexo;
use EduConnect\Models\Comentario;
use EduConnect\Models\Entrega;
use EduConnect\Models\Participacao;
use EduConnect\Models\Publicacao;
use EduConnect\Models\Usuario;
use Falha;

/** Trabalho do aluno (entregar, reenviar, cancelar) e a nota do professor. */
final class EntregaService
{
    public function __construct(
        private EntregaDAO $entregas = new EntregaDAO(),
        private ArquivoService $arquivos = new ArquivoService(),
    ) {}

    /**
     * Página "Trabalhos dos estudantes" (só professores): cada aluno da turma com a entrega (ou null), os arquivos
     * e a conversa particular.
     * @return list<array{aluno: Participacao, entrega: ?Entrega, anexos: list<Anexo>, comentarios: list<Comentario>}>
     */
    public function daAtividade(Usuario $u, int $atividadeId): array
    {
        $atividade = Permissoes::publicacao($atividadeId, Publicacao::ATIVIDADE);
        Permissoes::exigirMembro($atividade->turmaId, $u, true);
        $porAluno = [];
        foreach ($this->entregas->listarDaAtividade($atividade->id) as $e) $porAluno[$e->alunoId] = $e;
        $anexos = new AnexoDAO();
        $comentarios = new ComentarioDAO();
        $lista = [];
        foreach ((new ParticipacaoDAO())->listarDaTurma($atividade->turmaId) as $p) {
            if ($p->papel !== Participacao::ALUNO) continue;
            $e = $porAluno[$p->usuarioId] ?? null;
            $lista[] = [
                'aluno' => $p,
                'entrega' => $e,
                'anexos' => $e && $e->estaEntregue() ? $anexos->listarDaEntrega($e->id) : [],
                'comentarios' => $comentarios->listarDaPublicacao($atividade->id, $p->usuarioId),
            ];
        }
        return $lista;
    }

    /**
     * O trabalho do próprio aluno numa atividade: a entrega (ou null) e os arquivos enviados.
     * @return array{entrega: ?Entrega, anexos: list<Anexo>}
     */
    public function doAluno(Usuario $u, int $atividadeId): array
    {
        $atividade = Permissoes::publicacao($atividadeId, Publicacao::ATIVIDADE);
        Permissoes::exigirMembro($atividade->turmaId, $u);
        $e = $this->entregas->buscarDoAluno($atividade->id, $u->id);
        return ['entrega' => $e, 'anexos' => $e && $e->estaEntregue() ? (new AnexoDAO())->listarDaEntrega($e->id) : []];
    }

    /** Entregas válidas por atividade da turma (contadores da aba Atividades). @return array<int, int> */
    public function contarEntregues(Usuario $u, int $turmaId): array
    {
        Permissoes::exigirMembro($turmaId, $u);
        return $this->entregas->contarEntreguesDaTurma($turmaId);
    }

    /** Entrega (ou reenvia, trocando os arquivos). O atraso é calculado pelo prazo vigente. */
    public function entregar(Usuario $u, int $atividadeId, array $arquivos): Entrega
    {
        [$atividade, $entrega] = $this->entregaAlteravel($u, $atividadeId);
        if (!$arquivos) throw new Falha('Escolha ao menos um arquivo.');
        $this->arquivos->validar($arquivos, config('max_arquivos_entrega'));
        $atrasada = $atividade->prazoVencido(agora());

        return Conexao::transacao(function () use ($entrega, $atividade, $u, $atrasada, $arquivos) {
            if ($entrega) {
                $evento = $entrega->estaEntregue() ? 'reenviado' : 'enviado';
                $this->arquivos->removerAnexosDaEntrega($entrega->id);
                $entrega->status = Entrega::ENTREGUE;
                $entrega->atrasada = $atrasada;
                $entrega->enviadoEm = agora();
                $this->entregas->atualizar($entrega);
            } else {
                $evento = 'enviado';
                $entrega = new Entrega(atividadeId: $atividade->id, alunoId: $u->id, atrasada: $atrasada);
                $this->entregas->inserir($entrega);
            }
            $this->entregas->registrarEvento($entrega->id, $evento);
            $this->arquivos->guardarAnexos($arquivos, null, $entrega->id);
            return $entrega;
        });
    }

    public function cancelar(Usuario $u, int $atividadeId): void
    {
        [, $entrega] = $this->entregaAlteravel($u, $atividadeId);
        if (!$entrega || !$entrega->estaEntregue()) throw new Falha('Não há envio para cancelar.', 409);
        Conexao::transacao(function () use ($entrega) {
            $this->arquivos->removerAnexosDaEntrega($entrega->id);
            $entrega->status = Entrega::CANCELADA;
            $this->entregas->atualizar($entrega);
            $this->entregas->registrarEvento($entrega->id, 'cancelado');
        });
    }

    /** Só professores. $bruta: '' (sem nota) ou um inteiro de 0 a 100. */
    public function salvarNota(Usuario $u, int $entregaId, string $bruta): Entrega
    {
        $entrega = $this->entregas->buscarPorId($entregaId) ?? throw new Falha('Entrega não encontrada.', 404);
        Permissoes::exigirMembro($entrega->turmaId, $u, true);
        if (!$entrega->estaEntregue()) throw new Falha('O aluno cancelou esta entrega.', 409);
        $bruta = trim($bruta);
        if ($bruta === '') {
            $entrega->nota = null;
        } elseif (ctype_digit($bruta) && (int) $bruta <= 100) {
            $entrega->nota = (int) $bruta;
        } else {
            throw new Falha('Use um número de 0 a 100.');
        }
        $this->entregas->atualizar($entrega);
        return $entrega;
    }

    /**
     * Só alunos enviam, e trabalho já corrigido não muda.
     * @return array{0: Publicacao, 1: ?Entrega}
     */
    private function entregaAlteravel(Usuario $u, int $atividadeId): array
    {
        $atividade = Permissoes::publicacao($atividadeId, Publicacao::ATIVIDADE);
        $papel = Permissoes::exigirMembro($atividade->turmaId, $u);
        if ($papel !== Participacao::ALUNO) throw new Falha('Só alunos enviam trabalhos.', 403);
        $entrega = $this->entregas->buscarDoAluno($atividade->id, $u->id);
        if ($entrega?->foiCorrigida()) throw new Falha('Este trabalho já foi corrigido e não pode ser alterado.', 409);
        return [$atividade, $entrega];
    }
}
