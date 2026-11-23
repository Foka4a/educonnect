<?php
declare(strict_types=1);

function gerar_codigo(): string {
    $alfabeto = 'abcdefghijklmnopqrstuvwxyz0123456789';
    do {
        $codigo = '';
        for ($i = 0; $i < 8; $i++) $codigo .= $alfabeto[random_int(0, 35)];
    } while (um('SELECT id FROM turmas WHERE codigo = ?', [$codigo]));
    return $codigo;
}

function acao_criar_turma(): never {
    $u = exigir_login();
    $nome = texto('nome', 120, true, 'O nome da turma');
    $niveis = texto('niveis', 120);
    $materia = texto('materia', 120);
    $pdo = db();
    $pdo->beginTransaction();
    $id = executar('INSERT INTO turmas (nome, niveis, materia, codigo, criador_id, criado_em) VALUES (?,?,?,?,?,?)',
        [$nome, $niveis, $materia, gerar_codigo(), $u['id'], agora()]);
    executar('INSERT INTO participacoes (turma_id, usuario_id, papel, criado_em) VALUES (?,?,?,?)', [$id, $u['id'], 'professor', agora()]);
    $pdo->commit();
    sucesso('Turma criada.', pagina('mural.html', "turma=$id"), ['turma_id' => $id]);
}

function acao_editar_turma(): never {
    $u = exigir_login();
    $turma = turma_ou_falha(inteiro('turma_id', 'Turma'));
    exigir_membro((int) $turma['id'], $u, true);
    executar('UPDATE turmas SET nome = ?, niveis = ?, materia = ? WHERE id = ?',
        [texto('nome', 120, true, 'O nome da turma'), texto('niveis', 120), texto('materia', 120), $turma['id']]);
    sucesso('Turma atualizada.');
}

function acao_excluir_turma(): never {
    $u = exigir_login();
    $turma = turma_ou_falha(inteiro('turma_id', 'Turma'));
    exigir_criador($turma, $u);
    apagar_anexos_da_turma((int) $turma['id']);
    executar('DELETE FROM turmas WHERE id = ?', [$turma['id']]);
    sucesso('Turma excluída.', pagina('index.html'));
}

function acao_sair_da_turma(): never {
    $u = exigir_login();
    $turma = turma_ou_falha(inteiro('turma_id', 'Turma'));
    exigir_membro((int) $turma['id'], $u);
    if ((int) $turma['criador_id'] === (int) $u['id']) throw new Falha('O administrador não pode sair da turma. Exclua a turma, se for o caso.', 409);
    executar('DELETE FROM participacoes WHERE turma_id = ? AND usuario_id = ?', [$turma['id'], $u['id']]);
    sucesso('Você saiu da turma.', pagina('index.html'));
}

function acao_participar_turma(): never {
    $u = exigir_login();
    $codigo = strtolower(trim((string) ($_POST['codigo'] ?? '')));
    if (!preg_match('/^[a-z0-9]{5,8}$/', $codigo)) throw new Falha('Código inválido (5 a 8 letras ou números).');
    $turma = um('SELECT * FROM turmas WHERE codigo = ?', [$codigo]) ?? throw new Falha('Nenhuma turma encontrada com esse código.', 404);
    if (papel_na_turma((int) $turma['id'], (int) $u['id'])) throw new Falha('Você já participa desta turma.', 409);
    // regra de negócio: quem entra pelo código entra como aluno; o administrador pode promover depois
    executar('INSERT INTO participacoes (turma_id, usuario_id, papel, criado_em) VALUES (?,?,?,?)', [$turma['id'], $u['id'], 'aluno', agora()]);
    sucesso('Você entrou na turma ' . $turma['nome'] . '.', pagina('mural.html', 'turma=' . $turma['id']), ['turma_id' => (int) $turma['id']]);
}

function acao_personalizar_aparencia(): never {
    $u = exigir_login();
    $turma = turma_ou_falha(inteiro('turma_id', 'Turma'));
    exigir_membro((int) $turma['id'], $u, true);
    $envio = arquivos_enviados('imagem')[0] ?? throw new Falha('Escolha uma imagem.');
    if ($envio['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($envio['tmp_name'])) throw new Falha('Não foi possível receber a imagem.');
    if ($envio['size'] > config('max_bytes_imagem')) throw new Falha('Arquivo inválido. Use JPG ou PNG de até 2 MB.');
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($envio['tmp_name']);
    if (!in_array($mime, ['image/jpeg', 'image/png'], true) || @getimagesize($envio['tmp_name']) === false) {
        throw new Falha('Arquivo inválido. Use JPG ou PNG de até 2 MB.');
    }
    $pasta = config('uploads');
    if (!is_dir($pasta)) mkdir($pasta, 0755, true);
    $nome = 'turma-' . bin2hex(random_bytes(12)) . ($mime === 'image/png' ? '.png' : '.jpg');
    if (!move_uploaded_file($envio['tmp_name'], "$pasta/$nome")) throw new Falha('Falha ao gravar a imagem.', 500);
    if ($turma['imagem']) apagar_arquivo_fisico($turma['imagem']);
    executar('UPDATE turmas SET imagem = ? WHERE id = ?', [$nome, $turma['id']]);
    sucesso('Aparência atualizada.', null, ['imagem' => url_base_site() . 'backend/index.php/imagem-turma?id=' . $turma['id']]);
}

/** GET: imagem do cabeçalho do mural, só para quem participa da turma. */
function acao_imagem_turma(): never {
    $u = exigir_login();
    $turma = turma_ou_falha(inteiro('id', 'Turma'));
    exigir_membro((int) $turma['id'], $u);
    $arq = config('uploads') . '/' . basename((string) $turma['imagem']);
    if (!$turma['imagem'] || !is_file($arq)) throw new Falha('Esta turma não tem imagem.', 404);
    header('Content-Type: ' . (str_ends_with($arq, '.png') ? 'image/png' : 'image/jpeg'));
    header('X-Content-Type-Options: nosniff');
    header('Cache-Control: private, max-age=300');
    readfile($arq);
    exit;
}

function acao_convidar_alunos(): never {
    $u = exigir_login();
    $turma = turma_ou_falha(inteiro('turma_id', 'Turma'));
    exigir_membro((int) $turma['id'], $u, true);
    $lista = array_unique(array_filter(array_map(fn ($e) => mb_strtolower(trim($e)), preg_split('/[,;\s]+/', (string) ($_POST['emails'] ?? '')) ?: [])));
    if (!$lista) throw new Falha('Adicione ao menos um e-mail.');
    if (count($lista) > 50) throw new Falha('Convide no máximo 50 e-mails por vez.');
    foreach ($lista as $e) if (!filter_var($e, FILTER_VALIDATE_EMAIL) || mb_strlen($e) > 190) throw new Falha("E-mail inválido: $e");
    $novos = 0;
    foreach ($lista as $e) {
        if (um('SELECT id FROM convites WHERE turma_id = ? AND email = ?', [$turma['id'], $e])) continue;
        executar('INSERT INTO convites (turma_id, email, convidado_por, criado_em) VALUES (?,?,?,?)', [$turma['id'], $e, $u['id'], agora()]);
        $novos++;
    }
    // ponytail: o envio do e-mail em si ainda não existe (precisa de servidor de e-mail); o convite fica registrado.
    sucesso($novos === 1 ? '1 convite registrado.' : "$novos convites registrados.", null, ['convites' => $novos]);
}

function acao_redefinir_codigo(): never {
    $u = exigir_login();
    $turma = turma_ou_falha(inteiro('turma_id', 'Turma'));
    exigir_membro((int) $turma['id'], $u, true);
    $codigo = gerar_codigo();
    executar('UPDATE turmas SET codigo = ? WHERE id = ?', [$codigo, $turma['id']]);
    sucesso('Código redefinido.', null, ['codigo' => $codigo]);
}
