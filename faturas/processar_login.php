<?php
session_start();
include 'db_connect.php';

$email = $_POST['login'];
$senha = $_POST['senha'];

// Debug: Verifique se os dados foram recebidos corretamente
// echo "Email: $email, Senha: $senha";

$sql = "SELECT id, senha FROM clientes WHERE email='$email'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    // Debug: Verifique se a senha está sendo verificada corretamente
    // echo "Senha do Banco: " . $row['senha'];

    if (password_verify($senha, $row['senha'])) {
        $_SESSION['cliente_id'] = $row['id'];
        header("Location: listar_faturas.php");
        exit();
    } else {
        echo "Senha incorreta.";
    }
} else {
    echo "Login não encontrado.";
}

$conn->close();
?>
