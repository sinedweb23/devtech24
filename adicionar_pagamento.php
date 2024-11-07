<?php
require 'db.php';

$cliente_id = $_POST['cliente_id'];
$valor = $_POST['valor'];

// Registrar pagamento
$stmt = $pdo->prepare('INSERT INTO pagamentos (cliente_id, valor, data_hora) VALUES (?, ?, NOW())');
$stmt->execute([$cliente_id, $valor]);

// Atualizar saldo devedor do cliente
$stmt = $pdo->prepare('UPDATE clientes SET saldo_devedor = saldo_devedor - ? WHERE id = ?');
$stmt->execute([$valor, $cliente_id]);

echo 'success';
?>
