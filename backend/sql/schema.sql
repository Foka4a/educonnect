-- EduConnect: script de inicialização do banco (cria as tabelas que ainda não existem).
-- Escrito para o MySQL. Pode ser importado direto (phpMyAdmin, `mysql educonnect < schema.sql`), mas não precisa:
-- o back-end roda este arquivo sozinho na primeira conexão (src/Database/Esquema.php), e no SQLite
-- troca `INT AUTO_INCREMENT PRIMARY KEY` por `INTEGER PRIMARY KEY AUTOINCREMENT` e tira o `ENGINE=...`.
-- Cada comando termina com `;` no fim da linha.

CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    senha_hash VARCHAR(255) NOT NULL,
    criado_em DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS turmas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(120) NOT NULL,
    niveis VARCHAR(120) NOT NULL DEFAULT '',
    materia VARCHAR(120) NOT NULL DEFAULT '',
    codigo VARCHAR(8) NOT NULL UNIQUE,
    imagem VARCHAR(255) NULL,
    criador_id INT NOT NULL,
    criado_em DATETIME NOT NULL,
    descricao VARCHAR(1000) NOT NULL DEFAULT '',
    secao VARCHAR(120) NOT NULL DEFAULT '',
    sala VARCHAR(120) NOT NULL DEFAULT '',
    cor VARCHAR(7) NOT NULL DEFAULT '',
    FOREIGN KEY (criador_id) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Quem está em cada turma e com qual papel ('professor' ou 'aluno'; o administrador é o criador da turma).
CREATE TABLE IF NOT EXISTS participacoes (
    turma_id INT NOT NULL,
    usuario_id INT NOT NULL,
    papel VARCHAR(10) NOT NULL DEFAULT 'aluno',
    criado_em DATETIME NOT NULL,
    PRIMARY KEY (turma_id, usuario_id),
    FOREIGN KEY (turma_id) REFERENCES turmas(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Avisos, atividades e materiais (coluna `tipo`: 'aviso', 'atividade' ou 'material').
CREATE TABLE IF NOT EXISTS publicacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    turma_id INT NOT NULL,
    tipo VARCHAR(10) NOT NULL,
    titulo VARCHAR(120) NOT NULL,
    descricao TEXT NOT NULL,
    entrega_em DATETIME NULL,
    autor_id INT NOT NULL,
    criado_em DATETIME NOT NULL,
    atualizado_em DATETIME NULL,
    FOREIGN KEY (turma_id) REFERENCES turmas(id) ON DELETE CASCADE,
    FOREIGN KEY (autor_id) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS entregas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    atividade_id INT NOT NULL,
    aluno_id INT NOT NULL,
    status VARCHAR(10) NOT NULL DEFAULT 'entregue',
    atrasada TINYINT NOT NULL DEFAULT 0,
    nota INT NULL,
    enviado_em DATETIME NOT NULL,
    UNIQUE (atividade_id, aluno_id),
    FOREIGN KEY (atividade_id) REFERENCES publicacoes(id) ON DELETE CASCADE,
    FOREIGN KEY (aluno_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS entrega_historico (
    id INT AUTO_INCREMENT PRIMARY KEY,
    entrega_id INT NOT NULL,
    evento VARCHAR(12) NOT NULL,
    em DATETIME NOT NULL,
    FOREIGN KEY (entrega_id) REFERENCES entregas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Arquivos de uma publicação ou de uma entrega (um dos dois fica NULL).
CREATE TABLE IF NOT EXISTS anexos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    publicacao_id INT NULL,
    entrega_id INT NULL,
    nome VARCHAR(255) NOT NULL,
    caminho VARCHAR(80) NOT NULL,
    tipo VARCHAR(100) NOT NULL,
    tamanho INT NOT NULL,
    criado_em DATETIME NOT NULL,
    FOREIGN KEY (publicacao_id) REFERENCES publicacoes(id) ON DELETE CASCADE,
    FOREIGN KEY (entrega_id) REFERENCES entregas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- `particular` = 1: comentário particular sobre a entrega do aluno `aluno_id`; 0: comentário da turma.
CREATE TABLE IF NOT EXISTS comentarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    publicacao_id INT NOT NULL,
    autor_id INT NOT NULL,
    aluno_id INT NULL,
    texto VARCHAR(1000) NOT NULL,
    particular TINYINT NOT NULL DEFAULT 0,
    criado_em DATETIME NOT NULL,
    editado_em DATETIME NULL,
    FOREIGN KEY (publicacao_id) REFERENCES publicacoes(id) ON DELETE CASCADE,
    FOREIGN KEY (autor_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS convites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    turma_id INT NOT NULL,
    email VARCHAR(190) NOT NULL,
    convidado_por INT NOT NULL,
    criado_em DATETIME NOT NULL,
    UNIQUE (turma_id, email),
    FOREIGN KEY (turma_id) REFERENCES turmas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
