<?php
// Foto do cabeçalho do mural: imagem-turma.php?id=<turma>. Só para quem participa da turma (TurmaService).
require __DIR__ . '/../partes/pagina.php';

use EduConnect\Services\TurmaService;

try {
    $arq = (new TurmaService())->arquivoDaImagem(exigir_login(), inteiro('id', 'Turma'));
} catch (Falha $f) {
    falhou($f->getMessage(), $f->status);
}
header('Content-Type: ' . (str_ends_with($arq, '.png') ? 'image/png' : 'image/jpeg'));
header('Cache-Control: private, max-age=300');
readfile($arq);
