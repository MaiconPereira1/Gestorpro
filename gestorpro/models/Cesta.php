<?php
/**
 * GestorPro - Model da Cesta de Compras
 * Gerencia a cesta do usuário: criação, adição de itens, remoção e consulta
 * Relaciona-se com Usuario e Produto
 */

require_once __DIR__ . '/../config/database.php';

class Cesta
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConexao();
    }

    /**
     * Obtém o ID da cesta ativa do usuário, ou cria uma nova
     */
    private function obterOuCriarCesta(int $usuario_id): int
    {
        // Busca cesta existente do usuário
        $stmt = $this->pdo->prepare(
            "SELECT id FROM cesta WHERE usuario_id = :uid ORDER BY criado_em DESC LIMIT 1"
        );
        $stmt->execute([':uid' => $usuario_id]);
        $cesta = $stmt->fetch();

        if ($cesta) {
            return (int) $cesta['id'];
        }

        // Cria nova cesta
        $stmt = $this->pdo->prepare("INSERT INTO cesta (usuario_id) VALUES (:uid)");
        $stmt->execute([':uid' => $usuario_id]);
        return (int) $this->pdo->lastInsertId();
    }

    /**
     * Adiciona produtos à cesta do usuário (array de IDs de produtos)
     * Considera apenas 1 unidade de cada produto (sem campo de quantidade)
     * Retorna quantos produtos foram efetivamente adicionados
     */
    public function adicionarProdutos(int $usuario_id, array $produto_ids): int
    {
        if (empty($produto_ids)) {
            return 0;
        }

        $cesta_id   = $this->obterOuCriarCesta($usuario_id);
        $adicionados = 0;

        $stmt = $this->pdo->prepare(
            "INSERT IGNORE INTO cesta_itens (cesta_id, produto_id) VALUES (:cesta_id, :produto_id)"
        );

        foreach ($produto_ids as $pid) {
            $pid = (int) $pid;
            if ($pid > 0) {
                $ok = $stmt->execute([':cesta_id' => $cesta_id, ':produto_id' => $pid]);
                if ($ok && $stmt->rowCount() > 0) {
                    $adicionados++;
                }
            }
        }

        return $adicionados;
    }

    /**
     * Lista os itens da cesta do usuário com dados completos dos produtos
     */
    public function listarItens(int $usuario_id): array
    {
        $sql  = "SELECT ci.id AS item_id,
                        p.id  AS produto_id,
                        p.nome,
                        p.descricao,
                        p.preco,
                        f.razao_social AS fornecedor_nome
                 FROM cesta c
                 INNER JOIN cesta_itens ci ON ci.cesta_id = c.id
                 INNER JOIN produtos    p  ON p.id  = ci.produto_id
                 INNER JOIN fornecedores f ON f.id  = p.fornecedor_id
                 WHERE c.usuario_id = :uid
                 ORDER BY p.nome";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':uid' => $usuario_id]);
        return $stmt->fetchAll();
    }

    /**
     * Retorna o total de itens na cesta do usuário
     */
    public function totalItens(int $usuario_id): int
    {
        $sql  = "SELECT COUNT(ci.id) AS total
                 FROM cesta c
                 INNER JOIN cesta_itens ci ON ci.cesta_id = c.id
                 WHERE c.usuario_id = :uid";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':uid' => $usuario_id]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Retorna o valor total dos produtos na cesta do usuário
     */
    public function valorTotal(int $usuario_id): float
    {
        $sql  = "SELECT COALESCE(SUM(p.preco), 0) AS total
                 FROM cesta c
                 INNER JOIN cesta_itens ci ON ci.cesta_id = c.id
                 INNER JOIN produtos    p  ON p.id  = ci.produto_id
                 WHERE c.usuario_id = :uid";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':uid' => $usuario_id]);
        return (float) $stmt->fetchColumn();
    }

    /**
     * Remove um item específico da cesta do usuário
     */
    public function removerItem(int $item_id, int $usuario_id): bool
    {
        $sql  = "DELETE ci FROM cesta_itens ci
                 INNER JOIN cesta c ON c.id = ci.cesta_id
                 WHERE ci.id = :item_id AND c.usuario_id = :uid";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':item_id' => $item_id, ':uid' => $usuario_id]);
    }

    /**
     * Esvazia completamente a cesta do usuário
     */
    public function esvaziar(int $usuario_id): bool
    {
        $sql  = "DELETE ci FROM cesta_itens ci
                 INNER JOIN cesta c ON c.id = ci.cesta_id
                 WHERE c.usuario_id = :uid";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':uid' => $usuario_id]);
    }

    /**
     * Retorna os IDs dos produtos que já estão na cesta do usuário
     */
    public function idsNaCesta(int $usuario_id): array
    {
        $sql  = "SELECT ci.produto_id
                 FROM cesta_itens ci
                 INNER JOIN cesta c ON c.id = ci.cesta_id
                 WHERE c.usuario_id = :uid";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':uid' => $usuario_id]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
