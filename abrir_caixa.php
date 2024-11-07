<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require 'db.php';

$usuario_id = $_SESSION['user_id'];

// Verificar se o usuário já tem um caixa aberto
$stmt = $pdo->prepare('SELECT id FROM caixa WHERE usuario_id = ? AND status = "Aberto"');
$stmt->execute([$usuario_id]);
$caixa_aberto = $stmt->fetch();

if ($caixa_aberto) {
    $error = "Você já tem um caixa aberto. Por favor, feche o caixa antes de abrir um novo.";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !$caixa_aberto) {
    $fundo_de_troco = str_replace(',', '.', $_POST['fundo_de_troco']);

    $stmt = $pdo->prepare('INSERT INTO caixa (data_abertura, fundo_de_troco, usuario_id, status) VALUES (NOW(), ?, ?, "Aberto")');
    if ($stmt->execute([$fundo_de_troco, $usuario_id])) {
        header("Location: vendas.php");
        exit;
    } else {
        $error = "Erro ao abrir caixa.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Abrir Caixa</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script>
        function formatCurrency(input) {
            let value = input.value.replace(/\D/g, ''); // Remove todos os caracteres não numéricos
            value = (parseFloat(value) / 100).toFixed(2); // Converte para formato de moeda com duas casas decimais
            value = value.replace('.', ','); // Substitui ponto por vírgula
            input.value = value; // Define o valor formatado no campo
        }
    </script>
</head>
<body>
    <div class="container">
        <h2 class="mt-5">Abrir Caixa</h2>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        <form method="POST" action="abrir_caixa.php">
            <div class="form-group">
                <label for="fundo_de_troco">Fundo de Troco</label>
                <input type="text" name="fundo_de_troco" class="form-control" id="fundo_de_troco" oninput="formatCurrency(this)" <?= $caixa_aberto ? 'disabled' : '' ?> required>
            </div>
            <button type="submit" class="btn btn-primary" <?= $caixa_aberto ? 'disabled' : '' ?>>Abrir Caixa</button>
        </form>
    </div>
</body>
</html>
