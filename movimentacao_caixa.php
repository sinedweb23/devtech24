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
$caixa = $stmt->fetch();

if (!$caixa) {
    $error = "Nenhum caixa aberto encontrado.";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $caixa) {
    $tipo = $_POST['tipo'];
    $valor = str_replace(',', '.', str_replace('.', '', $_POST['valor'])); // Convertendo para formato decimal
    $descricao = $_POST['descricao'];
    $caixa_id = $caixa['id'];

    // Verificar saldo do caixa antes de realizar sangria
    if ($tipo == 'sangria') {
        $stmt = $pdo->prepare('
            SELECT 
                (caixa.fundo_de_troco 
                + COALESCE(SUM(CASE WHEN movimentacoes_caixa.tipo = "suprimento" THEN movimentacoes_caixa.valor ELSE 0 END), 0) 
                - COALESCE(SUM(CASE WHEN movimentacoes_caixa.tipo = "sangria" THEN movimentacoes_caixa.valor ELSE 0 END), 0)
                + COALESCE(SUM(CASE WHEN vendas.forma_pagamento1 = "dinheiro" AND vendas.caixa_id = caixa.id THEN vendas.valor_pagamento1 ELSE 0 END), 0)
                + COALESCE(SUM(CASE WHEN vendas.forma_pagamento2 = "dinheiro" AND vendas.caixa_id = caixa.id THEN vendas.valor_pagamento2 ELSE 0 END), 0)
                ) AS saldo_dinheiro
            FROM caixa 
            LEFT JOIN movimentacoes_caixa ON caixa.id = movimentacoes_caixa.caixa_id 
            LEFT JOIN vendas ON vendas.caixa_id = caixa.id 
            WHERE caixa.id = ?
        ');
        $stmt->execute([$caixa_id]);
        $saldo_dinheiro = $stmt->fetchColumn();

        if ($valor > $saldo_dinheiro) {
            $error = "Saldo insuficiente para realizar sangria.";
        }
    }

    if (!isset($error)) {
        $stmt = $pdo->prepare('INSERT INTO movimentacoes_caixa (caixa_id, tipo, valor, descricao) VALUES (?, ?, ?, ?)');
        if ($stmt->execute([$caixa_id, $tipo, $valor, $descricao])) {
            // Redirecionar para a página de impressão do comprovante
            header("Location: imprimir_movimentacao.php?movimentacao_id=" . $pdo->lastInsertId());
            exit;
        } else {
            $error = "Erro ao registrar movimentação.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movimentação de Caixa</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script>
        // Função para formatar valores como moeda
        function formatCurrency(input) {
            let value = input.value.replace(/\D/g, ''); // Remove todos os caracteres não numéricos
            value = (parseFloat(value) / 100).toFixed(2); // Converte para formato de moeda com duas casas decimais
            value = value.replace('.', ','); // Substitui ponto por vírgula
            input.value = value; // Define o valor formatado no campo
        }

        $(document).ready(function() {
            $('#valor').on('input', function() {
                formatCurrency(this);
            });
        });
    </script>
</head>
<body>
    <div class="container">
        <h2 class="mt-5">Movimentação de Caixa</h2>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        <form method="POST" action="movimentacao_caixa.php">
            <div class="form-group">
                <label for="tipo">Tipo</label>
                <select name="tipo" class="form-control" id="tipo" required>
                    <option value="sangria">Sangria</option>
                    <option value="suprimento">Suprimento</option>
                </select>
            </div>
            <div class="form-group">
                <label for="valor">Valor</label>
                <input type="text" name="valor" class="form-control" id="valor" required>
            </div>
            <div class="form-group">
                <label for="descricao">Descrição</label>
                <textarea name="descricao" class="form-control" id="descricao" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Registrar</button>
        </form>
    </div>
</body>
</html>
