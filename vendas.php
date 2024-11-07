<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require 'db.php';

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

// Obter todos os produtos, excluindo inativos e visíveis apenas no site
$stmt = $pdo->query('SELECT id, nome, preco, codigo FROM produtos WHERE status = "ativo" AND visivel_em != "Site"');
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obter todos os clientes conveniados
$stmt = $pdo->query('SELECT id, nome FROM clientes');
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obter dados da loja
$stmt = $pdo->query('SELECT * FROM configuracoes_loja WHERE id = 1');
$loja = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vendas</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
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
        .total-container, .payment-container, .change-container {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            margin-top: 20px;
        }
        .total-container h4, .change-container h4 {
            font-size: 1.5em;
        }
        .form-control {
            text-align: left;
        }
        .fa-trash, .fa-tag {
            cursor: pointer;
        }
        .fa-trash {
            color: red;
        }
        .fa-tag {
            color: green;
        }
        .hidden {
            display: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
            <a href="abrir_caixa.php" class="btn btn-primary">Abrir Caixa</a>
        <?php else: ?>
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2>Vendas</h2>
                <button class="btn btn-danger" onclick="window.location.href='logout.php'">Sair</button>
            </div>

            <div class="input-group mb-3">
                <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fas fa-barcode"></i></span>
                </div>
                <input type="text" id="codigo" class="form-control" placeholder="Digite o código do produto" style="text-align: left;">
            </div>

            <div class="input-group mb-3">
                <div class="input-group-prepend">
                    <label class="input-group-text" for="produtos">Produtos</label>
                </div>
                <select id="produtos" class="custom-select">
                    <?php foreach ($produtos as $produto): ?>
                        <option value="<?= $produto['id'] ?>" data-preco="<?= $produto['preco'] ?>" data-codigo="<?= $produto['codigo'] ?>">
                            <?= $produto['nome'] ?> - R$ <?= number_format($produto['preco'], 2, ',', '.') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="input-group-append">
                    <button id="adicionarProduto" class="btn btn-primary">Adicionar ao Carrinho</button>
                </div>
            </div>

            <h3>Carrinho</h3>
            <table class="table table-bordered" id="carrinho">
                <thead>
                    <tr>
                        <th>Produto</th>
                        <th>Quantidade</th>
                        <th>Preço</th>
                        <th>Desconto (%)</th>
                        <th>Total</th>
                        <th>Ação</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>

            <div class="total-container d-flex justify-content-between align-items-center">
                <h4>Total da Compra: R$ <span id="totalCompra">0,00</span></h4>
                <button id="botaoConvenio" class="btn btn-warning">Convênio</button>
            </div>

            <div class="payment-container">
                <h5>Forma de Pagamento</h5>
                <div class="form-group">
                    <label for="forma_pagamento1">Forma de Pagamento 1</label>
                    <select id="forma_pagamento1" class="form-control">
                        <option value="">Selecione</option>
                        <option value="dinheiro">Dinheiro</option>
                        <option value="debito">Débito</option>
                        <option value="credito">Crédito</option>
                        <option value="pix">Pix</option>
                    </select>
                    <input type="text" id="valor_pagamento1" class="form-control" placeholder="0,00">
                </div>
                <div class="form-group">
                    <label for="forma_pagamento2">Forma de Pagamento 2</label>
                    <select id="forma_pagamento2" class="form-control">
                        <option value="">Nenhum</option>
                        <option value="dinheiro">Dinheiro</option>
                        <option value="debito">Débito</option>
                        <option value="credito">Crédito</option>
                        <option value="pix">Pix</option>
                    </select>
                    <input type="text" id="valor_pagamento2" class="form-control" placeholder="0,00">
                </div>
            </div>

            <div class="change-container">
                <h4>Troco: R$ <span id="troco">0,00</span></h4>
                <button id="finalizarVenda" class="btn btn-success btn-lg btn-block">Finalizar Venda</button>
            </div>
        <?php endif; ?>
    </div>

    <!-- Modal de autenticação do administrador -->
    <div class="modal fade" id="adminAuthModal" tabindex="-1" role="dialog" aria-labelledby="adminAuthModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="adminAuthModalLabel">Autenticação do Administrador</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="adminAuthForm">
                        <div class="form-group">
                            <label for="adminLogin">Login</label>
                            <input type="text" class="form-control" id="adminLogin" required>
                        </div>
                        <div class="form-group">
                            <label for="adminSenha">Senha</label>
                            <input type="password" class="form-control" id="adminSenha" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Autenticar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de seleção de cliente conveniado -->
    <div class="modal fade" id="convenioModal" tabindex="-1" role="dialog" aria-labelledby="convenioModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="convenioModalLabel">Selecionar Cliente Conveniado</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="convenioForm">
                        <div class="form-group">
                            <label for="clienteConveniado">Cliente</label>
                            <select id="clienteConveniado" class="form-control" required>
                                <option value="">Selecione o cliente</option>
                                <?php foreach ($clientes as $cliente): ?>
                                    <option value="<?= $cliente['id'] ?>"><?= htmlspecialchars($cliente['nome']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Confirmar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        let carrinho = [];
        let totalCompra = 0;
        let descontoEmProduto = null;
        let loja = <?= json_encode($loja); ?>;
        let clienteConveniado = null;
        let nomeClienteConveniado = '';

        function formatCurrency(input) {
            if (input.id === 'codigo' || input.id === 'adminLogin' || input.id === 'adminSenha') return;
            var value = input.value.replace(/\D/g, '');
            value = (parseFloat(value) / 100).toFixed(2);
            value = value.toString().replace(".", ",");
            input.value = value;
        }

        $('#codigo').on('keypress', function(e) {
            if (e.which == 13) {
                const codigo = $(this).val().trim();
                if (codigo) {
                    let encontrado = false;
                    $('#produtos option').each(function() {
                        if ($(this).data('codigo') == codigo) {
                            $(this).prop('selected', true);
                            adicionarProduto();
                            encontrado = true;
                            $('#codigo').val('');
                            return false;
                        }
                    });
                    if (!encontrado) {
                        alert('Produto não encontrado.');
                    }
                }
            }
        });

        $('#adicionarProduto').on('click', function() {
            adicionarProduto();
            $('#codigo').val('');
        });

        function adicionarProduto() {
            const produtoId = $('#produtos').val();
            const produtoNome = $('#produtos option:selected').text();
            const produtoPreco = parseFloat($('#produtos option:selected').data('preco'));
            const quantidade = 1;
            const total = produtoPreco * quantidade;

            const itemCarrinho = {
                id: produtoId,
                nome: produtoNome,
                preco: produtoPreco,
                quantidade: quantidade,
                desconto: 0,
                total: total
            };

            carrinho.push(itemCarrinho);
            atualizarCarrinho();
        }

        function atualizarCarrinho() {
            const tbody = $('#carrinho tbody');
            tbody.empty();
            totalCompra = 0;

            carrinho.forEach(item => {
                item.total = (item.preco * item.quantidade) * (1 - item.desconto / 100);
                const row = `
                    <tr>
                        <td>${item.nome}</td>
                        <td><input type="number" class="form-control quantidade" data-id="${item.id}" value="${item.quantidade}" min="1"></td>
                        <td>${item.preco.toFixed(2).replace('.', ',')}</td>
                        <td>
                            <input type="number" class="form-control desconto" data-id="${item.id}" value="${item.desconto}" min="0" max="100">
                            <i class="fas fa-tag aplicar-desconto" data-id="${item.id}"></i>
                        </td>
                        <td>${item.total.toFixed(2).replace('.', ',')}</td>
                        <td><i class="fas fa-trash remover" data-id="${item.id}"></i></td>
                    </tr>
                `;
                tbody.append(row);
                totalCompra += item.total;
            });

            $('#totalCompra').text(totalCompra.toFixed(2).replace('.', ','));
        }

        $(document).on('change', '.quantidade', function() {
            const id = $(this).data('id');
            const quantidade = parseInt($(this).val());

            const item = carrinho.find(item => item.id == id);
            item.quantidade = quantidade;
            item.total = (item.preco * quantidade) * (1 - item.desconto / 100);

            atualizarCarrinho();
        });

        $(document).on('click', '.remover', function() {
            const id = $(this).data('id');
            carrinho = carrinho.filter(item => item.id != id);
            atualizarCarrinho();
        });

        $(document).on('click', '.aplicar-desconto', function() {
            descontoEmProduto = $(this).data('id');
            $('#adminAuthModal').modal('show');
        });

        $('#adminAuthForm').on('submit', function(e) {
            e.preventDefault();

            const adminLogin = $('#adminLogin').val();
            const adminSenha = $('#adminSenha').val();

            $.ajax({
                url: 'autenticar_admin.php',
                method: 'POST',
                data: {
                    login: adminLogin,
                    senha: adminSenha
                },
                success: function(response) {
                    if (typeof response === 'string') {
                        response = JSON.parse(response);
                    }
                    if (response.autenticado) {
                        const id = descontoEmProduto;
                        const desconto = parseFloat($(`.desconto[data-id='${id}']`).val()) || 0;

                        const item = carrinho.find(item => item.id == id);
                        item.desconto = desconto;
                        item.total = (item.preco * item.quantidade) * (1 - desconto / 100);

                        atualizarCarrinho();
                        $('#adminAuthModal').modal('hide');
                    } else {
                        alert('Autenticação do administrador falhou. Desconto não aplicado.');
                    }
                },
                error: function(error) {
                    alert('Erro ao autenticar o administrador.');
                }
            });
        });

        $('#forma_pagamento1, #forma_pagamento2').on('change', function() {
            const pagamento1 = $('#forma_pagamento1').val();
            const pagamento2 = $('#forma_pagamento2').val();
            
            if (pagamento1) {
                $('#valor_pagamento1').removeClass('hidden');
            } else {
                $('#valor_pagamento1').addClass('hidden').val('');
            }

            if (pagamento2 && pagamento2 !== pagamento1) {
                $('#valor_pagamento2').removeClass('hidden');
                const valorPagamento1 = parseFloat($('#valor_pagamento1').val().replace(',', '.')) || 0;
                const valorRestante = totalCompra - valorPagamento1;
                $('#valor_pagamento2').val(valorRestante.toFixed(2).replace('.', ','));
            } else {
                $('#valor_pagamento2').addClass('hidden').val('');
            }

            calcularTroco();
        });

        $('input[type="text"]').on('input', function() {
            formatCurrency(this);
            calcularTroco();
        });

        function calcularTroco() {
            const valorPagamento1 = parseFloat($('#valor_pagamento1').val().replace(',', '.')) || 0;
            const valorPagamento2 = parseFloat($('#valor_pagamento2').val().replace(',', '.')) || 0;
            const totalPago = valorPagamento1 + valorPagamento2;

            let troco = totalPago - totalCompra;
            troco = troco < 0 ? 0 : troco;

            $('#troco').text(troco.toFixed(2).replace('.', ','));
        }

        $('#botaoConvenio').on('click', function() {
            $('#convenioModal').modal('show');
        });

        $('#convenioForm').on('submit', function(e) {
            e.preventDefault();
            clienteConveniado = $('#clienteConveniado').val();
            nomeClienteConveniado = $('#clienteConveniado option:selected').text();
            if (!clienteConveniado) {
                alert('Selecione um cliente conveniado.');
                return;
            }
            $('#convenioModal').modal('hide');
            finalizarVendaConvenio();  // Chama a função para finalizar a venda ao confirmar o cliente conveniado
        });

        $('#finalizarVenda').on('click', function() {
            finalizarVenda();
        });

        function finalizarVendaConvenio() {
            const venda = {
                usuario_id: <?= $_SESSION['user_id'] ?>,
                caixa_id: <?= $caixa_id ?>,
                forma_pagamento1: 'convenio',
                valor_pagamento1: totalCompra,
                forma_pagamento2: null,
                valor_pagamento2: 0,
                total: totalCompra,
                itens: carrinho,
                cliente_conveniado: clienteConveniado,
                nome_cliente_conveniado: nomeClienteConveniado
            };

            $.ajax({
                url: 'salvar_venda.php',
                method: 'POST',
                data: JSON.stringify(venda),
                contentType: 'application/json',
                success: function(response) {
                    alert('Venda finalizada com sucesso.');
                    imprimirCupom(venda, response.venda_id); // Chama a função de impressão do cupom
                    location.reload();
                },
                error: function(error) {
                    alert('Erro ao finalizar a venda.');
                }
            });
        }

        function finalizarVenda() {
            const forma_pagamento1 = $('#forma_pagamento1').val();
            let valor_pagamento1 = parseFloat($('#valor_pagamento1').val().replace(',', '.')) || 0;
            const forma_pagamento2 = $('#forma_pagamento2').val();
            let valor_pagamento2 = parseFloat($('#valor_pagamento2').val().replace(',', '.')) || 0;

            if (totalCompra <= 0) {
                alert('Adicione produtos ao carrinho antes de finalizar a venda.');
                return;
            }

            if (valor_pagamento1 + valor_pagamento2 < totalCompra) {
                alert('O valor pago é menor que o total da compra.');
                return;
            }

            // Ajustar valores de pagamento
            if (valor_pagamento1 > totalCompra) {
                valor_pagamento1 = totalCompra;
                valor_pagamento2 = 0;
            } else if (valor_pagamento1 + valor_pagamento2 > totalCompra) {
                valor_pagamento2 = totalCompra - valor_pagamento1;
            }

            const venda = {
                usuario_id: <?= $_SESSION['user_id'] ?>,
                caixa_id: <?= $caixa_id ?>,
                forma_pagamento1: forma_pagamento1,
                valor_pagamento1: valor_pagamento1,
                forma_pagamento2: forma_pagamento2,
                valor_pagamento2: valor_pagamento2,
                total: totalCompra,
                itens: carrinho,
                cliente_conveniado: null,
                nome_cliente_conveniado: null
            };

            $.ajax({
                url: 'salvar_venda.php',
                method: 'POST',
                data: JSON.stringify(venda),
                contentType: 'application/json',
                success: function(response) {
                    alert('Venda finalizada com sucesso.');
                    imprimirCupom(venda, response.venda_id); // Chama a função de impressão do cupom
                    location.reload();
                },
                error: function(error) {
                    alert('Erro ao finalizar a venda.');
                }
            });
        }

        function imprimirCupom(venda, vendaId) {
            let janela = window.open('', 'PRINT', 'height=400,width=600');
            janela.document.write('<html><head><title>Cupom Não Fiscal</title>');
            janela.document.write('</head><body>');
            janela.document.write('<h3>Nome da Loja: ' + loja.nome_loja + '</h3>');
            janela.document.write('<p>Endereço: ' + loja.endereco + '</p>');
            janela.document.write('<p>CNPJ: ' + loja.cnpj + '</p>');
            janela.document.write('<p>IE: ' + loja.ie + '</p>');
            janela.document.write('<p>Telefone: ' + loja.telefone + '</p>');
            janela.document.write('<hr>');
            janela.document.write('<h4>Cupom Não Fiscal</h4>');
            janela.document.write('<p>ID: ' + vendaId + '</p>');
            janela.document.write('<p><br></p>');
            venda.itens.forEach(function(item) {
                janela.document.write('<p>' + item.id + ' ' + item.nome + ' ' + item.quantidade + ' ' + 'R$ ' + item.preco.toFixed(2).replace('.', ',') + ' ' + 'R$ ' + item.total.toFixed(2).replace('.', ',') + ' ' + 'Desconto: ' + item.desconto + '%</p>');
            });
            janela.document.write('<hr>');
            janela.document.write('<p>Total: R$ ' + venda.total.toFixed(2).replace('.', ',') + '</p>');
            janela.document.write('<p>Forma de Pagamento 1: ' + venda.forma_pagamento1 + ' - R$ ' + venda.valor_pagamento1.toFixed(2).replace('.', ',') + '</p>');
            if (venda.forma_pagamento2) {
                janela.document.write('<p>Forma de Pagamento 2: ' + venda.forma_pagamento2 + ' - R$ ' + venda.valor_pagamento2.toFixed(2).replace('.', ',') + '</p>');
            }
            if (venda.cliente_conveniado) {
                janela.document.write('<p>Cliente Conveniado: ' + venda.nome_cliente_conveniado + '</p>');
            }
            janela.document.write('</body></html>');

            janela.document.close();
            janela.focus();
            janela.print();
            janela.close();
        }
    </script>
</body>
</html>
