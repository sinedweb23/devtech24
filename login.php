<?php
session_start();
require 'db.php';

// Obter configurações atuais
$stmt = $pdo->query('SELECT * FROM configuracoes_loja WHERE id = 1');
$configuracoes = $stmt->fetch();

if (!$configuracoes) {
    // Inserir linha de configuração padrão se não existir
    $stmt = $pdo->prepare('INSERT INTO configuracoes_loja (nome_loja, cor_texto_menu, cor_div_menu) VALUES (?, ?, ?)');
    $stmt->execute(['Minha Loja', '#000000', '#ffffff']);
    // Obter a configuração recém inserida
    $stmt = $pdo->query('SELECT * FROM configuracoes_loja WHERE id = 1');
    $configuracoes = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE username = ?');
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['nivel_acesso'] = $user['nivel_acesso'];

        $redirect = isset($_SESSION['redirect_url']) ? $_SESSION['redirect_url'] : 'index.php';
        unset($_SESSION['redirect_url']);
        header("Location: $redirect");
        exit;
    } else {
        $error = "Usuário ou senha incorretos.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <?php if ($configuracoes['favicon']): ?>
        <link rel="icon" type="image/png" href="<?= $configuracoes['favicon'] ?>">
    <?php endif; ?>
</head>
<body>
    <div class="container">
        <h2 class="mt-5">Login</h2>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        <form method="POST" action="login.php">
            <div class="form-group">
                <label for="username">Usuário</label>
                <input type="text" name="username" class="form-control" id="username" required>
            </div>
            <div class="form-group">
                <label for="password">Senha</label>
                <input type="password" name="password" class="form-control" id="password" required>
            </div>
            <button type="submit" class="btn btn-primary">Login</button>
        </form>
    </div>

    <script>
        // Redirecionar para index.php na janela inteira após o login
        if (window.top !== window.self) {
            window.top.location.href = 'index.php';
        }
    </script>
</body>
</html>
