<?php
session_start();
if ($_SESSION['nivel_acesso'] !== 'admin') {
    header("Location: index.php");
    exit;
}
require 'db.php';

$id = $_GET['id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $primeiro_nome = $_POST['primeiro_nome'];
    $sobrenome = $_POST['sobrenome'];
    $username = $_POST['username'];
    $nivel_acesso = $_POST['nivel_acesso'];
    $nova_senha = $_POST['nova_senha'];

    if (!empty($nova_senha)) {
        // Hash da nova senha
        $hashed_nova_senha = password_hash($nova_senha, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('UPDATE usuarios SET primeiro_nome = ?, sobrenome = ?, username = ?, nivel_acesso = ?, password = ? WHERE id = ?');
        $stmt->execute([$primeiro_nome, $sobrenome, $username, $nivel_acesso, $hashed_nova_senha, $id]);
    } else {
        $stmt = $pdo->prepare('UPDATE usuarios SET primeiro_nome = ?, sobrenome = ?, username = ?, nivel_acesso = ? WHERE id = ?');
        $stmt->execute([$primeiro_nome, $sobrenome, $username, $nivel_acesso, $id]);
    }

    if ($stmt->rowCount() > 0) {
        $success = "Usuário atualizado com sucesso.";
    } else {
        $error = "Erro ao atualizar usuário.";
    }
}

$stmt = $pdo->prepare('SELECT * FROM usuarios WHERE id = ?');
$stmt->execute([$id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    $error = "Usuário não encontrado.";
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuário</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container">
        <h2 class="mt-5">Editar Usuário</h2>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label for="primeiro_nome">Primeiro Nome</label>
                <input type="text" name="primeiro_nome" class="form-control" id="primeiro_nome" value="<?= htmlspecialchars($usuario['primeiro_nome']) ?>" required>
            </div>
            <div class="form-group">
                <label for="sobrenome">Sobrenome</label>
                <input type="text" name="sobrenome" class="form-control" id="sobrenome" value="<?= htmlspecialchars($usuario['sobrenome']) ?>" required>
            </div>
            <div class="form-group">
                <label for="username">Usuário</label>
                <input type="text" name="username" class="form-control" id="username" value="<?= htmlspecialchars($usuario['username']) ?>" required>
            </div>
            <div class="form-group">
                <label for="nova_senha">Nova Senha</label>
                <input type="password" name="nova_senha" class="form-control" id="nova_senha">
            </div>
            <div class="form-group">
                <label for="nivel_acesso">Nível de Acesso</label>
                <select name="nivel_acesso" class="form-control" id="nivel_acesso" required>
                    <option value="usuario" <?= $usuario['nivel_acesso'] == 'usuario' ? 'selected' : '' ?>>Usuário</option>
                    <option value="admin" <?= $usuario['nivel_acesso'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Atualizar</button>
        </form>
    </div>
</body>
</html>
