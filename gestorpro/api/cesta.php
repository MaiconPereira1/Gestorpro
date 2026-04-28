<?php
/**
 * GestorPro - API da Cesta (AJAX)
 * Gerencia adição, remoção e consulta da cesta de compras
 */

require_once __DIR__ . '/../config/session.php';

if (!estaLogado()) {
    http_response_code(401);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Não autorizado.']);
    exit;
}

require_once __DIR__ . '/../controllers/CestaController.php';

$controller = new CestaController();
$acao       = $_GET['acao'] ?? '';
$metodo     = $_SERVER['REQUEST_METHOD'];

match (true) {
    $acao === 'resumo'                          => $controller->resumo(),
    $acao === 'adicionar' && $metodo === 'POST' => $controller->adicionar(),
    $acao === 'remover'  && $metodo === 'POST'  => $controller->remover(),
    $acao === 'esvaziar' && $metodo === 'POST'  => $controller->esvaziar(),
    default => (function () {
        http_response_code(400);
        echo json_encode(['sucesso' => false, 'mensagem' => 'Ação inválida.']);
    })()
};
