<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);
include('../db.php'); // Incluir arquivo de conexão com o banco de dados

// Definir timezone para garantir a data correta
date_default_timezone_set('America/Sao_Paulo'); // Ajuste conforme necessário

// Obter a data atual
$dataAtual = date('Y-m-d');

try {
    // Consulta SQL para calcular o lucro aproximado das vendas do dia
    $stmt = $pdo->prepare('
        SELECT SUM((itens_venda.quantidade * itens_venda.preco) - (itens_venda.quantidade * produtos.preco_custo)) AS lucro_aproximado
        FROM vendas
        JOIN itens_venda ON vendas.id = itens_venda.venda_id
        JOIN produtos ON itens_venda.produto_id = produtos.id
        WHERE DATE(vendas.data_venda) = ?
    ');

    $stmt->execute([$dataAtual]);
    $lucro = $stmt->fetch(PDO::FETCH_ASSOC);

    $lucroAproximado = $lucro['lucro_aproximado'] !== null ? $lucro['lucro_aproximado'] : 0;

    echo json_encode(['lucro_aproximado' => number_format((float)$lucroAproximado, 2, ',', '.')]);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Erro na consulta: ' . $e->getMessage()]);
}
?>
