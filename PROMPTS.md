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
| 1 | 2026-11-13 | "ajuste os li que eu esqueci de colocar" | Colocou o botão do menu, o logo e o `h1` da nav do `layout.html` dentro de `<li>`, o que deixou a `ul` com HTML válido. Os `li` do botão e do logo ganharam `d-flex` para não perder a centralização vertical. |

## Estilo (CSS)

| # | Data | Prompt | Impacto no resultado final |
|---|------|--------|----------------------------|
| 1 | 2026-11-13 | "como faz um botao sem estilo no bootstrap" | Explicou as opções do Bootstrap para botões sem aparência padrão (`btn`, `btn-link` e utilitários como `bg-transparent border-0 p-0`). Assim as ações continuam em `<button>`, sem trocar por `div`/`span` só por causa do visual. |
| 2 | 2026-11-13 | "como centralizar verticalmente os elementos dessa nav?" | Orientou o uso de `align-items-center` e `mb-0` no `h1` da nav do `layout.html`. Também apontou que `button`, `img` e `h1` não podem ficar soltos dentro de `ul`, o que leva a uma estrutura de cabeçalho semântica. |
| 3 | 2026-11-13 | "o texto tem que estar em vertical align também" | Adicionou `mb-0` ao `h1` (a margem inferior padrão do Bootstrap empurrava o texto para cima) e `d-flex` ao botão do menu, para o ícone também ficar centralizado. Agora texto, logo e ícone ficam alinhados no centro da nav. |
| 4 | 2026-11-13 | "como faz gap no flex?" | Explicou as classes `gap-*` do Bootstrap e a propriedade `gap` do CSS para espaçar os itens da nav sem margens individuais. |
| 5 | 2026-11-13 | "preciso que o perfil fique quadrado" | Na classe `.perfil` do `geral.css`, trocou o `padding` e o `vertical-align` por `display: inline-flex` com centralização. Assim o `width`/`height` de 36px passa a valer e o avatar com as iniciais fica um quadrado de 36×36 com o texto no centro. |
| 6 | 2026-11-13 | "agora como faz pra que isso fique na direita" | Adicionou `ms-auto` ao `li` do perfil no `layout.html`. A margem esquerda automática ocupa o espaço livre e leva o avatar para a ponta direita da nav, enquanto menu, logo e título ficam à esquerda. |
| 7 | 2026-11-17 | "Estou terminando a estrutura do layout das paginas, a nav e a side bar, mas nao consegui adpartar algumas partes e deixar tao responsivo, veja o mapa de paginas aqui [...] Google_Classroom_redesenhado.drawio, e me ajude a ajustar, a ideia é esse layout principal ser reutilizado nas outas paginas, para demorar menos." | Seguindo o mapa de telas, o `layout.css` passou a ter menu lateral fixo na rolagem, que no desktop recolhe para só ícones e no celular vira gaveta com fundo escurecido (fecha ao clicar fora ou com Esc). Também ganhou trilha "EduConnect › turma" no cabeçalho e rodapé. O JS do menu foi para `scripts/layout.js`, compartilhado entre as páginas. O `mural.html` passou a usar o layout, e o `mural.css` ficou só com o estilo do conteúdo do mural. |

## Acessibilidade

| # | Data | Prompt | Impacto no resultado final |
|---|------|--------|----------------------------|
| 1 | 2026-11-13 | (mesmo prompt de Configuração/Regras #1: tags semânticas e `alt`) | Leitores de tela e SEO passam a funcionar desde o início, porque as páginas já nascem com landmarks e textos alternativos. |

## Documentação

| # | Data | Prompt | Impacto no resultado final |
|---|------|--------|----------------------------|
| 1 | 2026-11-13 | "Corrija o arquivo claude.md, separando a parte de prompts, a parte de prompts num arquivo .md separado, e uma declaração de uso de IA em um arquivo separado tbm .md." | O `CLAUDE.md` ficou só com as regras do projeto. O registro de prompts foi para `PROMPTS.md` e a declaração de uso de IA para `DECLARACAO_IA.md`, o que deixa a documentação de entrega mais organizada. |
