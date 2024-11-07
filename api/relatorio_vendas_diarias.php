<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);
include('../db.php'); // Incluir arquivo de conexão com o banco de dados

// Obter o mês atual
$mesAtual = date('Y-m');

// Inicializar arrays para armazenar os dados
$data = [];
$labels = [];
$diasDoMes = cal_days_in_month(CAL_GREGORIAN, date('m'), date('Y'));

// Inicializar os arrays com zero para cada dia do mês
for ($i = 1; $i <= $diasDoMes; $i++) {
    $dia = sprintf('%02d', $i);
    $labels[] = "$mesAtual-$dia";
    $data["$mesAtual-$dia"] = 0;
}

// Consulta SQL para buscar dados de vendas diárias do mês atual
$sql = "SELECT DATE(data_venda) as data, SUM(total) as total 
        FROM vendas 
        WHERE DATE_FORMAT(data_venda, '%Y-%m') = '$mesAtual'
        GROUP BY DATE(data_venda)";

try {
    $stmt = $pdo->query($sql);

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $data[$row['data']] = $row['total'];
    }

    echo json_encode([
        'labels' => $labels,
        'datasets' => [
            [
                'label' => 'Vendas Diárias',
                'data' => array_values($data),
                'borderColor' => 'rgba(75, 192, 192, 1)',
                'backgroundColor' => 'rgba(75, 192, 192, 0.2)'
            ]
        ]
    ]);

} catch (PDOException $e) {
    echo json_encode(['error' => 'Erro na consulta: ' . $e->getMessage()]);
}
?>
