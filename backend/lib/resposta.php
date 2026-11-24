<?php
declare(strict_types=1);

/** Erro que vira mensagem para o usuário (status HTTP + texto). */
class Falha extends Exception {
    public function __construct(string $mensagem, public int $status = 400) {
        parent::__construct($mensagem);
    }
}

/** True quando a chamada veio de fetch() (nota e comentário em "Trabalhos dos estudantes"): a resposta é JSON em vez de redirecionamento. */
function eh_fetch(): bool {
    return ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'fetch';
}

/** A própria página que recebeu o formulário (os formulários postam para ela mesma), sem os avisos antigos. */
function pagina_de_origem(): string {
    $p = parse_url($_SERVER['REQUEST_URI'] ?? '/') ?: [];
    parse_str($p['query'] ?? '', $q);
    unset($q['msg'], $q['erro']);
    return ($p['path'] ?? '/') . ($q ? '?' . http_build_query($q) : '');
}

function com_aviso(string $url, string $chave, string $texto): string {
    return $url . (str_contains($url, '?') ? '&' : '?') . $chave . '=' . rawurlencode($texto);
}

function json_resposta(array $dados, int $status = 200): never {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($dados, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/** Deu certo: JSON para fetch(), redirecionamento com ?msg= para formulários. */
function sucesso(string $mensagem, ?string $destino = null, array $dados = []): never {
    if (eh_fetch()) json_resposta(['ok' => true, 'mensagem' => $mensagem] + $dados);
    header('Location: ' . com_aviso($destino ?? pagina_de_origem(), 'msg', $mensagem), true, 303);
    exit;
}

function falhou(string $mensagem, int $status = 400): never {
    if (eh_fetch()) json_resposta(['ok' => false, 'erro' => $mensagem], $status);
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET') {   // arquivo.php/imagem-turma.php: status HTTP de verdade (serve para <img> e links)
        http_response_code($status);
        header('Content-Type: text/plain; charset=utf-8');
        echo $mensagem;
        exit;
    }
    header('Location: ' . com_aviso(pagina_de_origem(), 'erro', $mensagem), true, 303);
    exit;
}
