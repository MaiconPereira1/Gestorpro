<?php
/**
 * GestorPro - Controller de Fornecedores
 * Processa CRUD de fornecedores via AJAX
 */

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../models/Fornecedor.php';

class FornecedorController
{
    private Fornecedor $fornecedorModel;

    public function __construct()
    {
        $this->fornecedorModel = new Fornecedor();
    }

    private function json(array $dados, int $codigo = 200): void
    {
        http_response_code($codigo);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($dados, JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function listar(): void
    {
        $fornecedores = $this->fornecedorModel->listar();
        $this->json(['sucesso' => true, 'dados' => $fornecedores]);
    }

    public function cadastrar(): void
    {
        $dados = $this->getDadosPost();

        if (empty($dados['razao_social']) || empty($dados['cnpj'])) {
            $this->json(['sucesso' => false, 'mensagem' => 'Razão Social e CNPJ são obrigatórios.'], 422);
        }

        if ($this->fornecedorModel->cadastrar($dados)) {
            $this->json(['sucesso' => true, 'mensagem' => 'Fornecedor cadastrado com sucesso!']);
        }

        $this->json(['sucesso' => false, 'mensagem' => 'Erro ao cadastrar fornecedor.'], 500);
    }

    public function buscar(int $id): void
    {
        $fornecedor = $this->fornecedorModel->buscarPorId($id);
        if ($fornecedor) {
            $this->json(['sucesso' => true, 'dados' => $fornecedor]);
        }
        $this->json(['sucesso' => false, 'mensagem' => 'Fornecedor não encontrado.'], 404);
    }

    public function atualizar(int $id): void
    {
        $dados = $this->getDadosPost();

        if (empty($dados['razao_social']) || empty($dados['cnpj'])) {
            $this->json(['sucesso' => false, 'mensagem' => 'Razão Social e CNPJ são obrigatórios.'], 422);
        }

        if ($this->fornecedorModel->atualizar($id, $dados)) {
            $this->json(['sucesso' => true, 'mensagem' => 'Fornecedor atualizado com sucesso!']);
        }

        $this->json(['sucesso' => false, 'mensagem' => 'Erro ao atualizar fornecedor.'], 500);
    }

    public function excluir(int $id): void
    {
        try {
            if ($this->fornecedorModel->excluir($id)) {
                $this->json(['sucesso' => true, 'mensagem' => 'Fornecedor excluído com sucesso!']);
            }
            $this->json(['sucesso' => false, 'mensagem' => 'Erro ao excluir fornecedor.'], 500);
        } catch (PDOException $e) {
            $this->json(['sucesso' => false, 'mensagem' => 'Fornecedor possui produtos vinculados.'], 409);
        }
    }

    private function getDadosPost(): array
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (str_contains($contentType, 'application/json')) {
            return json_decode(file_get_contents('php://input'), true) ?? [];
        }
        return $_POST;
    }
}
