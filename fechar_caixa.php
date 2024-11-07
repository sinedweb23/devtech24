<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require 'db.php';

$usuario_id = $_SESSION['user_id'];

// Obter o caixa aberto do usuário
$stmt = $pdo->prepare('SELECT id, fundo_de_troco FROM caixa WHERE usuario_id = ? AND status = "Aberto"');
$stmt->execute([$usuario_id]);
$caixa = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$caixa) {
    $error = "Nenhum caixa aberto encontrado.";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['verify'])) {
    $caixa_id = $caixa['id'];
    $total_dinheiro = (float) str_replace(',', '.', str_replace('.', '', $_POST['total_dinheiro']));
    $total_credito = (float) str_replace(',', '.', str_replace('.', '', $_POST['total_credito']));
    $total_debito = (float) str_replace(',', '.', str_replace('.', '', $_POST['total_debito']));
    $total_pix = (float) str_replace(',', '.', str_replace('.', '', $_POST['total_pix']));

    // Obter os valores de suprimentos e sangrias
    $stmt = $pdo->prepare('SELECT SUM(valor) as total_suprimentos FROM movimentacoes_caixa WHERE caixa_id = ? AND tipo = "suprimento"');
    $stmt->execute([$caixa_id]);
    $total_suprimentos = $stmt->fetchColumn() ?: 0;

    $stmt = $pdo->prepare('SELECT SUM(valor) as total_sangrias FROM movimentacoes_caixa WHERE caixa_id = ? AND tipo = "sangria"');
    $stmt->execute([$caixa_id]);
    $total_sangrias = $stmt->fetchColumn() ?: 0;

    // Calcular os totais de vendas para cada forma de pagamento
    $stmt = $pdo->prepare('
        SELECT 
            COALESCE(SUM(CASE WHEN forma_pagamento1 = "dinheiro" THEN valor_pagamento1 ELSE 0 END), 0)
            + COALESCE(SUM(CASE WHEN forma_pagamento2 = "dinheiro" THEN valor_pagamento2 ELSE 0 END), 0) AS total_dinheiro,
            COALESCE(SUM(CASE WHEN forma_pagamento1 = "credito" THEN valor_pagamento1 ELSE 0 END), 0)
            + COALESCE(SUM(CASE WHEN forma_pagamento2 = "credito" THEN valor_pagamento2 ELSE 0 END), 0) AS total_credito,
            COALESCE(SUM(CASE WHEN forma_pagamento1 = "debito" THEN valor_pagamento1 ELSE 0 END), 0)
            + COALESCE(SUM(CASE WHEN forma_pagamento2 = "debito" THEN valor_pagamento2 ELSE 0 END), 0) AS total_debito,
            COALESCE(SUM(CASE WHEN forma_pagamento1 = "pix" THEN valor_pagamento1 ELSE 0 END), 0)
            + COALESCE(SUM(CASE WHEN forma_pagamento2 = "pix" THEN valor_pagamento2 ELSE 0 END), 0) AS total_pix
        FROM vendas 
        WHERE caixa_id = ?
    ');
    $stmt->execute([$caixa_id]);
    $totais_vendas = $stmt->fetch(PDO::FETCH_ASSOC);

    // Calcular saldo real em dinheiro
    $saldo_real_dinheiro = $totais_vendas['total_dinheiro'] + $caixa['fundo_de_troco'] + $total_suprimentos - $total_sangrias;

    // Comparar os totais informados com os calculados
    $diferenca_dinheiro = $total_dinheiro - $saldo_real_dinheiro;
    $diferenca_credito = $total_credito - $totais_vendas['total_credito'];
    $diferenca_debito = $total_debito - $totais_vendas['total_debito'];
    $diferenca_pix = $total_pix - $totais_vendas['total_pix'];

    $differences_html = "
    <table class='table mt-5'>
        <thead>
            <tr>
                <th>Tipo</th>
                <th>Esperado</th>
                <th>Informado</th>
                <th>Diferença</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Dinheiro</td>
                <td>" . number_format($saldo_real_dinheiro, 2, ',', '.') . "</td>
                <td>" . number_format($total_dinheiro, 2, ',', '.') . "</td>
                <td>" . number_format($diferenca_dinheiro, 2, ',', '.') . "</td>
            </tr>
            <tr>
                <td>Crédito</td>
                <td>" . number_format($totais_vendas['total_credito'], 2, ',', '.') . "</td>
                <td>" . number_format($total_credito, 2, ',', '.') . "</td>
                <td>" . number_format($diferenca_credito, 2, ',', '.') . "</td>
            </tr>
            <tr>
                <td>Débito</td>
                <td>" . number_format($totais_vendas['total_debito'], 2, ',', '.') . "</td>
                <td>" . number_format($total_debito, 2, ',', '.') . "</td>
                <td>" . number_format($diferenca_debito, 2, ',', '.') . "</td>
            </tr>
            <tr>
                <td>PIX</td>
                <td>" . number_format($totais_vendas['total_pix'], 2, ',', '.') . "</td>
                <td>" . number_format($total_pix, 2, ',', '.') . "</td>
                <td>" . number_format($diferenca_pix, 2, ',', '.') . "</td>
            </tr>
        </tbody>
    </table>
    <form method='POST' action='confirmar_fechamento_caixa.php'>
        <input type='hidden' name='caixa_id' value='$caixa_id'>
        <input type='hidden' name='total_dinheiro' value='$total_dinheiro'>
        <input type='hidden' name='total_credito' value='$total_credito'>
        <input type='hidden' name='total_debito' value='$total_debito'>
        <input type='hidden' name='total_pix' value='$total_pix'>
        <button type='submit' class='btn btn-success'>Confirmar Fechamento</button>
    </form>
    ";
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fechar Caixa</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script>
        function formatCurrency(input) {
            let value = input.value.replace(/\D/g, ''); // Remove todos os caracteres não numéricos
            value = (parseFloat(value) / 100).toFixed(2); // Converte para formato de moeda com duas casas decimais
            value = value.replace('.', ','); // Substitui ponto por vírgula
            input.value = value; // Define o valor formatado no campo
        }

        $(document).ready(function() {
            $('#total_dinheiro, #total_credito, #total_debito, #total_pix').on('input', function() {
                formatCurrency(this);
            });
        });
    </script>
</head>
<body>
    <div class="container">
        <h2 class="mt-5">Fechar Caixa</h2>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        <form method="POST" action="fechar_caixa.php">
            <div class="form-group">
                <label for="total_dinheiro">Total em Dinheiro</label>
                <input type="text" name="total_dinheiro" class="form-control" id="total_dinheiro" required>
            </div>
            <div class="form-group">
                <label for="total_credito">Total em Crédito</label>
                <input type="text" name="total_credito" class="form-control" id="total_credito" required>
            </div>
            <div class="form-group">
                <label for="total_debito">Total em Débito</label>
                <input type="text" name="total_debito" class="form-control" id="total_debito" required>
            </div>
            <div class="form-group">
                <label for="total_pix">Total em PIX</label>
                <input type="text" name="total_pix" class="form-control" id="total_pix" required>
            </div>
            <button type="submit" name="verify" class="btn btn-primary">Verificar Diferenças</button>
        </form>
        <?php if (isset($differences_html)): echo $differences_html; endif; ?>
    </div>
</body>
</html>
