<?php
session_start();
require 'db.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Obter o nível de acesso do usuário
$nivel_acesso = $_SESSION['nivel_acesso'];

// Obter todos os produtos
$stmt = $pdo->query('SELECT id, nome, preco, codigo, estoque, status FROM produtos');
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Produtos</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            margin: 0;
        }
        .container-fluid {
            margin-top: 20px;
        }
        .table thead th {
            background-color: #343a40;
            color: white;
        }
        .table tbody tr {
            background-color: white;
        }
        .table tbody tr:nth-of-type(odd) {
            background-color: #f2f2f2;
        }
        .table td, .table th {
            vertical-align: middle;
            text-align: center;
        }
        .form-control {
            text-align: left;
        }
        .fa-edit, .fa-trash {
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <h2 class="mt-5">Lista de Produtos</h2>
        <div class="input-group mb-3">
            <div class="input-group-prepend">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
            </div>
            <input type="text" id="search" class="form-control" placeholder="Pesquise por produtos (código ou nome)">
        </div>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success"><?= $_GET['success'] ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['confirm_inativar'])): ?>
            <div class="alert alert-warning">
                <form method="POST" action="inativar_produto.php">
                    <input type="hidden" name="id" value="<?= $_SESSION['confirm_inativar'] ?>">
                    <p>Este produto não pode ser excluído porque já está associado a uma venda. Deseja inativar o produto?</p>
                    <button type="submit" class="btn btn-warning">Sim, inativar</button>
                    <a href="produtos.php" class="btn btn-secondary">Cancelar</a>
                </form>
            </div>
            <?php unset($_SESSION['confirm_inativar']); ?>
        <?php endif; ?>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Produto</th>
                    <th>Valor</th>
                    <th>Estoque</th>
                    <th>Status</th>
                    <?php if ($nivel_acesso === 'admin'): ?>
                        <th>Ações</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody id="productList">
                <?php foreach ($produtos as $produto): ?>
                    <tr>
                        <td><?= $produto['codigo'] ?></td>
                        <td><?= $produto['nome'] ?></td>
                        <td>R$ <?= number_format($produto['preco'], 2, ',', '.') ?></td>
                        <td><?= $produto['estoque'] ?></td>
                        <td><?= $produto['status'] == 'ativo' ? 'Ativo' : 'Inativo' ?></td>
                        <?php if ($nivel_acesso === 'admin'): ?>
                            <td>
                                <a href="editar_produto.php?id=<?= $produto['id'] ?>" class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="deletar_produto.php?id=<?= $produto['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja deletar este produto?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script>
        document.getElementById('search').addEventListener('input', function() {
            let filter = this.value.toUpperCase();
            let rows = document.getElementById('productList').getElementsByTagName('tr');

            for (let i = 0; i < rows.length; i++) {
                let codigo = rows[i].getElementsByTagName('td')[0].textContent.toUpperCase();
                let nome = rows[i].getElementsByTagName('td')[1].textContent.toUpperCase();

                if (codigo.indexOf(filter) > -1 || nome.indexOf(filter) > -1) {
                    rows[i].style.display = '';
                } else {
                    rows[i].style.display = 'none';
                }
            }
        });
    </script>
</body>
</html>
