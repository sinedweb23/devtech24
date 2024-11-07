<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require 'db.php';

$cliente_id = $_GET['cliente_id'];

// Obter as transações do cliente conveniado
$stmt = $pdo->prepare('SELECT v.id, v.data, v.total, GROUP_CONCAT(p.nome SEPARATOR ", ") AS itens, v.tipo 
                       FROM vendas v 
                       LEFT JOIN vendas_produtos vp ON v.id = vp.venda_id 
                       LEFT JOIN produtos p ON vp.produto_id = p.id 
                       WHERE v.cliente_conveniado = ? 
                       GROUP BY v.id 
                       ORDER BY v.data DESC');
$stmt->execute([$cliente_id]);
$transacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare('SELECT nome FROM clientes WHERE id = ?');
$stmt->execute([$cliente_id]);
$cliente = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gastos do Cliente Conveniado</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        .venda {
            color: red;
        }
        .pagamento {
            color: green;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="mt-5">Gastos de <?= htmlspecialchars($cliente['nome']) ?></h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID da Transação</th>
                    <th>Data e Hora</th>
                    <th>Valor Total</th>
                    <th>Itens / Pagamento</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($transacoes as $transacao): ?>
                    <tr class="<?= $transacao['tipo'] === 'venda' ? 'venda' : 'pagamento' ?>">
                        <td><?= htmlspecialchars($transacao['id']) ?></td>
                        <td><?= htmlspecialchars($transacao['data']) ?></td>
                        <td>R$ <?= number_format($transacao['tipo'] === 'venda' ? -$transacao['total'] : $transacao['total'], 2, ',', '.') ?></td>
                        <td><?= htmlspecialchars($transacao['tipo'] === 'venda' ? $transacao['itens'] : 'Pagamento') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h3>Adicionar Pagamento</h3>
        <form action="processar_pagamento.php" method="post">
            <div class="form-group">
                <label for="payment_amount">Valor do Pagamento</label>
                <input type="number" step="0.01" class="form-control" id="payment_amount" name="payment_amount" required>
            </div>
            <input type="hidden" name="cliente_id" value="<?= htmlspecialchars($cliente_id) ?>">
            <button type="submit" class="btn btn-primary">Adicionar Pagamento</button>
        </form>
    </div>
</body>
</html>
