,<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require 'db.php';

$caixa_id = $_POST['caixa_id'];
$total_dinheiro = (float) str_replace(',', '.', str_replace('.', '', $_POST['total_dinheiro']));
$total_credito = (float) str_replace(',', '.', str_replace('.', '', $_POST['total_credito']));
$total_debito = (float) str_replace(',', '.', str_replace('.', '', $_POST['total_debito']));
$total_pix = (float) str_replace(',', '.', str_replace('.', '', $_POST['total_pix']));

// Obter dados da loja
$stmt = $pdo->prepare('SELECT * FROM configuracoes_loja');
$stmt->execute();
$config_loja = $stmt->fetch(PDO::FETCH_ASSOC);

// Obter informações do caixa e movimentações
$stmt = $pdo->prepare('SELECT * FROM caixa WHERE id = ?');
$stmt->execute([$caixa_id]);
$caixa = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare('SELECT SUM(valor) as total_suprimentos FROM movimentacoes_caixa WHERE caixa_id = ? AND tipo = "suprimento"');
$stmt->execute([$caixa_id]);
$total_suprimentos = $stmt->fetchColumn() ?: 0;

$stmt = $pdo->prepare('SELECT SUM(valor) as total_sangrias FROM movimentacoes_caixa WHERE caixa_id = ? AND tipo = "sangria"');
$stmt->execute([$caixa_id]);
$total_sangrias = $stmt->fetchColumn() ?: 0;

// Consulta para obter os totais de vendas registrados
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

// Calcular o total de vendas real
$total_vendas = $totais_vendas['total_dinheiro'] + $totais_vendas['total_credito'] + $totais_vendas['total_debito'] + $totais_vendas['total_pix'];

// Atualizar status do caixa para fechado e total de vendas
$stmt = $pdo->prepare('UPDATE caixa SET data_fechamento = NOW(), total_vendas = ?, status = "Fechado" WHERE id = ?');
$stmt->execute([$total_vendas, $caixa_id]);

// Calcular saldo real em dinheiro
$saldo_real_dinheiro = $totais_vendas['total_dinheiro'] + $caixa['fundo_de_troco'] + $total_suprimentos - $total_sangrias;

// Gerar relatório de fechamento
$relatorio = "
----------------------------------------
{$config_loja['nome_loja']}
{$config_loja['endereco']}
CNPJ: {$config_loja['cnpj']}
IE: {$config_loja['ie']}
Telefone: {$config_loja['telefone']}
----------------------------------------
Data: " . date('d/m/Y H:i:s') . "
----------------------------------------
Fechamento de Caixa ID: {$caixa['id']}
----------------------------------------
Abertura: {$caixa['data_abertura']}
Sangria: R$ " . number_format($total_sangrias, 2, ',', '.') . "
Suprimentos: R$ " . number_format($total_suprimentos, 2, ',', '.') . "
Fundo de Troco: R$ " . number_format($caixa['fundo_de_troco'], 2, ',', '.') . "
----------------------------------------
Vendas
Dinheiro: R$ " . number_format($totais_vendas['total_dinheiro'], 2, ',', '.') . "
Crédito: R$ " . number_format($totais_vendas['total_credito'], 2, ',', '.') . "
Débito: R$ " . number_format($totais_vendas['total_debito'], 2, ',', '.') . "
PIX: R$ " . number_format($totais_vendas['total_pix'], 2, ',', '.') . "
----------------------------------------
Diferença de Caixa
Dinheiro: R$ " . number_format($total_dinheiro - $saldo_real_dinheiro, 2, ',', '.') . "
Crédito: R$ " . number_format($total_credito - $totais_vendas['total_credito'], 2, ',', '.') . "
Débito: R$ " . number_format($total_debito - $totais_vendas['total_debito'], 2, ',', '.') . "
PIX: R$ " . number_format($total_pix - $totais_vendas['total_pix'], 2, ',', '.') . "
";

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Fechamento de Caixa</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script>
        function printReport() {
            var divToPrint = document.getElementById('report');
            var newWin = window.open('', 'Print-Window');
            newWin.document.open();
            newWin.document.write('<html><body onload="window.print()">' + divToPrint.innerHTML + '</body></html>');
            newWin.document.close();
        }
    </script>
</head>
<body>
    <div class="container">
        <h2 class="mt-5">Relatório de Fechamento de Caixa</h2>
        <div id="report">
            <pre><?php echo $relatorio; ?></pre>
        </div>
        <button onclick="printReport()" class="btn btn-primary">Imprimir Relatório</button>
    </div>
</body>
</html>
