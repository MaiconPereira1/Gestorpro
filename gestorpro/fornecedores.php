<?php
/**
 * GestorPro - Gerenciamento de Fornecedores
 * ÁREA 1: Cadastro no banco de dados
 * ÁREA 2: Atualização via AJAX
 */

require_once __DIR__ . '/config/session.php';
exigirLogin();

$tituloPagina = 'Fornecedores';
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
            <i class="bi bi-building text-primary me-2"></i>Gerenciamento de Fornecedores
        </h4>
        <p class="text-muted mb-0">Cadastre, edite e remova fornecedores. A tabela atualiza via <strong>AJAX</strong>.</p>
    </div>
</div>

<!-- ====== ÁREA 1: FORMULÁRIO ====== -->
<div class="card border-0 mb-4">
    <div class="card-header d-flex align-items-center gap-2">
        <i class="bi bi-plus-circle text-primary"></i>
        <span id="tituloFormFornecedor">Cadastrar Novo Fornecedor</span>
    </div>
    <div class="card-body">
        <input type="hidden" id="fornecedorId">

        <form id="formFornecedor" onsubmit="salvarFornecedor(event)" novalidate>
            <div class="row g-3">

                <div class="col-md-6">
                    <label for="fornecedorRazaoSocial" class="form-label">
                        Razão Social <span class="text-danger">*</span>
                    </label>
                    <input type="text" class="form-control" id="fornecedorRazaoSocial"
                           placeholder="Nome da empresa" required>
                </div>

                <div class="col-md-6">
                    <label for="fornecedorCnpj" class="form-label">
                        CNPJ <span class="text-danger">*</span>
                    </label>
                    <input type="text" class="form-control" id="fornecedorCnpj"
                           placeholder="00.000.000/0000-00" required>
                </div>

                <div class="col-md-6">
                    <label for="fornecedorEmail" class="form-label">E-mail</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                        <input type="email" class="form-control" id="fornecedorEmail"
                               placeholder="contato@empresa.com">
                    </div>
                </div>

                <div class="col-md-6">
                    <label for="fornecedorTelefone" class="form-label">Telefone</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                        <input type="text" class="form-control" id="fornecedorTelefone"
                               placeholder="(00) 00000-0000">
                    </div>
                </div>

            </div>

            <div class="d-flex gap-2 mt-3">
                <button type="submit" class="btn btn-primary" id="btnSalvarFornecedor">
                    <i class="bi bi-plus-lg me-1"></i>Cadastrar Fornecedor
                </button>
                <button type="button" class="btn btn-outline-secondary" id="btnCancelarEdicaoFornecedor"
                        style="display:none" onclick="cancelarEdicaoFornecedor()">
                    <i class="bi bi-x-lg me-1"></i>Cancelar Edição
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ====== ÁREA 2: LISTAGEM AJAX ====== -->
<div class="card border-0">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>
            <i class="bi bi-table me-2 text-muted"></i>Lista de Fornecedores
            <span class="badge bg-primary ms-2">AJAX</span>
        </span>
        <button class="btn btn-sm btn-outline-secondary" onclick="carregarFornecedores()">
            <i class="bi bi-arrow-clockwise me-1"></i>Atualizar
        </button>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th style="width:60px">#</th>
                        <th>Razão Social</th>
                        <th>CNPJ</th>
                        <th>E-mail</th>
                        <th>Telefone</th>
                        <th style="width:110px" class="text-center">Ações</th>
                    </tr>
                </thead>
                <tbody id="tbodyFornecedores">
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

<script>
    document.getElementById('fornecedorId').addEventListener('change', function () {
        const titulo = document.getElementById('tituloFormFornecedor');
        titulo.textContent = this.value ? 'Editar Fornecedor' : 'Cadastrar Novo Fornecedor';
    });
</script>

<?php require_once __DIR__ . '/views/partials/footer.php'; ?>
