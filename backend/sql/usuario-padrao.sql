-- EduConnect: usuário padrão, para entrar no site sem precisar se cadastrar.
-- Roda depois do schema.sql, a cada conexão (src/Database/Esquema.php). O WHERE NOT EXISTS só insere se ainda não há
-- conta com esse e-mail: não duplica nem altera a que já existe, e a recria se for apagada. Funciona no MySQL e no SQLite.
--
--   E-mail: padrao@educonnect.test
--   Senha:  educonnect123
--
-- A senha fica guardada só como hash (password_hash do PHP). Para trocar, gere outro com
--   php -r 'echo password_hash("nova-senha", PASSWORD_DEFAULT);'
-- e substitua o valor abaixo. Uma conta que já existe no banco não muda: apague-a ou troque a senha por lá.

INSERT INTO usuarios (nome, email, senha_hash, criado_em)
SELECT 'Usuário Padrão', 'padrao@educonnect.test', '$2y$10$2ww.hwKYvCDCYsQgI3ybB.Tcq8.unSSeTDq2x2Fqy1V8ASbobjTpC', '2026-11-24 00:00:00'
FROM (SELECT 1 AS um) AS unica_linha
WHERE NOT EXISTS (SELECT 1 FROM usuarios WHERE email = 'padrao@educonnect.test');
