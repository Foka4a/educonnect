<?php
// PublicacaoService (avisos, atividades, materiais), EntregaService (nota, cancelar, atrasos) e ComentarioService.
// Envio de arquivos fica de fora: move_uploaded_file só funciona numa requisição HTTP de verdade. As entregas são
// gravadas direto pelo EntregaDAO, como se o aluno já tivesse enviado.

use EduConnect\DAO\AnexoDAO;
use EduConnect\DAO\EntregaDAO;
use EduConnect\Models\Anexo;
use EduConnect\Models\Entrega;
use EduConnect\Models\Publicacao;
use EduConnect\Models\Usuario;
use EduConnect\Services\ComentarioService;
use EduConnect\Services\EntregaService;
use EduConnect\Services\PublicacaoService;

function nova_atividade(Usuario $prof, int $turmaId, ?string $prazo = null): Publicacao {
    return (new PublicacaoService())->criar($prof, Publicacao::ATIVIDADE, $turmaId, 'Lista 1', 'Faça', $prazo, []);
}

function entrega_enviada(Publicacao $atividade, Usuario $aluno, string $enviadoEm = '2026-01-10 10:00:00'): Entrega {
    $e = new Entrega(atividadeId: $atividade->id, alunoId: $aluno->id, enviadoEm: $enviadoEm);
    (new EntregaDAO())->inserir($e);
    return $e;
}

teste('aviso: professor publica; aluno não', function () {
    [$prof, $aluno, $t] = turma_com_aluno();
    $s = new PublicacaoService();
    $a = $s->criarAviso($prof, $t->id, '<b>Prova sexta</b>');
    igual('<b>Prova sexta</b>', $a->descricao, 'aviso é texto puro (escapado na tela)');
    recusa(403, fn () => $s->criarAviso($aluno, $t->id, 'oi'));
    recusa(404, fn () => $s->criarAviso($prof, 999999, 'oi'));
});

teste('atividade: descrição passa pela lista de tags; material perde o prazo', function () {
    [$prof, $aluno, $t] = turma_com_aluno();
    $s = new PublicacaoService();
    $a = $s->criar($prof, Publicacao::ATIVIDADE, $t->id, 'T', '<b>ok</b><script>x</script>', '2026-12-01 23:59:00', []);
    igual('<b>ok</b>', $a->descricao);
    igual('2026-12-01 23:59:00', $a->entregaEm);
    $m = $s->criar($prof, Publicacao::MATERIAL, $t->id, 'Apostila', '', '2026-12-01 23:59:00', []);
    igual(null, $m->entregaEm);
    recusa(403, fn () => $s->criar($aluno, Publicacao::ATIVIDADE, $t->id, 'T', '', null, []));
});

teste('listar: só os tipos pedidos, da mais nova para a mais antiga; só membros', function () {
    [$prof, $aluno, $t] = turma_com_aluno();
    $s = new PublicacaoService();
    $a1 = nova_atividade($prof, $t->id);
    $s->criarAviso($prof, $t->id, 'aviso');
    $a2 = nova_atividade($prof, $t->id);
    igual([$a2->id, $a1->id], array_map(fn ($p) => $p->id, $s->listar($aluno, $t->id, [Publicacao::ATIVIDADE])));
    igual(3, count($s->listar($aluno, $t->id, [Publicacao::ATIVIDADE, Publicacao::AVISO])));
    recusa(403, fn () => $s->listar(novo_usuario(), $t->id, [Publicacao::ATIVIDADE]));
});

teste('próximas atividades: só prazo futuro, a mais próxima primeiro, com limite', function () {
    [$prof, $aluno, $t] = turma_com_aluno();
    nova_atividade($prof, $t->id, '2000-01-01 00:00:00');
    nova_atividade($prof, $t->id);
    $longe = nova_atividade($prof, $t->id, '2099-12-31 23:59:00');
    $perto = nova_atividade($prof, $t->id, '2099-01-01 23:59:00');
    $ids = array_map(fn ($p) => $p->id, (new PublicacaoService())->proximasAtividades($aluno, $t->id));
    igual([$perto->id, $longe->id], $ids);
    igual(1, count((new PublicacaoService())->proximasAtividades($aluno, $t->id, 1)));
});

teste('abrir: traz o autor; tipo errado é 404; quem não participa 403', function () {
    [$prof, $aluno, $t] = turma_com_aluno();
    $a = nova_atividade($prof, $t->id);
    igual('Professora Ana', (new PublicacaoService())->abrir($aluno, $a->id)->autor);
    recusa(404, fn () => (new PublicacaoService())->abrir($aluno, $a->id, Publicacao::MATERIAL));
    recusa(403, fn () => (new PublicacaoService())->abrir(novo_usuario(), $a->id));
});

teste('editar: mudar o prazo recalcula os atrasos das entregas já enviadas', function () {
    [$prof, $aluno, $t] = turma_com_aluno();
    $a = nova_atividade($prof, $t->id, '2026-01-05 23:59:00');
    $e = entrega_enviada($a, $aluno, '2026-01-10 10:00:00');
    $s = new PublicacaoService();
    $dao = new EntregaDAO();

    $s->editar($prof, Publicacao::ATIVIDADE, $a->id, 'Lista 1', '', '2026-01-05 23:59:00', [], []);
    verdade($dao->buscarPorId($e->id)->atrasada, 'enviada depois do prazo = atrasada');
    $s->editar($prof, Publicacao::ATIVIDADE, $a->id, 'Lista 1', '', '2026-01-20 23:59:00', [], []);
    verdade(!$dao->buscarPorId($e->id)->atrasada, 'prazo adiado = em dia');
    $s->editar($prof, Publicacao::ATIVIDADE, $a->id, 'Lista 1', '', null, [], []);
    verdade(!$dao->buscarPorId($e->id)->atrasada, 'sem prazo = em dia');
    recusa(403, fn () => $s->editar($aluno, Publicacao::ATIVIDADE, $a->id, 'X', '', null, [], []));
    recusa(404, fn () => $s->editar($prof, Publicacao::MATERIAL, $a->id, 'X', '', null, [], []));
});

teste('excluir: só professor; entregas da atividade saem junto', function () {
    [$prof, $aluno, $t] = turma_com_aluno();
    $a = nova_atividade($prof, $t->id);
    $e = entrega_enviada($a, $aluno);
    $s = new PublicacaoService();
    recusa(403, fn () => $s->excluir($aluno, Publicacao::ATIVIDADE, $a->id));
    $s->excluir($prof, Publicacao::ATIVIDADE, $a->id);
    recusa(404, fn () => $s->abrir($prof, $a->id));
    igual(null, (new EntregaDAO())->buscarPorId($e->id), 'entrega excluída em cascata');
});

teste('download: anexo de entrega só para o aluno dono e os professores', function () {
    [$prof, $aluno, $t] = turma_com_aluno();
    $colega = novo_usuario();
    (new \EduConnect\Services\TurmaService())->participar($colega, $t->codigo);
    $e = entrega_enviada(nova_atividade($prof, $t->id), $aluno);
    $anexo = new Anexo(entregaId: $e->id, nome: 'trabalho.pdf', caminho: 'nao-existe-no-disco', tamanho: 10);
    (new AnexoDAO())->inserir($anexo);
    $s = new PublicacaoService();
    recusa(403, fn () => $s->anexoParaDownload($colega, $anexo->id), 'não pode ver');
    recusa(403, fn () => $s->anexoParaDownload(novo_usuario(), $anexo->id));
    recusa(404, fn () => $s->anexoParaDownload($aluno, $anexo->id), 'Arquivo não encontrado');   // permitido, mas sem arquivo no disco
    recusa(404, fn () => $s->anexoParaDownload($prof, $anexo->id), 'Arquivo não encontrado');
});

teste('nota: 0 a 100 ou vazio; só professor; entrega cancelada não recebe nota', function () {
    [$prof, $aluno, $t] = turma_com_aluno();
    $e = entrega_enviada(nova_atividade($prof, $t->id), $aluno);
    $s = new EntregaService();
    igual(0, $s->salvarNota($prof, $e->id, '0')->nota);
    igual(100, $s->salvarNota($prof, $e->id, ' 100 ')->nota);
    igual(null, $s->salvarNota($prof, $e->id, '')->nota);
    foreach (['101', '-1', '9.5', 'dez'] as $ruim) recusa(400, fn () => $s->salvarNota($prof, $e->id, $ruim), '0 a 100');
    recusa(403, fn () => $s->salvarNota($aluno, $e->id, '100'));
    recusa(404, fn () => $s->salvarNota($prof, 999999, '10'));
    $e->status = Entrega::CANCELADA;
    (new EntregaDAO())->atualizar($e);
    recusa(409, fn () => $s->salvarNota($prof, $e->id, '50'));
});

teste('cancelar envio: some da contagem; sem envio é 409; corrigido não muda', function () {
    [$prof, $aluno, $t] = turma_com_aluno();
    $a = nova_atividade($prof, $t->id);
    $s = new EntregaService();
    recusa(409, fn () => $s->cancelar($aluno, $a->id), 'Não há envio');
    $e = entrega_enviada($a, $aluno);
    igual([$a->id => 1], $s->contarEntregues($prof, $t->id));
    $s->cancelar($aluno, $a->id);
    igual(Entrega::CANCELADA, $s->doAluno($aluno, $a->id)['entrega']->status);
    igual([], $s->contarEntregues($prof, $t->id));
    recusa(409, fn () => $s->cancelar($aluno, $a->id), 'Não há envio');

    $a2 = nova_atividade($prof, $t->id);
    $e2 = entrega_enviada($a2, $aluno);
    $s->salvarNota($prof, $e2->id, '80');
    recusa(409, fn () => $s->cancelar($aluno, $a2->id), 'já foi corrigido');
    recusa(409, fn () => $s->entregar($aluno, $a2->id, []), 'já foi corrigido');
});

teste('entregar: só aluno; sem arquivo é recusado', function () {
    [$prof, $aluno, $t] = turma_com_aluno();
    $a = nova_atividade($prof, $t->id);
    $s = new EntregaService();
    recusa(403, fn () => $s->entregar($prof, $a->id, []), 'Só alunos');
    recusa(403, fn () => $s->entregar(novo_usuario(), $a->id, []));
    recusa(400, fn () => $s->entregar($aluno, $a->id, []), 'ao menos um arquivo');
    $m = (new PublicacaoService())->criar($prof, Publicacao::MATERIAL, $t->id, 'M', '', null, []);
    recusa(404, fn () => $s->entregar($aluno, $m->id, []));   // material não recebe entrega
});

teste('trabalhos dos estudantes: todos os alunos, com ou sem entrega; só professor', function () {
    [$prof, $aluno, $t] = turma_com_aluno();
    $sem = novo_usuario();
    (new \EduConnect\Services\TurmaService())->participar($sem, $t->codigo);
    $a = nova_atividade($prof, $t->id);
    $e = entrega_enviada($a, $aluno);
    $lista = (new EntregaService())->daAtividade($prof, $a->id);
    $porAluno = [];
    foreach ($lista as $l) $porAluno[$l['aluno']->usuarioId] = $l['entrega']?->id;
    igual([$aluno->id => $e->id, $sem->id => null], $porAluno, 'professor não aparece');
    recusa(403, fn () => (new EntregaService())->daAtividade($aluno, $a->id));
});

teste('comentário público: membros comentam e leem; quem não participa não', function () {
    [$prof, $aluno, $t] = turma_com_aluno();
    $a = nova_atividade($prof, $t->id);
    $s = new ComentarioService();
    $c = $s->comentar($aluno, 'Dúvida', $a->id, null, false, 999);
    igual(null, $c->alunoId, 'público não guarda aluno');
    igual(['Dúvida'], array_map(fn ($x) => $x->texto, $s->publicos($prof, $a->id)));
    recusa(403, fn () => $s->comentar(novo_usuario(), 'oi', $a->id, null, false, null));
    recusa(403, fn () => $s->publicos(novo_usuario(), $a->id));
});

teste('comentário particular: aluno só fala da própria entrega; colegas não leem', function () {
    [$prof, $aluno, $t] = turma_com_aluno();
    $colega = novo_usuario();
    (new \EduConnect\Services\TurmaService())->participar($colega, $t->codigo);
    $a = nova_atividade($prof, $t->id);
    $s = new ComentarioService();

    $c = $s->comentar($aluno, 'Particular', $a->id, null, true, $colega->id);
    igual($aluno->id, $c->alunoId, 'aluno não escolhe outro aluno');
    igual(['Particular'], array_map(fn ($x) => $x->texto, $s->meusParticulares($aluno, $a->id)));
    igual([], $s->meusParticulares($colega, $a->id));
    igual([], $s->publicos($colega, $a->id), 'particular não aparece como público');

    recusa(400, fn () => $s->comentar($prof, 'x', $a->id, null, true, null), 'Aluno inválido');
    recusa(404, fn () => $s->comentar($prof, 'x', $a->id, null, true, $prof->id), 'Aluno não encontrado');
    $m = (new PublicacaoService())->criar($prof, Publicacao::MATERIAL, $t->id, 'M', '', null, []);
    recusa(400, fn () => $s->comentar($aluno, 'x', $m->id, null, true, null), 'só existe em atividades');
});

teste('comentário pela entrega (modal do professor): vira particular com o dono da entrega', function () {
    [$prof, $aluno, $t] = turma_com_aluno();
    $a = nova_atividade($prof, $t->id);
    $e = entrega_enviada($a, $aluno);
    $c = (new ComentarioService())->comentar($prof, 'Muito bom', null, $e->id, false, null);
    verdade($c->particular, 'particular');
    igual($aluno->id, $c->alunoId);
    igual(['id', 'autor', 'texto', 'particular', 'hora'], array_keys($c->paraArray()), 'formato do JSON do modal');
    recusa(404, fn () => (new ComentarioService())->comentar($prof, 'x', null, 999999, false, null));
});

teste('editar comentário: só o autor; excluir: o autor ou um professor', function () {
    [$prof, $aluno, $t] = turma_com_aluno();
    $colega = novo_usuario();
    (new \EduConnect\Services\TurmaService())->participar($colega, $t->codigo);
    $a = nova_atividade($prof, $t->id);
    $s = new ComentarioService();
    $c = $s->comentar($aluno, 'Original', $a->id, null, false, null);

    igual('Editado', $s->editar($aluno, $c->id, 'Editado')->texto);
    recusa(403, fn () => $s->editar($prof, $c->id, 'x'), 'Só o autor');
    recusa(403, fn () => $s->excluir($colega, $c->id));
    $s->excluir($prof, $c->id);
    recusa(404, fn () => $s->editar($aluno, $c->id, 'x'));

    $c2 = $s->comentar($colega, 'Meu', $a->id, null, false, null);
    $s->excluir($colega, $c2->id);
    igual([], $s->publicos($prof, $a->id));
});
