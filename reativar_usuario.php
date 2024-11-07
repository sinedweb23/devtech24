<?php
session_start();
if ($_SESSION['nivel_acesso'] !== 'admin') {
    header("Location: index.php");
    exit;
}

require 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['id'])) {
    $usuario_id = $_GET['id'];

    // Verificar se o usuário existe e está inativo
    $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE id = ? AND status = "inativo"');
    $stmt->execute([$usuario_id]);
    $usuario = $stmt->fetch();

    if (!$usuario) {
        header("Location: index.php");
        exit;
    }

    // Atualizar o status do usuário para ativo
    $stmt = $pdo->prepare('UPDATE usuarios SET status = "ativo" WHERE id = ?');
    $stmt->execute([$usuario_id]);

    // Redirecionar de volta para a página de usuários
    header("Location: cadastro_usuario.php");
    exit;
} else {
    header("Location: index.php");
    exit;
}
?>
