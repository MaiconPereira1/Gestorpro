<?php
/**
 * GestorPro - Controller da Cesta de Compras
 * Gerencia adição de produtos, remoção de itens e esvaziamento da cesta
 */

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../models/Cesta.php';

class CestaController
{
    private Cesta $cestaModel;

    public function __construct()
    {
        $this->cestaModel = new Cesta();
    }

    private function json(array $dados, int $codigo = 200): void
    {
        http_response_code($codigo);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($dados, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Adiciona produtos selecionados à cesta (chamada AJAX)
     * Valida se ao menos 1 produto foi selecionado
     */
    public function adicionar(): void
    {
        $usuario = getUsuarioLogado();
        $dados   = json_decode(file_get_contents('php://input'), true) ?? [];
        $ids     = $dados['produtos'] ?? [];

        // Validação: ao menos um produto deve ser selecionado
        if (empty($ids) || !is_array($ids)) {
            $this->json(['sucesso' => false, 'mensagem' => 'Selecione ao menos um produto.'], 422);
        }

        // Filtra apenas inteiros positivos
        $ids = array_filter(array_map('intval', $ids), fn($id) => $id > 0);

        if (empty($ids)) {
            $this->json(['sucesso' => false, 'mensagem' => 'Produtos inválidos.'], 422);
        }

        $adicionados = $this->cestaModel->adicionarProdutos((int) $usuario['id'], $ids);

        $this->json([
            'sucesso'    => true,
            'mensagem'   => "$adicionados produto(s) adicionado(s) à cesta!",
            'adicionados' => $adicionados,
        ]);
    }

    /**
     * Remove um item da cesta (chamada AJAX)
     */
    public function remover(): void
    {
        $usuario = getUsuarioLogado();
        $dados   = json_decode(file_get_contents('php://input'), true) ?? [];
        $item_id = (int) ($dados['item_id'] ?? 0);

        if ($item_id <= 0) {
            $this->json(['sucesso' => false, 'mensagem' => 'Item inválido.'], 422);
        }

        if ($this->cestaModel->removerItem($item_id, (int) $usuario['id'])) {
            $this->json(['sucesso' => true, 'mensagem' => 'Item removido da cesta.']);
        }

        $this->json(['sucesso' => false, 'mensagem' => 'Erro ao remover item.'], 500);
    }

    /**
     * Esvazia a cesta do usuário (chamada AJAX)
     */
    public function esvaziar(): void
    {
        $usuario = getUsuarioLogado();

        if ($this->cestaModel->esvaziar((int) $usuario['id'])) {
            $this->json(['sucesso' => true, 'mensagem' => 'Cesta esvaziada com sucesso.']);
        }

        $this->json(['sucesso' => false, 'mensagem' => 'Erro ao esvaziar cesta.'], 500);
    }

    /**
     * Retorna os dados resumo da cesta (total de itens + valor total)
     */
    public function resumo(): void
    {
        $usuario  = getUsuarioLogado();
        $uid      = (int) $usuario['id'];
        $total    = $this->cestaModel->totalItens($uid);
        $valor    = $this->cestaModel->valorTotal($uid);

        $this->json([
            'sucesso'       => true,
            'total_itens'   => $total,
            'valor_total'   => $valor,
            'valor_formatado' => 'R$ ' . number_format($valor, 2, ',', '.'),
        ]);
    }
}
