<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);
include('../db.php'); // Incluir arquivo de conexão com o banco de dados

// Obter o ano atual
$anoAtual = date('Y');

// Inicializar arrays para armazenar os dados
$data = [];
$labels = [];
$meses = ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12'];

// Inicializar os arrays com zero para cada mês do ano
foreach ($meses as $mes) {
    $labels[] = "$anoAtual-$mes";
    $data["$anoAtual-$mes"] = 0;
}

// Consulta SQL para buscar dados de vendas mensais do ano atual
$sql = "SELECT DATE_FORMAT(data_venda, '%Y-%m') as mes, SUM(total) as total 
        FROM vendas 
        WHERE YEAR(data_venda) = '$anoAtual'
        GROUP BY DATE_FORMAT(data_venda, '%Y-%m')";

try {
    $stmt = $pdo->query($sql);

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $data[$row['mes']] = $row['total'];
    }

    echo json_encode([
        'labels' => $labels,
        'datasets' => [
            [
                'label' => 'Vendas Mensais',
                'data' => array_values($data),
                'borderColor' => 'rgba(255, 99, 132, 1)',
                'backgroundColor' => 'rgba(255, 99, 132, 0.2)'
            ]
        ]
    ]);

} catch (PDOException $e) {
    echo json_encode(['error' => 'Erro na consulta: ' . $e->getMessage()]);
}
?>
