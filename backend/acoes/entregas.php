<?php
declare(strict_types=1);

use EduConnect\Database\Conexao;
use EduConnect\DAO\EntregaDAO;
use EduConnect\Models\Entrega;
use EduConnect\Models\Participacao;
use EduConnect\Models\Publicacao;

/** Modal "Enviar trabalho" (aluno): botão `acao` = entregar | cancelar. */
function acao_enviar_trabalho(): never {
    $u = exigir_login();
    $atividade = publicacao_ou_falha(inteiro('atividade_id', 'Atividade'), Publicacao::ATIVIDADE);
    $papel = exigir_membro($atividade->turmaId, $u);
    if ($papel !== Participacao::ALUNO) throw new Falha('Só alunos enviam trabalhos.', 403);
    $acao = (string) ($_POST['acao'] ?? 'entregar');
    $entregas = new EntregaDAO();
    $entrega = $entregas->buscarDoAluno($atividade->id, $u->id);
    if ($entrega?->foiCorrigida()) throw new Falha('Este trabalho já foi corrigido e não pode ser alterado.', 409);

    if ($acao === 'cancelar') {
        if (!$entrega || !$entrega->estaEntregue()) throw new Falha('Não há envio para cancelar.', 409);
        Conexao::transacao(function () use ($entregas, $entrega) {
            remover_anexos_da_entrega($entrega->id);
            $entrega->status = Entrega::CANCELADA;
            $entregas->atualizar($entrega);
            $entregas->registrarEvento($entrega->id, 'cancelado');
        });
        sucesso('Envio cancelado.');
    }
    if ($acao !== 'entregar') throw new Falha('Ação inválida.');

    $arquivos = arquivos_enviados('arquivos');
    if (!$arquivos) throw new Falha('Escolha ao menos um arquivo.');
    validar_arquivos($arquivos, config('max_arquivos_entrega'));
    $atrasada = $atividade->prazoVencido(agora());

    $entrega = Conexao::transacao(function () use ($entregas, $entrega, $atividade, $u, $atrasada, $arquivos) {
        if ($entrega) {
            $evento = $entrega->estaEntregue() ? 'reenviado' : 'enviado';
            remover_anexos_da_entrega($entrega->id);
            $entrega->status = Entrega::ENTREGUE;
            $entrega->atrasada = $atrasada;
            $entrega->enviadoEm = agora();
            $entregas->atualizar($entrega);
        } else {
            $evento = 'enviado';
            $entrega = new Entrega(atividadeId: $atividade->id, alunoId: $u->id, atrasada: $atrasada);
            $entregas->inserir($entrega);
        }
        $entregas->registrarEvento($entrega->id, $evento);
        guardar_anexos($arquivos, null, $entrega->id);
        return $entrega;
    });
    sucesso($atrasada ? 'Trabalho entregue com atraso.' : 'Trabalho entregue.', null, ['entrega_id' => $entrega->id, 'atrasada' => $atrasada]);
}

/** Modal "Entrega do aluno" (professor): nota de 0 a 100 ou vazio ("Sem nota"). */
function acao_salvar_nota(): never {
    $u = exigir_login();
    $entregas = new EntregaDAO();
    $entrega = $entregas->buscarPorId(inteiro('entrega_id', 'Entrega')) ?? throw new Falha('Entrega não encontrada.', 404);
    exigir_membro($entrega->turmaId, $u, true);
    if (!$entrega->estaEntregue()) throw new Falha('O aluno cancelou esta entrega.', 409);
    $bruta = trim((string) ($_POST['nota'] ?? ''));
    if ($bruta === '') {
        $entrega->nota = null;
    } elseif (ctype_digit($bruta) && (int) $bruta <= 100) {
        $entrega->nota = (int) $bruta;
    } else {
        throw new Falha('Use um número de 0 a 100.');
    }
    $entregas->atualizar($entrega);
    sucesso($entrega->nota === null ? 'Sem nota.' : 'Nota salva.', null, ['nota' => $entrega->nota]);
}
