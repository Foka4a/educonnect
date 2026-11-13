# Registro de prompts — EduConnect

Todo prompt do trabalho entra na tabela da categoria certa, com o impacto no resultado final.
Categorias: **Configuração/Regras**, **Estrutura (HTML)**, **Estilo (CSS)**, **Interatividade (JS)**, **Acessibilidade**, **Conteúdo**, **Correção de bugs**, **Documentação**.

## Configuração/Regras

| # | Data | Prompt | Impacto no resultado final |
|---|------|--------|----------------------------|
| 1 | 2026-11-13 | "Use tags semanticas, e adicione alt em todas as imagens, registre isso num claude.md. Registre todos os prompts do trabalho categorizados por categoria junto com o impacto no resultado final do trabalho." | Criou o `CLAUDE.md` com as regras de HTML semântico e `alt` obrigatório, que valem para todo o código do projeto. Também criou o registro de prompts. |

## Acessibilidade

| # | Data | Prompt | Impacto no resultado final |
|---|------|--------|----------------------------|
| 1 | 2026-11-13 | (mesmo prompt de Configuração/Regras #1: tags semânticas e `alt`) | Leitores de tela e SEO passam a funcionar desde o início, porque as páginas já nascem com landmarks e textos alternativos. |
| 2 | 2026-11-13 | "leia o claude.md e siga as instruções dos arquivos .md" | Os modais foram revisados com as regras do `CLAUDE.md`: `header`, `footer`, `section`, `ul`/`li` e `aside` no lugar de `div` quando há tag semântica, títulos em ordem (`h1`, `h2`) e nenhuma `<img>` sem `alt` (a logo é SVG inline com `aria-hidden`). |

## Estrutura (HTML)

| # | Data | Prompt | Impacto no resultado final |
|---|------|--------|----------------------------|
| 1 | 2026-11-13 | "Preciso que crie uns modelos de modal, para usarmos em uma cópia do google classroom" | Gerou os seis modelos de modal (criar turma, participar da turma, personalizar aparência, entrega do aluno, promover a professor e excluir atividade), que viram os componentes de diálogo do site. |

## Estilo (CSS)

| # | Data | Prompt | Impacto no resultado final |
|---|------|--------|----------------------------|
| 1 | 2026-11-13 | "preciso que seja baseado nisso" (guia de estilos, mapa de telas, ícones e `PROMPTS.md` anexados) | Os modais passaram a usar as cores (#3a86eb, #e97a1b, #d3e3fd, #bdc1c6), a fonte Google Sans e os tamanhos de 12 px/14 px do guia, e os quatro modais do mapa de telas, com a logo do EduConnect. |
| 2 | 2026-11-13 | "iremos utilizar o bootstrap também" | Os modais foram refeitos com o componente Modal do Bootstrap 5.3. O guia de estilos entra por variáveis CSS (`--bs-primary` etc.), então o resto do site herda o mesmo tema. |

## Interatividade (JS)

| # | Data | Prompt | Impacto no resultado final |
|---|------|--------|----------------------------|
| 1 | 2026-11-13 | (mesmo prompt de Estrutura (HTML) #1 e Estilo (CSS) #2) | Os modais trazem o botão "Criar" que só habilita com o nome da turma, a validação do código de 5 a 8 letras/números e a prévia da cor do cabeçalho. O envio ao PHP ainda precisa ser ligado. |

## Documentação

| # | Data | Prompt | Impacto no resultado final |
|---|------|--------|----------------------------|
| 1 | 2026-11-13 | "Corrija o arquivo claude.md, separando a parte de prompts, a parte de prompts num arquivo .md separado, e uma declaração de uso de IA em um arquivo separado tbm .md." | O `CLAUDE.md` ficou só com as regras do projeto. O registro de prompts foi para `PROMPTS.md` e a declaração de uso de IA para `DECLARACAO_IA.md`, o que deixa a documentação de entrega mais organizada. |
