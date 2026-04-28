<?php
/**
 * GestorPro - API de Produtos (AJAX)
 * Responde com JSON para todas as operações CRUD
 */

require_once __DIR__ . '/../config/session.php';

// Exige autenticação para todas as operações
if (!estaLogado()) {
    http_response_code(401);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Não autorizado.']);
    exit;
}

require_once __DIR__ . '/../controllers/ProdutoController.php';

$controller = new ProdutoController();
$acao       = $_GET['acao'] ?? '';
$id         = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$metodo     = $_SERVER['REQUEST_METHOD'];

// Roteamento
match (true) {
    $acao === 'listar'                           => $controller->listar(),
    $acao === 'buscar' && $id > 0               => $controller->buscar($id),
    $acao === 'cadastrar' && $metodo === 'POST'  => $controller->cadastrar(),
    $acao === 'atualizar' && $id > 0            => $controller->atualizar($id),
    $acao === 'excluir'  && $id > 0             => $controller->excluir($id),
    default => (function() {
        http_response_code(400);
        echo json_encode(['sucesso' => false, 'mensagem' => 'Ação inválida.']);
    })()
};
