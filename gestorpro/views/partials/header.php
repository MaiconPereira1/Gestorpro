<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($tituloPagina ?? 'GestorPro') ?> — GestorPro</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <!-- CSS customizado -->
    <link href="<?= BASE_URL ?>public/css/style.css" rel="stylesheet">
    <!-- BASE_URL global para o JavaScript -->
    <script>const BASE_URL = '<?= BASE_URL ?>';</script>
</head>
<body>

<?php
$usuario = getUsuarioLogado();
$paginaAtual = basename($_SERVER['PHP_SELF'], '.php');
?>

<!-- ====== NAVBAR ====== -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm sticky-top">
    <div class="container-fluid px-4">

        <!-- Logo -->
        <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="<?= BASE_URL ?>dashboard.php">
            <i class="bi bi-box-seam-fill text-success"></i>
            GestorPro
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMenu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarMenu">
            <!-- Links principais -->
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?= $paginaAtual === 'dashboard' ? 'active' : '' ?>"
                       href="<?= BASE_URL ?>dashboard.php">
                        <i class="bi bi-speedometer2 me-1"></i>Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $paginaAtual === 'produtos' ? 'active' : '' ?>"
                       href="<?= BASE_URL ?>produtos.php">
                        <i class="bi bi-box me-1"></i>Produtos
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $paginaAtual === 'fornecedores' ? 'active' : '' ?>"
                       href="<?= BASE_URL ?>fornecedores.php">
                        <i class="bi bi-building me-1"></i>Fornecedores
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $paginaAtual === 'selecionar' ? 'active' : '' ?>"
                       href="<?= BASE_URL ?>selecionar.php">
                        <i class="bi bi-check2-square me-1"></i>Selecionar Produtos
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link position-relative <?= $paginaAtual === 'cesta' ? 'active' : '' ?>"
                       href="<?= BASE_URL ?>cesta.php" id="linkCesta">
                        <i class="bi bi-cart3 me-1"></i>Cesta
                        <span class="badge bg-success rounded-pill ms-1" id="badgeCesta" style="font-size:.65rem">0</span>
                    </a>
                </li>
            </ul>

            <!-- Usuário logado -->
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center gap-1"
                       href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle"></i>
                        <?= htmlspecialchars($usuario['nome']) ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow">
                        <li>
                            <a class="dropdown-item text-danger" href="<?= BASE_URL ?>logout.php">
                                <i class="bi bi-box-arrow-right me-2"></i>Sair
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- ====== CONTEÚDO PRINCIPAL ====== -->
<main class="container-fluid px-4 py-4">
