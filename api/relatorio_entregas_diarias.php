<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);
include('../db.php'); // Incluir arquivo de conexão com o banco de dados

// Definir timezone
date_default_timezone_set('America/Sao_Paulo');

// Obter o mês atual
$anoMesAtual = date('Y-m');

// Gerar uma lista de todas as datas do mês atual
$period = new DatePeriod(
    new DateTime("$anoMesAtual-01"),
    new DateInterval('P1D'),
    (new DateTime("$anoMesAtual-01"))->modify('first day of next month')
);

$dates = [];
foreach ($period as $date) {
    $dates[$date->format('Y-m-d')] = 0;
}

// Consulta SQL para buscar as entregas diárias e somar os valores dos pedidos entregues no mês atual
$entregasDiariasQuery = "
    SELECT DATE(created_at) as data, SUM(total) as valor_total_entregas
    FROM pedidos
    WHERE status = 'entregue' AND DATE(created_at) LIKE '$anoMesAtual-%'
    GROUP BY DATE(created_at)
    ORDER BY DATE(created_at)
";

try {
    // Verificando a conexão com o banco de dados
    if ($pdo) {
        error_log("Conexão com o banco de dados estabelecida.");
    } else {
        error_log("Falha na conexão com o banco de dados.");
    }

    // Executando consulta
    $entregasDiariasStmt = $pdo->query($entregasDiariasQuery);
    $entregasDiarias = $entregasDiariasStmt->fetchAll(PDO::FETCH_ASSOC);

    // Atualizar a lista de datas com os valores das entregas
    foreach ($entregasDiarias as $row) {
        $dates[$row['data']] = (float)$row['valor_total_entregas'];
    }

    // Preparar dados para o Chart.js
    $labels = array_keys($dates);
    $data = array_values($dates);

    echo json_encode([
        'labels' => $labels,
        'datasets' => [
            [
                'label' => 'Total Entregas Diárias (R$)',
                'backgroundColor' => 'rgba(54, 162, 235, 0.2)',
                'borderColor' => 'rgba(54, 162, 235, 1)',
                'borderWidth' => 1,
                'data' => $data
            ]
        ]
    ]);

} catch (PDOException $e) {
    error_log("Erro na consulta: " . $e->getMessage());
    echo json_encode(['error' => 'Erro na consulta: ' . $e->getMessage()]);
}
?>
