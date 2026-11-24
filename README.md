# EduConnect

Clone educacional do Google Classroom, feito como trabalho acadêmico, sem vínculo com o Google. O front-end é HTML, CSS e JavaScript; as páginas são renderizadas no servidor em PHP 8, com banco de dados (MySQL ou SQLite) e contas de usuário.

Como as páginas precisam de PHP, o site não roda no GitHub Pages: use o Docker ou o servidor do PHP (veja "Rodar localmente").

## Integrantes

- Gabriel Somariva
- Gustavo Peretti
- João Raimundi

## O que tem no site

| Página | Arquivo | O que faz |
|--------|---------|-----------|
| Início | `pages/index.php` | Cards das turmas, menu "+" para criar ou participar de uma turma e troca de tema claro/escuro. |
| Login e cadastro | `pages/login.php`, `pages/cadastro.php` | Telas de acesso (conta real, com senha guardada pelo back-end). |
| Mural | `pages/mural.php` | Banner da turma, código da turma, próximas atividades, novo aviso e publicações. |
| Atividades | `pages/atividades.php` | Lista de atividades e materiais, com as subpáginas "Criar atividade" e "Criar material". |
| Pessoas | `pages/pessoas.php` | Professores e estudantes da turma, com remoção de estudante. |
| Instruções | `pages/instrucoes.php` | Detalhes de uma atividade; o aluno entrega o trabalho por aqui. |
| Trabalhos dos estudantes | `pages/trabalho-estudantes.php` | Entregas dos alunos para o professor avaliar. |

Os modais (criar turma, participar da turma, configurações da turma, personalizar aparência etc.) são Web Components em `modais/components/`. A página `modais/index.html` mostra todos eles.

## Como usar

1. Rode o projeto (veja abaixo), crie uma conta em **Criar conta** e entre. A página inicial mostra as suas turmas.
2. Clique em um card para entrar na turma. As abas **Mural**, **Atividades** e **Pessoas** ficam no topo da turma.
3. Use o **+** da barra superior para criar uma turma ou participar de uma (código de 5 a 8 letras ou números).
4. No Mural, o **lápis** personaliza o banner (foto ou cor), o **ⓘ** mostra as informações da turma e a **engrenagem** abre as configurações.
5. Em Atividades, o **+ Criar** abre as telas de nova atividade ou novo material. O menu **⋮** de cada item tem editar e excluir.

Turmas, avisos, atividades, entregas, notas, comentários, configurações e aparência ficam no banco de dados. Só o tema claro/escuro fica no navegador (`localStorage`). Quem cria a turma é o administrador; quem entra pelo código entra como estudante.

## Rodar localmente

```bash
git clone https://github.com/Foka4a/educonnect.git
```

Com o PHP 8 instalado, na pasta do projeto rode `php -S localhost:8000 backend/servidor-dev.php` e abra `http://localhost:8000/frontend/pages/login.php`. O banco SQLite é criado sozinho em `backend/storage/`. Mais detalhes em [`backend/README.md`](backend/README.md).

### Com Docker (front-end + back-end + MySQL)

Com o Docker Desktop aberto, na pasta do projeto:

```bash
docker compose up -d --build
```

Acesse `http://localhost:8080/frontend/pages/login.php`. O compose sobe o PHP 8.3 com Apache (serve `frontend/` e `backend/`) e um MySQL 8.4 (porta `3307` no computador, usuário e senha `educonnect`). As tabelas são criadas sozinhas na primeira chamada. Os arquivos ficam ligados à pasta do projeto, então basta salvar e recarregar a página. Para parar: `docker compose down` (acrescente `-v` para apagar o banco e os uploads).

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
```

## Uso de IA

O trabalho usou IA como assistente. A declaração está em [`DECLARACAO_IA.md`](DECLARACAO_IA.md) e todos os prompts, com o impacto de cada um, estão em [`PROMPTS.md`](PROMPTS.md).
