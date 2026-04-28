<?php
/**
 * GestorPro - Controle de Sessão e Autenticação
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Verifica se o usuário está autenticado
 */
function estaLogado(): bool
{
    return isset($_SESSION['usuario_id']) && !empty($_SESSION['usuario_id']);
}

/**
 * Exige que o usuário esteja logado, redireciona caso contrário
 */
function exigirLogin(): void
{
    if (!estaLogado()) {
        header('Location: ' . BASE_URL . 'index.php');
        exit;
    }
}

/**
 * Retorna os dados do usuário logado da sessão
 */
function getUsuarioLogado(): array
{
    return [
        'id'   => $_SESSION['usuario_id']   ?? null,
        'nome' => $_SESSION['usuario_nome'] ?? '',
    ];
}

/**
 * Inicia a sessão do usuário após login bem-sucedido
 */
function iniciarSessao(int $id, string $nome): void
{
    session_regenerate_id(true);
    $_SESSION['usuario_id']   = $id;
    $_SESSION['usuario_nome'] = $nome;
}

/**
 * Encerra a sessão do usuário
 */
function encerrarSessao(): void
{
    session_unset();
    session_destroy();
}

/**
 * Retorna e limpa uma mensagem flash da sessão
 */
function getFlash(string $chave): string
{
    $msg = $_SESSION['flash'][$chave] ?? '';
    unset($_SESSION['flash'][$chave]);
    return $msg;
}

/**
 * Define uma mensagem flash na sessão
 */
function setFlash(string $chave, string $msg): void
{
    $_SESSION['flash'][$chave] = $msg;
}

/**
 * Retorna a URL base do projeto
 */
define('BASE_URL', '/gestorpro/');
