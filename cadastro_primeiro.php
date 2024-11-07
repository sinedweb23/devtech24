<?php

require 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $nivel_acesso = $_POST['nivel_acesso'];
    $primeiro_nome = $_POST['primeiro_nome'];
    $sobrenome = $_POST['sobrenome'];

    // Verificar se o usuário já existe
    $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE username = ?');
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user) {
        $error = "Usuário já existe.";
    } else {
        // Inserir novo usuário
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('INSERT INTO usuarios (username, password, nivel_acesso, primeiro_nome, sobrenome) VALUES (?, ?, ?, ?, ?)');
        if ($stmt->execute([$username, $hashed_password, $nivel_acesso, $primeiro_nome, $sobrenome])) {
            $success = "Usuário cadastrado com sucesso.";
        } else {
            $error = "Erro ao cadastrar usuário.";
        }
    }
}

// Obter todos os usuários cadastrados
$stmt = $pdo->query('SELECT * FROM usuarios');
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Usuário</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
<body>
    <div class="container">
        <h2 class="mt-5">Cadastro de Usuário</h2>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>
        <form method="POST" action="cadastro_usuario.php">
            <div class="form-group">
                <label for="primeiro_nome">Primeiro Nome</label>
                <input type="text" name="primeiro_nome" class="form-control" id="primeiro_nome" required>
            </div>
            <div class="form-group">
                <label for="sobrenome">Sobrenome</label>
                <input type="text" name="sobrenome" class="form-control" id="sobrenome" required>
            </div>
            <div class="form-group">
                <label for="username">Usuário</label>
                <input type="text" name="username" class="form-control" id="username" required>
            </div>
            <div class="form-group">
                <label for="password">Senha</label>
                <input type="password" name="password" class="form-control" id="password" required>
            </div>
            <div class="form-group">
                <label for="nivel_acesso">Nível de Acesso</label>
                <select name="nivel_acesso" class="form-control" id="nivel_acesso" required>
                    <option value="usuario">Usuário</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Cadastrar</button>
        </form>

        <h2 class="mt-5">Usuários Cadastrados</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Primeiro Nome</th>
                    <th>Sobrenome</th>
                    <th>Usuário</th>
                    <th>Nível de Acesso</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usuarios as $usuario): ?>
                    <tr>
                        <td><?= htmlspecialchars($usuario['primeiro_nome']) ?></td>
                        <td><?= htmlspecialchars($usuario['sobrenome']) ?></td>
                        <td><?= htmlspecialchars($usuario['username']) ?></td>
                        <td><?= htmlspecialchars($usuario['nivel_acesso']) ?></td>
                        <td>
                            <?php if ($usuario['status'] == 'ativo'): ?>
                                <a href="editar_usuario.php?id=<?= $usuario['id'] ?>" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
                                <a href="excluir_usuario.php?id=<?= $usuario['id'] ?>" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></a>
                                <a href="inativar_usuario.php?id=<?= $usuario['id'] ?>" class="btn btn-secondary btn-sm"><i class="fas fa-user-slash"></i></a>
                            <?php else: ?>
                                <a href="reativar_usuario.php?id=<?= $usuario['id'] ?>" class="btn btn-success btn-sm"><i class="fas fa-user-check"></i></a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
