<?php
/**
 * GestorPro - Gerenciamento de Produtos
 * ÁREA 1: Cadastro no banco de dados
 * ÁREA 2: Atualização via AJAX (listagem, edição, exclusão sem reload)
 */

require_once __DIR__ . '/config/session.php';
exigirLogin();

require_once __DIR__ . '/models/Fornecedor.php';

$fornecedorModel = new Fornecedor();
$fornecedores    = $fornecedorModel->listarParaSelect();

$tituloPagina = 'Produtos';
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
            <i class="bi bi-box text-success me-2"></i>Gerenciamento de Produtos
        </h4>
        <p class="text-muted mb-0">Cadastre, edite e remova produtos. A tabela atualiza via <strong>AJAX</strong>.</p>
    </div>
</div>

<!-- ====== ÁREA 1: FORMULÁRIO DE CADASTRO / EDIÇÃO ====== -->
<div class="card border-0 mb-4">
    <div class="card-header d-flex align-items-center gap-2">
        <i class="bi bi-plus-circle text-success"></i>
        <span id="tituloFormProduto">Cadastrar Novo Produto</span>
    </div>
    <div class="card-body">
        <!-- input hidden para armazenar o ID durante edição -->
        <input type="hidden" id="produtoId">

        <form id="formProduto" onsubmit="salvarProduto(event)" novalidate>
            <div class="row g-3">

                <div class="col-md-6">
                    <label for="produtoNome" class="form-label">
                        Nome do Produto <span class="text-danger">*</span>
                    </label>
                    <input type="text" class="form-control" id="produtoNome"
                           placeholder="Ex: Notebook Dell Inspiron 15" required>
                </div>

                <div class="col-md-6">
                    <label for="produtoFornecedor" class="form-label">
                        Fornecedor <span class="text-danger">*</span>
                    </label>
                    <select class="form-select" id="produtoFornecedor" required>
                        <option value="">— Selecione um fornecedor —</option>
                        <?php foreach ($fornecedores as $f): ?>
                            <option value="<?= $f['id'] ?>">
                                <?= htmlspecialchars($f['razao_social']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (empty($fornecedores)): ?>
                        <div class="form-text text-warning">
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            Nenhum fornecedor cadastrado.
                            <a href="<?= BASE_URL ?>fornecedores.php">Cadastrar fornecedor</a>.
                        </div>
                    <?php endif; ?>
                </div>

                <div class="col-md-8">
                    <label for="produtoDescricao" class="form-label">Descrição</label>
                    <input type="text" class="form-control" id="produtoDescricao"
                           placeholder="Breve descrição do produto (opcional)">
                </div>

                <div class="col-md-4">
                    <label for="produtoPreco" class="form-label">
                        Preço (R$) <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text">R$</span>
                        <input type="number" class="form-control" id="produtoPreco"
                               placeholder="0,00" step="0.01" min="0" required>
                    </div>
                </div>

            </div><!-- /row -->

            <div class="d-flex gap-2 mt-3">
                <button type="submit" class="btn btn-success" id="btnSalvarProduto">
                    <i class="bi bi-plus-lg me-1"></i>Cadastrar Produto
                </button>
                <button type="button" class="btn btn-outline-secondary" id="btnCancelarEdicaoProduto"
                        style="display:none" onclick="cancelarEdicaoProduto()">
                    <i class="bi bi-x-lg me-1"></i>Cancelar Edição
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ====== ÁREA 2: LISTAGEM VIA AJAX ====== -->
<div class="card border-0">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>
            <i class="bi bi-table me-2 text-muted"></i>Lista de Produtos
            <span class="badge bg-success ms-2" id="badgeTotalProdutos" title="Atualizado via AJAX">AJAX</span>
        </span>
        <button class="btn btn-sm btn-outline-secondary" onclick="carregarProdutos()">
            <i class="bi bi-arrow-clockwise me-1"></i>Atualizar
        </button>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th style="width:60px">#</th>
                        <th>Produto</th>
                        <th>Fornecedor</th>
                        <th>Descrição</th>
                        <th style="width:120px">Preço</th>
                        <th style="width:110px" class="text-center">Ações</th>
                    </tr>
                </thead>
                <tbody id="tbodyProdutos">
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">
                            <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                            Carregando...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Script específico desta página -->
<script>
    // Atualiza o título do formulário quando editando
    document.getElementById('produtoId').addEventListener('change', function () {
        const titulo = document.getElementById('tituloFormProduto');
        titulo.textContent = this.value ? 'Editar Produto' : 'Cadastrar Novo Produto';
    });
</script>

<?php require_once __DIR__ . '/views/partials/footer.php'; ?>
