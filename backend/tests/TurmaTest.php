<?php
// TurmaService e PessoaService: criar, entrar pelo código, permissões de professor/administrador, pessoas.

use EduConnect\DAO\ConviteDAO;
use EduConnect\Models\Participacao;
use EduConnect\Services\PessoaService;
use EduConnect\Services\TurmaService;

teste('criar turma: quem cria vira administrador e professor; código de 8 letras/números', function () {
    $prof = novo_usuario();
    $t = (new TurmaService())->criar($prof, 'História', '', '');
    verdade((bool) preg_match('/^[a-z0-9]{8}$/', $t->codigo), "código $t->codigo");
    $aberta = (new TurmaService())->abrir($prof, $t->id);
    verdade($aberta->ehProfessor() && $aberta->ehAdministrador($prof), 'professor e administrador');
});

teste('participar: entra como aluno, aceita código em maiúsculas', function () {
    [, $aluno, $t] = turma_com_aluno();
    igual(Participacao::ALUNO, (new TurmaService())->abrir($aluno, $t->id)->papel);
    $outro = novo_usuario();
    (new TurmaService())->participar($outro, '  ' . strtoupper($t->codigo) . ' ');
    igual(Participacao::ALUNO, (new TurmaService())->abrir($outro, $t->id)->papel);
});

teste('participar: código mal formatado 400, inexistente 404, já participa 409', function () {
    [, $aluno, $t] = turma_com_aluno();
    $s = new TurmaService();
    foreach (['abc', 'abcdefghi', 'abc-12', ''] as $ruim) recusa(400, fn () => $s->participar($aluno, $ruim));
    recusa(404, fn () => $s->participar($aluno, 'zzzzzzz'));
    recusa(409, fn () => $s->participar($aluno, $t->codigo));
});

teste('abrir/listar: quem não participa não vê a turma', function () {
    [, , $t] = turma_com_aluno();
    $estranho = novo_usuario();
    recusa(403, fn () => (new TurmaService())->abrir($estranho, $t->id));
    recusa(404, fn () => (new TurmaService())->abrir($estranho, 999999));
    igual([], (new TurmaService())->listarDoUsuario($estranho));
});

teste('editar turma: só professor; campos opcionais null ficam como estão', function () {
    [$prof, $aluno, $t] = turma_com_aluno();
    $s = new TurmaService();
    $s->editar($prof, $t->id, 'Mat', 'A', 'B', 'Descrição', 'Seção 1', 'Sala 9');
    $e = $s->editar($prof, $t->id, 'Mat 2', 'A', 'B');
    igual(['Mat 2', 'Descrição', 'Seção 1', 'Sala 9'], [$e->nome, $e->descricao, $e->secao, $e->sala]);
    recusa(403, fn () => $s->editar($aluno, $t->id, 'Hack', '', ''));
});

teste('sair: aluno sai; administrador não pode sair', function () {
    [$prof, $aluno, $t] = turma_com_aluno();
    $s = new TurmaService();
    $s->sair($aluno, $t->id);
    recusa(403, fn () => $s->abrir($aluno, $t->id));
    recusa(409, fn () => $s->sair($prof, $t->id));
});

teste('excluir turma: só o administrador (nem outro professor)', function () {
    [$prof, $aluno, $t] = turma_com_aluno();
    $prof2 = novo_usuario();
    $s = new TurmaService();
    $s->participar($prof2, $t->codigo);
    (new PessoaService())->promover($prof, $t->id, $prof2->id);
    recusa(403, fn () => $s->excluir($aluno, $t->id));
    recusa(403, fn () => $s->excluir($prof2, $t->id));
    $s->excluir($prof, $t->id);
    recusa(404, fn () => $s->abrir($prof, $t->id));
});

teste('redefinir código: o antigo deixa de valer; aluno não redefine', function () {
    [$prof, $aluno, $t] = turma_com_aluno();
    $s = new TurmaService();
    $novo = $s->redefinirCodigo($prof, $t->id);
    verdade($novo !== $t->codigo, 'código mudou');
    recusa(404, fn () => $s->participar(novo_usuario(), $t->codigo));
    $s->participar(novo_usuario(), $novo);
    recusa(403, fn () => $s->redefinirCodigo($aluno, $t->id));
});

teste('convidar: separa por vírgula/;/espaço, ignora repetidos, recusa inválido', function () {
    [$prof, $aluno, $t] = turma_com_aluno();
    $s = new TurmaService();
    igual(2, $s->convidar($prof, $t->id, 'a@x.com; B@x.com  a@x.com'));
    igual(1, $s->convidar($prof, $t->id, 'a@x.com, c@x.com'), 'a@x.com já convidado');
    verdade((new ConviteDAO())->existe($t->id, 'b@x.com'), 'grava em minúsculas');
    recusa(400, fn () => $s->convidar($prof, $t->id, ' , ;'), 'ao menos um');
    recusa(400, fn () => $s->convidar($prof, $t->id, 'ok@x.com, ruim@'), 'E-mail inválido');
    recusa(400, fn () => $s->convidar($prof, $t->id, implode(',', array_map(fn ($i) => "p$i@x.com", range(1, 51)))), 'no máximo 50');
    recusa(403, fn () => $s->convidar($aluno, $t->id, 'd@x.com'));
});

teste('personalizar aparência: só cores da lista; nada escolhido é erro; aluno não pode', function () {
    [$prof, $aluno, $t] = turma_com_aluno();
    $s = new TurmaService();
    igual('#3a86eb', $s->personalizarAparencia($prof, $t->id, null, '#3a86eb')->cor);
    igual('', $s->personalizarAparencia($prof, $t->id, null, '')->cor, 'volta ao padrão');
    recusa(400, fn () => $s->personalizarAparencia($prof, $t->id, null, '#000000'), 'Cor inválida');
    recusa(400, fn () => $s->personalizarAparencia($prof, $t->id, null), 'Escolha uma imagem ou uma cor');
    recusa(403, fn () => $s->personalizarAparencia($aluno, $t->id, null, '#3a86eb'));
});

teste('imagem da turma: sem imagem é 404; quem não participa é 403', function () {
    [$prof, , $t] = turma_com_aluno();
    recusa(404, fn () => (new TurmaService())->arquivoDaImagem($prof, $t->id));
    recusa(403, fn () => (new TurmaService())->arquivoDaImagem(novo_usuario(), $t->id));
});

teste('pessoas: lista só para membros, professores primeiro', function () {
    [$prof, $aluno, $t] = turma_com_aluno();
    $lista = (new PessoaService())->listar($aluno, $t->id);
    igual([$prof->id, $aluno->id], array_map(fn ($p) => $p->usuarioId, $lista));
    recusa(403, fn () => (new PessoaService())->listar(novo_usuario(), $t->id));
});

teste('promover: só o administrador; quem não participa 404; já professor 409', function () {
    [$prof, $aluno, $t] = turma_com_aluno();
    $p = new PessoaService();
    recusa(403, fn () => $p->promover($aluno, $t->id, $aluno->id));
    recusa(404, fn () => $p->promover($prof, $t->id, novo_usuario()->id));
    $p->promover($prof, $t->id, $aluno->id);
    igual(Participacao::PROFESSOR, (new TurmaService())->abrir($aluno, $t->id)->papel);
    recusa(409, fn () => $p->promover($prof, $t->id, $aluno->id));
});

teste('remover: professor remove aluno; professor só o administrador remove; ninguém remove o administrador', function () {
    [$prof, $aluno, $t] = turma_com_aluno();
    $prof2 = novo_usuario();
    $aluno2 = novo_usuario();
    (new TurmaService())->participar($prof2, $t->codigo);
    (new TurmaService())->participar($aluno2, $t->codigo);
    $p = new PessoaService();
    $p->promover($prof, $t->id, $prof2->id);

    recusa(403, fn () => $p->remover($aluno2, $t->id, $aluno->id), 'Só professores');
    recusa(403, fn () => $p->remover($prof2, $t->id, $prof->id), 'administrador não pode');
    recusa(409, fn () => $p->remover($prof2, $t->id, $prof2->id), 'Sair da turma');
    $p->remover($prof2, $t->id, $aluno->id);
    recusa(403, fn () => (new TurmaService())->abrir($aluno, $t->id));
    $p->promover($prof, $t->id, $aluno2->id);
    recusa(403, fn () => $p->remover($prof2, $t->id, $aluno2->id), 'administrador');
    $p->remover($prof, $t->id, $aluno2->id);
    recusa(404, fn () => $p->remover($prof, $t->id, $aluno2->id));
});
