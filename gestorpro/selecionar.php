<?php
/**
 * GestorPro - Seleção de Produtos para a Cesta
 * ÁREA 3: Exibe produtos com checkbox; valida seleção antes de adicionar à cesta
 */

require_once __DIR__ . '/config/session.php';
exigirLogin();

require_once __DIR__ . '/models/Produto.php';
require_once __DIR__ . '/models/Cesta.php';

$usuario      = getUsuarioLogado();
$produtoModel = new Produto();
$cestaModel   = new Cesta();

$produtos     = $produtoModel->listarDisponiveisParaSelecao((int) $usuario['id']);
$idsNaCesta   = $cestaModel->idsNaCesta((int) $usuario['id']);

$tituloPagina = 'Selecionar Produtos';
?>
<?php require_once __DIR__ . '/views/partials/header.php'; ?>

<div id="alertaContainer"></div>
<div id="spinnerOverlay" class="spinner-overlay">
    <div class="spinner-border text-success" style="width:3rem;height:3rem;"></div>
</div>

<!-- Cabeçalho -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-1 fw-bold">
            <i class="bi bi-check2-square text-warning me-2"></i>Selecionar Produtos
        </h4>
        <p class="text-muted mb-0">
            Marque os produtos desejados e clique em <strong>"Adicionar à Cesta"</strong>.
        </p>
    </div>
    <a href="<?= BASE_URL ?>cesta.php" class="btn btn-outline-success">
        <i class="bi bi-cart3 me-1"></i>Ver Cesta
    </a>
</div>

<!-- Barra de ação fixa -->
<div class="card border-0 mb-4 sticky-top" style="top:65px; z-index:100">
    <div class="card-body py-2 d-flex flex-wrap justify-content-between align-items-center gap-2">
        <span id="contadorSelecionados" class="text-muted">
            <i class="bi bi-check2 me-1"></i>Nenhum produto selecionado
        </span>
        <div class="d-flex gap-2">
            <button class="btn btn-sm btn-outline-secondary" onclick="desmarcarTodos()">
                <i class="bi bi-x-square me-1"></i>Desmarcar Todos
            </button>
            <button class="btn btn-sm btn-secondary" id="btnAdicionarCesta"
                    onclick="adicionarCestaAjax()" disabled>
                <i class="bi bi-cart-plus me-1"></i>Adicionar à Cesta
            </button>
        </div>
    </div>
</div>

<?php if (empty($produtos)): ?>
    <!-- Estado vazio -->
    <div class="card border-0 text-center py-5">
        <div class="card-body">
            <i class="bi bi-box fs-1 text-muted opacity-25 d-block mb-3"></i>
            <h5 class="text-muted">Nenhum produto disponível</h5>
            <p class="text-muted small">Cadastre produtos primeiro para poder selecioná-los.</p>
            <a href="<?= BASE_URL ?>produtos.php" class="btn btn-success">
                <i class="bi bi-plus-lg me-1"></i>Cadastrar Produto
            </a>
        </div>
    </div>

<?php else: ?>

    <!-- Grade de produtos com checkbox -->
    <div class="row g-3" id="gridProdutos">
        <?php foreach ($produtos as $produto): ?>
            <?php $jaEstaNaCesta = in_array($produto['id'], $idsNaCesta); ?>
            <div class="col-6 col-md-4 col-lg-3">
                <div class="produto-card h-100 p-3 <?= $jaEstaNaCesta ? 'opacity-50' : '' ?>"
                     id="card-<?= $produto['id'] ?>"
                     onclick="<?= $jaEstaNaCesta ? '' : "toggleSelecionarProduto({$produto['id']}, this)" ?>">

                    <!-- Ícone de seleção (checkbox visual) -->
                    <div class="check-overlay" id="check-<?= $produto['id'] ?>">
                        <i class="bi bi-check" style="display:none"></i>
                    </div>

                    <!-- Ícone do produto -->
                    <div class="text-center mb-2">
                        <div class="rounded-circle bg-success bg-opacity-10 d-inline-flex
                                    align-items-center justify-content-center"
                             style="width:52px;height:52px">
                            <i class="bi bi-box text-success fs-4"></i>
                        </div>
                    </div>

                    <!-- Dados do produto -->
                    <h6 class="fw-semibold mb-1 text-truncate" title="<?= htmlspecialchars($produto['nome']) ?>">
                        <?= htmlspecialchars($produto['nome']) ?>
                    </h6>
                    <small class="text-muted d-block mb-2">
                        <i class="bi bi-building me-1"></i>
                        <?= htmlspecialchars($produto['fornecedor_nome']) ?>
                    </small>

                    <?php if (!empty($produto['descricao'])): ?>
                        <small class="text-muted d-block mb-2" style="font-size:.78rem">
                            <?= htmlspecialchars(mb_strimwidth($produto['descricao'], 0, 60, '...')) ?>
                        </small>
                    <?php endif; ?>

                    <div class="d-flex justify-content-between align-items-center mt-auto">
                        <span class="preco">R$ <?= number_format($produto['preco'], 2, ',', '.') ?></span>
                        <?php if ($jaEstaNaCesta): ?>
                            <span class="badge bg-success">
                                <i class="bi bi-cart-check me-1"></i>Na cesta
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Instrução ao usuário -->
    <div class="alert alert-info d-flex align-items-center gap-2 mt-4" role="alert">
        <i class="bi bi-info-circle-fill flex-shrink-0"></i>
        <div>
            <strong>Dica:</strong> Clique nos cards para selecioná-los.
            Produtos já adicionados à cesta aparecem esmaecidos.
            Cada produto é adicionado com <strong>1 unidade</strong>.
        </div>
    </div>

<?php endif; ?>

<script>
    // Variável BASE_URL disponível para o JS
    const BASE_URL = '<?= BASE_URL ?>';

    /**
     * Desmarca todos os produtos selecionados
     */
    function desmarcarTodos() {
        produtosSelecionados.forEach(id => {
            const card  = document.getElementById('card-' + id);
            const check = document.getElementById('check-' + id);
            if (card)  card.classList.remove('selecionado');
            if (check) check.querySelector('i').style.display = 'none';
        });
        produtosSelecionados.clear();
        atualizarContadorSelecao();
    }

    // Override do toggleSelecionarProduto para atualizar o ícone visual do check
    const _toggleOriginal = toggleSelecionarProduto;
    window.toggleSelecionarProduto = function (id, card) {
        _toggleOriginal(id, card);
        const check = document.getElementById('check-' + id);
        if (check) {
            check.querySelector('i').style.display =
                produtosSelecionados.has(id) ? 'block' : 'none';
        }
    };
</script>

<?php require_once __DIR__ . '/views/partials/footer.php'; ?>
