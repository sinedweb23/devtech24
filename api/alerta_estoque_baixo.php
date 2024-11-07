<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);
include('../db.php'); // Incluir arquivo de conexão com o banco de dados

try {
    $stmt = $pdo->query('SELECT nome, estoque, estoque_minimo FROM produtos WHERE estoque <= estoque_minimo');
    $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($produtos);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Erro na consulta: ' . $e->getMessage()]);
}
?>
