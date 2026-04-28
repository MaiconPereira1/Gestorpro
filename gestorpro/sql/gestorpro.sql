-- ============================================================
-- GestorPro - Script SQL de Criação do Banco de Dados
-- Disciplina: Programação para Internet
-- ============================================================

CREATE DATABASE IF NOT EXISTS gestorpro
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE gestorpro;

-- Tabela de usuários
CREATE TABLE IF NOT EXISTS usuarios (
    id          INT          UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome        VARCHAR(100) NOT NULL,
    email       VARCHAR(150) NOT NULL UNIQUE,
    senha       CHAR(64)     NOT NULL COMMENT 'Hash SHA-256',
    criado_em   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de fornecedores
CREATE TABLE IF NOT EXISTS fornecedores (
    id           INT          UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    razao_social VARCHAR(150) NOT NULL,
    cnpj         VARCHAR(18)  NOT NULL,
    email        VARCHAR(150) DEFAULT NULL,
    telefone     VARCHAR(20)  DEFAULT NULL,
    criado_em    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de produtos (relacionada com fornecedores)
CREATE TABLE IF NOT EXISTS produtos (
    id            INT           UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    fornecedor_id INT           UNSIGNED NOT NULL,
    nome          VARCHAR(150)  NOT NULL,
    descricao     TEXT          DEFAULT NULL,
    preco         DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    criado_em     DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_produto_fornecedor
        FOREIGN KEY (fornecedor_id)
        REFERENCES fornecedores(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela da cesta (por sessão de usuário)
CREATE TABLE IF NOT EXISTS cesta (
    id          INT      UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id  INT      UNSIGNED NOT NULL,
    criado_em   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_cesta_usuario
        FOREIGN KEY (usuario_id)
        REFERENCES usuarios(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela dos itens da cesta (relaciona cesta <-> produtos)
CREATE TABLE IF NOT EXISTS cesta_itens (
    id          INT      UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cesta_id    INT      UNSIGNED NOT NULL,
    produto_id  INT      UNSIGNED NOT NULL,
    UNIQUE KEY  uk_cesta_produto (cesta_id, produto_id),
    CONSTRAINT fk_item_cesta
        FOREIGN KEY (cesta_id)
        REFERENCES cesta(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_item_produto
        FOREIGN KEY (produto_id)
        REFERENCES produtos(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
