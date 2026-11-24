<?php
/*
 * Fecha a .estrutura-aplicacao aberta em partes/topo.php e põe o que é comum a todas as páginas:
 * aviso (mostra ?msg=/?erro= vindos do back-end), modais Criar/Participar e, nas páginas de turma,
 * Configurações/Informações da turma (precisa de ../modais/components/configuracoes-turma.js no <head>).
 */
$turma ??= null;
?>
    </div>

    <p class="aviso" role="status" data-aviso></p>
    <edu-modal-criar-turma></edu-modal-criar-turma>
    <edu-modal-participar-turma></edu-modal-participar-turma>
    <?php if ($turma): ?>
    <edu-modal-configuracoes-turma turma-id="<?= $turma->id ?>" nome="<?= e($turma->nome) ?>" descricao="<?= e($turma->descricao) ?>"
        secao="<?= e($turma->secao) ?>" niveis="<?= e($turma->niveis) ?>" materia="<?= e($turma->materia) ?>" sala="<?= e($turma->sala) ?>"
        <?php if ($turma->ehProfessor()): ?>codigo="<?= e($turma->codigo) ?>" pode-editar<?php endif; ?>></edu-modal-configuracoes-turma>
    <?php endif; ?>
</body>

</html>
