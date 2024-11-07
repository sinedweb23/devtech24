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

// Obter os dados atuais do usuário
$stmt = $pdo->prepare('SELECT primeiro_nome, sobrenome FROM usuarios WHERE id = ?');
$stmt->execute([$user_id]);
$user_data = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minha Conta</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container">
        <h2 class="mt-5">Minha Conta</h2>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php elseif (isset($_GET['success'])): ?>
            <div class="alert alert-success">Dados atualizados com sucesso!</div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label for="primeiro_nome">Primeiro Nome</label>
                <input type="text" class="form-control" id="primeiro_nome" name="primeiro_nome" value="<?= htmlspecialchars($user_data['primeiro_nome']) ?>" required>
            </div>
            <div class="form-group">
                <label for="sobrenome">Sobrenome</label>
                <input type="text" class="form-control" id="sobrenome" name="sobrenome" value="<?= htmlspecialchars($user_data['sobrenome']) ?>" required>
            </div>
            <div class="form-group">
                <label for="nova_senha">Nova Senha</label>
                <input type="password" class="form-control" id="nova_senha" name="nova_senha" required>
            </div>
            <div class="form-group">
                <label for="confirmar_senha">Confirmar Senha</label>
                <input type="password" class="form-control" id="confirmar_senha" name="confirmar_senha" required>
            </div>
            <button type="submit" class="btn btn-primary">Atualizar</button>
        </form>
    </div>
</body>
</html>
