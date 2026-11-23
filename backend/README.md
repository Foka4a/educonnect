# Back-end do EduConnect

PHP 8 + PDO. Cada modal/formulário do front-end envia (POST) para `backend/index.php/<rota>`; o back-end valida, grava e
**volta para a página de origem** com `?msg=…` (deu certo) ou `?erro=…` (falhou). Quem mostra o aviso é o `scripts/backend.js`.
Chamadas por `fetch()` (cabeçalho `X-Requested-With: fetch`) recebem JSON: `{"ok": true, "mensagem": "…", …}` ou `{"ok": false, "erro": "…"}`.

## Como rodar

1. **Servidor do PHP** (na pasta do projeto): `php -S localhost:8000 backend/servidor-dev.php` e abra `http://localhost:8000/pages/login.html`.
   O `servidor-dev.php` bloqueia o acesso direto a `config`, `lib`, `acoes` e `storage` (o servidor embutido não lê `.htaccess`).
2. **XAMPP/Apache**: coloque o projeto em `htdocs`. O `backend/.htaccess` já bloqueia tudo, menos o `index.php`.
3. **Banco**: por padrão é SQLite (arquivo em `backend/storage/`, criado sozinho, nada para instalar). Para MySQL, edite `backend/config.php`
   (`dsn`, `usuario`, `senha`) e crie o banco vazio `educonnect`; as tabelas são criadas na primeira chamada.

## Rotas

| Modal / tela | Rota (POST) | Campos | Quem pode |
|---|---|---|---|
| Cadastro / Login / Sair | `cadastro`, `login`, `sair` (`eu` é GET) | nome, email, senha, confirmar-senha | público |
| Criar turma | `criar-turma` | nome*, niveis, materia | logado (vira administrador e professor) |
| Participar da turma | `participar-turma` | codigo (5 a 8 letras/números) | logado (entra como aluno) |
| Editar turma | `editar-turma` | turma_id, nome*, niveis, materia | professor |
| Excluir turma | `excluir-turma` | turma_id | administrador |
| Sair da turma | `sair-da-turma` | turma_id | membro (menos o administrador) |
| Personalizar aparência | `personalizar-aparencia` | turma_id, imagem (JPG/PNG até 2 MB) | professor |
| Convidar alunos | `convidar-alunos`, `redefinir-codigo` | turma_id, emails (separados por vírgula) | professor |
| Promover a professor | `promover-professor` | turma_id, pessoa_id | administrador |
| Remover da turma | `remover-pessoa` | turma_id, pessoa_id | professor (professores: só o administrador) |
| Criar / Editar / Excluir atividade | `criar-atividade`, `editar-atividade`, `excluir-atividade` | turma_id ou atividade_id, titulo*, descricao, entrega (ou prazo_tipo/prazo_data/prazo_hora), anexos[], remover_anexos[] | professor |
| Criar / Editar / Excluir material | `criar-material`, `editar-material`, `excluir-material` | turma_id ou material_id, titulo*, descricao, anexos[], remover_anexos[] | professor |
| Enviar trabalho | `enviar-trabalho` | atividade_id, acao (`entregar` ou `cancelar`), arquivos[] | aluno (não altera trabalho já corrigido) |
| Entrega do aluno: nota | `salvar-nota` | entrega_id, nota (0 a 100 ou vazio) | professor |
| Comentários | `comentar`, `editar-comentario`, `excluir-comentario` | publicacao_id (ou entrega_id), texto, particular; comentario_id | membros; editar: só o autor; excluir: autor ou professor |
| Baixar anexo / imagem da turma | `arquivo?id=`, `imagem-turma?id=` (GET) | | membros (entrega: o aluno dono e os professores) |

## Regras e segurança

- Regras de negócio do trabalho: quem entra pelo código é aluno e só o administrador (criador) promove a professor; atrasos
  são calculados pela data de entrega vigente (ao editar o prazo, as entregas já enviadas são reavaliadas).
- Senhas com `password_hash`; sessão com cookie `HttpOnly` e `SameSite=Lax`; POST só aceito do próprio site (`Origin`).
- Todas as consultas usam parâmetros (PDO). A descrição das atividades/materiais passa por uma lista de tags permitidas
  (`b`, `i`, `u`, `ul`, `li`, `br`...), sem atributos.
- Uploads: até 20 MB por arquivo (10 por atividade/material, 5 por entrega), extensões executáveis recusadas, nome aleatório no disco
  (`backend/storage/uploads`) e download só por `arquivo?id=`, depois de conferir a turma.

## Ainda não existe

- As páginas continuam estáticas (`.html`): para listar turmas, atividades e pessoas vindas do banco falta uma leitura (páginas em PHP ou rotas GET em JSON).
- Envio de e-mail dos convites (o convite é só registrado) e limite de tentativas de login.
