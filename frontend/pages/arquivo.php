<?php
// Download de um anexo (atividade, material ou trabalho entregue): arquivo.php?id=<anexo>.
// Quem pode baixar é conferido no PublicacaoService (participante da turma; trabalho de aluno só o dono e os professores).
require __DIR__ . '/../partes/pagina.php';

use EduConnect\Services\PublicacaoService;

try {
    [$anexo, $arq] = (new PublicacaoService())->anexoParaDownload(exigir_login(), inteiro('id', 'Arquivo'));
} catch (Falha $f) {
    falhou($f->getMessage(), $f->status);
}
header('Content-Type: application/octet-stream');
header("Content-Disposition: attachment; filename*=UTF-8''" . rawurlencode($anexo->nome));
header('Content-Length: ' . filesize($arq));
readfile($arq);
