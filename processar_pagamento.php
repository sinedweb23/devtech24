<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $cliente_id = $_POST['cliente_id'];
    $payment_amount = $_POST['payment_amount'];

    // Exibir os valores recebidos pelo formulário
    echo "Cliente ID recebido: " . htmlspecialchars($cliente_id) . "<br>";
    echo "Pagamento recebido: " . htmlspecialchars($payment_amount) . "<br>";

    try {
        $pdo->beginTransaction();

        // Verificar o saldo devedor antes da atualização
        $stmt = $pdo->prepare('SELECT saldo_devedor FROM clientes WHERE id = ?');
        $stmt->execute([$cliente_id]);
        $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
        $saldo_devedor_anterior = $cliente['saldo_devedor'];

        echo "Saldo devedor anterior: " . $saldo_devedor_anterior . "<br>";

        // Registrar o pagamento na tabela de vendas
        $stmt = $pdo->prepare('INSERT INTO vendas (usuario_id, caixa_id, forma_pagamento1, valor_pagamento1, total, cliente_conveniado, tipo) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$_SESSION['user_id'], null, 'pagamento', $payment_amount, $payment_amount, $cliente_id, 'pagamento']);

        // Atualizar o saldo devedor do cliente conveniado
        $stmt = $pdo->prepare('UPDATE clientes SET saldo_devedor = saldo_devedor - ? WHERE id = ?');
        $stmt->execute([$payment_amount, $cliente_id]);

        // Verificar o saldo devedor após a atualização
        $stmt = $pdo->prepare('SELECT saldo_devedor FROM clientes WHERE id = ?');
        $stmt->execute([$cliente_id]);
        $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
        $saldo_devedor_atual = $cliente['saldo_devedor'];

        echo "Saldo devedor atual: " . $saldo_devedor_atual . "<br>";

        $pdo->commit();
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "Erro: " . $e->getMessage();
    }
}
?>
