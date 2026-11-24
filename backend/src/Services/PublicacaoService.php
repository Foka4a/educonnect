<?php
declare(strict_types=1);

namespace EduConnect\Services;

use EduConnect\Database\Conexao;
use EduConnect\DAO\AnexoDAO;
use EduConnect\DAO\EntregaDAO;
use EduConnect\DAO\PublicacaoDAO;
use EduConnect\Models\Anexo;
use EduConnect\Models\Participacao;
use EduConnect\Models\Publicacao;
use EduConnect\Models\Usuario;
use Falha;

/** Atividades, materiais e avisos do mural (tipo = Publicacao::ATIVIDADE | MATERIAL | AVISO), listagens e download dos anexos. */
final class PublicacaoService
{
    public function __construct(
        private PublicacaoDAO $publicacoes = new PublicacaoDAO(),
        private AnexoDAO $anexos = new AnexoDAO(),
        private ArquivoService $arquivos = new ArquivoService(),
    ) {}

    /** Publicações da turma, da mais nova para a mais antiga (só para membros). @param list<string> $tipos @return list<Publicacao> */
    public function listar(Usuario $u, int $turmaId, array $tipos): array
    {
        Permissoes::exigirMembro($turmaId, $u);
        return $this->publicacoes->listarDaTurma($turmaId, $tipos);
    }

    /** Atividades com prazo ainda por vencer, da mais próxima para a mais distante. @return list<Publicacao> */
    public function proximasAtividades(Usuario $u, int $turmaId, int $limite = 3): array
    {
        Permissoes::exigirMembro($turmaId, $u);
        return $this->publicacoes->proximasAtividades($turmaId, agora(), $limite);
    }

    /** Uma publicação para a página de detalhe (com o autor), só para membros da turma. */
    public function abrir(Usuario $u, int $id, ?string $tipo = null): Publicacao
    {
        $p = $this->publicacoes->buscarComAutor($id);
        if (!$p || ($tipo !== null && $p->tipo !== $tipo)) throw new Falha('Item não encontrado.', 404);
        Permissoes::exigirMembro($p->turmaId, $u);
        return $p;
    }

    /** Anexos de cada publicação. @param list<Publicacao> $publicacoes @return array<int, list<Anexo>> id => anexos */
    public function anexosDe(array $publicacoes): array
    {
        $anexos = [];
        foreach ($publicacoes as $p) $anexos[$p->id] = $this->anexos->listarDaPublicacao($p->id);
        return $anexos;
    }

    /** Aviso do mural: texto simples (mostrado sempre escapado). Só professores. */
    public function criarAviso(Usuario $u, int $turmaId, string $texto): Publicacao
    {
        $turma = Permissoes::turma($turmaId);
        Permissoes::exigirMembro($turma->id, $u, true);
        $p = new Publicacao(turmaId: $turma->id, tipo: Publicacao::AVISO, titulo: '', descricao: $texto, autorId: $u->id);
        $this->publicacoes->inserir($p);
        return $p;
    }

    /** Só professores publicam. Material não tem prazo; a descrição passa pela lista de tags permitidas. */
    public function criar(Usuario $u, string $tipo, int $turmaId, string $titulo, string $descricao, ?string $entregaEm, array $arquivos): Publicacao
    {
        $turma = Permissoes::turma($turmaId);
        Permissoes::exigirMembro($turma->id, $u, true);
        $p = new Publicacao(
            turmaId: $turma->id,
            tipo: $tipo,
            titulo: $titulo,
            descricao: limpar_html($descricao),
            entregaEm: $tipo === Publicacao::ATIVIDADE ? $entregaEm : null,
            autorId: $u->id,
        );
        $this->arquivos->validar($arquivos, config('max_arquivos_publicacao'));

        Conexao::transacao(function () use ($p, $arquivos) {
            $this->publicacoes->inserir($p);
            $this->arquivos->guardarAnexos($arquivos, $p->id, null);
        });
        return $p;
    }

    /**
     * $remover: nomes dos anexos atuais que saem. Ao mudar o prazo de uma atividade, os atrasos das entregas
     * já enviadas são recalculados.
     */
    public function editar(Usuario $u, string $tipo, int $id, string $titulo, string $descricao, ?string $entregaEm,
                           array $novos, array $remover): Publicacao
    {
        $p = Permissoes::publicacao($id, $tipo);
        Permissoes::exigirMembro($p->turmaId, $u, true);
        $p->titulo = $titulo;
        $p->descricao = limpar_html($descricao);
        $p->entregaEm = $p->ehAtividade() ? $entregaEm : null;
        $atuais = $this->anexos->listarDaPublicacao($p->id);
        $paraApagar = array_values(array_filter($atuais, fn (Anexo $a) => in_array($a->nome, $remover, true)));
        if (count($atuais) - count($paraApagar) + count($novos) > config('max_arquivos_publicacao')) {
            throw new Falha('Máximo de ' . config('max_arquivos_publicacao') . ' arquivos por item.');
        }
        $this->arquivos->validar($novos, config('max_arquivos_publicacao'));

        Conexao::transacao(function () use ($p, $paraApagar, $novos) {
            $this->publicacoes->atualizar($p);
            foreach ($paraApagar as $a) {
                $this->anexos->excluir($a->id);
                $this->arquivos->apagarFisico($a->caminho);
            }
            $this->arquivos->guardarAnexos($novos, $p->id, null);
            if ($p->ehAtividade()) (new EntregaDAO())->recalcularAtrasos($p->id, $p->entregaEm);
        });
        return $p;
    }

    /** Devolve a publicação excluída (para o controller saber a turma). */
    public function excluir(Usuario $u, string $tipo, int $id): Publicacao
    {
        $p = Permissoes::publicacao($id, $tipo);
        Permissoes::exigirMembro($p->turmaId, $u, true);
        $this->arquivos->apagarDaPublicacao($p->id);
        $this->publicacoes->excluir($p->id);
        return $p;
    }

    /**
     * Anexo de atividade/material: qualquer membro. Anexo de entrega: o aluno dono ou os professores.
     * @return array{0: Anexo, 1: string} o anexo e o caminho no disco
     */
    public function anexoParaDownload(Usuario $u, int $anexoId): array
    {
        $a = $this->anexos->buscarPorId($anexoId) ?? throw new Falha('Arquivo não encontrado.', 404);
        if ($a->publicacaoId) {
            $p = Permissoes::publicacao($a->publicacaoId);
            Permissoes::exigirMembro($p->turmaId, $u);
        } else {
            $e = (new EntregaDAO())->buscarPorId((int) $a->entregaId) ?? throw new Falha('Arquivo não encontrado.', 404);
            $papel = Permissoes::exigirMembro($e->turmaId, $u);
            if ($papel !== Participacao::PROFESSOR && $e->alunoId !== $u->id) throw new Falha('Você não pode ver este arquivo.', 403);
        }
        $arq = ArquivoService::caminhoNoDisco($a->caminho);
        if (!is_file($arq)) throw new Falha('Arquivo não encontrado.', 404);
        return [$a, $arq];
    }
}
