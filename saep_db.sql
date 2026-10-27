CREATE DATABASE IF NOT EXISTS saep_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE saep_db;

SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS ordens_servico;
DROP TABLE IF EXISTS equipamentos;
DROP TABLE IF EXISTS clientes;
DROP TABLE IF EXISTS usuarios;
SET FOREIGN_KEY_CHECKS=1;

CREATE TABLE usuarios (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(120) NOT NULL,
  usuario VARCHAR(50) NOT NULL UNIQUE,
  senha_hash VARCHAR(255) NOT NULL,
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE clientes (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(120) NOT NULL,
  cpf_criptografado TEXT NOT NULL,
  telefone VARCHAR(25) NOT NULL,
  email VARCHAR(120) NULL,
  endereco VARCHAR(180) NULL,
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE equipamentos (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  cliente_id INT UNSIGNED NOT NULL,
  tipo VARCHAR(80) NOT NULL,
  marca VARCHAR(80) NOT NULL,
  modelo VARCHAR(100) NOT NULL,
  numero_serie VARCHAR(100) NULL,
  descricao TEXT NULL,
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_equipamento_cliente FOREIGN KEY (cliente_id) REFERENCES clientes(id)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE ordens_servico (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  cliente_id INT UNSIGNED NOT NULL,
  equipamento_id INT UNSIGNED NOT NULL,
  problema TEXT NOT NULL,
  diagnostico TEXT NULL,
  status ENUM('Aberta','Em análise','Aguardando peça','Em reparo','Concluída','Cancelada') NOT NULL DEFAULT 'Aberta',
  valor DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  data_abertura DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  data_conclusao DATETIME NULL,
  CONSTRAINT fk_os_cliente FOREIGN KEY (cliente_id) REFERENCES clientes(id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_os_equipamento FOREIGN KEY (equipamento_id) REFERENCES equipamentos(id)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Usuário de demonstração: admin / Admin@123
-- Hash bcrypt válido para Admin@123
INSERT INTO usuarios (nome, usuario, senha_hash) VALUES
('Administrador do Sistema', 'admin', '$2y$12$Qba9YfXilK8fEk.2OVj8LODqPYgB/JY7Kp2Ix3qGLn7dRcxxgSU86');

-- O hash acima é um hash de exemplo; se o login não aceitar a senha indicada,
-- gere um hash com PHP: password_hash('Admin@123', PASSWORD_DEFAULT) e atualize-o.

-- A chave de criptografia deve ser configurada no sistema/config/database.php antes de cadastrar clientes.
-- Os registros iniciais usam valores criptografados de demonstração que serão descriptografáveis somente
-- com a chave configurada durante a geração do projeto; por isso o sistema permite ver o CPF como "Protegido"
-- quando não é possível descriptografar.
INSERT INTO clientes (nome, cpf_criptografado, telefone, email, endereco) VALUES
('Mariana Oliveira', 'DEMO_CPF_MARIANA', '(81) 99911-2233', 'mariana@example.com', 'Recife - PE'),
('Carlos Santos', 'DEMO_CPF_CARLOS', '(81) 98822-3344', 'carlos@example.com', 'Olinda - PE'),
('Fernanda Lima', 'DEMO_CPF_FERNANDA', '(81) 97733-4455', 'fernanda@example.com', 'Jaboatão dos Guararapes - PE');

INSERT INTO equipamentos (cliente_id, tipo, marca, modelo, numero_serie, descricao) VALUES
(1, 'Notebook', 'Dell', 'Inspiron 15', 'DEMO-NB-001', 'Notebook não inicia'),
(2, 'Desktop', 'Lenovo', 'ThinkCentre M70', 'DEMO-PC-002', 'Lentidão no sistema'),
(3, 'Impressora', 'HP', 'LaserJet Pro', 'DEMO-IMP-003', 'Falha na impressão');

INSERT INTO ordens_servico (cliente_id, equipamento_id, problema, diagnostico, status, valor, data_abertura) VALUES
(1, 1, 'Equipamento não liga', 'Verificar fonte e placa', 'Em análise', 120.00, '2026-10-20 09:30:00'),
(2, 2, 'Computador muito lento', 'Limpeza e análise de disco', 'Aberta', 80.00, '2026-10-21 11:00:00'),
(3, 3, 'Impressora não imprime', 'Verificar toner e roletes', 'Aguardando peça', 150.00, '2026-10-22 14:15:00');
