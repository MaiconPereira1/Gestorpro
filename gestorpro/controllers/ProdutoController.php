<?php
/**
 * GestorPro - Controller de Produtos
 * Processa CRUD de produtos (cadastro via POST e respostas AJAX)
 */

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../models/Produto.php';
require_once __DIR__ . '/../models/Fornecedor.php';

class ProdutoController
{
    private Produto    $produtoModel;
    private Fornecedor $fornecedorModel;

    public function __construct()
    {
        $this->produtoModel    = new Produto();
        $this->fornecedorModel = new Fornecedor();
    }

    /**
     * Responde com JSON (para chamadas AJAX)
     */
    private function json(array $dados, int $codigo = 200): void
    {
        http_response_code($codigo);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($dados, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Lista todos os produtos (resposta AJAX)
     */
    public function listar(): void
    {
        $produtos = $this->produtoModel->listar();
        $this->json(['sucesso' => true, 'dados' => $produtos]);
    }

    /**
     * Cadastra um produto (resposta AJAX)
     */
    public function cadastrar(): void
    {
        $dados = $this->getDadosPost();

        if (!$this->validar($dados)) {
            $this->json(['sucesso' => false, 'mensagem' => 'Preencha todos os campos obrigatórios.'], 422);
        }

        if ($this->produtoModel->cadastrar($dados)) {
            $this->json(['sucesso' => true, 'mensagem' => 'Produto cadastrado com sucesso!']);
        }

        $this->json(['sucesso' => false, 'mensagem' => 'Erro ao cadastrar produto.'], 500);
    }

    /**
     * Busca um produto pelo ID (resposta AJAX)
     */
    public function buscar(int $id): void
    {
        $produto = $this->produtoModel->buscarPorId($id);

        if ($produto) {
            $this->json(['sucesso' => true, 'dados' => $produto]);
        }

        $this->json(['sucesso' => false, 'mensagem' => 'Produto não encontrado.'], 404);
    }

    /**
     * Atualiza um produto (resposta AJAX)
     */
    public function atualizar(int $id): void
    {
        $dados = $this->getDadosPost();

        if (!$this->validar($dados)) {
            $this->json(['sucesso' => false, 'mensagem' => 'Preencha todos os campos obrigatórios.'], 422);
        }

        if ($this->produtoModel->atualizar($id, $dados)) {
            $this->json(['sucesso' => true, 'mensagem' => 'Produto atualizado com sucesso!']);
        }

        $this->json(['sucesso' => false, 'mensagem' => 'Erro ao atualizar produto.'], 500);
    }

    /**
     * Exclui um produto (resposta AJAX)
     */
    public function excluir(int $id): void
    {
        try {
            if ($this->produtoModel->excluir($id)) {
                $this->json(['sucesso' => true, 'mensagem' => 'Produto excluído com sucesso!']);
            }
            $this->json(['sucesso' => false, 'mensagem' => 'Erro ao excluir produto.'], 500);
        } catch (PDOException $e) {
            $this->json(['sucesso' => false, 'mensagem' => 'Produto vinculado a uma cesta. Remova da cesta primeiro.'], 409);
        }
    }

    /**
     * Obtém dados do POST (suporta JSON e form-data)
     */
    private function getDadosPost(): array
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (str_contains($contentType, 'application/json')) {
            return json_decode(file_get_contents('php://input'), true) ?? [];
        }
        return $_POST;
    }

    /**
     * Valida os dados do produto
     */
    private function validar(array $dados): bool
    {
        return !empty($dados['nome'])
            && !empty($dados['fornecedor_id'])
            && isset($dados['preco'])
            && is_numeric(str_replace(',', '.', $dados['preco']));
    }
}
