<?php
include 'conexao.php';
session_start();

// Verifica se o cliente está logado
if (!isset($_SESSION['cliente_id'])) {
    header('Location: login_cliente.php');
    exit();
}

$cliente_id = $_SESSION['cliente_id'];

$faturas_sql = "SELECT * FROM Faturas WHERE cliente_id = $cliente_id";
$faturas_result = mysqli_query($conn, $faturas_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minhas Faturas</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Minhas Faturas</h1>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Data de Vencimento</th>
                    <th>Valor</th>
                    <th>Status</th>
                    <th>Ação</th>
                </tr>
            </thead>
            <tbody>
                <?php
                while ($fatura = mysqli_fetch_assoc($faturas_result)) {
                    echo "<tr>";
                    echo "<td>{$fatura['data_vencimento']}</td>";
                    echo "<td>{$fatura['valor']}</td>";
                    echo "<td>{$fatura['status']}</td>";
                    if ($fatura['status'] == 'pendente') {
                        echo "<td><a href='{$fatura['link_pagamento']}' class='btn btn-success'>Pagar</a></td>";
                    } else {
                        echo "<td>Pago</td>";
                    }
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
</body>
</html>
