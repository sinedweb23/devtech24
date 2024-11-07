<?php
require_once '../db.php';

$pedido_id = $_GET['pedido_id'];

// Fetch pedido details
$pedido = $pdo->query("SELECT p.*, c.nome AS cliente_nome, c.cep, c.endereco, c.numero, c.bairro, c.ponto_de_referencia, c.telefone, e.nome AS entregador_nome, f.descricao AS forma_pagamento
                       FROM pedidos p
                       JOIN clientes c ON p.cliente_id = c.id
                       JOIN entregadores e ON p.entregador_id = e.id
                       JOIN formas_pagamento f ON p.forma_pagamento_id = f.id
                       WHERE p.id = $pedido_id")->fetch(PDO::FETCH_ASSOC);

$itens = $pdo->query("SELECT pi.*, pr.nome
                      FROM pedido_itens pi
                      JOIN produtos pr ON pi.produto_id = pr.id
                      WHERE pi.pedido_id = $pedido_id")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Cupom de Entrega</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</head>
<body>
<div class="container">
    <h2>Cupom de Entrega</h2>
    <p><strong>Cliente:</strong> <?= $pedido['cliente_nome'] ?></p>
    <p><strong>CEP:</strong> <?= $pedido['cep'] ?></p>
    <p><strong>Endereço:</strong> <?= $pedido['endereco'] ?>, <?= $pedido['numero'] ?></p>
    <p><strong>Bairro:</strong> <?= $pedido['bairro'] ?></p>
    <p><strong>Ponto de Referência:</strong> <?= $pedido['ponto_de_referencia'] ?></p>
    <p><strong>Telefone:</strong> <?= $pedido['telefone'] ?></p>
    <p><strong>Entregador:</strong> <?= $pedido['entregador_nome'] ?></p>
    <p><strong>Forma de Pagamento:</strong> <?= $pedido['forma_pagamento'] ?></p>
    <?php if ($pedido['forma_pagamento'] == 'Dinheiro'): ?>
        <p><strong>Valor Recebido:</strong> R$ <?= number_format($pedido['valor_recebido'], 2) ?></p>
        <p><strong>Troco:</strong> R$ <?= number_format($pedido['troco'], 2) ?></p>
    <?php endif; ?>
    <hr>
    <h4>Produtos</h4>
    <table class="table">
        <thead>
            <tr>
                <th>Produto</th>
                <th>Quantidade</th>
                <th>Preço</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($itens as $item): ?>
                <tr>
                    <td><?= $item['nome'] ?></td>
                    <td><?= $item['quantidade'] ?></td>
                    <td>R$ <?= number_format($item['preco'], 2) ?></td>
                    <td>R$ <?= number_format($item['preco'] * $item['quantidade'], 2) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <hr>
    <h4>Total: R$ <?= number_format($pedido['total'], 2) ?></h4>
</div>
</body>
</html>
