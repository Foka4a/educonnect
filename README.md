# EduConnect

Clone educacional do Google Classroom, feito como trabalho acadêmico, sem vínculo com o Google. O front-end é HTML, CSS e JavaScript; as páginas são renderizadas no servidor em PHP 8, com banco de dados (MySQL ou SQLite) e contas de usuário.

Como as páginas precisam de PHP, o site não roda no GitHub Pages: use o Docker, o servidor do PHP ou o XAMPP (veja "Rodar localmente").

## Integrantes

- Gabriel Somariva
- Gustavo Peretti
- João Raimundi

## O que tem no site

| Página | Arquivo | O que faz |
|--------|---------|-----------|
| Início | `pages/index.php` | Cards das turmas (o ⋮ de cada card exclui a turma, para o administrador, ou sai dela), menu "+" para criar ou participar de uma turma e troca de tema claro/escuro. |
| Login e cadastro | `pages/login.php`, `pages/cadastro.php` | Telas de acesso (conta real, com senha guardada pelo back-end). |
| Mural | `pages/mural.php` | Banner da turma, código da turma, próximas atividades, novo aviso e publicações. |
| Atividades | `pages/atividades.php` | Lista de atividades e materiais, com as subpáginas "Criar atividade" e "Criar material". |
| Pessoas | `pages/pessoas.php` | Professores e estudantes da turma, com remoção de estudante. |
| Instruções | `pages/instrucoes.php` | Detalhes de uma atividade; o aluno entrega o trabalho por aqui. |
| Trabalhos dos estudantes | `pages/trabalho-estudantes.php` | Entregas dos alunos para o professor avaliar. |

Os modais (criar turma, participar da turma, configurações da turma, personalizar aparência etc.) são Web Components em `modais/components/`. A página `modais/index.html` mostra todos eles.

## Como usar

1. Rode o projeto (veja abaixo) e entre com o usuário padrão (e-mail `padrao@educonnect.test`, senha `educonnect123`) ou crie uma conta em **Criar conta**. A página inicial mostra as suas turmas.
2. Clique em um card para entrar na turma. As abas **Mural**, **Atividades** e **Pessoas** ficam no topo da turma.
3. Use o **+** da barra superior para criar uma turma ou participar de uma (código de 5 a 8 letras ou números).
4. No Mural, o **lápis** personaliza o banner (foto ou cor), o **ⓘ** mostra as informações da turma e a **engrenagem** abre as configurações.
5. Em Atividades, o **+ Criar** abre as telas de nova atividade ou novo material. O menu **⋮** de cada item tem editar e excluir.

Turmas, avisos, atividades, entregas, notas, comentários, configurações e aparência ficam no banco de dados. Só o tema claro/escuro fica no navegador (`localStorage`). Quem cria a turma é o administrador; quem entra pelo código entra como estudante.

## Rodar localmente

Clone o repositório e entre na pasta:

```bash
git clone https://github.com/Foka4a/educonnect.git
cd educonnect
```

Escolha **uma** das três formas abaixo. Em todas, o banco se inicializa sozinho (veja "Inicialização do banco"), e é só entrar com o usuário padrão.

### Opção 1: Docker (recomendado)

Precisa do Docker Desktop (ou Docker + Compose) aberto. Ele sobe o PHP 8.3 com Apache e um MySQL 8.4, sem instalar mais nada.

```bash
docker compose up -d --build
```

1. Espere o MySQL ficar pronto (alguns segundos; `docker compose ps` mostra `healthy`).
2. Abra `http://localhost/frontend/pages/login.php`.
3. Entre com `padrao@educonnect.test` / `educonnect123`.

| Comando | O que faz |
|---------|-----------|
| `docker compose ps` | Mostra se os contêineres estão rodando. |
| `docker compose logs -f web` | Mostra os erros do PHP/Apache. |
| `docker compose down` | Para tudo. O banco e os uploads continuam salvos nos volumes. |
| `docker compose down -v` | Para tudo **e apaga** o banco e os uploads. Na próxima subida, o banco começa do zero. |

Os arquivos do projeto ficam ligados ao contêiner: basta salvar e recarregar a página, sem rebuild.

Portas: o site usa a `80` e o MySQL a `3306` do computador (usuário e senha `educonnect`). Se alguma já estiver ocupada (por exemplo, pelo XAMPP), troque na hora de subir:

```bash
WEB_PORT=8080 DB_PORT_HOST=3307 docker compose up -d --build
```

e abra `http://localhost:8080/frontend/pages/login.php`.

### Opção 2: servidor embutido do PHP (SQLite)

Precisa do PHP 8 com a extensão `pdo_sqlite`. Não precisa de banco instalado.

```bash
php -S localhost:8000 backend/servidor-dev.php
```

Abra `http://localhost:8000/frontend/pages/login.php`. O banco é um arquivo SQLite criado sozinho em `backend/storage/educonnect.sqlite`. Para recomeçar do zero, pare o servidor e apague esse arquivo.

### Opção 3: XAMPP (Apache + MySQL)

1. Copie o projeto para `htdocs/educonnect` e ligue o Apache e o MySQL no painel do XAMPP.
2. No phpMyAdmin, crie um banco vazio chamado `educonnect` (`utf8mb4`).
3. Em `backend/config.php`, troque o `dsn`, o `usuario` e a `senha` para o MySQL:
   `'mysql:host=localhost;dbname=educonnect;charset=utf8mb4'`, `'root'` e `''`.
4. Abra `http://localhost/educonnect/frontend/pages/login.php`.

### Inicialização do banco

Não é preciso rodar nenhum script na mão. Na primeira página aberta, o back-end (`backend/src/Database/Esquema.php`) roda:

1. `backend/sql/schema.sql`, que cria as tabelas que ainda não existem;
2. `backend/sql/usuario-padrao.sql`, que cria o usuário padrão se ele ainda não existe.

Isso se repete em toda requisição, mas só cria o que falta: os dados já gravados não mudam. O usuário padrão é gravado no banco como qualquer conta e só some se o banco for apagado (e aí volta na página seguinte).

| Usuário padrão | |
|----------------|---|
| E-mail | `padrao@educonnect.test` |
| Senha | `educonnect123` |

Ele serve só para desenvolvimento. Antes de publicar o site, troque o hash da senha em `backend/sql/usuario-padrao.sql` ou apague o arquivo. Se preferir criar as tabelas por fora, o `schema.sql` está escrito para o MySQL e pode ser importado direto no phpMyAdmin.

### Testes

```bash
php backend/tests/run.php      # back-end (SQLite em memória, não mexe no banco de verdade)
npm install && npm test        # front-end (Node 18+)
```

Sem PHP instalado, rode os testes do back-end pelo Docker:
`docker run --rm -v "$PWD":/app -w /app php:8.3-cli php backend/tests/run.php`.

Mais detalhes do back-end (camadas, ações de cada página, segurança) estão em [`backend/README.md`](backend/README.md).

## Estrutura

```
frontend/
  pages/     páginas do site (.php: cada uma lê os dados e trata os próprios formulários)
  partes/    pedaços comuns das páginas (cabeçalho, menu lateral, abas, rodapé)
  style/     CSS de cada página (layout.css é o compartilhado)
  scripts/   JavaScript de cada página (layout.js, tema.js e backend.js são compartilhados)
  modais/    modais como Web Components
  img/       imagens e ícone
backend/     PHP: regras de negócio (Services), banco (DAO + Model) e apoio (lib/); não é aberto direto pelo navegador
  sql/       script de inicialização do banco (schema.sql) e usuário padrão (usuario-padrao.sql)
```

## Uso de IA

O trabalho usou IA como assistente. A declaração está em [`DECLARACAO_IA.md`](DECLARACAO_IA.md) e todos os prompts, com o impacto de cada um, estão em [`PROMPTS.md`](PROMPTS.md).
