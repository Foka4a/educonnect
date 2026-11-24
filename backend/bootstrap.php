<?php
declare(strict_types=1);
// Carrega o back-end (classes e funções de apoio). Usado pelas páginas, via frontend/partes/pagina.php.
require __DIR__ . '/src/autoload.php';   // Database, Models, DAO, Services
require __DIR__ . '/lib/config.php';
require __DIR__ . '/lib/resposta.php';
require __DIR__ . '/lib/sessao.php';
require __DIR__ . '/lib/validacao.php';

date_default_timezone_set(config('fuso'));
header('X-Content-Type-Options: nosniff');
