<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

require 'db.php';

$cliente_id = $_GET['id'];

// Obter informações do cliente
$stmt = $pdo->prepare('SELECT * FROM clientes WHERE id = ?');
$stmt->execute([$cliente_id]);
$cliente = $stmt->fetch();

if (!$cliente) {
    $error = "Cliente não encontrado.";
    header("Location: cadastro_cliente.php?error=" . urlencode($error));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome'];
    $cpf = $_POST['cpf'];
    $telefone = $_POST['telefone'];
    $cep = $_POST['cep'];
    $endereco = $_POST['endereco'];
    $numero = $_POST['numero'];
    $bairro = $_POST['bairro'];
    $ponto_de_referencia = $_POST['ponto_de_referencia'];

    // Atualizar informações do cliente
    $stmt = $pdo->prepare('UPDATE clientes SET nome = ?, cpf = ?, telefone = ?, cep = ?, endereco = ?, numero = ?, bairro = ?, ponto_de_referencia = ? WHERE id = ?');
    if ($stmt->execute([$nome, $cpf, $telefone, $cep, $endereco, $numero, $bairro, $ponto_de_referencia, $cliente_id])) {
        $success = "Cliente atualizado com sucesso.";
        header("Location: cadastro_cliente.php?success=" . urlencode($success));
        exit;
    } else {
        $error = "Erro ao atualizar cliente.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Cliente</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
<body>
    <div class="container">
        <h2 class="mt-5">Editar Cliente</h2>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        <form method="POST" action="editar_cliente.php?id=<?= $cliente_id ?>">
            <div class="form-group">
                <label for="nome">Nome</label>
                <input type="text" name="nome" class="form-control" id="nome" value="<?= htmlspecialchars($cliente['nome']) ?>" required>
            </div>
            <div class="form-group">
                <label for="cpf">CPF</label>
                <input type="text" name="cpf" class="form-control" id="cpf" value="<?= htmlspecialchars($cliente['cpf']) ?>">
            </div>
            <div class="form-group">
                <label for="telefone">Telefone</label>
                <input type="text" name="telefone" class="form-control" id="telefone" value="<?= htmlspecialchars($cliente['telefone']) ?>">
            </div>
            <div class="form-group">
                <label for="cep">CEP</label>
                <input type="text" name="cep" class="form-control" id="cep" value="<?= htmlspecialchars($cliente['cep']) ?>">
            </div>
            <div class="form-group">
                <label for="endereco">Endereço</label>
                <input type="text" name="endereco" class="form-control" id="endereco" value="<?= htmlspecialchars($cliente['endereco']) ?>" required>
            </div>
            <div class="form-group">
                <label for="numero">Número</label>
                <input type="text" name="numero" class="form-control" id="numero" value="<?= htmlspecialchars($cliente['numero']) ?>" required>
            </div>
            <div class="form-group">
                <label for="bairro">Bairro</label>
                <input type="text" name="bairro" class="form-control" id="bairro" value="<?= htmlspecialchars($cliente['bairro']) ?>" required>
            </div>
            <div class="form-group">
                <label for="ponto_de_referencia">Ponto de Referência</label>
                <input type="text" name="ponto_de_referencia" class="form-control" id="ponto_de_referencia" value="<?= htmlspecialchars($cliente['ponto_de_referencia']) ?>">
            </div>
            <button type="submit" class="btn btn-primary">Atualizar</button>
        </form>
    </div>
</body>
</html>
