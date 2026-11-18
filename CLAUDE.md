# EduConnect — Instruções do projeto

## Regras de HTML (obrigatórias)

1. **Tags semânticas.** Use `header`, `nav`, `main`, `section`, `article`, `aside`, `footer`, `figure`/`figcaption`, `h1`–`h6` em ordem hierárquica, `button` para ações e `a` para navegação. `div`/`span` só quando nenhuma tag semântica servir.
2. **`alt` em todas as imagens.** Toda `<img>` tem `alt`:
   - imagem informativa → descreve o conteúdo/função (`alt="Aluna estudando no notebook"`);
   - imagem decorativa → `alt=""` (vazio, mas presente).
   - Ícone dentro de link/botão sem texto → `alt` diz a ação (`alt="Abrir menu"`).

Checagem rápida antes de entregar: `grep -n "<img" -r . | grep -v "alt="` deve retornar vazio.

## Registro de prompts e uso de IA

> **⚠️ OBRIGATÓRIO — NÃO ESQUECER:** ao terminar de atender **cada** prompt do usuário (pergunta, ajuste ou explicação, por menor que seja), registre-o em [`PROMPTS.md`](PROMPTS.md) **na mesma resposta**, sem esperar o usuário pedir. Nenhum prompt do trabalho pode ficar sem registro.

- Todo prompt do trabalho entra em [`PROMPTS.md`](PROMPTS.md), na categoria certa, com o texto do prompt entre aspas, a data e o impacto no resultado final.
- Prompts de dúvida/explicação também entram (o impacto é o que foi explicado ou orientado).
- Só fica de fora o pedido de registrar os próprios prompts ("registre os prompts").
- A declaração de uso de IA fica em [`DECLARACAO_IA.md`](DECLARACAO_IA.md). Mantenha-a coerente com o registro de prompts.
- Antes de qualquer alteração nos arquivos [`PROMPTS.md`](PROMPTS.md) e [`DECLARACAO_IA.md`](DECLARACAO_IA.md) verifique possiveis conflitos com outras branches, para evitar problemas no marge.