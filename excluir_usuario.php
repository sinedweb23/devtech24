<?php
session_start();
if ($_SESSION['nivel_acesso'] !== 'admin') {
    header("Location: index.php");
    exit;
}
require 'db.php';

$id = $_GET['id'];

// Verificar se o usuário possui movimentações
$stmt = $pdo->prepare('SELECT COUNT(*) FROM vendas WHERE usuario_id = ?');
$stmt->execute([$id]);
$tem_movimentacao = $stmt->fetchColumn();

if ($tem_movimentacao) {
    $error = "Não é possível excluir usuário com vendas. Sugiro inativá-lo.";
} else {
    $stmt = $pdo->prepare('DELETE FROM usuarios WHERE id = ?');
    if ($stmt->execute([$id])) {
        header("Location: cadastro_usuario.php");
        exit;
    } else {
        $error = "Erro ao excluir usuário.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Excluir Usuário</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container">
        <h2 class="mt-5">Excluir Usuário</h2>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        <a href="cadastro_usuario.php" class="btn btn-primary">Voltar</a>
    </div>
</body>
</html>
