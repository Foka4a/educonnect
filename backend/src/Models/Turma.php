<?php
declare(strict_types=1);

namespace EduConnect\Models;

/** Linha da tabela `turmas`. O criador é o administrador da turma. */
final class Turma
{
    public function __construct(
        public ?int $id = null,
        public string $nome = '',
        public string $niveis = '',
        public string $materia = '',
        public string $codigo = '',
        public ?string $imagem = null,
        public int $criadorId = 0,
        public ?string $criadoEm = null,
        public string $descricao = '',
        public string $secao = '',
        public string $sala = '',
        public string $cor = '',              // cor do cabeçalho do mural ('' = padrão); a imagem, se houver, vale mais
        public ?string $papel = null,         // papel de quem está vendo (JOIN com participacoes)
        public ?string $criadorNome = null,   // nome do administrador (JOIN com usuarios)
    ) {}

    public static function deLinha(array $l): self
    {
        return new self(
            id: (int) $l['id'], nome: $l['nome'], niveis: $l['niveis'], materia: $l['materia'], codigo: $l['codigo'],
            imagem: $l['imagem'] ?? null, criadorId: (int) $l['criador_id'], criadoEm: $l['criado_em'] ?? null,
            descricao: $l['descricao'] ?? '', secao: $l['secao'] ?? '', sala: $l['sala'] ?? '', cor: $l['cor'] ?? '',
            papel: $l['papel'] ?? null, criadorNome: $l['criador_nome'] ?? null,
        );
    }

    public function ehAdministrador(Usuario $u): bool
    {
        return $this->criadorId === $u->id;
    }

    /** Papel de quem está vendo a turma (vem de TurmaDAO::buscarParaUsuario/listarDoUsuario). */
    public function ehProfessor(): bool
    {
        return $this->papel === Participacao::PROFESSOR;
    }
}
