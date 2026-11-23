<?php
declare(strict_types=1);

function texto(string $campo, int $max, bool $obrigatorio = false, string $rotulo = ''): string {
    $v = trim((string) ($_POST[$campo] ?? ''));
    $v = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $v) ?? '';
    if ($obrigatorio && $v === '') throw new Falha(($rotulo ?: 'O campo') . ' é obrigatório.');
    if (mb_strlen($v) > $max) throw new Falha(($rotulo ?: 'O campo') . " deve ter no máximo $max caracteres.");
    return $v;
}

function inteiro(string $campo, string $rotulo = 'Identificador'): int {
    $v = $_POST[$campo] ?? $_GET[$campo] ?? '';
    if (!is_string($v) || !ctype_digit($v) || $v === '') throw new Falha("$rotulo inválido.");
    return (int) $v;
}

/** Texto do editor da descrição: só negrito, itálico, sublinhado, lista e quebra de linha, sem atributos. */
function limpar_html(string $html): string {
    $html = trim($html);
    if ($html === '') return '';
    $permitidas = ['b', 'strong', 'i', 'em', 'u', 'ul', 'li', 'br', 'div', 'p'];
    $origem = new DOMDocument();
    libxml_use_internal_errors(true);
    $origem->loadHTML('<?xml encoding="utf-8"?><body>' . $html . '</body>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();
    $corpo = $origem->getElementsByTagName('body')->item(0);
    if (!$corpo) return '';
    $saida = new DOMDocument();
    $raiz = $saida->createElement('body');
    $saida->appendChild($raiz);
    $copiar = function (DOMNode $de, DOMNode $para) use (&$copiar, $permitidas, $saida): void {
        foreach ($de->childNodes as $no) {
            if ($no instanceof DOMText) {
                $para->appendChild($saida->createTextNode($no->nodeValue));
            } elseif ($no instanceof DOMElement) {
                $tag = strtolower($no->tagName);
                if (in_array($tag, ['script', 'style'], true)) continue;
                if (in_array($tag, $permitidas, true)) {
                    $novo = $saida->createElement($tag);      // sem atributos: nada de style, onclick, href...
                    $para->appendChild($novo);
                    $copiar($no, $novo);
                } else {
                    $copiar($no, $para);                       // tag desconhecida: fica só o texto
                }
            }
        }
    };
    $copiar($corpo, $raiz);
    $resultado = '';
    foreach ($raiz->childNodes as $no) $resultado .= $saida->saveHTML($no);
    return trim($resultado);
}

/** Data de entrega vinda do modal (`entrega`) ou da subpágina (`prazo_tipo`, `prazo_data`, `prazo_hora`). */
function data_de_entrega(): ?string {
    $data = null;
    if (($_POST['prazo_tipo'] ?? '') === 'definir') {
        $data = trim((string) ($_POST['prazo_data'] ?? '')) . 'T' . (trim((string) ($_POST['prazo_hora'] ?? '')) ?: '23:59');
    } elseif (trim((string) ($_POST['entrega'] ?? '')) !== '') {
        $data = trim((string) $_POST['entrega']);
    } else {
        return null;
    }
    $dt = DateTime::createFromFormat('Y-m-d\TH:i', $data);
    if (!$dt || $dt->format('Y-m-d\TH:i') !== $data) throw new Falha('Data de entrega inválida.');
    return $dt->format('Y-m-d H:i:00');
}
