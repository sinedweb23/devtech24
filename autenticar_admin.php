<?php
require 'db.php';

$username = $_POST['login'];
$password = $_POST['senha'];

// Adicionar log para depuração
error_log("Username recebido: $username");
error_log("Senha recebida: $password");

// Corrigir a consulta para garantir que estamos buscando pelo campo correto
$stmt = $pdo->prepare('SELECT password FROM usuarios WHERE username = ? AND nivel_acesso = "admin"');
$stmt->execute([$username]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

// Adicionar log para depuração
if ($usuario) {
    error_log("Usuário encontrado: " . print_r($usuario, true));
} else {
    error_log("Usuário não encontrado para o username: $username");
}

$response = ['autenticado' => false];

if ($usuario && password_verify($password, $usuario['password'])) {
    $response['autenticado'] = true;
}

// Adicionar log para depuração
error_log("Resposta de autenticação: " . json_encode($response));

echo json_encode($response);
?>
