<?php
// Roteador para o servidor embutido do PHP (que não lê .htaccess). Uso, na pasta do projeto:
//   php -S localhost:8000 backend/servidor-dev.php
// Bloqueia o acesso direto a config, lib, acoes e storage (banco e uploads); o resto segue normal.
$caminho = rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/');
$caminho = preg_replace('#/+#', '/', $caminho);
$bloqueado = str_contains($caminho, '..')
    || preg_match('#/backend/(?!index\.php($|/)|servidor-dev\.php$|public/).+#', $caminho);
if ($bloqueado) {
    http_response_code(404);
    exit('Não encontrado.');
}
return false; // o servidor entrega o arquivo/rota normalmente
