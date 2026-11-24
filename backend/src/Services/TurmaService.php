<?php
declare(strict_types=1);

namespace EduConnect\Services;

use EduConnect\Database\Conexao;
use EduConnect\DAO\ConviteDAO;
use EduConnect\DAO\ParticipacaoDAO;
use EduConnect\DAO\TurmaDAO;
use EduConnect\Models\Convite;
use EduConnect\Models\Participacao;
use EduConnect\Models\Turma;
use EduConnect\Models\Usuario;
use Falha;

/** Turmas: listar, abrir, criar, editar, excluir, entrar pelo código, sair, aparência, convites e código de acesso. */
final class TurmaService
{
    /** Cores do cabeçalho do mural que o modal "Personalizar aparência" oferece ('' = padrão). */
    public const CORES = ['', '#3a86eb', '#e97a1b', '#bdc1c6', '#d3e3fd'];

    public function __construct(
        private TurmaDAO $turmas = new TurmaDAO(),
        private ParticipacaoDAO $participacoes = new ParticipacaoDAO(),
        private ArquivoService $arquivos = new ArquivoService(),
    ) {}

    /** Turmas de que o usuário participa (com o papel dele em cada uma). @return list<Turma> */
    public function listarDoUsuario(Usuario $u): array
    {
        return $this->turmas->listarDoUsuario($u->id);
    }

    /** A turma para uma página: só para quem participa. O papel do usuário fica em $turma->papel. */
    public function abrir(Usuario $u, int $turmaId): Turma
    {
        $turma = $this->turmas->buscarParaUsuario($turmaId, $u->id) ?? throw new Falha('Turma não encontrada.', 404);
        if ($turma->papel === null) throw new Falha('Você não participa desta turma.', 403);
        return $turma;
    }

    /** Quem cria vira administrador e professor da turma. */
    public function criar(Usuario $u, string $nome, string $niveis, string $materia): Turma
    {
        $turma = new Turma(nome: $nome, niveis: $niveis, materia: $materia, codigo: $this->gerarCodigo(), criadorId: $u->id);
        Conexao::transacao(function () use ($turma, $u) {
            $this->turmas->inserir($turma);
            $this->participacoes->inserir(new Participacao($turma->id, $u->id, Participacao::PROFESSOR));
        });
        return $turma;
    }

    /** Só professores. Descrição, seção e sala null = continuam como estão (formulários que não têm esses campos). */
    public function editar(Usuario $u, int $turmaId, string $nome, string $niveis, string $materia,
                           ?string $descricao = null, ?string $secao = null, ?string $sala = null): Turma
    {
        $turma = Permissoes::turma($turmaId);
        Permissoes::exigirMembro($turma->id, $u, true);
        $turma->nome = $nome;
        $turma->niveis = $niveis;
        $turma->materia = $materia;
        $turma->descricao = $descricao ?? $turma->descricao;
        $turma->secao = $secao ?? $turma->secao;
        $turma->sala = $sala ?? $turma->sala;
        $this->turmas->atualizar($turma);
        return $turma;
    }

    public function excluir(Usuario $u, int $turmaId): void
    {
        $turma = Permissoes::turma($turmaId);
        Permissoes::exigirAdministrador($turma, $u);
        $this->arquivos->apagarDaTurma($turma);
        $this->turmas->excluir($turma->id);
    }

    public function sair(Usuario $u, int $turmaId): void
    {
        $turma = Permissoes::turma($turmaId);
        Permissoes::exigirMembro($turma->id, $u);
        if ($turma->ehAdministrador($u)) throw new Falha('O administrador não pode sair da turma. Exclua a turma, se for o caso.', 409);
        $this->participacoes->excluir($turma->id, $u->id);
    }

    /** Regra de negócio: quem entra pelo código entra como aluno; o administrador pode promover depois. */
    public function participar(Usuario $u, string $codigo): Turma
    {
        $codigo = strtolower(trim($codigo));
        if (!preg_match('/^[a-z0-9]{5,8}$/', $codigo)) throw new Falha('Código inválido (5 a 8 letras ou números).');
        $turma = $this->turmas->buscarPorCodigo($codigo) ?? throw new Falha('Nenhuma turma encontrada com esse código.', 404);
        if ($this->participacoes->papel($turma->id, $u->id)) throw new Falha('Você já participa desta turma.', 409);
        $this->participacoes->inserir(new Participacao($turma->id, $u->id, Participacao::ALUNO));
        return $turma;
    }

    /**
     * Cabeçalho do mural: uma foto ($envio, item de arquivos_enviados('imagem')) ou uma cor de TurmaService::CORES.
     * A foto vale mais que a cor; $removerFoto volta para a cor. $cor null = mantém a atual.
     */
    public function personalizarAparencia(Usuario $u, int $turmaId, ?array $envio, ?string $cor = null, bool $removerFoto = false): Turma
    {
        $turma = Permissoes::turma($turmaId);
        Permissoes::exigirMembro($turma->id, $u, true);
        if (!$envio && $cor === null && !$removerFoto) throw new Falha('Escolha uma imagem ou uma cor.');
        if ($cor !== null && !in_array($cor, self::CORES, true)) throw new Falha('Cor inválida.');
        $novaImagem = $envio ? $this->arquivos->guardarImagemTurma($envio) : null;
        if (($novaImagem || $removerFoto) && $turma->imagem) {
            $this->arquivos->apagarFisico($turma->imagem);
            $turma->imagem = null;
        }
        $turma->imagem = $novaImagem ?? $turma->imagem;
        $turma->cor = $cor ?? $turma->cor;
        $this->turmas->atualizar($turma);
        return $turma;
    }

    /** Caminho no disco da imagem do cabeçalho, só para quem participa da turma. */
    public function arquivoDaImagem(Usuario $u, int $turmaId): string
    {
        $turma = Permissoes::turma($turmaId);
        Permissoes::exigirMembro($turma->id, $u);
        $arq = ArquivoService::caminhoNoDisco((string) $turma->imagem);
        if (!$turma->imagem || !is_file($arq)) throw new Falha('Esta turma não tem imagem.', 404);
        return $arq;
    }

    /**
     * Registra convites para os e-mails (separados por vírgula, ponto e vírgula ou espaço). Devolve quantos são novos.
     * ponytail: o envio do e-mail em si ainda não existe (precisa de servidor de e-mail); o convite fica registrado.
     */
    public function convidar(Usuario $u, int $turmaId, string $emails): int
    {
        $turma = Permissoes::turma($turmaId);
        Permissoes::exigirMembro($turma->id, $u, true);
        $lista = array_unique(array_filter(array_map(fn ($e) => mb_strtolower(trim($e)), preg_split('/[,;\s]+/', $emails) ?: [])));
        if (!$lista) throw new Falha('Adicione ao menos um e-mail.');
        if (count($lista) > 50) throw new Falha('Convide no máximo 50 e-mails por vez.');
        foreach ($lista as $e) if (!filter_var($e, FILTER_VALIDATE_EMAIL) || mb_strlen($e) > 190) throw new Falha("E-mail inválido: $e");
        $convites = new ConviteDAO();
        $novos = 0;
        foreach ($lista as $e) {
            if ($convites->existe($turma->id, $e)) continue;
            $convites->inserir(new Convite(turmaId: $turma->id, email: $e, convidadoPor: $u->id));
            $novos++;
        }
        return $novos;
    }

    public function redefinirCodigo(Usuario $u, int $turmaId): string
    {
        $turma = Permissoes::turma($turmaId);
        Permissoes::exigirMembro($turma->id, $u, true);
        $turma->codigo = $this->gerarCodigo();
        $this->turmas->atualizar($turma);
        return $turma->codigo;
    }

    private function gerarCodigo(): string
    {
        $alfabeto = 'abcdefghijklmnopqrstuvwxyz0123456789';
        do {
            $codigo = '';
            for ($i = 0; $i < 8; $i++) $codigo .= $alfabeto[random_int(0, 35)];
        } while ($this->turmas->codigoExiste($codigo));
        return $codigo;
    }
}
