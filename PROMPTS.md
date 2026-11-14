# Registro de prompts — EduConnect

Todo prompt do trabalho entra na tabela da categoria certa, com o impacto no resultado final.
Categorias: **Configuração/Regras**, **Estrutura (HTML)**, **Estilo (CSS)**, **Interatividade (JS)**, **Acessibilidade**, **Conteúdo**, **Correção de bugs**, **Documentação**.

## Configuração/Regras

| # | Data | Prompt | Impacto no resultado final |
|---|------|--------|----------------------------|
| 1 | 2026-11-13 | "Use tags semanticas, e adicione alt em todas as imagens, registre isso num claude.md. Registre todos os prompts do trabalho categorizados por categoria junto com o impacto no resultado final do trabalho." | Criou o `CLAUDE.md` com as regras de HTML semântico e `alt` obrigatório, que valem para todo o código do projeto. Também criou o registro de prompts. |

## Estrutura (HTML)

| # | Data | Prompt | Impacto no resultado final |
|---|------|--------|----------------------------|
| 1 | 2026-11-13 | "Estou fazendo a estrutura da pagina de login na branch login, coloquei os botões, inputs e titulos. Veja se precisa de alguma correção na estrutura, principalmente na organização e distribuição das tags." + "pode aplicar" | `pages/login.html` reestruturado conforme o wireframe: `main` > `section` (card) com `header` (logo, `h1`, subtítulo), `form` só com os campos e o botão Entrar, e link "Criar conta" fora do form. Ganhou `footer` com o aviso do projeto, `lang="pt-BR"` e título descritivo. Corrigiu o bug do botão "Criar Conta", que enviava o formulário. |

## Estilo (CSS)

| # | Data | Prompt | Impacto no resultado final |
|---|------|--------|----------------------------|
| 1 | 2026-11-13 | "Revisa o codigo do style das paginas no cadastro.style, verifique se condiz com mapa de telas do projeto, nas telas de login e cadastro." | Revisão do `style/cadastro.css` contra o mapa de telas (`Google_Classroom_redesenhado.drawio`). Apontou diferenças com o wireframe: layout mobile sem card, faixa do rodapé, mensagem de erro do cadastro e asterisco de obrigatório. Também apontou problemas de organização (nome do arquivo, classe e id específicos do login usados no cadastro). |
| 2 | 2026-11-13 | "Mobile nao precisa aplicar. O restante das irregularidades podem ser aplicadas" | CSS renomeado para `style/acesso.css`, com nomes neutros (`.card-acesso`, `#titulo-acesso`, `.erro`) servindo login e cadastro. Ficou mais perto do wireframe: rodapé em faixa com borda superior, asterisco nos campos obrigatórios, inputs com fundo cinza, "⚠" nas mensagens de erro, título "Criar conta" no cadastro e erro "As senhas não conferem" acima do botão. A fonte Inter passou a ser carregada via `<link>` (carrega mais rápido), o foco dos inputs usa `outline` (visível no alto contraste) e a senha do cadastro usa `autocomplete="new-password"`. Layout mobile sem card ficou de fora, a pedido. |

## Acessibilidade

| # | Data | Prompt | Impacto no resultado final |
|---|------|--------|----------------------------|
| 1 | 2026-11-13 | (mesmo prompt de Configuração/Regras #1: tags semânticas e `alt`) | Leitores de tela e SEO passam a funcionar desde o início, porque as páginas já nascem com landmarks e textos alternativos. |
| 2 | 2026-11-13 | (mesmo prompt de Estrutura (HTML) #1: revisão do login) | Inputs com `<label>` associado, `type="email"`, `autocomplete` e `required`. A mensagem de erro já está no HTML com `role="alert"`, então o leitor de tela anuncia quando o JS a mostrar. O logo tem `alt=""`, porque o nome já está no `h1`. |

## Documentação

| # | Data | Prompt | Impacto no resultado final |
|---|------|--------|----------------------------|
| 1 | 2026-11-13 | "Corrija o arquivo claude.md, separando a parte de prompts, a parte de prompts num arquivo .md separado, e uma declaração de uso de IA em um arquivo separado tbm .md." | O `CLAUDE.md` ficou só com as regras do projeto. O registro de prompts foi para `PROMPTS.md` e a declaração de uso de IA para `DECLARACAO_IA.md`, o que deixa a documentação de entrega mais organizada. |
