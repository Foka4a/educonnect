<?php
declare(strict_types=1);

use EduConnect\DAO\UsuarioDAO;
use EduConnect\Models\Usuario;

function iniciar_sessao(): void {
    if (session_status() === PHP_SESSION_ACTIVE) return;
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax', 'secure' => !empty($_SERVER['HTTPS'])]);
    session_name('educonnect');
    session_start();
}

/** Proteção contra CSRF sem token: só aceita POST vindo do próprio site (cookie SameSite=Lax já ajuda). */
function conferir_origem(): void {
    $origem = $_SERVER['HTTP_ORIGIN'] ?? '';
    if ($origem !== '') {
        $p = parse_url($origem);
        $host = ($p['host'] ?? '') . (isset($p['port']) ? ':' . $p['port'] : '');
        if ($host !== ($_SERVER['HTTP_HOST'] ?? '')) throw new Falha('Origem não permitida.', 403);
    } elseif (!in_array($_SERVER['HTTP_SEC_FETCH_SITE'] ?? 'same-origin', ['same-origin', 'none'], true)) {
        throw new Falha('Origem não permitida.', 403);
    }
}

function usuario(): ?Usuario {
    static $cache = false;
    if ($cache !== false) return $cache;
    $id = $_SESSION['usuario_id'] ?? null;
    return $cache = $id ? (new UsuarioDAO())->buscarPorId((int) $id) : null;
}

function exigir_login(): Usuario {
    return usuario() ?? throw new Falha('Entre na sua conta para continuar.', 401);
}

function entrar_na_sessao(int $usuarioId): void {
    session_regenerate_id(true);
    $_SESSION['usuario_id'] = $usuarioId;
}
