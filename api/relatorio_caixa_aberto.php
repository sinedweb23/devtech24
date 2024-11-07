<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);
include('../db.php'); // Incluir arquivo de conexão com o banco de dados

// Consulta SQL para buscar dados de caixa aberto
$sql = "SELECT DATE(data_abertura) as data, SUM(fundo_de_troco) as total 
        FROM caixa 
        WHERE status = 'aberto'
        GROUP BY DATE(data_abertura)";

try {
    $stmt = $pdo->query($sql);
    $data = [];
    $labels = [];

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $labels[] = $row['data'];
        $data[] = $row['total'];
    }

    echo json_encode([
        'labels' => $labels,
        'datasets' => [
            [
                'label' => 'Caixa Aberto',
                'data' => $data,
                'borderColor' => 'rgba(255, 99, 132, 1)',
                'backgroundColor' => 'rgba(255, 99, 132, 0.2)'
            ]
        ]
    ]);

} catch (PDOException $e) {
    echo json_encode(['error' => 'Erro na consulta: ' . $e->getMessage()]);
}
?>
