<?php
session_start();
if ($_SESSION['nivel_acesso'] !== 'admin') {
    header("Location: index.php");
    exit;
}
require '../db.php';

$id = $_GET['id'];
$stmt = $pdo->prepare('SELECT * FROM entregadores WHERE id = ?');
$stmt->execute([$id]);
$entregador = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$entregador) {
    die('Entregador não encontrado');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome'];
    $telefone = $_POST['telefone'];
    $status = $_POST['status'];

    $stmt = $pdo->prepare('UPDATE entregadores SET nome = ?, telefone = ?, status = ? WHERE id = ?');
    if ($stmt->execute([$nome, $telefone, $status, $id])) {
        header('Location: cadastrar_entregador.php');
        exit;
    } else {
        $error = "Erro ao atualizar entregador.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Entregador</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
<body>
    <div class="container">
        <h2 class="mt-5">Editar Entregador</h2>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        <form method="POST" action="editar_entregador.php?id=<?= $id ?>">
            <div class="form-group">
                <label for="nome">Nome</label>
                <input type="text" name="nome" class="form-control" id="nome" value="<?= htmlspecialchars($entregador['nome']) ?>" required>
            </div>
            <div class="form-group">
                <label for="telefone">Telefone</label>
                <input type="text" name="telefone" class="form-control" id="telefone" value="<?= htmlspecialchars($entregador['telefone']) ?>" required>
            </div>
            <div class="form-group">
                <label for="status">Status</label>
                <select name="status" class="form-control" id="status" required>
                    <option value="ativo" <?= $entregador['status'] == 'ativo' ? 'selected' : '' ?>>Ativo</option>
                    <option value="inativo" <?= $entregador['status'] == 'inativo' ? 'selected' : '' ?>>Inativo</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Salvar</button>
        </form>
    </div>
</body>
</html>
