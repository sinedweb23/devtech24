<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);
include('../db.php'); // Incluir arquivo de conexão com o banco de dados

// Definir timezone
date_default_timezone_set('America/Sao_Paulo');

// Obter a data atual
$dataAtual = date('Y-m-d');

// Consulta SQL para buscar dados resumidos diários
$totalVendasQuery = "SELECT COUNT(id) as total_vendas, SUM(total) as valor_total_vendido FROM vendas WHERE DATE(data_venda) = '$dataAtual'";
$ticketMedioQuery = "SELECT IFNULL(SUM(total) / COUNT(id), 0) as ticket_medio FROM vendas WHERE DATE(data_venda) = '$dataAtual'";
$vendasCanceladasQuery = "SELECT COUNT(id) as vendas_canceladas FROM vendas_canceladas WHERE DATE(data_cancelamento) = '$dataAtual'";
$entregasDiaQuery = "SELECT COUNT(id) as entregas_dia FROM pedidos WHERE DATE(created_at) = '$dataAtual' AND status = 'entregue'";

try {
    // Verificando a conexão com o banco de dados
    if ($pdo) {
        error_log("Conexão com o banco de dados estabelecida.");
    } else {
        error_log("Falha na conexão com o banco de dados.");
    }

    // Executando consultas
    $totalVendasStmt = $pdo->query($totalVendasQuery);
    $ticketMedioStmt = $pdo->query($ticketMedioQuery);
    $vendasCanceladasStmt = $pdo->query($vendasCanceladasQuery);
    $entregasDiaStmt = $pdo->query($entregasDiaQuery);

    $totalVendas = $totalVendasStmt->fetch(PDO::FETCH_ASSOC);
    $ticketMedio = $ticketMedioStmt->fetch(PDO::FETCH_ASSOC);
    $vendasCanceladas = $vendasCanceladasStmt->fetch(PDO::FETCH_ASSOC);
    $entregasDia = $entregasDiaStmt->fetch(PDO::FETCH_ASSOC);

    // Verificando os resultados das consultas
    if ($totalVendas && $ticketMedio && $vendasCanceladas && $entregasDia) {
        error_log("Consultas executadas com sucesso.");
        echo json_encode([
            'total_vendas' => $totalVendas['total_vendas'],
            'valor_total_vendido' => number_format($totalVendas['valor_total_vendido'], 2, ',', '.'),
            'ticket_medio' => number_format($ticketMedio['ticket_medio'], 2, ',', '.'),
            'vendas_canceladas' => $vendasCanceladas['vendas_canceladas'],
            'entregas_dia' => $entregasDia['entregas_dia']
        ]);
    } else {
        error_log("Erro ao executar consultas.");
        echo json_encode(['error' => 'Erro ao executar consultas.']);
    }

} catch (PDOException $e) {
    error_log("Erro na consulta: " . $e->getMessage());
    echo json_encode(['error' => 'Erro na consulta: ' . $e->getMessage()]);
}
?>
