<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    header("Location: login.php");
    exit;
}

require 'db.php';

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $primeiro_nome = $_POST['primeiro_nome'];
    $sobrenome = $_POST['sobrenome'];
    $nova_senha = $_POST['nova_senha'];
    $confirmar_senha = $_POST['confirmar_senha'];

    if ($nova_senha !== $confirmar_senha) {
        $error = "As senhas não coincidem.";
    } else {
        // Hash da nova senha
        $hashed_nova_senha = password_hash($nova_senha, PASSWORD_DEFAULT);
        // Atualizar dados do usuário no banco de dados
        $stmt = $pdo->prepare('UPDATE usuarios SET primeiro_nome = ?, sobrenome = ?, password = ? WHERE id = ?');
        if ($stmt->execute([$primeiro_nome, $sobrenome, $hashed_nova_senha, $user_id])) {
            header("Location: minha_conta.php?success=true");
            exit;
        } else {
            $error = "Erro ao atualizar os dados.";
        }
    }
}
?>
