# EduConnect

Clone educacional do Google Classroom, feito como trabalho acadêmico. É um site estático em HTML, CSS e JavaScript, sem back-end e sem vínculo com o Google. Todos os dados são fictícios.

**Acesse o site:** https://foka4a.github.io/educonnect/pages/index.html

## Integrantes

- Gabriel Somariva
- Gustavo Peretti
- João Raimundi

## O que tem no site

| Página | Arquivo | O que faz |
|--------|---------|-----------|
| Início | `pages/index.html` | Cards das turmas, menu "+" para criar ou participar de uma turma e troca de tema claro/escuro. |
| Login e cadastro | `pages/login.html`, `pages/cadastro.html` | Telas de acesso (só visuais, sem autenticação real). |
| Mural | `pages/mural.html` | Banner da turma, código da turma, próximas atividades, novo aviso e publicações. |
| Atividades | `pages/atividades.html` | Lista de atividades e materiais, com as subpáginas "Criar atividade" e "Criar material". |
| Pessoas | `pages/pessoas.html` | Professores e estudantes da turma, com remoção de estudante. |
| Instruções | `pages/instrucoes.html` | Detalhes de uma atividade. |
| Trabalhos dos estudantes | `pages/trabalho-estudantes.html` | Entregas dos alunos para o professor avaliar. |

Os modais (criar turma, participar da turma, configurações da turma, personalizar aparência etc.) são Web Components em `modais/components/`. A página `modais/index.html` mostra todos eles.

## Como usar

1. Abra o [link do site](https://foka4a.github.io/educonnect/pages/index.html). Ele cai na página inicial com as turmas.
2. Clique em um card para entrar na turma. As abas **Mural**, **Atividades** e **Pessoas** ficam no topo da turma.
3. Use o **+** da barra superior para criar uma turma ou participar de uma (código de 5 a 8 letras ou números).
4. No Mural, o **lápis** personaliza o banner (foto ou cor), o **ⓘ** mostra as informações da turma e a **engrenagem** abre as configurações.
5. Em Atividades, o **+ Criar** abre as telas de nova atividade ou novo material. O menu **⋮** de cada item tem editar e excluir.

Como não há servidor, o que você salva (tema, configurações da turma, aparência, avisos e código da turma) fica guardado só no seu navegador, pelo `localStorage`. Para apagar tudo, limpe os dados do site no navegador.

## Rodar localmente

```bash
git clone https://github.com/Foka4a/educonnect.git
```

Depois abra `pages/index.html` no navegador. Não precisa instalar nada. Se algum modal não abrir direto do arquivo, sirva a pasta com um servidor simples, por exemplo a extensão Live Server do VS Code ou `python -m http.server`, e acesse `http://localhost:8000/pages/index.html`.

## Estrutura

```
pages/     páginas do site
style/     CSS de cada página (layout.css é o compartilhado)
scripts/   JavaScript de cada página (layout.js e tema.js são compartilhados)
modais/    modais como Web Components
img/       imagens e ícone
```

## Uso de IA

O trabalho usou IA como assistente. A declaração está em [`DECLARACAO_IA.md`](DECLARACAO_IA.md) e todos os prompts, com o impacto de cada um, estão em [`PROMPTS.md`](PROMPTS.md).
