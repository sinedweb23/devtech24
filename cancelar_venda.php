<?php
session_start();
if ($_SESSION['nivel_acesso'] !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'Acesso não autorizado.']);
    exit;
}

require 'db.php';

// Obter o ID da venda a ser cancelada
$id = $_POST['id'];

// Obter os dados da venda
$stmt = $pdo->prepare('SELECT * FROM vendas WHERE id = ?');
$stmt->execute([$id]);
$venda = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$venda) {
    echo json_encode(['success' => false, 'error' => 'Venda não encontrada.']);
    exit;
}

// Inserir dados na tabela vendas_canceladas
$stmt = $pdo->prepare('INSERT INTO vendas_canceladas (venda_id, data_cancelamento, cancelado_por, data_venda, forma_pagamento1, valor_pagamento1, forma_pagamento2, valor_pagamento2, total, usuario_id) VALUES (?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?)');
$stmt->execute([$venda['id'], $_SESSION['user_id'], $venda['data_venda'], $venda['forma_pagamento1'], $venda['valor_pagamento1'], $venda['forma_pagamento2'], $venda['valor_pagamento2'], $venda['total'], $venda['usuario_id']]);

// Obter o ID da venda cancelada inserida
$venda_cancelada_id = $pdo->lastInsertId();

// Obter os itens da venda
$stmt = $pdo->prepare('SELECT * FROM itens_venda WHERE venda_id = ?');
$stmt->execute([$id]);
$itens_venda = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Inserir itens na tabela itens_venda_cancelados e estornar o estoque
foreach ($itens_venda as $item) {
    $stmt = $pdo->prepare('INSERT INTO itens_venda_cancelados (venda_cancelada_id, produto_id, quantidade, preco) VALUES (?, ?, ?, ?)');
    $stmt->execute([$venda_cancelada_id, $item['produto_id'], $item['quantidade'], $item['preco']]);

    // Estornar o estoque
    $stmt = $pdo->prepare('UPDATE produtos SET estoque = estoque + ? WHERE id = ?');
    $stmt->execute([$item['quantidade'], $item['produto_id']]);
}

// Deletar os itens da venda
$stmt = $pdo->prepare('DELETE FROM itens_venda WHERE venda_id = ?');
$stmt->execute([$id]);

// Deletar a venda
$stmt = $pdo->prepare('DELETE FROM vendas WHERE id = ?');
$stmt->execute([$id]);

echo json_encode(['success' => true]);
?>
