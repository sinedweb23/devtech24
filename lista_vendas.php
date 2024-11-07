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
$user_id = $_SESSION['user_id'];

// Obter as vendas com base no nível de acesso
if ($nivel_acesso === 'admin') {
    $stmt = $pdo->prepare('SELECT vendas.id, vendas.total, vendas.data_venda, usuarios.username AS vendedor FROM vendas JOIN usuarios ON vendas.usuario_id = usuarios.id ORDER BY vendas.data_venda DESC');
    $stmt->execute();
} else {
    $stmt = $pdo->prepare('SELECT vendas.id, vendas.total, vendas.data_venda, usuarios.username AS vendedor FROM vendas JOIN usuarios ON vendas.usuario_id = usuarios.id WHERE vendas.usuario_id = ? ORDER BY vendas.data_venda DESC');
    $stmt->execute([$user_id]);
}
$vendas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Vendas</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
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
        .items-container {
            display: none;
            margin-top: 20px;
        }
        .items-table {
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="mt-5">Lista de Vendas</h2>
        <div class="input-group mb-3">
            <div class="input-group-prepend">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
            </div>
            <input type="text" id="search" class="form-control" placeholder="Pesquise por vendas (vendedor ou data)">
        </div>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Vendedor</th>
                    <th>Total</th>
                    <th>Data e Hora</th>
                    <?php if ($nivel_acesso === 'admin'): ?>
                        <th>Ações</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody id="vendasList">
                <?php foreach ($vendas as $venda): ?>
                    <tr data-id="<?= $venda['id'] ?>" class="venda-row">
                        <td><?= $venda['id'] ?></td>
                        <td><?= $venda['vendedor'] ?></td>
                        <td>R$ <?= number_format($venda['total'], 2, ',', '.') ?></td>
                        <td><?= date('d/m/Y H:i:s', strtotime($venda['data_venda'])) ?></td>
                        <?php if ($nivel_acesso === 'admin'): ?>
                            <td>
                                <a href="editar_venda.php?id=<?= $venda['id'] ?>" class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button class="btn btn-danger btn-sm delete-btn">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        <?php endif; ?>
                    </tr>
                    <tr class="items-container" id="items-<?= $venda['id'] ?>">
                        <td colspan="5">
                            <table class="table table-bordered items-table">
                                <thead>
                                    <tr>
                                        <th>Produto</th>
                                        <th>Quantidade</th>
                                        <th>Preço</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody class="items-body"></tbody>
                            </table>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Modal de confirmação -->
    <div class="modal fade" id="confirmCancelModal" tabindex="-1" aria-labelledby="confirmCancelModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmCancelModalLabel">Cancelar Venda</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Tem certeza que deseja cancelar esta venda?</p>
                    <input type="hidden" id="vendaIdToCancel">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" id="confirmCancelBtn">Sim, cancelar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        $(document).ready(function() {
            // Função para filtrar vendas na tabela
            $('#search').on('input', function() {
                let filter = this.value.toUpperCase();
                let rows = $('#vendasList tr.venda-row');

                rows.each(function() {
                    let vendedor = $(this).find('td').eq(1).text().toUpperCase();
                    let data = $(this).find('td').eq(3).text().toUpperCase();

                    if (vendedor.indexOf(filter) > -1 || data.indexOf(filter) > -1) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            });

            // Função para mostrar itens da venda
            $('.venda-row').on('click', function() {
                let vendaId = $(this).data('id');
                let itemsContainer = $('#items-' + vendaId);
                let itemsBody = itemsContainer.find('.items-body');

                if (itemsContainer.is(':visible')) {
                    itemsContainer.hide();
                } else {
                    itemsBody.empty();
                    $.ajax({
                        url: 'obter_itens_venda.php',
                        method: 'GET',
                        data: { venda_id: vendaId },
                        success: function(data) {
                            data.forEach(function(item) {
                                itemsBody.append(`
                                    <tr>
                                        <td>${item.produto_nome}</td>
                                        <td>${item.quantidade}</td>
                                        <td>R$ ${parseFloat(item.preco).toFixed(2).replace('.', ',')}</td>
                                        <td>R$ ${(item.quantidade * item.preco).toFixed(2).replace('.', ',')}</td>
                                    </tr>
                                `);
                            });
                            itemsContainer.show();
                        }
                    });
                }
            });

            // Função para cancelar venda via AJAX
            $('.delete-btn').on('click', function(e) {
                e.stopPropagation(); // Prevenir o clique na linha da venda
                let vendaId = $(this).closest('tr.venda-row').data('id');
                $('#vendaIdToCancel').val(vendaId);
                $('#confirmCancelModal').modal('show');
            });

            $('#confirmCancelBtn').on('click', function() {
                let vendaId = $('#vendaIdToCancel').val();

                $.ajax({
                    url: 'cancelar_venda.php',
                    method: 'POST',
                    data: { id: vendaId },
                    success: function(response) {
                        $('#confirmCancelModal').modal('hide');
                        location.reload();
                    }
                });
            });
        });
    </script>
</body>
</html>
