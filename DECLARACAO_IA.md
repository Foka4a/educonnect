# Declaração de uso de Inteligência Artificial — EduConnect

## Ferramenta utilizada

- **Claude Code** (Anthropic), modelo Claude Opus 5.5, usado como assistente de programação no terminal.
- **Claude** (Anthropic), aplicativo web/desktop, modelo Claude Sonnet 5.5, usado para criar os modais e resolver um conflito de merge do Git.
- **Claude** (Anthropic), aplicativo web/desktop, modelo Claude Sonnet 5.5, usado para gerar os modelos de modal.

## Finalidade do uso

A IA foi usada para:

- definir e registrar as regras de código do projeto (HTML semântico e `alt` em todas as imagens), no `CLAUDE.md`;
- organizar a documentação do trabalho (registro de prompts e esta declaração);
- ajustar o layout base (cabeçalho, menu lateral responsivo e rodapé) e o JavaScript do menu, reutilizados pelas páginas;
- montar a aba Atividades conforme o mapa de telas (lista expansível, menu "+ Criar", menu ⋮ e confirmação de exclusão) e revisar os modais da pasta `modais/`;
- montar a aba Pessoas conforme o mapa de telas (professores, estudantes, convite e remoção da turma), com o CSS e o JavaScript próprios;
- criar os modais como componentes (Web Components com Bootstrap 5.3), seguindo o guia de estilos e os wireframes do mapa de telas do grupo, em versão desktop e mobile, mais 15 modais derivados das funcionalidades do projeto (criar, editar, excluir, enviar trabalho), na pasta `modais/`;
- criar as subpáginas "Criar atividade" e "Criar material" (telas 10 e 11 do mapa), em tela cheia dentro da página Atividades, com CSS e JS próprios (`style/subpagina-criar.css`, `scripts/subpagina-criar.js`), seguindo o padrão do front-end;
- resolver um conflito de merge do Git e consolidar o registro de prompts;
- criar os modelos de modal em HTML com Bootstrap 5.3, seguindo o guia de estilos do grupo (pasta `modais/`);
- desenvolver o back-end em PHP (`backend/`): banco de dados, conta de usuário, regras de permissão e uma rota para cada modal, com testes automatizados feitos pela IA.

<!-- Atualize esta lista conforme a IA for usada em outras partes (HTML, CSS, JS, conteúdo, correção de bugs). -->

## Registro

Todos os prompts enviados à IA, com categoria e impacto no resultado final, estão em [`PROMPTS.md`](PROMPTS.md).

## Responsabilidade

Todo conteúdo gerado com IA foi revisado pelo(s) autor(es) do trabalho, que assumem a responsabilidade pelo resultado final entregue.
