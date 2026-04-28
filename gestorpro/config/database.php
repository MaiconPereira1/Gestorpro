<?php
/**
 * GestorPro - Configuração e conexão com o banco de dados
 * Utiliza PDO com criação automática do banco e das tabelas
 */

define('DB_HOST',    'localhost');
define('DB_NOME',    'gestorpro');
define('DB_USUARIO', 'root');
define('DB_SENHA',   '');
define('DB_CHARSET', 'utf8mb4');

class Database
{
    private static ?PDO $instancia = null;

    /**
     * Retorna a instância única da conexão PDO (Singleton)
     */
    public static function getConexao(): PDO
    {
        if (self::$instancia === null) {
            try {
                // Conecta sem selecionar banco para poder criá-lo
                $dsn = 'mysql:host=' . DB_HOST . ';charset=' . DB_CHARSET;
                $pdo = new PDO($dsn, DB_USUARIO, DB_SENHA, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);

                // Cria o banco caso não exista
                $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NOME . "`
                            CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                $pdo->exec("USE `" . DB_NOME . "`");

                // Cria as tabelas automaticamente
                self::criarTabelas($pdo);

                self::$instancia = $pdo;

            } catch (PDOException $e) {
                // Em produção, logar o erro sem expor detalhes
                die(json_encode([
                    'erro' => 'Falha na conexão com o banco de dados: ' . $e->getMessage()
                ]));
            }
        }

        return self::$instancia;
    }

    /**
     * Cria todas as tabelas do sistema (se não existirem)
     */
    private static function criarTabelas(PDO $pdo): void
    {
        // Habilita FK checks
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS usuarios (
                id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                nome      VARCHAR(100) NOT NULL,
                email     VARCHAR(150) NOT NULL UNIQUE,
                senha     CHAR(64)     NOT NULL COMMENT 'Hash SHA-256',
                criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS fornecedores (
                id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                razao_social VARCHAR(150) NOT NULL,
                cnpj         VARCHAR(18)  NOT NULL,
                email        VARCHAR(150) DEFAULT NULL,
                telefone     VARCHAR(20)  DEFAULT NULL,
                criado_em    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS produtos (
                id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                fornecedor_id INT UNSIGNED NOT NULL,
                nome          VARCHAR(150) NOT NULL,
                descricao     TEXT DEFAULT NULL,
                preco         DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                criado_em     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT fk_produto_fornecedor
                    FOREIGN KEY (fornecedor_id) REFERENCES fornecedores(id)
                    ON DELETE RESTRICT ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS cesta (
                id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                usuario_id INT UNSIGNED NOT NULL,
                criado_em  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT fk_cesta_usuario
                    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
                    ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS cesta_itens (
                id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                cesta_id   INT UNSIGNED NOT NULL,
                produto_id INT UNSIGNED NOT NULL,
                UNIQUE KEY uk_cesta_produto (cesta_id, produto_id),
                CONSTRAINT fk_item_cesta
                    FOREIGN KEY (cesta_id) REFERENCES cesta(id)
                    ON DELETE CASCADE,
                CONSTRAINT fk_item_produto
                    FOREIGN KEY (produto_id) REFERENCES produtos(id)
                    ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    }
}
