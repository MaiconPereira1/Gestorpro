<?php
/**
 * GestorPro - Controller de Autenticação
 * Processa login, logout e cadastro de usuários
 */

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../models/Usuario.php';

class AuthController
{
    private Usuario $usuarioModel;

    public function __construct()
    {
        $this->usuarioModel = new Usuario();
    }

    /**
     * Processa o formulário de login
     */
    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        $email = trim($_POST['email'] ?? '');
        $senha = $_POST['senha'] ?? '';

        // Validações básicas
        if (empty($email) || empty($senha)) {
            setFlash('erro_login', 'Preencha e-mail e senha.');
            return;
        }

        $usuario = $this->usuarioModel->autenticar($email, $senha);

        if ($usuario) {
            iniciarSessao((int) $usuario['id'], $usuario['nome']);
            header('Location: ' . BASE_URL . 'dashboard.php');
            exit;
        }

        setFlash('erro_login', 'E-mail ou senha incorretos.');
    }

    /**
     * Processa o logout do usuário
     */
    public function logout(): void
    {
        encerrarSessao();
        header('Location: ' . BASE_URL . 'index.php');
        exit;
    }

    /**
     * Processa o cadastro de novo usuário
     */
    public function cadastrar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        $nome  = trim($_POST['nome']  ?? '');
        $email = trim($_POST['email'] ?? '');
        $senha = $_POST['senha']      ?? '';
        $conf  = $_POST['confirmacao'] ?? '';

        // Validações
        if (empty($nome) || empty($email) || empty($senha)) {
            setFlash('erro_cadastro', 'Todos os campos são obrigatórios.');
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            setFlash('erro_cadastro', 'E-mail inválido.');
            return;
        }

        if (strlen($senha) < 6) {
            setFlash('erro_cadastro', 'A senha deve ter pelo menos 6 caracteres.');
            return;
        }

        if ($senha !== $conf) {
            setFlash('erro_cadastro', 'As senhas não coincidem.');
            return;
        }

        if ($this->usuarioModel->emailExiste($email)) {
            setFlash('erro_cadastro', 'Este e-mail já está cadastrado.');
            return;
        }

        if ($this->usuarioModel->cadastrar($nome, $email, $senha)) {
            setFlash('sucesso_cadastro', 'Usuário cadastrado com sucesso! Faça login.');
            header('Location: ' . BASE_URL . 'index.php');
            exit;
        }

        setFlash('erro_cadastro', 'Erro ao cadastrar. Tente novamente.');
    }
}
