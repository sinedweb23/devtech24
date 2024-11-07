<?php
session_start();
require 'db.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header("Content-Type: application/json");
    echo json_encode(['error' => 'Usuário não está logado']);
    exit;
}

// Obter o ID da venda
$venda_id = $_GET['venda_id'];

$stmt = $pdo->prepare('SELECT itens_venda.*, produtos.nome AS produto_nome FROM itens_venda JOIN produtos ON itens_venda.produto_id = produtos.id WHERE itens_venda.venda_id = ?');
$stmt->execute([$venda_id]);
$itens_venda = $stmt->fetchAll(PDO::FETCH_ASSOC);

header("Content-Type: application/json");
echo json_encode($itens_venda);
?>
