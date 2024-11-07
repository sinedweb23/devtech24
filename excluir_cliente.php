<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

require 'db.php';

$cliente_id = $_GET['id'];

// Verificar se o cliente tem vendas registradas
$stmt = $pdo->prepare('SELECT COUNT(*) FROM vendas WHERE cliente_conveniado = ?');
$stmt->execute([$cliente_id]);
$vendasCount = $stmt->fetchColumn();

if ($vendasCount > 0) {
    $error = "Cliente não pode ser excluído pois possui vendas.";
    header("Location: cadastro_cliente.php?error=" . urlencode($error));
    exit;
}

try {
    // Excluir cliente
    $stmt = $pdo->prepare('DELETE FROM clientes WHERE id = ?');
    if ($stmt->execute([$cliente_id])) {
        $success = "Cliente excluído com sucesso.";
        header("Location: cadastro_cliente.php?success=" . urlencode($success));
    } else {
        throw new Exception("Erro ao excluir cliente.");
    }
} catch (PDOException $e) {
    if ($e->getCode() == 23000) {
        $error = "Cliente não pode ser excluído pois possui registros relacionados.";
    } else {
        $error = "Erro ao excluir cliente: " . $e->getMessage();
    }
    header("Location: cadastro_cliente.php?error=" . urlencode($error));
}
?>
