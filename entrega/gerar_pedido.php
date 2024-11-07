<?php
require_once '../db.php';

// Fetch clients, products, deliverers, and payment methods
$clientes = $pdo->query("SELECT id, nome FROM clientes")->fetchAll(PDO::FETCH_ASSOC);
$produtos = $pdo->query("SELECT id, nome, preco, codigo FROM produtos WHERE status = 'ativo'")->fetchAll(PDO::FETCH_ASSOC);
$entregadores = $pdo->query("SELECT id, nome FROM entregadores WHERE status = 'ativo'")->fetchAll(PDO::FETCH_ASSOC);
$formas_pagamento = $pdo->query("SELECT id, descricao FROM formas_pagamento WHERE descricao IN ('Dinheiro', 'Cartão de Crédito', 'Cartão de Débito', 'Pix')")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cliente_id = $_POST['cliente_id'];
    $entregador_id = $_POST['entregador_id'];
    $produtos_selecionados = json_decode($_POST['produtos'], true);
    $forma_pagamento_id = $_POST['forma_pagamento_id'];
    $valor_recebido = isset($_POST['valor_recebido']) ? floatval(str_replace(',', '.', str_replace('.', '', $_POST['valor_recebido']))) : 0.0;
    $total = 0.0;

    foreach ($produtos_selecionados as $produto) {
        $total += floatval($produto['preco']) * intval($produto['quantidade']);
    }

    if ($forma_pagamento_id != 1) {
        $valor_recebido = $total; // For non-cash payments, the received amount is the total amount
    }

    $troco = $valor_recebido - $total;

    // Insere o pedido
    $stmt = $pdo->prepare("INSERT INTO pedidos (cliente_id, total, entregador_id, forma_pagamento_id, valor_recebido, troco) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$cliente_id, $total, $entregador_id, $forma_pagamento_id, $valor_recebido, $troco]);
    $pedido_id = $pdo->lastInsertId();

    // Insere os itens do pedido
    $stmt = $pdo->prepare("INSERT INTO pedido_itens (pedido_id, produto_id, quantidade, preco) VALUES (?, ?, ?, ?)");
    foreach ($produtos_selecionados as $produto) {
        $stmt->execute([$pedido_id, $produto['id'], intval($produto['quantidade']), floatval($produto['preco'])]);
    }

    // Redireciona para a página de impressão do cupom
    header("Location: imprime_cupom.php?pedido_id=$pedido_id");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Gerar Pedido de Entrega</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
</head>
<body>
<div class="container">
    <h1>Gerar Pedido de Entrega</h1>
    <form method="post" id="pedidoForm">
        <div class="mb-3">
            <label for="cliente_id" class="form-label">Cliente</label>
            <select name="cliente_id" id="cliente_id" class="form-select">
                <?php foreach ($clientes as $cliente): ?>
                    <option value="<?= $cliente['id'] ?>"><?= $cliente['nome'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Adicionar Produto por Código</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" id="produto_codigo" placeholder="Digite o código do produto">
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">Selecionar Produto da Lista</label>
            <select class="form-select" id="lista_produtos">
                <option value="">Selecione um produto</option>
                <?php foreach ($produtos as $produto): ?>
                    <option value="<?= $produto['id'] ?>"><?= $produto['nome'] ?> (Código: <?= $produto['codigo'] ?>) - R$<?= number_format($produto['preco'], 2) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="button" class="btn btn-outline-secondary mt-2" id="adicionarProdutoLista">Adicionar Produto da Lista</button>
        </div>
        <div id="produtos-selecionados" class="mb-3">
            <!-- Produtos adicionados aparecerão aqui -->
        </div>
        <div class="mb-3">
            <label for="entregador_id" class="form-label">Entregador</label>
            <select name="entregador_id" id="entregador_id" class="form-select">
                <?php foreach ($entregadores as $entregador): ?>
                    <option value="<?= $entregador['id'] ?>"><?= $entregador['nome'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="forma_pagamento_id" class="form-label">Forma de Pagamento</label>
            <select name="forma_pagamento_id" id="forma_pagamento_id" class="form-select">
                <?php foreach ($formas_pagamento as $forma): ?>
                    <option value="<?= $forma['id'] ?>"><?= $forma['descricao'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3" id="valor_recebido_div">
            <label for="valor_recebido" class="form-label">Valor Recebido (para Dinheiro)</label>
            <input type="text" name="valor_recebido" id="valor_recebido" class="form-control">
        </div>
        <div class="mb-3">
            <p>Total: R$ <span id="total">0.00</span></p>
        </div>
        <button type="submit" class="btn btn-primary">Gerar Pedido</button>
    </form>
</div>

<script>
let produtos = <?= json_encode($produtos) ?>;
let produtosSelecionados = [];

$(document).ready(function() {
    $('#produto_codigo').on('keypress', function(e) {
        if (e.which === 13) { // Enter key pressed
            let codigo = $('#produto_codigo').val().trim();
            let produto = produtos.find(p => p.codigo === codigo);
            if (produto) {
                adicionarProduto(produto);
                $('#produto_codigo').val('');
            } else {
                alert('Produto não encontrado!');
            }
            e.preventDefault();
        }
    });

    $('#adicionarProdutoLista').on('click', function() {
        let produtoId = $('#lista_produtos').val();
        let produto = produtos.find(p => p.id == produtoId);
        if (produto) {
            adicionarProduto(produto);
        }
    });

    $(document).on('change', '.quantidade', function() {
        let id = $(this).data('id');
        let quantidade = parseInt($(this).val());
        atualizarQuantidade(id, quantidade);
    });

    $(document).on('click', '.remover-produto', function() {
        let id = $(this).data('id');
        removerProduto(id);
    });

    $('#forma_pagamento_id').on('change', function() {
        var valorRecebidoDiv = $('#valor_recebido_div');
        if (this.value == '1') { // Assuming 1 is the ID for cash
            valorRecebidoDiv.show();
        } else {
            valorRecebidoDiv.hide();
        }
    });

    $('#valor_recebido').mask('000.000.000.000.000,00', {reverse: true});

    $('#pedidoForm').on('submit', function(e) {
        e.preventDefault();
        $('#pedidoForm').append('<input type="hidden" name="produtos" value=\'' + JSON.stringify(produtosSelecionados) + '\'>');
        this.submit();
    });
});

function adicionarProduto(produto) {
    if (produtosSelecionados.find(p => p.id === produto.id)) {
        alert('Produto já adicionado!');
        return;
    }
    produto.quantidade = 1;
    produtosSelecionados.push(produto);
    renderizarProdutos();
}

function atualizarQuantidade(id, quantidade) {
    let produto = produtosSelecionados.find(p => p.id === id);
    if (produto) {
        produto.quantidade = quantidade;
        renderizarProdutos();
    }
}

function removerProduto(id) {
    produtosSelecionados = produtosSelecionados.filter(p => p.id !== id);
    renderizarProdutos();
}

function renderizarProdutos() {
    let container = $('#produtos-selecionados');
    container.empty();
    let total = 0;

    produtosSelecionados.forEach(produto => {
        total += produto.preco * produto.quantidade;
        container.append(`
            <div class="mb-3 input-group">
                <input type="text" class="form-control" value="${produto.nome}" readonly>
                <input type="number" class="form-control quantidade" data-id="${produto.id}" value="${produto.quantidade}" min="1">
                <input type="text" class="form-control" value="R$${(produto.preco * produto.quantidade).toFixed(2)}" readonly>
                <button type="button" class="btn btn-danger remover-produto" data-id="${produto.id}">Remover</button>
            </div>
        `);
    });

    $('#total').text(total.toFixed(2));
}
</script>
</body>
</html>
