<?php
/**
 * GestorPro - Dashboard
 * Painel principal após login com estatísticas do sistema
 */

require_once __DIR__ . '/config/session.php';
exigirLogin();

require_once __DIR__ . '/models/Produto.php';
require_once __DIR__ . '/models/Fornecedor.php';
require_once __DIR__ . '/models/Cesta.php';

$produtoModel    = new Produto();
$fornecedorModel = new Fornecedor();
$cestaModel      = new Cesta();

$usuario = getUsuarioLogado();

$totalProdutos    = count($produtoModel->listar());
$totalFornecedores = count($fornecedorModel->listar());
$totalCesta       = $cestaModel->totalItens((int) $usuario['id']);
$valorCesta       = $cestaModel->valorTotal((int) $usuario['id']);
$ultimosProdutos  = array_slice($produtoModel->listar(), 0, 5);

$tituloPagina = 'Dashboard';
?>
<?php require_once __DIR__ . '/views/partials/header.php'; ?>

<!-- Alerta container (AJAX) -->
<div id="alertaContainer"></div>
<!-- Spinner -->
<div id="spinnerOverlay" class="spinner-overlay">
    <div class="spinner-border text-success" style="width:3rem;height:3rem;"></div>
</div>

<!-- Cabeçalho da página -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-bold">
            <i class="bi bi-speedometer2 text-success me-2"></i>Dashboard
        </h4>
        <p class="text-muted mb-0">
            Bem-vindo, <strong><?= htmlspecialchars($usuario['nome']) ?></strong>!
            Aqui está um resumo do sistema.
        </p>
    </div>
</div>

<!-- Cards de estatísticas -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card stat-card border-0 h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="icon-wrap bg-success bg-opacity-10">
                    <i class="bi bi-box text-success"></i>
                </div>
                <div>
                    <div class="text-muted small">Produtos</div>
                    <div class="fs-3 fw-bold"><?= $totalProdutos ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card border-0 h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="icon-wrap bg-primary bg-opacity-10">
                    <i class="bi bi-building text-primary"></i>
                </div>
                <div>
                    <div class="text-muted small">Fornecedores</div>
                    <div class="fs-3 fw-bold"><?= $totalFornecedores ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card border-0 h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="icon-wrap bg-warning bg-opacity-10">
                    <i class="bi bi-cart3 text-warning"></i>
                </div>
                <div>
                    <div class="text-muted small">Itens na Cesta</div>
                    <div class="fs-3 fw-bold"><?= $totalCesta ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card border-0 h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="icon-wrap bg-info bg-opacity-10">
                    <i class="bi bi-currency-dollar text-info"></i>
                </div>
                <div>
                    <div class="text-muted small">Total da Cesta</div>
                    <div class="fs-5 fw-bold text-info">
                        R$ <?= number_format($valorCesta, 2, ',', '.') ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Atalhos rápidos -->
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="card border-0">
            <div class="card-header">
                <i class="bi bi-lightning-fill text-warning me-2"></i>Acesso Rápido
            </div>
            <div class="card-body">
                <div class="d-flex flex-wrap gap-2">
                    <a href="<?= BASE_URL ?>produtos.php" class="btn btn-outline-success">
                        <i class="bi bi-box me-1"></i>Gerenciar Produtos
                    </a>
                    <a href="<?= BASE_URL ?>fornecedores.php" class="btn btn-outline-primary">
                        <i class="bi bi-building me-1"></i>Gerenciar Fornecedores
                    </a>
                    <a href="<?= BASE_URL ?>selecionar.php" class="btn btn-outline-warning">
                        <i class="bi bi-check2-square me-1"></i>Selecionar Produtos
                    </a>
                    <a href="<?= BASE_URL ?>cesta.php" class="btn btn-outline-danger">
                        <i class="bi bi-cart3 me-1"></i>Ver Cesta
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Últimos produtos -->
<div class="card border-0">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-clock-history me-2 text-muted"></i>Últimos Produtos Cadastrados</span>
        <a href="<?= BASE_URL ?>produtos.php" class="btn btn-sm btn-outline-secondary">
            Ver todos <i class="bi bi-arrow-right ms-1"></i>
        </a>
    </div>
    <div class="card-body p-0">
        <?php if (empty($ultimosProdutos)): ?>
            <div class="text-center text-muted py-5">
                <i class="bi bi-inbox fs-1 d-block mb-2 opacity-25"></i>
                Nenhum produto cadastrado ainda.
                <br>
                <a href="<?= BASE_URL ?>produtos.php" class="btn btn-success mt-3">
                    <i class="bi bi-plus-lg me-1"></i>Cadastrar primeiro produto
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Produto</th>
                            <th>Fornecedor</th>
                            <th>Preço</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ultimosProdutos as $p): ?>
                            <tr>
                                <td class="text-muted"><?= $p['id'] ?></td>
                                <td class="fw-semibold"><?= htmlspecialchars($p['nome']) ?></td>
                                <td><?= htmlspecialchars($p['fornecedor_nome']) ?></td>
                                <td class="text-success fw-semibold">
                                    R$ <?= number_format($p['preco'], 2, ',', '.') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/views/partials/footer.php'; ?>
