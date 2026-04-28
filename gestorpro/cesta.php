<?php
/**
 * GestorPro - Cesta de Compras
 * ÁREA 4: Exibe os produtos selecionados, valor total e quantidade.
 *         Cada produto com 1 unidade — sem campo de quantidade.
 */

require_once __DIR__ . '/config/session.php';
exigirLogin();

require_once __DIR__ . '/models/Cesta.php';

$usuario    = getUsuarioLogado();
$cestaModel = new Cesta();

$itens      = $cestaModel->listarItens((int) $usuario['id']);
$totalItens = $cestaModel->totalItens((int) $usuario['id']);
$valorTotal = $cestaModel->valorTotal((int) $usuario['id']);

$tituloPagina = 'Cesta de Compras';
?>
<?php require_once __DIR__ . '/views/partials/header.php'; ?>

<div id="alertaContainer"></div>
<div id="spinnerOverlay" class="spinner-overlay">
    <div class="spinner-border text-success" style="width:3rem;height:3rem;"></div>
</div>

<!-- Cabeçalho -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-bold">
            <i class="bi bi-cart3 text-danger me-2"></i>Cesta de Compras
        </h4>
        <p class="text-muted mb-0">
            Confira os produtos selecionados. Cada produto representa <strong>1 unidade</strong>.
        </p>
    </div>
    <a href="<?= BASE_URL ?>selecionar.php" class="btn btn-outline-warning">
        <i class="bi bi-check2-square me-1"></i>Selecionar mais
    </a>
</div>

<?php if (empty($itens)): ?>
    <!-- Cesta vazia -->
    <div id="cestaVazio" class="card border-0 text-center py-5">
        <div class="card-body">
            <i class="bi bi-cart-x fs-1 text-muted opacity-25 d-block mb-3"></i>
            <h5 class="text-muted">Sua cesta está vazia</h5>
            <p class="text-muted small">Acesse "Selecionar Produtos" para adicionar itens.</p>
            <a href="<?= BASE_URL ?>selecionar.php" class="btn btn-warning">
                <i class="bi bi-check2-square me-1"></i>Selecionar Produtos
            </a>
        </div>
    </div>

<?php else: ?>

    <div class="row g-4">

        <!-- ====== COLUNA ESQUERDA: Itens da cesta ====== -->
        <div class="col-lg-8">
            <div class="card border-0" id="cestaTabela">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>
                        <i class="bi bi-list-ul me-2 text-muted"></i>Itens Selecionados
                    </span>
                    <button class="btn btn-sm btn-outline-danger" onclick="esvaziarCesta()">
                        <i class="bi bi-trash me-1"></i>Esvaziar Cesta
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Produto</th>
                                    <th>Fornecedor</th>
                                    <th style="width:80px" class="text-center">Qtd.</th>
                                    <th style="width:120px">Preço Unit.</th>
                                    <th style="width:80px" class="text-center">Ações</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyCesta">
                                <?php foreach ($itens as $item): ?>
                                    <tr id="item-<?= $item['item_id'] ?>">
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="rounded bg-success bg-opacity-10 d-flex
                                                            align-items-center justify-content-center"
                                                     style="width:34px;height:34px;flex-shrink:0">
                                                    <i class="bi bi-box text-success small"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-semibold small">
                                                        <?= htmlspecialchars($item['nome']) ?>
                                                    </div>
                                                    <?php if (!empty($item['descricao'])): ?>
                                                        <div class="text-muted" style="font-size:.75rem">
                                                            <?= htmlspecialchars(mb_strimwidth($item['descricao'], 0, 50, '...')) ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-muted small align-middle">
                                            <?= htmlspecialchars($item['fornecedor_nome']) ?>
                                        </td>
                                        <td class="text-center align-middle">
                                            <!-- Sem campo de quantidade: sempre 1 unidade -->
                                            <span class="badge bg-secondary rounded-pill">1</span>
                                        </td>
                                        <td class="align-middle fw-semibold text-success">
                                            R$ <?= number_format($item['preco'], 2, ',', '.') ?>
                                        </td>
                                        <td class="text-center align-middle">
                                            <button class="btn btn-sm btn-outline-danger"
                                                    onclick="removerItemCesta(<?= $item['item_id'] ?>)"
                                                    title="Remover item">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- ====== COLUNA DIREITA: Resumo da cesta ====== -->
        <div class="col-lg-4">
            <div class="card border-0 border-success border-opacity-25">
                <div class="card-header bg-success bg-opacity-10">
                    <i class="bi bi-receipt me-2 text-success"></i>
                    <strong>Resumo da Cesta</strong>
                </div>
                <div class="card-body">

                    <!-- Quantidade de produtos -->
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">Quantidade de produtos:</span>
                        <span class="fw-bold fs-5" id="cestaQuantidade">
                            <?= $totalItens ?> produto<?= $totalItens !== 1 ? 's' : '' ?>
                        </span>
                    </div>

                    <!-- Unidade por produto -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted">Unidade por produto:</span>
                        <span class="badge bg-secondary">1 unidade</span>
                    </div>

                    <hr class="my-2">

                    <!-- Valor total -->
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="fw-semibold">Valor Total:</span>
                        <span class="fs-4 fw-bold text-success" id="cestaTotal">
                            R$ <?= number_format($valorTotal, 2, ',', '.') ?>
                        </span>
                    </div>

                    <hr class="my-3">

                    <!-- Botões de ação -->
                    <div class="d-grid gap-2">
                        <a href="<?= BASE_URL ?>selecionar.php" class="btn btn-outline-warning">
                            <i class="bi bi-check2-square me-2"></i>Continuar Selecionando
                        </a>
                        <button class="btn btn-success" disabled title="Funcionalidade de finalização">
                            <i class="bi bi-bag-check me-2"></i>Finalizar Compra
                        </button>
                    </div>

                    <div class="text-center mt-3">
                        <small class="text-muted">
                            <i class="bi bi-info-circle me-1"></i>
                            Cada produto conta como 1 unidade.
                        </small>
                    </div>
                </div>
            </div>
        </div>

    </div><!-- /row -->

    <!-- Div oculta para estado vazio (usada pelo JS ao remover último item) -->
    <div id="cestaVazio" class="card border-0 text-center py-5" style="display:none">
        <div class="card-body">
            <i class="bi bi-cart-x fs-1 text-muted opacity-25 d-block mb-3"></i>
            <h5 class="text-muted">Cesta esvaziada!</h5>
            <a href="<?= BASE_URL ?>selecionar.php" class="btn btn-warning mt-2">
                <i class="bi bi-check2-square me-1"></i>Selecionar Produtos
            </a>
        </div>
    </div>

<?php endif; ?>

<script>
    const BASE_URL = '<?= BASE_URL ?>';
</script>

<?php require_once __DIR__ . '/views/partials/footer.php'; ?>
