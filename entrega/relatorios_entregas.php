<?php
require_once '../db.php';

$entregas_mes = $pdo->query("SELECT COUNT(*) as total FROM pedidos WHERE status = 'entregue' AND MONTH(created_at) = MONTH(CURRENT_DATE())")->fetch(PDO::FETCH_ASSOC);
$entregas_ano = $pdo->query("SELECT COUNT(*) as total FROM pedidos WHERE status = 'entregue' AND YEAR(created_at) = YEAR(CURRENT_DATE())")->fetch(PDO::FETCH_ASSOC);

$entregas_em_andamento = $pdo->query("SELECT COUNT(*) as total FROM pedidos WHERE status = 'em_entrega'")->fetch(PDO::FETCH_ASSOC);
$entregas_canceladas = $pdo->query("SELECT COUNT(*) as total FROM pedidos WHERE status = 'cancelado'")->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Relatórios de Entregas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container">
    <h1>Relatórios de Entregas</h1>
    <p>Entregas no mês: <?= $entregas_mes['total'] ?></p>
    <p>Entregas no ano: <?= $entregas_ano['total'] ?></p>
    <p>Entregas em andamento: <?= $entregas_em_andamento['total'] ?></p>
    <p>Entregas canceladas: <?= $entregas_canceladas['total'] ?></p>
</div>
</body>
</html>
