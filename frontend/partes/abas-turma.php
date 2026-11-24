<?php
/* Abas Mural / Atividades / Pessoas. Antes do include: $turma e $aba ('mural' | 'atividades' | 'pessoas'). Engrenagem só para professor. */
$abas = ['mural' => 'Mural', 'atividades' => 'Atividades', 'pessoas' => 'Pessoas'];
?>
        <nav class="abas-mural" aria-label="Seções da turma">
            <?php foreach ($abas as $arquivo => $rotulo): ?>
            <a class="aba<?= $aba === $arquivo ? ' aba--ativa' : '' ?>" href="<?= $arquivo ?>.php?turma=<?= $turma->id ?>"<?= $aba === $arquivo ? ' aria-current="page"' : '' ?>><?= $rotulo ?></a>
            <?php endforeach; ?>
            <?php if ($turma->ehProfessor()): ?>
            <button class="botao-icone abas-mural__config" type="button" data-abrir="modal-configuracoes-turma" aria-label="Configurações da turma" title="Configurações da turma">
                <span class="material-symbols-outlined" aria-hidden="true">settings</span>
            </button>
            <?php endif; ?>
        </nav>
