<?php
/**
 * GestorPro - Página de Login
 * Área 1 do sistema: autenticação com hash SHA-256
 */

require_once __DIR__ . '/config/session.php';

// Se já estiver logado, redireciona ao dashboard
if (estaLogado()) {
    header('Location: ' . BASE_URL . 'dashboard.php');
    exit;
}

require_once __DIR__ . '/controllers/AuthController.php';

$ctrl = new AuthController();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao'])) {
    if ($_POST['acao'] === 'login') {
        $ctrl->login();
    } elseif ($_POST['acao'] === 'cadastrar') {
        $ctrl->cadastrar();
    }
}

$erroLogin      = getFlash('erro_login');
$erroCadastro   = getFlash('erro_cadastro');
$sucessoCadastro = getFlash('sucesso_cadastro');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GestorPro — Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>public/css/style.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%); min-height: 100vh; }
        .login-card { max-width: 460px; border-radius: 16px; box-shadow: 0 20px 60px rgba(0,0,0,.4); }
        .logo-icon { width: 64px; height: 64px; background: linear-gradient(135deg, #198754, #20c997); border-radius: 16px;
                     display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; font-size: 2rem; }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center py-5">

<div class="container">
    <div class="login-card card mx-auto border-0">
        <div class="card-body p-4 p-md-5">

            <!-- Logo -->
            <div class="text-center mb-4">
                <div class="logo-icon"><i class="bi bi-box-seam-fill text-white"></i></div>
                <h3 class="fw-bold mb-1">GestorPro</h3>
                <p class="text-muted small">Mini Sistema de Gestão de Produtos</p>
            </div>

            <!-- Alertas -->
            <?php if ($sucessoCadastro): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($sucessoCadastro) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Abas: Login / Cadastro -->
            <ul class="nav nav-pills nav-fill mb-4" id="authTabs">
                <li class="nav-item">
                    <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tabLogin">
                        <i class="bi bi-box-arrow-in-right me-1"></i>Entrar
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tabCadastro">
                        <i class="bi bi-person-plus me-1"></i>Cadastrar
                    </button>
                </li>
            </ul>

            <div class="tab-content">

                <!-- ── TAB LOGIN ── -->
                <div class="tab-pane fade show active" id="tabLogin">
                    <?php if ($erroLogin): ?>
                        <div class="alert alert-danger small">
                            <i class="bi bi-exclamation-circle me-1"></i><?= htmlspecialchars($erroLogin) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="<?= BASE_URL ?>index.php" novalidate>
                        <input type="hidden" name="acao" value="login">

                        <div class="mb-3">
                            <label for="loginEmail" class="form-label">E-mail</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                <input type="email" class="form-control" id="loginEmail" name="email"
                                       placeholder="seu@email.com" required autofocus>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="loginSenha" class="form-label">Senha</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password" class="form-control" id="loginSenha" name="senha"
                                       placeholder="••••••••" required>
                                <button class="btn btn-outline-secondary" type="button"
                                        onclick="toggleSenha('loginSenha', this)">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-success w-100 py-2 fw-semibold">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Entrar no Sistema
                        </button>
                    </form>

                    <div class="text-center mt-3">
                        <small class="text-muted">
                            <i class="bi bi-shield-lock me-1"></i>
                            Senha protegida com hash SHA-256
                        </small>
                    </div>
                </div>

                <!-- ── TAB CADASTRO ── -->
                <div class="tab-pane fade" id="tabCadastro">
                    <?php if ($erroCadastro): ?>
                        <div class="alert alert-danger small">
                            <i class="bi bi-exclamation-circle me-1"></i><?= htmlspecialchars($erroCadastro) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="<?= BASE_URL ?>index.php" novalidate>
                        <input type="hidden" name="acao" value="cadastrar">

                        <div class="mb-3">
                            <label for="cadNome" class="form-label">Nome completo</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                <input type="text" class="form-control" id="cadNome" name="nome"
                                       placeholder="Seu nome" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="cadEmail" class="form-label">E-mail</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                <input type="email" class="form-control" id="cadEmail" name="email"
                                       placeholder="seu@email.com" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="cadSenha" class="form-label">Senha <small class="text-muted">(mín. 6 caracteres)</small></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password" class="form-control" id="cadSenha" name="senha"
                                       placeholder="••••••••" required minlength="6">
                                <button class="btn btn-outline-secondary" type="button"
                                        onclick="toggleSenha('cadSenha', this)">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="cadConfirmacao" class="form-label">Confirmar senha</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                <input type="password" class="form-control" id="cadConfirmacao" name="confirmacao"
                                       placeholder="••••••••" required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                            <i class="bi bi-person-check me-2"></i>Criar Conta
                        </button>
                    </form>
                </div>

            </div><!-- /tab-content -->
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function toggleSenha(inputId, btn) {
    const inp = document.getElementById(inputId);
    const icon = btn.querySelector('i');
    if (inp.type === 'password') {
        inp.type = 'text';
        icon.className = 'bi bi-eye-slash';
    } else {
        inp.type = 'password';
        icon.className = 'bi bi-eye';
    }
}
// Se houve erro no cadastro, abre a aba de cadastro
<?php if ($erroCadastro): ?>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelector('[data-bs-target="#tabCadastro"]').click();
});
<?php endif; ?>
</script>
</body>
</html>
