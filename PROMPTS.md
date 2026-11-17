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
| 2 | 2026-11-13 | "Preciso que crie uns modelos de modal, para usarmos em uma cópia do google classroom" | Gerou os seis modelos de modal (criar turma, participar da turma, personalizar aparência, entrega do aluno, promover a professor e excluir atividade), que viraram componentes de diálogo do site. |
| 3 | 2026-11-13 | "voltando aos modais, crie em arquivos separados para usarmos depois" | Cada modal virou um arquivo próprio em `modais/` (formulário com `method="post"`), pronto para ser reaproveitado nas páginas do site. |
| 4 | 2026-11-13 | "não com o php por favor" | Os modais foram refeitos em HTML puro, sem PHP: os arquivos `.php` foram removidos e o conteúdo dinâmico virou texto de exemplo para ser trocado depois. |
| 5 | 2026-11-13 | "faça como components" | Cada modal virou um componente reutilizável (Web Component, `<edu-modal-…>`) em `modais/components/`, sem PHP e sem build. Dados como nome do aluno e destino do formulário entram por atributos da tag. As páginas HTML avulsas foram substituídas por um `index.html` de demonstração. |
| 6 | 2026-11-13 | "recrie os modals, preciso que seja responsivo para celular também, preciso que siga tudo que está no arquivo que te enviei" (`Google_Classroom_remodelado.drawio` anexado) | Os modais foram refeitos a partir dos wireframes do mapa de telas: Criar turma (Nome, Níveis, Matéria), Participar da turma (código de 5 a 8 caracteres), Personalizar aparência (upload de foto) e Entrega do aluno (nota, arquivo, histórico e comentários particulares). |
| 7 | 2026-11-13 | "parece estar faltando coisa" / "antes de pedir para refazer os modais parecia ter mais opções de modais" | Voltaram três modais extras como componentes, marcados como "extras" por não estarem nos wireframes: Promover a professor, Excluir atividade e Convidar alunos. Agora são sete modais no total. |
| 8 | 2026-11-13 | "ajuste os li que eu esqueci de colocar" | Colocou o botão do menu, o logo e o `h1` da nav do `layout.html` dentro de `<li>`, o que deixou a `ul` com HTML válido. Os `li` do botão e do logo ganharam `d-flex` para não perder a centralização vertical. |

## Estilo (CSS)

| # | Data | Prompt | Impacto no resultado final |
|---|------|--------|----------------------------|
| 1 | 2026-11-13 | "Revisa o codigo do style das paginas no cadastro.style, verifique se condiz com mapa de telas do projeto, nas telas de login e cadastro." | Revisão do `style/cadastro.css` contra o mapa de telas (`Google_Classroom_redesenhado.drawio`). Apontou diferenças com o wireframe e problemas de organização. |
| 2 | 2026-11-13 | "Mobile nao precisa aplicar. O restante das irregularidades podem ser aplicadas" | CSS renomeado para `style/acesso.css`, com nomes neutros servindo login e cadastro. O rodapé, campos obrigatórios, inputs, mensagens de erro, foco e preenchimento de senha foram ajustados conforme o wireframe. |
| 3 | 2026-11-13 | "preciso que seja baseado nisso" (guia de estilos, mapa de telas, ícones e `PROMPTS.md` anexados) | Os modais passaram a usar as cores, a fonte e os tamanhos do guia, com a logo do EduConnect. |
| 4 | 2026-11-13 | "iremos utilizar o bootstrap também" | Os modais foram refeitos com o componente Modal do Bootstrap 5.3, usando variáveis CSS para compartilhar o tema. |
| 5 | 2026-11-13 | (mesmo prompt de Estrutura (HTML) #3: arquivos separados) | O tema e os estilos dos modais foram para `modais/css/educonnect-modais.css`, que o site inteiro pode reaproveitar. |
| 6 | 2026-11-13 | (mesmo prompt de Estrutura (HTML) #6: recriar os modais) | Layout responsivo conforme os wireframes de 1280 px e 360 px. |
| 7 | 2026-11-13 | "como faz um botao sem estilo no bootstrap" | Explicou as opções do Bootstrap para botões sem aparência padrão, mantendo as ações em `<button>`. |
| 8 | 2026-11-13 | "como centralizar verticalmente os elementos dessa nav?" / "o texto tem que estar em vertical align também" | Orientou o uso de `align-items-center`, `mb-0` e `d-flex` para alinhar verticalmente texto, logo e ícone da nav. |
| 9 | 2026-11-13 | "como faz gap no flex?" | Explicou as classes `gap-*` do Bootstrap e a propriedade `gap` do CSS para espaçar os itens da nav. |
| 10 | 2026-11-13 | "preciso que o perfil fique quadrado" / "agora como faz pra que isso fique na direita" | O avatar recebeu dimensões e centralização consistentes, e `ms-auto` o levou para a ponta direita da nav. |
| 11 | 2026-11-17 | "Estou terminando a estrutura do layout das paginas, a nav e a side bar, mas nao consegui adpartar algumas partes e deixar tao responsivo, veja o mapa de paginas aqui [...] Google_Classroom_redesenhado.drawio, e me ajude a ajustar, a ideia é esse layout principal ser reutilizado nas outas paginas, para demorar menos." | O layout ganhou menu lateral responsivo, trilha no cabeçalho e rodapé, e JavaScript compartilhado entre as páginas. |
| 12 | 2026-11-17 | "estou corrigindo o posicionamento de uma barra no mural e no mobile o espaçamento fica desconfigurado, analisei e pode ser um problema no grid, faz essa conferencia e procure uma possivel solução, as alterações que fiz foi alterar a posição das abas, coloquei para cima, verifique se essas alterações também estão de acordo e nao desconfiguram nada importante." | Encontrou a causa: as abas viraram filhas diretas do grid `.estrutura-aplicacao`, cuja 1ª linha era `1fr`, e por isso esticavam e abriam um espaço vazio acima do conteúdo. As linhas passaram para `auto 1fr` e o `max-height` que tentava contornar isso saiu do `mural.css`. As abas no topo continuam de acordo com o mapa de telas, e o `layout.html` sem abas não foi afetado. |
| 13 | 2026-11-17 | "outra pequena alteração que reparei, tanto a top bar, quanto a barra de abas nao vão até o final da tela" | Encontrou a causa no reboot do Bootstrap 6, que aplica `scrollbar-gutter: stable` no `:root` e reserva cerca de 15px para a barra de rolagem mesmo quando a página não rola. O `layout.css` passou a usar `scrollbar-gutter: auto`, e a barra superior e as abas agora vão até a borda da tela. |

## Interatividade (JS)

| # | Data | Prompt | Impacto no resultado final |
|---|------|--------|----------------------------|
| 1 | 2026-11-13 | (mesmo prompt de Estrutura (HTML) #2 e Estilo (CSS) #4) | Os modais trazem validação do nome e do código e a prévia da cor do cabeçalho. |
| 2 | 2026-11-13 | (mesmo prompt de Estrutura (HTML) #1: revisão do login) | Inputs com `<label>` associado, `type="email"`, `autocomplete` e `required`; a mensagem de erro usa `role="alert"`. |
| 3 | 2026-11-13 | (mesmo prompt de Estrutura (HTML) #5: components) | A validação passou a usar delegação de eventos, funcionando com os componentes em qualquer ordem de carregamento. |
| 4 | 2026-11-13 | (mesmo prompt de Estrutura (HTML) #6: recriar os modais) | Cada componente traz a lógica descrita nas notas do mapa, incluindo validação de arquivo, nota e comentários particulares. |

## Acessibilidade

| # | Data | Prompt | Impacto no resultado final |
|---|------|--------|----------------------------|
| 1 | 2026-11-13 | (mesmo prompt de Configuração/Regras #1: tags semânticas e `alt`) | Leitores de tela e SEO passam a funcionar desde o início, porque as páginas já nascem com landmarks e textos alternativos. |
| 2 | 2026-11-13 | "leia o claude.md e siga as instruções dos arquivos .md" | Os modais foram revisados com as regras do `CLAUDE.md`, títulos em ordem e nenhuma `<img>` sem `alt`. |

## Correção de bugs

| # | Data | Prompt | Impacto no resultado final |
|---|------|--------|----------------------------|
| 1 | 2026-11-13 | "o que faço" (captura de tela do erro "you need to resolve your current index first") + "faça para mim, só não faça o commit" | Conflito de merge resolvido e o registro de prompts consolidado, mantendo as entradas dos colegas. |

## Documentação

| # | Data | Prompt | Impacto no resultado final |
|---|------|--------|----------------------------|
| 1 | 2026-11-13 | "Corrija o arquivo claude.md, separando a parte de prompts, a parte de prompts num arquivo .md separado, e uma declaração de uso de IA em um arquivo separado tbm .md." | O `CLAUDE.md` ficou só com as regras do projeto. O registro de prompts foi para `PROMPTS.md` e a declaração de uso de IA para `DECLARACAO_IA.md`. |
