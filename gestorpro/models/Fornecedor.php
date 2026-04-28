<?php
/**
 * GestorPro - Model de Fornecedor
 * Responsável por todas as operações de fornecedor no banco de dados
 */

require_once __DIR__ . '/../config/database.php';

class Fornecedor
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConexao();
    }

    /**
     * Lista todos os fornecedores ordenados por razão social
     */
    public function listar(): array
    {
        $stmt = $this->pdo->query(
            "SELECT id, razao_social, cnpj, email, telefone, criado_em
             FROM fornecedores
             ORDER BY razao_social"
        );
        return $stmt->fetchAll();
    }

    /**
     * Busca um fornecedor pelo ID
     */
    public function buscarPorId(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            "SELECT id, razao_social, cnpj, email, telefone
             FROM fornecedores WHERE id = :id LIMIT 1"
        );
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Cadastra um novo fornecedor
     */
    public function cadastrar(array $dados): bool
    {
        $sql  = "INSERT INTO fornecedores (razao_social, cnpj, email, telefone)
                 VALUES (:razao_social, :cnpj, :email, :telefone)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':razao_social' => trim($dados['razao_social']),
            ':cnpj'         => trim($dados['cnpj']),
            ':email'        => trim($dados['email'] ?? ''),
            ':telefone'     => trim($dados['telefone'] ?? ''),
        ]);
    }

    /**
     * Atualiza os dados de um fornecedor
     */
    public function atualizar(int $id, array $dados): bool
    {
        $sql  = "UPDATE fornecedores
                 SET razao_social = :razao_social,
                     cnpj         = :cnpj,
                     email        = :email,
                     telefone     = :telefone
                 WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':razao_social' => trim($dados['razao_social']),
            ':cnpj'         => trim($dados['cnpj']),
            ':email'        => trim($dados['email'] ?? ''),
            ':telefone'     => trim($dados['telefone'] ?? ''),
            ':id'           => $id,
        ]);
    }

    /**
     * Remove um fornecedor pelo ID
     */
    public function excluir(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM fornecedores WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Retorna a lista de fornecedores para uso em selects (id => razão social)
     */
    public function listarParaSelect(): array
    {
        $stmt = $this->pdo->query("SELECT id, razao_social FROM fornecedores ORDER BY razao_social");
        return $stmt->fetchAll();
    }
}
