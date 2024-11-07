<?php
require 'db.php';

$venda_id = $_GET['venda_id'];

// Obter dados da venda
$stmt = $pdo->prepare('SELECT * FROM vendas WHERE id = ?');
$stmt->execute([$venda_id]);
$venda = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$venda) {
    die('Venda não encontrada.');
}

// Obter itens da venda
$stmt = $pdo->prepare('SELECT * FROM itens_venda WHERE venda_id = ?');
$stmt->execute([$venda_id]);
$itens_venda = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obter dados da loja
$stmt = $pdo->query('SELECT * FROM configuracoes_loja WHERE id = 1');
$loja = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Imprimir Cupom</title>
</head>
<body>
    <h3><?= $loja['nome_loja'] ?></h3>
    <p>Endereço: <?= $loja['endereco'] ?></p>
    <p>CNPJ: <?= $loja['cnpj'] ?></p>
    <p>IE: <?= $loja['ie'] ?></p>
    <p>Telefone: <?= $loja['telefone'] ?></p>
    <hr>
    <h4>Cupom Não Fiscal</h4>
   
    <p><br></p>
    <?php foreach ($itens_venda as $item): ?>
        <p><?= $item['produto_id'] ?> <?= $item['nome_produto'] ?> <?= $item['quantidade'] ?> R$ <?= number_format($item['preco_unitario'], 2, ',', '.') ?> R$ <?= number_format($item['total'], 2, ',', '.') ?></p>
    <?php endforeach; ?>
    <hr>
    <p>Total: R$ <?= number_format($venda['total'], 2, ',', '.') ?></p>
    <p>Forma de Pagamento 1: <?= $venda['forma_pagamento1'] ?> - R$ <?= number_format($venda['valor_pagamento1'], 2, ',', '.') ?></p>
    <?php if ($venda['forma_pagamento2']): ?>
        <p>Forma de Pagamento 2: <?= $venda['forma_pagamento2'] ?> - R$ <?= number_format($venda['valor_pagamento2'], 2, ',', '.') ?></p>
    <?php endif; ?>
    <script>
        window.print();
        window.onafterprint = function () {
            window.close();
        };
    </script>
</body>
</html>
