<?php
session_start();
if ($_SESSION['nivel_acesso'] !== 'admin') {
    header("Location: index.php");
    exit;
}

require 'db.php';

// Obter o ID da venda a ser editada
$id = $_GET['id'];

// Obter os dados da venda
$stmt = $pdo->prepare('SELECT * FROM vendas WHERE id = ?');
$stmt->execute([$id]);
$venda = $stmt->fetch(PDO::FETCH_ASSOC);

// Obter os itens da venda
$stmt = $pdo->prepare('SELECT * FROM itens_venda WHERE venda_id = ?');
$stmt->execute([$id]);
$itens_venda = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Atualizar os dados da venda
    $forma_pagamento1 = $_POST['forma_pagamento1'];
    $valor_pagamento1 = str_replace(',', '.', $_POST['valor_pagamento1']);
    $forma_pagamento2 = $_POST['forma_pagamento2'];
    $valor_pagamento2 = str_replace(',', '.', $_POST['valor_pagamento2']);
    $total = str_replace(',', '.', $_POST['total']);

    $stmt = $pdo->prepare('UPDATE vendas SET forma_pagamento1 = ?, valor_pagamento1 = ?, forma_pagamento2 = ?, valor_pagamento2 = ?, total = ? WHERE id = ?');
    $stmt->execute([$forma_pagamento1, $valor_pagamento1, $forma_pagamento2, $valor_pagamento2, $total, $id]);

    header("Location: lista_vendas.php?success=Venda editada com sucesso.");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Venda</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container">
        <h2 class="mt-5">Editar Venda</h2>
        <form method="POST" action="editar_venda.php?id=<?= $id ?>">
            <div class="form-group">
                <label for="forma_pagamento1">Forma de Pagamento 1</label>
                <select name="forma_pagamento1" class="form-control" id="forma_pagamento1" required>
                    <option value="dinheiro" <?= $venda['forma_pagamento1'] == 'dinheiro' ? 'selected' : '' ?>>Dinheiro</option>
                    <option value="debito" <?= $venda['forma_pagamento1'] == 'debito' ? 'selected' : '' ?>>Débito</option>
                    <option value="credito" <?= $venda['forma_pagamento1'] == 'credito' ? 'selected' : '' ?>>Crédito</option>
                    <option value="pix" <?= $venda['forma_pagamento1'] == 'pix' ? 'selected' : '' ?>>Pix</option>
                </select>
                <input type="text" name="valor_pagamento1" class="form-control" id="valor_pagamento1" value="<?= number_format($venda['valor_pagamento1'], 2, ',', '.') ?>" required>
            </div>
            <div class="form-group">
                <label for="forma_pagamento2">Forma de Pagamento 2</label>
                <select name="forma_pagamento2" class="form-control" id="forma_pagamento2">
                    <option value="" <?= $venda['forma_pagamento2'] == '' ? 'selected' : '' ?>>Nenhum</option>
                    <option value="dinheiro" <?= $venda['forma_pagamento2'] == 'dinheiro' ? 'selected' : '' ?>>Dinheiro</option>
                    <option value="debito" <?= $venda['forma_pagamento2'] == 'debito' ? 'selected' : '' ?>>Débito</option>
                    <option value="credito" <?= $venda['forma_pagamento2'] == 'credito' ? 'selected' : '' ?>>Crédito</option>
                    <option value="pix" <?= $venda['forma_pagamento2'] == 'pix' ? 'selected' : '' ?>>Pix</option>
                </select>
                <input type="text" name="valor_pagamento2" class="form-control" id="valor_pagamento2" value="<?= number_format($venda['valor_pagamento2'], 2, ',', '.') ?>">
            </div>
            <div class="form-group">
                <label for="total">Total</label>
                <input type="text" name="total" class="form-control" id="total" value="<?= number_format($venda['total'], 2, ',', '.') ?>" required>
            </div>
            <!-- Adicionar outros campos necessários para editar a venda -->
            <button type="submit" class="btn btn-primary">Salvar</button>
        </form>
    </div>
</body>
</html>
