# Back-end do EduConnect

PHP 8 + PDO com **renderização no servidor**. Cada página `frontend/pages/*.php` lê os próprios dados e trata os
próprios formulários:

- **Ler:** a página chama os Services e já chega ao navegador com os dados (turmas, mural, atividades, pessoas,
  entregas). Sem login, vai para `login.php`; sem permissão, volta para o início com o aviso.
- **Gravar:** os formulários e modais postam para **a própria página**, com um campo oculto `acao`
  (ex.: `acao=criar-aviso`). No topo, a página chama `formularios([...])`, que acha a ação, confere a origem, chama o
  Service e volta para a página com `?msg=…` (deu certo) ou `?erro=…` (falhou). Quem mostra o aviso é o `scripts/backend.js`.
- Duas ações salvam sem recarregar (nota e comentário em "Trabalhos dos estudantes"): `fetch()` para a própria página,
  com o cabeçalho `X-Requested-With: fetch`, que recebe JSON `{"ok": true, …}` ou `{"ok": false, "erro": "…"}`.

## Como rodar

1. **Servidor do PHP** (na raiz do projeto): `php -S localhost:8000 backend/servidor-dev.php` e abra `http://localhost:8000/frontend/pages/login.php`.
   O `servidor-dev.php` bloqueia o acesso direto à pasta `backend/` e a `frontend/partes` (o servidor embutido não lê `.htaccess`).
2. **XAMPP/Apache**: coloque o projeto em `htdocs` (ex.: `http://localhost/educonnect/frontend/pages/login.php`). O `backend/.htaccess` bloqueia a pasta inteira e o `frontend/partes/.htaccess` bloqueia as partes das páginas.
3. **Docker Compose** (na raiz do projeto): `docker compose up -d --build` e abra `http://localhost/frontend/pages/login.php` (ou a porta definida em `WEB_PORT`).
   Sobe PHP 8.3 + Apache (lê os `.htaccess`) e MySQL 8.4; o `config.php` usa o MySQL sozinho quando a variável `DB_HOST` existe.
   Uploads ficam no volume `uploads` e o banco no volume `mysql`.
4. **Banco**: por padrão é SQLite (arquivo em `backend/storage/`, criado sozinho, nada para instalar). Para MySQL, edite `backend/config.php`
   (`dsn`, `usuario`, `senha`) e crie o banco vazio `educonnect`; as tabelas são criadas na primeira chamada, e um banco
   criado por uma versão anterior ganha sozinho as colunas novas (`Esquema::adicionarColunas`).
   O script de inicialização fica em `sql/schema.sql` (escrito para o MySQL, dá para importar direto no phpMyAdmin);
   o `Esquema.php` roda esse arquivo e, no SQLite, adapta a chave autoincremento.
   Em seguida roda o `sql/usuario-padrao.sql`, que cria a conta **padrao@educonnect.test** / senha **educonnect123**
   se ela ainda não existe (também volta se for apagada). É só para desenvolvimento: troque o hash ou tire o arquivo antes de publicar.

## Testes

- **Back-end** (SQLite na memória, não mexe no banco de verdade): `php backend/tests/run.php`. Sem PHP instalado:
  `docker run --rm -v "$PWD":/app -w /app php:8.3-cli php backend/tests/run.php`. Teste novo: um `backend/tests/*Test.php`
  com `teste('nome', function () {...})` e os ajudantes `igual()`, `verdade()`, `recusa(status, fn)`, `novo_usuario()` e
  `turma_com_aluno()` (do `run.php`). O envio de arquivos não é testado: `move_uploaded_file` só funciona numa requisição HTTP.
- **Front-end** (Node 20+): `npm install` uma vez e `npm test`. Os testes ficam em `frontend/tests/*.test.js` e rodam os
  scripts numa página falsa (`jsdom`), montada por `frontend/tests/pagina.js`.

## Onde fica cada coisa

O navegador só abre `frontend/`. A pasta `backend/` não é chamada direto: as páginas carregam o `backend/bootstrap.php`
(pelo `frontend/partes/pagina.php`) e usam as classes dela.

### Camadas

```
frontend/pages/*.php  páginas: no topo, formularios([...]) trata o POST (lê os campos e chama o Service); depois,
                      carregar(fn () => ...) lê pelos Services e o HTML do grupo mostra os dados (foreach + e()).
                      arquivo.php e imagem-turma.php: download de anexo e foto da turma.
frontend/partes/      pedaços comuns das páginas: pagina.php (carrega o back-end, exige login, formularios() com as ações
                      comuns: sair, criar/participar de turma, configurações da turma; ajudantes e(), acao(), data_br(),
                      carregar()), cabeca.php (<head>), topo.php (barra superior e menu lateral),
                      abas-turma.php (Mural/Atividades/Pessoas) e rodape.php (aviso e modais comuns)
backend/bootstrap.php carrega as classes e o lib/
src/Services/         as regras de negócio, usadas pelas páginas (leituras e gravações):
                      quem pode o quê (Permissoes), prazos e atrasos, nota, código da turma, convites, avisos,
                      comentários particulares, aparência, transações e arquivos no disco (ArquivoService).
                      Recebem valores já lidos (não mexem em $_POST) e lançam Falha com a mensagem e o status HTTP.
lib/                  apoio HTTP: config, sessão, leitura/validação dos campos, limpeza do HTML e respostas. Sem SQL.
src/Database/         Conexao.php: o PDO único (MySQL no Docker, SQLite por padrão) e Conexao::transacao();
                      Esquema.php: roda o sql/schema.sql e o sql/usuario-padrao.sql e acrescenta colunas novas em bancos antigos.
sql/schema.sql        script de inicialização do banco: os CREATE TABLE de todas as tabelas.
sql/usuario-padrao.sql   a conta padrão (padrao@educonnect.test), criada se ainda não existe.
src/Models/           uma classe por tabela (Usuario, Turma, Participacao, Publicacao, Entrega, Anexo, Comentario, Convite),
                      com os dados de uma linha (deLinha) e regras pequenas (ehAdministrador, prazoVencido, foiCorrigida...).
src/DAO/              uma classe por tabela com todo o SQL (buscarPorId, listar..., inserir, atualizar, excluir...), sempre com
                      prepared statements, recebendo e devolvendo Models.
src/autoload.php      carrega as classes do namespace EduConnect\ (PSR-4).
```

Exemplo de leitura (`pessoas.php`): `$turma = (new TurmaService())->abrir($usuario, $_GET['turma'])` confere que a pessoa
participa da turma e traz o papel dela; `(new PessoaService())->listar($usuario, $turma->id)` devolve as pessoas, e a página
mostra o menu "Promover" só se `$turma->ehAdministrador($usuario)`.

Exemplo de gravação (`pessoas.php`): o formulário "Remover da turma" posta `acao=remover-pessoa`, `turma_id` e `pessoa_id`
para a própria página → `formularios(['remover-pessoa' => function () {...}])` lê os campos e chama
`PessoaService::remover($usuario, $turmaId, $pessoaId)`, que confere se é professor e grava pelo DAO → `sucesso('Pessoa
removida da turma.')` volta para `pessoas.php?turma=…&msg=…`.

Página nova: comece com `require __DIR__ . '/../partes/pagina.php';` e `$usuario = usuario_da_pagina();`, trate os
formulários com `formularios([...])`, leia os dados dentro de `carregar(fn () => ...)` (chamando Services, nunca DAOs) e
escape tudo com `e()`. Ação nova: a regra num método de um Service e uma entrada em `formularios()` da página que tem o
formulário (ou em `acoes_comuns()`, se o formulário aparece em todas). `backend/config/` e `.env.example` ainda não são usados.

## Ações

| Página | `acao` | Campos | Quem pode |
|---|---|---|---|
| `cadastro.php` / `login.php` | `cadastro`, `login` | nome, email, senha, confirmar-senha | público |
| todas (menu da conta) | `sair` | | logado |
| todas (menu "+") | `criar-turma` | nome*, niveis, materia | logado (vira administrador e professor) |
| todas (menu "+") | `participar-turma` | codigo (5 a 8 letras/números) | logado (entra como aluno) |
| todas da turma (Configurações) | `editar-turma` | turma_id, nome*, niveis, materia, descricao, secao, sala (os três últimos: ausente = mantém) | professor |
| `mural.php` | `personalizar-aparencia` | turma_id, imagem (JPG/PNG até 2 MB) ou cor (uma das cores do modal), remover_foto | professor |
| `mural.php` | `criar-aviso`, `excluir-aviso` | turma_id, texto* (até 2000); aviso_id | professor |
| `mural.php` | `redefinir-codigo` | turma_id | professor |
| `mural.php`, `atividades.php`, `instrucoes.php` | `excluir-atividade`, `excluir-material` | atividade_id ou material_id | professor |
| `atividades.php` | `criar-atividade`, `editar-atividade`, `criar-material`, `editar-material` | turma_id ou atividade_id/material_id, titulo*, descricao, prazo_tipo/prazo_data/prazo_hora (ou entrega), anexos[], remover_anexos[] | professor |
| `pessoas.php` | `promover-professor` | turma_id, pessoa_id | administrador |
| `pessoas.php` | `remover-pessoa` | turma_id, pessoa_id | professor (professores: só o administrador) |
| `instrucoes.php` | `entregar-trabalho`, `cancelar-envio` | atividade_id, arquivos[] | aluno (não altera trabalho já corrigido) |
| `instrucoes.php` | `comentar` | publicacao_id, texto, particular (1 = só para os professores) | membros |
| `trabalho-estudantes.php` (fetch) | `salvar-nota`, `comentar` | entrega_id, nota (0 a 100 ou vazio); entrega_id, texto | professor |
| `arquivo.php?id=`, `imagem-turma.php?id=` (GET) | | | membros (entrega: o aluno dono e os professores) |

Uma ação só vale na página que a trata: `acao=criar-aviso` postado em `pessoas.php` volta com "Ação inválida.".

## Regras e segurança

- Regras de negócio do trabalho: quem entra pelo código é aluno e só o administrador (criador) promove a professor; atrasos
  são calculados pela data de entrega vigente (ao editar o prazo, as entregas já enviadas são reavaliadas).
- Senhas com `password_hash`; sessão com cookie `HttpOnly` e `SameSite=Lax`; POST só aceito do próprio site (`Origin`).
- Todas as consultas ficam nos DAOs e usam parâmetros (PDO). A descrição das atividades/materiais passa por uma lista de tags permitidas
  (`b`, `i`, `u`, `ul`, `li`, `br`...), sem atributos.
- Uploads: até 20 MB por arquivo (10 por atividade/material, 5 por entrega), extensões executáveis recusadas, nome aleatório no disco
  (`backend/storage/uploads`) e download só por `arquivo.php?id=`, depois de conferir a turma.

## Páginas

| Página | Lê (Services) | Quem vê o quê |
|---|---|---|
| `index.php` | `TurmaService::listarDoUsuario` | turmas de que a pessoa participa |
| `mural.php?turma=` | `TurmaService::abrir`, `PublicacaoService::proximasAtividades`, `listar` (avisos, atividades, materiais) | código, Novo aviso, Personalizar e Excluir: só professor |
| `atividades.php?turma=` (`&editar=`) | `PublicacaoService::listar`, `anexosDe`, `EntregaService::contarEntregues`, `PessoaService::listar` | Criar, Editar, Excluir e contadores: só professor |
| `pessoas.php?turma=` | `PessoaService::listar` | Remover: professor; Promover e remover professor: administrador |
| `instrucoes.php?atividade=` | `PublicacaoService::abrir`, `ComentarioService::publicos`; aluno: `EntregaService::doAluno`, `ComentarioService::meusParticulares` | aluno: "Seu trabalho" (entregar, reenviar, cancelar) e comentários particulares |
| `trabalho-estudantes.php?atividade=` | `EntregaService::daAtividade` | só professor (nota e comentário particular por fetch) |
| `login.php`, `cadastro.php` | — | público (quem já entrou vai para o início) |

## Ainda não existe

- Na página inicial, o menu ⋮ dos cards (sair da turma / excluir turma), o botão "Convidar" da aba Pessoas e editar ou
  excluir comentários ainda não têm tela. As regras já existem nos Services (`TurmaService::sair`, `excluir`, `convidar`;
  `ComentarioService::editar`, `excluir`): falta o formulário e a entrada em `formularios()` da página.
- O interruptor "Aceita envios" de "Trabalhos dos estudantes" saiu da tela: não há regra para fechar as entregas.
- Envio de e-mail dos convites (o convite é só registrado) e limite de tentativas de login.
