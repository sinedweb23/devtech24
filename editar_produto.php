<?php
session_start();
if ($_SESSION['nivel_acesso'] !== 'admin') {
    header("Location: index.php");
    exit;
}

require 'db.php';

// Obter o ID do produto a ser editado
$id = $_GET['id'];

// Obter os dados do produto
$stmt = $pdo->prepare('SELECT * FROM produtos WHERE id = ?');
$stmt->execute([$id]);
$produto = $stmt->fetch(PDO::FETCH_ASSOC);

// Obter todas as categorias
$stmt = $pdo->query('SELECT id, nome FROM categorias');
$categorias = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome'];
    $preco = str_replace(',', '.', str_replace('.', '', $_POST['preco'])); // Convertendo para formato decimal
    $preco_custo = str_replace(',', '.', str_replace('.', '', $_POST['preco_custo'])); // Convertendo para formato decimal
    $unidade = $_POST['unidade'];
    $categoria_id = $_POST['categoria_id'];
    $codigo = $_POST['codigo'];
    $status = $_POST['status'];
    $visivel_em = $_POST['visivel_em'];
    $estoque = $_POST['estoque'];
    $estoque_minimo = $_POST['estoque_minimo'];

    // Verificar se o novo código já existe em outro produto
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM produtos WHERE codigo = ? AND id != ?');
    $stmt->execute([$codigo, $id]);
    $codigo_existe = $stmt->fetchColumn();

    if ($codigo_existe) {
        $error = "Erro: Código do produto já existe.";
    } else {
        $stmt = $pdo->prepare('UPDATE produtos SET nome = ?, preco = ?, preco_custo = ?, unidade = ?, categoria_id = ?, codigo = ?, status = ?, visivel_em = ?, estoque = ?, estoque_minimo = ? WHERE id = ?');
        if ($stmt->execute([$nome, $preco, $preco_custo, $unidade, $categoria_id, $codigo, $status, $visivel_em, $estoque, $estoque_minimo, $id])) {
            $success = "Produto atualizado com sucesso.";
        } else {
            $error = "Erro ao atualizar produto.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Produto</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container">
        <h2 class="mt-5">Editar Produto</h2>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>
        <form method="POST" action="editar_produto.php?id=<?= $id ?>">
            <div class="form-group">
                <label for="nome">Nome do Produto</label>
                <input type="text" name="nome" class="form-control" id="nome" value="<?= $produto['nome'] ?>" required>
            </div>
            <div class="form-group">
                <label for="preco">Preço</label>
                <input type="text" name="preco" class="form-control" id="preco" value="<?= number_format($produto['preco'], 2, ',', '.') ?>" required>
            </div>
            <div class="form-group">
                <label for="preco_custo">Preço de Custo</label>
                <input type="text" name="preco_custo" class="form-control" id="preco_custo" value="<?= number_format($produto['preco_custo'], 2, ',', '.') ?>" required>
            </div>
            <div class="form-group">
                <label for="unidade">Unidade</label>
                <select name="unidade" class="form-control" id="unidade" required>
                    <option value="unidade" <?= $produto['unidade'] == 'unidade' ? 'selected' : '' ?>>Unidade</option>
                    <option value="kilograma" <?= $produto['unidade'] == 'kilograma' ? 'selected' : '' ?>>Kilograma</option>
                    <option value="centimetros" <?= $produto['unidade'] == 'centimetros' ? 'selected' : '' ?>>Centímetros</option>
                </select>
            </div>
            <div class="form-group">
                <label for="categoria_id">Categoria</label>
                <select name="categoria_id" class="form-control" id="categoria_id" required>
                    <?php foreach ($categorias as $categoria): ?>
                        <option value="<?= $categoria['id'] ?>" <?= $produto['categoria_id'] == $categoria['id'] ? 'selected' : '' ?>><?= $categoria['nome'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="codigo">Código</label>
                <input type="text" name="codigo" class="form-control" id="codigo" value="<?= $produto['codigo'] ?>" required>
            </div>
            <div class="form-group">
                <label for="status">Status</label>
                <select name="status" class="form-control" id="status" required>
                    <option value="ativo" <?= $produto['status'] == 'ativo' ? 'selected' : '' ?>>Ativo</option>
                    <option value="inativo" <?= $produto['status'] == 'inativo' ? 'selected' : '' ?>>Inativo</option>
                </select>
            </div>
            <div class="form-group">
                <label for="visivel_em">Visível em</label>
                <select name="visivel_em" class="form-control" id="visivel_em" required>
                    <option value="PDV" <?= $produto['visivel_em'] == 'PDV' ? 'selected' : '' ?>>PDV</option>
                    <option value="Site" <?= $produto['visivel_em'] == 'Site' ? 'selected' : '' ?>>Site</option>
                    <option value="Ambos" <?= $produto['visivel_em'] == 'Ambos' ? 'selected' : '' ?>>Ambos</option>
                </select>
            </div>
            <div class="form-group">
                <label for="estoque">Estoque</label>
                <input type="number" name="estoque" class="form-control" id="estoque" value="<?= $produto['estoque'] ?>" required>
            </div>
            <div class="form-group">
                <label for="estoque_minimo">Estoque Mínimo</label>
                <input type="number" name="estoque_minimo" class="form-control" id="estoque_minimo" value="<?= $produto['estoque_minimo'] ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Atualizar</button>
        </form>
    </div>

    <script>
        // Formatando o campo de valor enquanto o usuário digita
        function formatCurrency(input) {
            var value = input.value.replace(/\D/g, ''); // Remover todos os caracteres não numéricos
            value = (parseFloat(value) / 100).toFixed(2); // Converter para formato de moeda com duas casas decimais
            value = value.toString().replace(".", ","); // Substituir ponto por vírgula
            input.value = value; // Definir o valor formatado no campo
        }

        document.getElementById("preco").addEventListener("input", function() {
            formatCurrency(this);
        });

        document.getElementById("preco_custo").addEventListener("input", function() {
            formatCurrency(this);
        });

        // Evitar envio automático do formulário ao bipar o código de barras
        document.getElementById("codigo").addEventListener("keypress", function(event) {
            if (event.key === "Enter") {
                event.preventDefault();
                let nextElement = this.nextElementSibling;
                if (nextElement && nextElement.tagName === "INPUT") {
                    nextElement.focus();
                } else if (nextElement && nextElement.tagName === "SELECT") {
                    nextElement.focus();
                }
            }
        });
    </script>
</body>
</html>
