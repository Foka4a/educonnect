<?php
declare(strict_types=1);

namespace EduConnect\Models;

/** Linha da tabela `comentarios`. Particular = conversa entre um aluno (alunoId) e os professores. */
final class Comentario
{
    public function __construct(
        public ?int $id = null,
        public int $publicacaoId = 0,
        public int $autorId = 0,
        public ?int $alunoId = null,
        public string $texto = '',
        public bool $particular = false,
        public ?string $criadoEm = null,
        public ?string $editadoEm = null,
        public ?string $autor = null,     // nome do autor (JOIN com usuarios)
        public ?int $turmaId = null,      // turma da publicação (JOIN com publicacoes)
    ) {}

    public static function deLinha(array $l): self
    {
        return new self((int) $l['id'], (int) $l['publicacao_id'], (int) $l['autor_id'],
            $l['aluno_id'] === null ? null : (int) $l['aluno_id'], $l['texto'], (bool) $l['particular'],
            $l['criado_em'] ?? null, $l['editado_em'] ?? null, $l['autor'] ?? null,
            isset($l['turma_id']) ? (int) $l['turma_id'] : null);
    }

    /** Formato que os modais esperam na resposta JSON. */
    public function paraArray(): array
    {
        return ['id' => $this->id, 'autor' => $this->autor ?? '', 'texto' => $this->texto, 'particular' => $this->particular,
                'hora' => date('H:i', strtotime((string) $this->criadoEm))];
    }
}
