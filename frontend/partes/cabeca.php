<?php
/*
 * <head> comum das páginas internas. Antes do include: $titulo. Depois dele, a página acrescenta o CSS/JS dela e fecha o </head>.
 * Os modais Criar turma e Participar da turma (menu "+" do topo) estão em todas as páginas.
 */
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($titulo) ?></title>
    <script src="../scripts/tema.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@6.0.0-alpha.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-B/GM4XqrwHnWXNOWMbloTmrYXZg10cakYGmpfsR/bbzQ6JAJI4ihuyADKLnBgrCe" crossorigin="anonymous">
    <script type="module" src="https://cdn.jsdelivr.net/npm/bootstrap@6.0.0-alpha.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-1a/pXj49ZQ1aHEmrJ+gMw1otqoVsYwlEnlD8mIfY2TV03r20Y0CN7uqx1tQogjPL" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Google+Sans:wght@400;500;600&display=swap">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <link rel="stylesheet" href="../style/layout.css">
    <link rel="stylesheet" href="../modais/css/criar-turma.css">
    <link rel="stylesheet" href="../modais/css/participar-turma.css">
    <script src="../scripts/layout.js" defer></script>
    <script src="../scripts/backend.js" defer></script>
    <script src="../modais/components/criar-turma.js" defer></script>
    <script src="../modais/components/participar-turma.js" defer></script>
