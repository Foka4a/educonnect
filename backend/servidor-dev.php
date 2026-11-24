<?php
// Roteador para o servidor embutido do PHP (que não lê .htaccess). Uso, na pasta do projeto:
//   php -S localhost:8000 backend/servidor-dev.php
// Bloqueia o acesso direto ao back-end (config, lib, src, storage com banco e uploads) e às partes das páginas (frontend/partes);
// o resto segue normal (as páginas .php de frontend/pages rodam pelo PHP).
$caminho = rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/');
$caminho = preg_replace('#/+#', '/', $caminho);
$bloqueado = str_contains($caminho, '..')
    || str_contains($caminho, '/backend/')
    || str_contains($caminho, '/frontend/partes/');
if ($bloqueado) {
    http_response_code(404);
    exit('Não encontrado.');
}
return false; // o servidor entrega o arquivo/rota normalmente
