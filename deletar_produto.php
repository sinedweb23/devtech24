<?php
session_start();
if ($_SESSION['nivel_acesso'] !== 'admin') {
    header("Location: index.php");
    exit;
}

require 'db.php';

// Obter o ID do produto a ser deletado
$id = $_GET['id'];

try {
    // Tentar excluir o produto
    $stmt = $pdo->prepare('DELETE FROM produtos WHERE id = ?');
    $stmt->execute([$id]);

    // Redirecionar com mensagem de sucesso se a exclusão for bem-sucedida
    header("Location: produtos.php?success=Produto deletado com sucesso.");
    exit;
} catch (PDOException $e) {
    // Verificar se o erro é de violação de chave estrangeira
    if ($e->getCode() == 23000) {
        // Mostrar mensagem de erro e oferecer a opção de inativar o produto
        $_SESSION['error'] = "Este produto não pode ser excluído porque já está associado a uma venda.";
        $_SESSION['confirm_inativar'] = $id;
        header("Location: produtos.php");
        exit;
    } else {
        // Mostrar mensagem de erro genérica
        $_SESSION['error'] = "Erro ao deletar produto: " . $e->getMessage();
        header("Location: produtos.php");
        exit;
    }
}
?>
