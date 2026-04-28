<?php
/**
 * GestorPro - Model de Usuário
 * Responsável por todas as operações de usuário no banco de dados
 */

require_once __DIR__ . '/../config/database.php';

class Usuario
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConexao();
    }

    /**
     * Cadastra um novo usuário com senha em hash SHA-256
     */
    public function cadastrar(string $nome, string $email, string $senha): bool
    {
        $hashSenha = hash('sha256', $senha);

        $sql  = "INSERT INTO usuarios (nome, email, senha) VALUES (:nome, :email, :senha)";
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            ':nome'  => trim($nome),
            ':email' => strtolower(trim($email)),
            ':senha' => $hashSenha,
        ]);
    }

    /**
     * Autentica o usuário verificando email e hash SHA-256 da senha
     * Retorna os dados do usuário ou null se inválido
     */
    public function autenticar(string $email, string $senha): ?array
    {
        $hashSenha = hash('sha256', $senha);

        $sql  = "SELECT id, nome, email FROM usuarios
                 WHERE email = :email AND senha = :senha
                 LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':email' => strtolower(trim($email)),
            ':senha' => $hashSenha,
        ]);

        $usuario = $stmt->fetch();
        return $usuario ?: null;
    }

    /**
     * Verifica se um email já está cadastrado
     */
    public function emailExiste(string $email): bool
    {
        $sql  = "SELECT id FROM usuarios WHERE email = :email LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':email' => strtolower(trim($email))]);
        return (bool) $stmt->fetch();
    }

    /**
     * Lista todos os usuários
     */
    public function listar(): array
    {
        $stmt = $this->pdo->query("SELECT id, nome, email, criado_em FROM usuarios ORDER BY nome");
        return $stmt->fetchAll();
    }
}
