/**
 * GestorPro - JavaScript principal
 * Gerencia AJAX para Produtos, Fornecedores e Cesta
 */

/* ===================================================
   UTILITÁRIOS
   =================================================== */

/**
 * Exibe um alerta toast no canto superior direito
 */
function exibirAlerta(mensagem, tipo = 'success', duracao = 4000) {
    const container = document.getElementById('alertaContainer');
    if (!container) return;

    const id   = 'alerta-' + Date.now();
    const html = `
        <div id="${id}" class="alert alert-${tipo} alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-${tipo === 'success' ? 'check-circle' : tipo === 'danger' ? 'x-circle' : 'info-circle'} me-2"></i>
            ${mensagem}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>`;

    container.insertAdjacentHTML('beforeend', html);

    setTimeout(() => {
        const el = document.getElementById(id);
        if (el) el.remove();
    }, duracao);
}

/**
 * Mostra/esconde o spinner de carregamento
 */
function setSpinner(ativo) {
    const spinner = document.getElementById('spinnerOverlay');
    if (spinner) spinner.classList.toggle('ativo', ativo);
}

/**
 * Formata valor monetário em reais
 */
function formatarMoeda(valor) {
    return parseFloat(valor).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
}

/**
 * Requisição AJAX genérica
 */
async function ajax(url, metodo = 'GET', corpo = null) {
    const opcoes = {
        method: metodo,
        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
    };
    if (corpo) opcoes.body = JSON.stringify(corpo);

    const resp = await fetch(url, opcoes);
    return resp.json();
}

/* ===================================================
   BADGE DA CESTA NA NAVBAR
   =================================================== */

async function atualizarBadgeCesta() {
    try {
        const dados = await ajax(BASE_URL + 'api/cesta.php?acao=resumo');
        const badge = document.getElementById('badgeCesta');
        if (badge && dados.sucesso) {
            badge.textContent = dados.total_itens;
            badge.style.display = dados.total_itens > 0 ? 'inline' : 'none';
        }
    } catch (_) { /* silencioso */ }
}

/* ===================================================
   CRUD DE PRODUTOS (AJAX)
   =================================================== */

let tabelaProdutos = null;

async function carregarProdutos() {
    try {
        const dados = await ajax(BASE_URL + 'api/produtos.php?acao=listar');
        const tbody = document.getElementById('tbodyProdutos');
        if (!tbody || !dados.sucesso) return;

        if (dados.dados.length === 0) {
            tbody.innerHTML = `<tr><td colspan="6" class="text-center text-muted py-4">
                <i class="bi bi-inbox fs-4 d-block mb-1"></i>Nenhum produto cadastrado ainda.</td></tr>`;
            return;
        }

        tbody.innerHTML = dados.dados.map(p => `
            <tr>
                <td>${p.id}</td>
                <td class="fw-semibold">${escHtml(p.nome)}</td>
                <td>${escHtml(p.fornecedor_nome)}</td>
                <td>${escHtml(p.descricao || '—')}</td>
                <td class="text-success fw-semibold">${formatarMoeda(p.preco)}</td>
                <td>
                    <button class="btn btn-sm btn-outline-primary me-1"
                            onclick="editarProduto(${p.id})" title="Editar">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger"
                            onclick="confirmarExclusaoProduto(${p.id}, '${escHtml(p.nome)}')" title="Excluir">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>`).join('');
    } catch (e) {
        console.error('Erro ao carregar produtos:', e);
    }
}

async function salvarProduto(event) {
    event.preventDefault();
    const form   = document.getElementById('formProduto');
    const id     = document.getElementById('produtoId').value;
    const acao   = id ? 'atualizar' : 'cadastrar';
    const url    = BASE_URL + `api/produtos.php?acao=${acao}${id ? '&id=' + id : ''}`;
    const metodo = id ? 'PUT' : 'POST';

    const corpo = {
        nome:          document.getElementById('produtoNome').value,
        fornecedor_id: document.getElementById('produtoFornecedor').value,
        descricao:     document.getElementById('produtoDescricao').value,
        preco:         document.getElementById('produtoPreco').value,
    };

    setSpinner(true);
    try {
        const resp = await ajax(url, metodo, corpo);
        if (resp.sucesso) {
            exibirAlerta(resp.mensagem, 'success');
            form.reset();
            document.getElementById('produtoId').value = '';
            document.getElementById('btnSalvarProduto').innerHTML =
                '<i class="bi bi-plus-lg me-1"></i>Cadastrar Produto';
            document.getElementById('btnCancelarEdicaoProduto').style.display = 'none';
            await carregarProdutos();
        } else {
            exibirAlerta(resp.mensagem, 'danger');
        }
    } catch (e) {
        exibirAlerta('Erro de comunicação. Tente novamente.', 'danger');
    } finally {
        setSpinner(false);
    }
}

async function editarProduto(id) {
    try {
        const resp = await ajax(BASE_URL + `api/produtos.php?acao=buscar&id=${id}`);
        if (!resp.sucesso) { exibirAlerta(resp.mensagem, 'danger'); return; }

        const p = resp.dados;
        document.getElementById('produtoId').value            = p.id;
        document.getElementById('produtoNome').value          = p.nome;
        document.getElementById('produtoFornecedor').value    = p.fornecedor_id;
        document.getElementById('produtoDescricao').value     = p.descricao || '';
        document.getElementById('produtoPreco').value         = p.preco;
        document.getElementById('btnSalvarProduto').innerHTML =
            '<i class="bi bi-check-lg me-1"></i>Salvar Alterações';
        document.getElementById('btnCancelarEdicaoProduto').style.display = 'inline-block';

        document.getElementById('formProduto').scrollIntoView({ behavior: 'smooth' });
    } catch (e) {
        exibirAlerta('Erro ao carregar produto.', 'danger');
    }
}

function cancelarEdicaoProduto() {
    document.getElementById('formProduto').reset();
    document.getElementById('produtoId').value = '';
    document.getElementById('btnSalvarProduto').innerHTML = '<i class="bi bi-plus-lg me-1"></i>Cadastrar Produto';
    document.getElementById('btnCancelarEdicaoProduto').style.display = 'none';
}

function confirmarExclusaoProduto(id, nome) {
    if (!confirm(`Deseja excluir o produto "${nome}"?`)) return;
    excluirProduto(id);
}

async function excluirProduto(id) {
    setSpinner(true);
    try {
        const resp = await ajax(BASE_URL + `api/produtos.php?acao=excluir&id=${id}`, 'DELETE');
        exibirAlerta(resp.mensagem, resp.sucesso ? 'success' : 'danger');
        if (resp.sucesso) await carregarProdutos();
    } catch (e) {
        exibirAlerta('Erro ao excluir produto.', 'danger');
    } finally {
        setSpinner(false);
    }
}

/* ===================================================
   CRUD DE FORNECEDORES (AJAX)
   =================================================== */

async function carregarFornecedores() {
    try {
        const dados = await ajax(BASE_URL + 'api/fornecedores.php?acao=listar');
        const tbody = document.getElementById('tbodyFornecedores');
        if (!tbody || !dados.sucesso) return;

        if (dados.dados.length === 0) {
            tbody.innerHTML = `<tr><td colspan="5" class="text-center text-muted py-4">
                <i class="bi bi-inbox fs-4 d-block mb-1"></i>Nenhum fornecedor cadastrado ainda.</td></tr>`;
            return;
        }

        tbody.innerHTML = dados.dados.map(f => `
            <tr>
                <td>${f.id}</td>
                <td class="fw-semibold">${escHtml(f.razao_social)}</td>
                <td>${escHtml(f.cnpj)}</td>
                <td>${escHtml(f.email || '—')}</td>
                <td>${escHtml(f.telefone || '—')}</td>
                <td>
                    <button class="btn btn-sm btn-outline-primary me-1"
                            onclick="editarFornecedor(${f.id})" title="Editar">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger"
                            onclick="confirmarExclusaoFornecedor(${f.id}, '${escHtml(f.razao_social)}')" title="Excluir">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>`).join('');
    } catch (e) {
        console.error('Erro ao carregar fornecedores:', e);
    }
}

async function salvarFornecedor(event) {
    event.preventDefault();
    const id     = document.getElementById('fornecedorId').value;
    const acao   = id ? 'atualizar' : 'cadastrar';
    const url    = BASE_URL + `api/fornecedores.php?acao=${acao}${id ? '&id=' + id : ''}`;
    const metodo = id ? 'PUT' : 'POST';

    const corpo = {
        razao_social: document.getElementById('fornecedorRazaoSocial').value,
        cnpj:         document.getElementById('fornecedorCnpj').value,
        email:        document.getElementById('fornecedorEmail').value,
        telefone:     document.getElementById('fornecedorTelefone').value,
    };

    setSpinner(true);
    try {
        const resp = await ajax(url, metodo, corpo);
        if (resp.sucesso) {
            exibirAlerta(resp.mensagem, 'success');
            document.getElementById('formFornecedor').reset();
            document.getElementById('fornecedorId').value = '';
            document.getElementById('btnSalvarFornecedor').innerHTML =
                '<i class="bi bi-plus-lg me-1"></i>Cadastrar Fornecedor';
            document.getElementById('btnCancelarEdicaoFornecedor').style.display = 'none';
            await carregarFornecedores();
        } else {
            exibirAlerta(resp.mensagem, 'danger');
        }
    } catch (e) {
        exibirAlerta('Erro de comunicação.', 'danger');
    } finally {
        setSpinner(false);
    }
}

async function editarFornecedor(id) {
    try {
        const resp = await ajax(BASE_URL + `api/fornecedores.php?acao=buscar&id=${id}`);
        if (!resp.sucesso) { exibirAlerta(resp.mensagem, 'danger'); return; }

        const f = resp.dados;
        document.getElementById('fornecedorId').value           = f.id;
        document.getElementById('fornecedorRazaoSocial').value  = f.razao_social;
        document.getElementById('fornecedorCnpj').value         = f.cnpj;
        document.getElementById('fornecedorEmail').value        = f.email || '';
        document.getElementById('fornecedorTelefone').value     = f.telefone || '';
        document.getElementById('btnSalvarFornecedor').innerHTML =
            '<i class="bi bi-check-lg me-1"></i>Salvar Alterações';
        document.getElementById('btnCancelarEdicaoFornecedor').style.display = 'inline-block';

        document.getElementById('formFornecedor').scrollIntoView({ behavior: 'smooth' });
    } catch (e) {
        exibirAlerta('Erro ao carregar fornecedor.', 'danger');
    }
}

function cancelarEdicaoFornecedor() {
    document.getElementById('formFornecedor').reset();
    document.getElementById('fornecedorId').value = '';
    document.getElementById('btnSalvarFornecedor').innerHTML =
        '<i class="bi bi-plus-lg me-1"></i>Cadastrar Fornecedor';
    document.getElementById('btnCancelarEdicaoFornecedor').style.display = 'none';
}

function confirmarExclusaoFornecedor(id, nome) {
    if (!confirm(`Deseja excluir o fornecedor "${nome}"?`)) return;
    excluirFornecedor(id);
}

async function excluirFornecedor(id) {
    setSpinner(true);
    try {
        const resp = await ajax(BASE_URL + `api/fornecedores.php?acao=excluir&id=${id}`, 'DELETE');
        exibirAlerta(resp.mensagem, resp.sucesso ? 'success' : 'danger');
        if (resp.sucesso) await carregarFornecedores();
    } catch (e) {
        exibirAlerta('Erro ao excluir fornecedor.', 'danger');
    } finally {
        setSpinner(false);
    }
}

/* ===================================================
   SELEÇÃO DE PRODUTOS (tela selecionar.php)
   =================================================== */

const produtosSelecionados = new Set();

function toggleSelecionarProduto(id, card) {
    if (produtosSelecionados.has(id)) {
        produtosSelecionados.delete(id);
        card.classList.remove('selecionado');
    } else {
        produtosSelecionados.add(id);
        card.classList.add('selecionado');
    }
    atualizarContadorSelecao();
}

function atualizarContadorSelecao() {
    const n      = produtosSelecionados.size;
    const count  = document.getElementById('contadorSelecionados');
    const btnAdd = document.getElementById('btnAdicionarCesta');

    if (count) count.textContent = `${n} produto(s) selecionado(s)`;
    if (btnAdd) {
        btnAdd.disabled = n === 0;
        btnAdd.classList.toggle('btn-success', n > 0);
        btnAdd.classList.toggle('btn-secondary', n === 0);
    }
}

async function adicionarCestaAjax() {
    // Validação: pelo menos um produto deve estar selecionado
    if (produtosSelecionados.size === 0) {
        exibirAlerta('Selecione ao menos um produto para adicionar à cesta!', 'warning');
        return;
    }

    setSpinner(true);
    try {
        const resp = await ajax(BASE_URL + 'api/cesta.php?acao=adicionar', 'POST', {
            produtos: Array.from(produtosSelecionados),
        });

        if (resp.sucesso) {
            exibirAlerta(resp.mensagem, 'success');
            produtosSelecionados.clear();
            document.querySelectorAll('.produto-card.selecionado')
                    .forEach(c => c.classList.remove('selecionado'));
            atualizarContadorSelecao();
            await atualizarBadgeCesta();
        } else {
            exibirAlerta(resp.mensagem, 'danger');
        }
    } catch (e) {
        exibirAlerta('Erro ao adicionar à cesta.', 'danger');
    } finally {
        setSpinner(false);
    }
}

/* ===================================================
   CESTA (tela cesta.php)
   =================================================== */

async function removerItemCesta(itemId) {
    if (!confirm('Remover este item da cesta?')) return;

    setSpinner(true);
    try {
        const resp = await ajax(BASE_URL + 'api/cesta.php?acao=remover', 'POST', { item_id: itemId });
        if (resp.sucesso) {
            exibirAlerta(resp.mensagem, 'success');
            // Remove a linha da tabela e atualiza totais
            const linha = document.getElementById('item-' + itemId);
            if (linha) linha.remove();
            await atualizarResumosCesta();
            await atualizarBadgeCesta();
        } else {
            exibirAlerta(resp.mensagem, 'danger');
        }
    } catch (e) {
        exibirAlerta('Erro ao remover item.', 'danger');
    } finally {
        setSpinner(false);
    }
}

async function esvaziarCesta() {
    if (!confirm('Deseja esvaziar toda a cesta?')) return;

    setSpinner(true);
    try {
        const resp = await ajax(BASE_URL + 'api/cesta.php?acao=esvaziar', 'POST');
        if (resp.sucesso) {
            exibirAlerta(resp.mensagem, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            exibirAlerta(resp.mensagem, 'danger');
        }
    } catch (e) {
        exibirAlerta('Erro ao esvaziar cesta.', 'danger');
    } finally {
        setSpinner(false);
    }
}

async function atualizarResumosCesta() {
    const resp = await ajax(BASE_URL + 'api/cesta.php?acao=resumo');
    if (!resp.sucesso) return;

    const elTotal   = document.getElementById('cestaTotal');
    const elQtd     = document.getElementById('cestaQuantidade');
    const elVazio   = document.getElementById('cestaVazio');
    const elTabela  = document.getElementById('cestaTabela');

    if (elTotal) elTotal.textContent   = resp.valor_formatado;
    if (elQtd)   elQtd.textContent     = resp.total_itens + ' produto(s)';

    if (resp.total_itens === 0) {
        if (elTabela) elTabela.style.display = 'none';
        if (elVazio)  elVazio.style.display  = 'block';
    }
}

/* ===================================================
   SEGURANÇA - escapa HTML para evitar XSS
   =================================================== */
function escHtml(str) {
    const el = document.createElement('span');
    el.textContent = str || '';
    return el.innerHTML;
}

/* ===================================================
   INICIALIZAÇÃO
   =================================================== */
document.addEventListener('DOMContentLoaded', function () {
    // Atualiza badge da cesta na navbar
    atualizarBadgeCesta();

    // Carrega dados via AJAX conforme a página atual
    if (document.getElementById('tbodyProdutos'))    carregarProdutos();
    if (document.getElementById('tbodyFornecedores')) carregarFornecedores();

    // Contador inicial da seleção
    if (document.getElementById('contadorSelecionados')) atualizarContadorSelecao();
});
