<?php
require_once '../db.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$usuario_id = $_SESSION['user_id'];

// Fetch open caixa for the user
$stmt = $pdo->prepare('SELECT id FROM caixa WHERE usuario_id = ? AND status = "Aberto"');
$stmt->execute([$usuario_id]);
$caixa = $stmt->fetch();
if ($caixa) {
    $caixa_id = $caixa['id'];
} else {
    $caixa_id = null;
}

$pedidos = $pdo->query("SELECT p.id, p.cliente_id, c.nome AS cliente_nome, p.total, e.nome AS entregador_nome, fp.descricao AS forma_pagamento, p.status, p.valor_recebido, p.troco 
                        FROM pedidos p 
                        JOIN clientes c ON p.cliente_id = c.id 
                        JOIN entregadores e ON p.entregador_id = e.id
                        JOIN formas_pagamento fp ON p.forma_pagamento_id = fp.id")->fetchAll(PDO::FETCH_ASSOC);

if (isset($_POST['action'])) {
    $pedido_id = $_POST['pedido_id'];
    $action = $_POST['action'];

    if ($action == 'cancelado') {
        $stmt = $pdo->prepare("DELETE FROM pedido_itens WHERE pedido_id = ?");
        $stmt->execute([$pedido_id]);

        $stmt = $pdo->prepare("DELETE FROM pedidos WHERE id = ?");
        $stmt->execute([$pedido_id]);
    } elseif ($action == 'em_entrega') {
        $stmt = $pdo->prepare("UPDATE pedidos SET status = ? WHERE id = ?");
        $stmt->execute([$action, $pedido_id]);
    } elseif ($action == 'entregue') {
        $stmt = $pdo->prepare("UPDATE pedidos SET status = ? WHERE id = ?");
        $stmt->execute([$action, $pedido_id]);

        // Insert into vendas table
        $pedido = $pdo->query("SELECT * FROM pedidos WHERE id = $pedido_id")->fetch(PDO::FETCH_ASSOC);
        $stmt = $pdo->prepare("INSERT INTO vendas (data_venda, forma_pagamento1, valor_pagamento1, total, usuario_id, caixa_id, cliente_conveniado, data_hora, tipo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            date('Y-m-d H:i:s'),
            $pedido['forma_pagamento_id'],
            $pedido['total'], // Ensure total is registered correctly
            $pedido['total'],
            $usuario_id,
            $caixa_id,
            $pedido['cliente_id'],
            date('Y-m-d H:i:s'),
            'venda'
        ]);
        $venda_id = $pdo->lastInsertId();

        // Insert into itens_venda table
        $itens_pedido = $pdo->query("SELECT * FROM pedido_itens WHERE pedido_id = $pedido_id")->fetchAll(PDO::FETCH_ASSOC);
        $stmt = $pdo->prepare("INSERT INTO itens_venda (venda_id, produto_id, quantidade, preco) VALUES (?, ?, ?, ?)");
        foreach ($itens_pedido as $item) {
            $stmt->execute([$venda_id, $item['produto_id'], $item['quantidade'], $item['preco']]);
        }
    }

    header("Location: gerenciar_pedidos.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Gerenciar Pedidos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .card-deck {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
        }
        .card {
            flex: 1 1 calc(33% - 1rem);
            margin: 0.5rem;
        }
    </style>
    <script>
        function confirmarAcao(acao, pedidoId) {
            let mensagem = '';
            if (acao === 'cancelado') {
                mensagem = 'Tem certeza que deseja cancelar este pedido?';
            } else if (acao === 'em_entrega') {
                mensagem = 'Tem certeza que deseja marcar este pedido como "Saiu para Entrega"?';
            } else if (acao === 'entregue') {
                mensagem = 'Tem certeza que deseja marcar este pedido como "Entregue"?';
            }
            if (confirm(mensagem)) {
                document.getElementById('pedido_id').value = pedidoId;
                document.getElementById('action').value = acao;
                document.getElementById('pedidoForm').submit();
            }
        }

        $(document).ready(function() {
            $('button[name="action"][value="em_entrega"]').on('click', function() {
                $(this).hide();
            });
        });

        function reimprimir(pedidoId) {
            window.open('imprime_cupom.php?pedido_id=' + pedidoId, 'PRINT', 'height=400,width=600');
        }
    </script>
</head>
<body>
<div class="container">
    <h1>Pedidos</h1>
    <form method="post" id="pedidoForm">
        <input type="hidden" name="pedido_id" id="pedido_id">
        <input type="hidden" name="action" id="action">
    </form>
    <div class="card-deck">
        <?php foreach ($pedidos as $pedido): ?>
            <div class="card <?= $pedido['status'] == 'em_entrega' ? 'bg-warning' : ($pedido['status'] == 'entregue' ? 'd-none' : '') ?>">
                <div class="card-body">
                    <h5 class="card-title">Cliente: <?= htmlspecialchars($pedido['cliente_nome']) ?></h5>
                    <p class="card-text">Total: R$<?= number_format($pedido['total'], 2) ?></p>
                    <p class="card-text">Entregador: <?= htmlspecialchars($pedido['entregador_nome']) ?></p>
                    <p class="card-text">Forma de Pagamento: <?= htmlspecialchars($pedido['forma_pagamento']) ?></p>
                    <p class="card-text">Valor a Receber: R$<?= number_format($pedido['forma_pagamento'] == 'Dinheiro' ? $pedido['valor_recebido'] : $pedido['total'], 2) ?></p>
                    <?php if ($pedido['forma_pagamento'] == 'Dinheiro'): ?>
                        <p class="card-text">Troco: R$<?= number_format($pedido['troco'], 2) ?></p>
                    <?php endif; ?>
                    <p class="card-text">Status: <?= htmlspecialchars($pedido['status']) ?></p>
                    <button type="button" class="btn btn-danger" onclick="confirmarAcao('cancelado', <?= $pedido['id'] ?>)">Cancelar Pedido</button>
                    <?php if ($pedido['status'] != 'em_entrega'): ?>
                        <button type="button" class="btn btn-warning" onclick="confirmarAcao('em_entrega', <?= $pedido['id'] ?>)">Saiu para Entrega</button>
                    <?php endif; ?>
                    <button type="button" class="btn btn-success" onclick="confirmarAcao('entregue', <?= $pedido['id'] ?>)">Entregue</button>
                    <button type="button" class="btn btn-secondary" onclick="reimprimir(<?= $pedido['id'] ?>)">Reimprimir</button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
</body>
</html>
