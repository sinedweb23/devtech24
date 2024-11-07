<?php
require 'db.php';

$movimentacao_id = $_GET['movimentacao_id'];

// Obter dados da movimentação
$stmt = $pdo->prepare('SELECT * FROM movimentacoes_caixa WHERE id = ?');
$stmt->execute([$movimentacao_id]);
$movimentacao = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$movimentacao) {
    die('Movimentação não encontrada.');
}

// Obter dados da loja
$stmt = $pdo->query('SELECT * FROM configuracoes_loja WHERE id = 1');
$loja = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Imprimir Comprovante</title>
</head>
<body>
    <h3> <?= $loja['nome_loja'] ?></h3>
    <p>Endereço: <?= $loja['endereco'] ?></p>
    <p>CNPJ: <?= $loja['cnpj'] ?></p>
    <p>IE: <?= $loja['ie'] ?></p>
    <p>Telefone: <?= $loja['telefone'] ?></p>
    <hr>
    <h4>Comprovante de Movimentação de Caixa</h4>
    <p>ID da Movimentação: <?= $movimentacao['id'] ?></p>
    <p><br></p>
    <p><strong>Tipo:</strong> <?= ucfirst($movimentacao['tipo']) ?></p>
    <p><strong>Valor:</strong> R$ <?= number_format($movimentacao['valor'], 2, ',', '.') ?></p>
    <p><strong>Descrição:</strong> <?= $movimentacao['descricao'] ?></p>
    <hr>
    <p>Operador: ___________________________________</p>
    <p>Supervisor: ___________________________________</p>
    <script>
        window.print();
        window.onafterprint = function () {
            window.close();
        };
    </script>
</body>
</html>
