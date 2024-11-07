<?php
session_start();
if ($_SESSION['nivel_acesso'] !== 'admin') {
    header("Location: index.php");
    exit;
}

require 'db.php';

// Obter o ID do produto a ser inativado
$id = $_POST['id'];

// Inativar o produto
$stmt = $pdo->prepare('UPDATE produtos SET status = ? WHERE id = ?');
if ($stmt->execute(['inativo', $id])) {
    $_SESSION['success'] = "Produto inativado com sucesso.";
} else {
    $_SESSION['error'] = "Erro ao inativar produto.";
}

// Redirecionar de volta para a página de listagem de produtos
header("Location: produtos.php");
exit;
?>
