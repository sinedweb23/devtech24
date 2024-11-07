<?php
session_start();
if (!isset($_SESSION['cliente_id'])) {
    header("Location: login.php");
    exit();
}

include 'db_connect.php';

$cliente_id = $_SESSION['cliente_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minhas Faturas</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .status-pendente {
            background-color: #fff3cd; /* Amarelo claro */
        }
        .status-vencida {
            background-color: #f8d7da; /* Vermelho claro */
        }
        .status-paga {
            background-color: #d4edda; /* Verde claro */
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h2>Minhas Faturas</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    
                    <th>Data de Vencimento</th>
                    <th>Plano</th>
                    <th>Serviço</th>
                    <th>Valor</th>
                    <th>Status</th>
                    <th>Link de Pagamento</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $result = $conn->query("SELECT id, data_vencimento, plano, servico, valor, status, link_pagamento FROM faturas WHERE cliente_id='$cliente_id'");
                while ($row = $result->fetch_assoc()) {
                    $status_class = '';
                    $status_label = '';

                    // Formatação da data de vencimento
                    $data_vencimento_formatada = date('d-m-Y', strtotime($row['data_vencimento']));

                    // Verifica se a fatura está vencida
                    if ($row['status'] == 'paga') {
                        $status_class = 'status-paga';
                        $status_label = 'Paga';
                    } elseif (strtotime($row['data_vencimento']) < time()) {
                        $status_class = 'status-vencida';
                        $status_label = 'Vencida';
                    } else {
                        $status_class = 'status-pendente';
                        $status_label = 'Pendente';
                    }

                    echo "<tr class='$status_class'>
                            
                            <td>{$data_vencimento_formatada}</td>
                            <td>{$row['plano']}</td>
                            <td>{$row['servico']}</td>
                            <td>{$row['valor']}</td>
                            <td>{$status_label}</td>";
                    if ($row['status'] != 'paga') {
                        echo "<td><a href='{$row['link_pagamento']}' target='_blank' class='btn btn-primary'>Pagar</a></td>";
                    } else {
                        echo "<td></td>";
                    }
                    echo "</tr>";
                }
                $conn->close();
                ?>
            </tbody>
        </table>
      
    </div>
</body>
</html>
