<?php
session_start();
if ($_SESSION['nivel_acesso'] !== 'admin') {
    header("Location: index.php");
    exit;
}
require '../db.php';

$id = $_GET['id'];
$stmt = $pdo->prepare('DELETE FROM entregadores WHERE id = ?');
if ($stmt->execute([$id])) {
    header('Location: cadastrar_entregador.php');
    exit;
} else {
    die('Erro ao excluir entregador');
}
?>
