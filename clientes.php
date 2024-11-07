<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require 'db.php';

// Obter todos os clientes e seus saldos devedores
$stmt = $pdo->query('SELECT id, nome, saldo_devedor FROM clientes');
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Verificar se há um caixa aberto para o usuário
$usuario_id = $_SESSION['user_id'];
$stmt = $pdo->prepare('SELECT id FROM caixa WHERE usuario_id = ? AND status = "Aberto"');
$stmt->execute([$usuario_id]);
$caixa = $stmt->fetch();

if (!$caixa) {
    $error = "Nenhum caixa aberto encontrado. Por favor, abra um caixa antes de realizar vendas.";
} else {
    $caixa_id = $caixa['id'];
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clientes e Saldos Devedores</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>
<body>
    <div class="container">
        <h2 class="mt-5">Clientes e Saldos Devedores</h2>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        <table class="table table-bordered mt-3">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Saldo Devedor</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($clientes as $cliente): ?>
                    <tr>
                        <td><?= htmlspecialchars($cliente['nome']) ?></td>
                        <td>R$ <?= number_format($cliente['saldo_devedor'], 2, ',', '.') ?></td>
                        <td>
                            <button class="btn btn-primary" onclick="verCompras(<?= $cliente['id'] ?>)">Ver Compras</button>
                            <button class="btn btn-success" onclick="adicionarPagamento(<?= $cliente['id'] ?>)">Adicionar Pagamento</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Modal de Compras -->
    <div class="modal fade" id="comprasModal" tabindex="-1" role="dialog" aria-labelledby="comprasModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="comprasModalLabel">Compras do Cliente</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="comprasConteudo"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Pagamento -->
    <div class="modal fade" id="pagamentoModal" tabindex="-1" role="dialog" aria-labelledby="pagamentoModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="pagamentoModalLabel">Adicionar Pagamento</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="pagamentoForm">
                        <div class="form-group">
                            <label for="valorPagamento">Valor do Pagamento</label>
                            <input type="text" class="form-control" id="valorPagamento" required>
                        </div>
                        <div class="form-group">
                            <label for="formaPagamento">Forma de Pagamento</label>
                            <select class="form-control" id="formaPagamento" required>
                                <option value="dinheiro">Dinheiro</option>
                                <option value="debito">Débito</option>
                                <option value="credito">Crédito</option>
                                <option value="pix">Pix</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Adicionar Pagamento</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        let clienteId = null;

        function verCompras(id) {
            $.ajax({
                url: 'ver_compras.php',
                method: 'GET',
                data: { cliente_id: id },
                success: function(response) {
                    $('#comprasConteudo').html(response);
                    $('#comprasModal').modal('show');
                }
            });
        }

        function adicionarPagamento(id) {
            clienteId = id;
            $('#pagamentoModal').modal('show');
        }

        $('#pagamentoForm').on('submit', function(e) {
            e.preventDefault();
            const valor = parseFloat($('#valorPagamento').val().replace(',', '.'));
            const formaPagamento = $('#formaPagamento').val();

            if (!valor || valor <= 0) {
                alert('Por favor, insira um valor válido.');
                return;
            }

            const pagamento = {
                usuario_id: <?= $_SESSION['user_id'] ?>,
                caixa_id: <?= $caixa_id ?>,
                cliente_conveniado: clienteId,
                forma_pagamento1: formaPagamento,
                valor_pagamento1: valor,
                forma_pagamento2: null,
                valor_pagamento2: 0,
                total: valor,
                itens: [],
                tipo: 'pagamento' // Definindo o tipo como pagamento
            };

            fetch('salvar_venda.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(pagamento)
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert('Pagamento registrado com sucesso.');
                    $('#pagamentoModal').modal('hide');
                    location.reload();
                } else {
                    alert('Erro ao registrar pagamento.');
                }
            })
            .catch(error => {
                alert('Erro ao registrar pagamento.');
                console.error(error);
            });
        });

        function formatCurrency(input) {
            let value = input.value.replace(/\D/g, '');
            value = (parseFloat(value) / 100).toFixed(2);
            value = value.toString().replace(".", ",");
            input.value = value;
        }

        $('#valorPagamento').on('input', function() {
            formatCurrency(this);
        });
    </script>
</body>
</html>
