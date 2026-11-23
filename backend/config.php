<?php
// Configuração do back-end do EduConnect. Nada aqui é secreto: troque os valores conforme o ambiente.
return [
    // Padrão: SQLite (um arquivo, sem instalar nada). Para MySQL/XAMPP use, por exemplo:
    //   'dsn' => 'mysql:host=localhost;dbname=educonnect;charset=utf8mb4', 'usuario' => 'root', 'senha' => ''
    // (crie o banco vazio `educonnect`; as tabelas são criadas sozinhas).
    'dsn'     => 'sqlite:' . __DIR__ . '/storage/educonnect.sqlite',
    'usuario' => null,
    'senha'   => null,

    'uploads' => __DIR__ . '/storage/uploads',          // fora do que o site serve: só baixa via /arquivo
    'max_bytes_arquivo'  => 20 * 1024 * 1024,           // 20 MB por arquivo (mesmo limite dos modais)
    'max_bytes_imagem'   => 2 * 1024 * 1024,            // 2 MB (Personalizar aparência)
    'max_arquivos_publicacao' => 10,                    // atividade/material
    'max_arquivos_entrega'    => 5,                     // trabalho do aluno
    'senha_minima' => 8,
    'fuso' => 'America/Sao_Paulo',
];
