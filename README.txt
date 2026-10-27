PROJETO: Sistema de Assistência Técnica
Tecnologias: PHP básico, MySQL/MariaDB, HTML/CSS, XAMPP.
Conexão com banco feita com mysqli (sem PDO).

INSTALAÇÃO NO XAMPP
1. Coloque os arquivos desta pasta dentro de C:\xampp\htdocs\Projeto_Assistencia_Tecnica\Projeto_Assistencia_Tecnica\ se quiser manter o caminho duplicado que aparece no navegador.
2. No XAMPP, inicie Apache e MySQL.
3. O arquivo sistema/config/database.php está configurado para MySQL na porta 3307, conforme a captura do phpMyAdmin. Se seu MySQL estiver em 3306, altere $porta para 3306.
4. O banco esperado é saep_db, usuário root e senha vazia (configuração padrão local do XAMPP).
5. Como o banco saep_db já existe, NÃO importe saep_db.sql novamente se quiser preservar seus dados: esse arquivo apaga e recria as tabelas.
6. Acesse http://localhost/Projeto_Assistencia_Tecnica/Projeto_Assistencia_Tecnica/sistema/login.php
7. Acesso inicial: usuário admin / senha Admin@123

FUNCIONALIDADES
- Login e sessão com expiração após 15 minutos sem atividade.
- Painel inicial com contagem de clientes, equipamentos e ordens de serviço.
- Cadastro, edição, busca e exclusão de clientes.
- CPF armazenado criptografado usando OpenSSL.
- Cadastro, edição e exclusão de equipamentos vinculados a clientes.
- Cadastro, edição e exclusão de ordens de serviço.
- Consultas preparadas com mysqli, escape de saída e token CSRF nos formulários.

OBSERVAÇÃO
Projeto didático para uso local. Antes de uso real, configure segredos seguros, HTTPS, backups e controles de segurança adicionais.
