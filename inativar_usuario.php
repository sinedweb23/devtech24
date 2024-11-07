<?php
session_start();
if ($_SESSION['nivel_acesso'] !== 'admin') {
    header("Location: index.php");
    exit;
}
require 'db.php';

$id = $_GET['id'];

$stmt = $pdo->prepare('UPDATE usuarios SET status = "inativo" WHERE id = ?');
if ($stmt->execute([$id])) {
    header("Location: cadastro_usuario.php");
} else {
    echo "Erro ao inativar usuário.";
}
?>
