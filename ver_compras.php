<?php
require 'db.php';

$cliente_id = $_GET['cliente_id'];

// Obter todas as compras do cliente
$stmt = $pdo->prepare('SELECT v.id, v.data_hora, v.total FROM vendas v WHERE v.cliente_conveniado = ?');
$stmt->execute([$cliente_id]);
$compras = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obter nome do cliente
$stmt = $pdo->prepare('SELECT nome FROM clientes WHERE id = ?');
$stmt->execute([$cliente_id]);
$cliente = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<h4>Compras de <?= htmlspecialchars($cliente['nome']) ?></h4>
<table class="table table-bordered mt-3">
    <thead>
        <tr>
            <th>Data e Hora</th>
            <th>Total</th>
            <th>Itens</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($compras as $compra): ?>
            <tr>
                <td><?= $compra['data_hora'] ?></td>
                <td>R$ <?= number_format($compra['total'], 2, ',', '.') ?></td>
                <td>
                    <button class="btn btn-info btn-sm" onclick="verItens(<?= $compra['id'] ?>)">Ver Itens</button>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<div id="itensConteudo"></div>

<script>
    function verItens(venda_id) {
        $.ajax({
            url: 'ver_itens.php',
            method: 'GET',
            data: { venda_id: venda_id },
            success: function(response) {
                $('#itensConteudo').html(response);
            }
        });
    }
</script>
