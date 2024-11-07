<?php
require 'db.php';

$data = json_decode(file_get_contents('php://input'), true);

$usuario_id = $data['usuario_id'];
$caixa_id = $data['caixa_id'] ?? null;
$forma_pagamento1 = $data['forma_pagamento1'];
$valor_pagamento1 = $data['valor_pagamento1'];
$forma_pagamento2 = $data['forma_pagamento2'];
$valor_pagamento2 = $data['valor_pagamento2'];
$total = $data['total'];
$itens = $data['itens'];
$cliente_conveniado = $data['cliente_conveniado'] ?? null;
$tipo = $data['tipo'] ?? 'venda'; // Adicione um campo tipo para diferenciar entre venda e pagamento

try {
    $pdo->beginTransaction();

    // Inserir venda ou pagamento
    $stmt = $pdo->prepare('INSERT INTO vendas (usuario_id, caixa_id, forma_pagamento1, valor_pagamento1, forma_pagamento2, valor_pagamento2, total, cliente_conveniado, tipo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([$usuario_id, $caixa_id, $forma_pagamento1, $valor_pagamento1, $forma_pagamento2, $valor_pagamento2, $total, $cliente_conveniado, $tipo]);
    $venda_id = $pdo->lastInsertId();

    // Inserir itens da venda e atualizar estoque, se houver itens
    if (!empty($itens)) {
        $stmtItem = $pdo->prepare('INSERT INTO itens_venda (venda_id, produto_id, quantidade, preco) VALUES (?, ?, ?, ?)');
        $stmtUpdateEstoque = $pdo->prepare('UPDATE produtos SET estoque = estoque - ? WHERE id = ?');

        foreach ($itens as $item) {
            // Calcular o valor do produto com desconto
            $preco_com_desconto = $item['preco'] * (1 - $item['desconto'] / 100);
            $stmtItem->execute([$venda_id, $item['id'], $item['quantidade'], $preco_com_desconto]);
            $stmtUpdateEstoque->execute([$item['quantidade'], $item['id']]);
        }
    }

    // Atualizar saldo devedor do cliente conveniado
    if ($cliente_conveniado) {
        if ($tipo === 'venda') {
            // Adicionar ao saldo devedor para vendas
            $stmt = $pdo->prepare('UPDATE clientes SET saldo_devedor = saldo_devedor + ? WHERE id = ?');
            $stmt->execute([$total, $cliente_conveniado]);
        } else if ($tipo === 'pagamento') {
            // Subtrair do saldo devedor para pagamentos
            $stmt = $pdo->prepare('UPDATE clientes SET saldo_devedor = saldo_devedor - ? WHERE id = ?');
            $stmt->execute([$total, $cliente_conveniado]);
        }
    }

    $pdo->commit();
    echo json_encode(['status' => 'success', 'venda_id' => $venda_id]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
