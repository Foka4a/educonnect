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
| 2 | 2026-11-13 | "leia o claude.md e siga as instruções dos arquivos .md" | Os modais foram revisados com as regras do `CLAUDE.md`: `header`, `footer`, `section`, `ul`/`li` e `aside` no lugar de `div` quando há tag semântica, títulos em ordem (`h1`, `h2`) e nenhuma `<img>` sem `alt` (a logo é SVG inline com `aria-hidden`). |

## Estrutura (HTML)

| # | Data | Prompt | Impacto no resultado final |
|---|------|--------|----------------------------|
| 1 | 2026-11-13 | "Preciso que crie uns modelos de modal, para usarmos em uma cópia do google classroom" | Gerou os seis modelos de modal (criar turma, participar da turma, personalizar aparência, entrega do aluno, promover a professor e excluir atividade), que viram os componentes de diálogo do site. |
| 2 | 2026-11-13 | "voltando aos modais, crie em arquivos separados para usarmos depois" | Cada modal virou um arquivo HTML próprio em `modais/` (formulário com `method="post"`), com página de teste e bloco pronto para copiar nas páginas do site. |
| 3 | 2026-11-13 | "não com o php por favor" | Os modais foram refeitos em HTML puro, sem PHP: os arquivos `.php` foram removidos e o conteúdo dinâmico virou texto de exemplo para ser trocado depois. |
| 4 | 2026-11-13 | "faça como components" | Cada modal virou um componente reutilizável (Web Component, `<edu-modal-…>`) em `modais/components/`, sem PHP e sem build. Dados como nome do aluno e destino do formulário entram por atributos da tag. As páginas HTML avulsas foram substituídas por um `index.html` de demonstração. |

## Estilo (CSS)

| # | Data | Prompt | Impacto no resultado final |
|---|------|--------|----------------------------|
| 1 | 2026-11-13 | "preciso que seja baseado nisso" (guia de estilos, mapa de telas, ícones e `PROMPTS.md` anexados) | Os modais passaram a usar as cores (#3a86eb, #e97a1b, #d3e3fd, #bdc1c6), a fonte Google Sans e os tamanhos de 12 px/14 px do guia, e os quatro modais do mapa de telas, com a logo do EduConnect. |
| 2 | 2026-11-13 | "iremos utilizar o bootstrap também" | Os modais foram refeitos com o componente Modal do Bootstrap 5.3. O guia de estilos entra por variáveis CSS (`--bs-primary` etc.), então o resto do site herda o mesmo tema. |
| 3 | 2026-11-13 | (mesmo prompt de Estrutura (HTML) #2) | O tema do guia de estilos e os estilos dos modais saíram da página de demonstração e foram para `modais/css/educonnect-modais.css`, que o site inteiro pode reaproveitar. |

## Interatividade (JS)

| # | Data | Prompt | Impacto no resultado final |
|---|------|--------|----------------------------|
| 1 | 2026-11-13 | (mesmo prompt de Estrutura (HTML) #1 e Estilo (CSS) #2) | Os modais trazem o botão "Criar" que só habilita com o nome da turma, a validação do código de 5 a 8 letras/números e a prévia da cor do cabeçalho. O envio ao PHP ainda precisa ser ligado. |
| 2 | 2026-11-13 | (mesmo prompt de Estrutura (HTML) #2) | A validação e a prévia dos modais ficaram em `modais/js/modais.js`, que só age nos modais presentes na página. O envio dos formulários fica a cargo do back-end; `modais/js/demo.js` só serve para testar e não deve ir ao site final. |
| 3 | 2026-11-13 | (mesmo prompt de Estrutura (HTML) #4) | `modais/js/modais.js` passou a usar delegação de eventos, então a validação e a prévia funcionam com os componentes em qualquer ordem de carregamento. |
| 2 | 2026-11-13 | (mesmo prompt de Estrutura (HTML) #1: revisão do login) | Inputs com `<label>` associado, `type="email"`, `autocomplete` e `required`. A mensagem de erro já está no HTML com `role="alert"`, então o leitor de tela anuncia quando o JS a mostrar. O logo tem `alt=""`, porque o nome já está no `h1`. |

## Documentação

| # | Data | Prompt | Impacto no resultado final |
|---|------|--------|----------------------------|
| 1 | 2026-11-13 | "Corrija o arquivo claude.md, separando a parte de prompts, a parte de prompts num arquivo .md separado, e uma declaração de uso de IA em um arquivo separado tbm .md." | O `CLAUDE.md` ficou só com as regras do projeto. O registro de prompts foi para `PROMPTS.md` e a declaração de uso de IA para `DECLARACAO_IA.md`, o que deixa a documentação de entrega mais organizada. |
