<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome'];
    $cpf = $_POST['cpf'] ?? null;
    $telefone = $_POST['telefone'] ?? null;
    $cep = $_POST['cep'] ?? null;
    $endereco = $_POST['endereco'];
    $numero = $_POST['numero'];
    $bairro = $_POST['bairro'];
    $ponto_de_referencia = $_POST['ponto_de_referencia'] ?? null;
    $saldo_devedor = 0.0; // Inicializa o saldo devedor como 0.0

    // Verificar se o cliente já existe
    $stmt = $pdo->prepare('SELECT * FROM clientes WHERE cpf = ?');
    $stmt->execute([$cpf]);
    $cliente = $stmt->fetch();

    if ($cliente) {
        $error = "Cliente já existe.";
    } else {
        // Inserir novo cliente
        $stmt = $pdo->prepare('INSERT INTO clientes (nome, cpf, telefone, saldo_devedor, cep, endereco, numero, bairro, ponto_de_referencia) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
        if ($stmt->execute([$nome, $cpf, $telefone, $saldo_devedor, $cep, $endereco, $numero, $bairro, $ponto_de_referencia])) {
            $success = "Cliente cadastrado com sucesso.";
        } else {
            $error = "Erro ao cadastrar cliente.";
        }
    }
}

// Obter todos os clientes cadastrados
$stmt = $pdo->query('SELECT * FROM clientes');
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Exibir mensagens de erro e sucesso passadas pela URL
if (isset($_GET['error'])) {
    $error = urldecode($_GET['error']);
}
if (isset($_GET['success'])) {
    $success = urldecode($_GET['success']);
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Cliente</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
<body>
    <div class="container">
        <h2 class="mt-5">Cadastro de Cliente</h2>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>
        <form method="POST" action="cadastro_cliente.php">
            <div class="form-group">
                <label for="nome">Nome</label>
                <input type="text" name="nome" class="form-control" id="nome" required>
            </div>
            <div class="form-group">
                <label for="cpf">CPF</label>
                <input type="text" name="cpf" class="form-control" id="cpf">
            </div>
            <div class="form-group">
                <label for="telefone">Telefone</label>
                <input type="text" name="telefone" class="form-control" id="telefone">
            </div>
            <div class="form-group">
                <label for="cep">CEP</label>
                <input type="text" name="cep" class="form-control" id="cep">
            </div>
            <div class="form-group">
                <label for="endereco">Endereço</label>
                <input type="text" name="endereco" class="form-control" id="endereco" required>
            </div>
            <div class="form-group">
                <label for="numero">Número</label>
                <input type="text" name="numero" class="form-control" id="numero" required>
            </div>
            <div class="form-group">
                <label for="bairro">Bairro</label>
                <input type="text" name="bairro" class="form-control" id="bairro" required>
            </div>
            <div class="form-group">
                <label for="ponto_de_referencia">Ponto de Referência</label>
                <input type="text" name="ponto_de_referencia" class="form-control" id="ponto_de_referencia">
            </div>
            <button type="submit" class="btn btn-primary">Cadastrar</button>
        </form>

        <h2 class="mt-5">Clientes Cadastrados</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>CPF</th>
                    <th>Telefone</th>
                    <th>CEP</th>
                    <th>Endereço</th>
                    <th>Número</th>
                    <th>Bairro</th>
                    <th>Ponto de Referência</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($clientes as $cliente): ?>
                    <tr>
                        <td><?= htmlspecialchars($cliente['nome']) ?></td>
                        <td><?= htmlspecialchars($cliente['cpf']) ?></td>
                        <td><?= htmlspecialchars($cliente['telefone']) ?></td>
                        <td><?= htmlspecialchars($cliente['cep']) ?></td>
                        <td><?= htmlspecialchars($cliente['endereco']) ?></td>
                        <td><?= htmlspecialchars($cliente['numero']) ?></td>
                        <td><?= htmlspecialchars($cliente['bairro']) ?></td>
                        <td><?= htmlspecialchars($cliente['ponto_de_referencia']) ?></td>
                        <td>
                            <a href="editar_cliente.php?id=<?= $cliente['id'] ?>" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
                            <a href="excluir_cliente.php?id=<?= $cliente['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja excluir este cliente?')"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
