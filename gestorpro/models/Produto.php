<?php
/**
 * GestorPro - Model de Produto
 * Responsável por todas as operações de produto no banco de dados
 * Possui relacionamento com a entidade Fornecedor
 */

require_once __DIR__ . '/../config/database.php';

class Produto
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConexao();
    }

    /**
     * Lista todos os produtos com o nome do fornecedor (JOIN)
     */
    public function listar(): array
    {
        $sql  = "SELECT p.id, p.nome, p.descricao, p.preco, p.criado_em,
                        p.fornecedor_id, f.razao_social AS fornecedor_nome
                 FROM produtos p
                 INNER JOIN fornecedores f ON f.id = p.fornecedor_id
                 ORDER BY p.nome";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Busca um produto pelo ID com dados do fornecedor
     */
    public function buscarPorId(int $id): ?array
    {
        $sql  = "SELECT p.id, p.nome, p.descricao, p.preco,
                        p.fornecedor_id, f.razao_social AS fornecedor_nome
                 FROM produtos p
                 INNER JOIN fornecedores f ON f.id = p.fornecedor_id
                 WHERE p.id = :id LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Cadastra um novo produto
     */
    public function cadastrar(array $dados): bool
    {
        $sql  = "INSERT INTO produtos (fornecedor_id, nome, descricao, preco)
                 VALUES (:fornecedor_id, :nome, :descricao, :preco)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':fornecedor_id' => (int) $dados['fornecedor_id'],
            ':nome'          => trim($dados['nome']),
            ':descricao'     => trim($dados['descricao'] ?? ''),
            ':preco'         => (float) str_replace(',', '.', $dados['preco']),
        ]);
    }

    /**
     * Atualiza os dados de um produto
     */
    public function atualizar(int $id, array $dados): bool
    {
        $sql  = "UPDATE produtos
                 SET fornecedor_id = :fornecedor_id,
                     nome          = :nome,
                     descricao     = :descricao,
                     preco         = :preco
                 WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':fornecedor_id' => (int) $dados['fornecedor_id'],
            ':nome'          => trim($dados['nome']),
            ':descricao'     => trim($dados['descricao'] ?? ''),
            ':preco'         => (float) str_replace(',', '.', $dados['preco']),
            ':id'            => $id,
        ]);
    }

    /**
     * Remove um produto pelo ID
     */
    public function excluir(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM produtos WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Lista produtos que NÃO estão na cesta do usuário (para a tela de seleção)
     */
    public function listarDisponiveisParaSelecao(int $usuario_id): array
    {
        $sql = "SELECT p.id, p.nome, p.descricao, p.preco,
                       f.razao_social AS fornecedor_nome
                FROM produtos p
                INNER JOIN fornecedores f ON f.id = p.fornecedor_id
                ORDER BY p.nome";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }
}
