<?php
session_start();
if ($_SESSION['nivel_acesso'] !== 'admin') {
    header("Location: index.php");
    exit;
}

require 'db.php';

// Obter todos os produtos, excluindo inativos e visíveis apenas no site
$stmt = $pdo->query('SELECT id, nome, preco, preco_custo, estoque FROM produtos WHERE status = "ativo" AND visivel_em != "Site"');
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $entradas = $_POST['entradas'];
    foreach ($entradas as $entrada) {
        $produto_id = $entrada['produto_id'];
        $quantidade = $entrada['quantidade'];
        $preco_custo = str_replace(',', '.', str_replace('.', '', $entrada['preco_custo']));
        $preco_venda = str_replace(',', '.', str_replace('.', '', $entrada['preco_venda']));

        // Atualizar o estoque e os valores de custo e venda do produto
        $stmt = $pdo->prepare('UPDATE produtos SET estoque = estoque + ?, preco_custo = ?, preco = ? WHERE id = ?');
        $stmt->execute([$quantidade, $preco_custo, $preco_venda, $produto_id]);
    }

    $success = "Entrada de produtos realizada com sucesso.";
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entrada de Produtos</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container">
        <h2 class="mt-5">Entrada de Produtos</h2>
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>
        <form method="POST" action="entrada_produtos.php">
            <div id="entradas">
                <div class="entrada mb-3">
                    <div class="form-row">
                        <div class="col-md-4">
                            <label for="produto_id">Produto</label>
                            <select name="entradas[0][produto_id]" class="form-control produto_id" required>
                                <option value="">Selecione o Produto</option>
                                <?php foreach ($produtos as $produto): ?>
                                    <option value="<?= $produto['id'] ?>" data-preco="<?= $produto['preco'] ?>" data-preco_custo="<?= $produto['preco_custo'] ?>" data-estoque="<?= $produto['estoque'] ?>">
                                        <?= $produto['nome'] ?> (Estoque: <?= $produto['estoque'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="quantidade">Quantidade</label>
                            <input type="number" name="entradas[0][quantidade]" class="form-control quantidade" min="1" required>
                        </div>
                        <div class="col-md-3">
                            <label for="preco_custo">Preço de Custo</label>
                            <input type="text" name="entradas[0][preco_custo]" class="form-control preco_custo" required>
                        </div>
                        <div class="col-md-3">
                            <label for="preco_venda">Preço de Venda</label>
                            <input type="text" name="entradas[0][preco_venda]" class="form-control preco_venda" required>
                        </div>
                    </div>
                </div>
            </div>
            <button type="button" id="addEntrada" class="btn btn-secondary mb-3">Adicionar Produto</button>
            <button type="submit" class="btn btn-primary">Salvar Entrada</button>
            <h4 class="mt-4">Total da Nota: R$ <span id="totalNota">0,00</span></h4>
        </form>
    </div>

    <script>
        let entradaIndex = 1;

        function formatCurrency(input) {
            var value = input.value.replace(/\D/g, ''); // Remover todos os caracteres não numéricos
            value = (parseFloat(value) / 100).toFixed(2); // Converter para formato de moeda com duas casas decimais
            value = value.toString().replace(".", ","); // Substituir ponto por vírgula
            input.value = value; // Definir o valor formatado no campo
        }

        document.querySelectorAll('.preco_custo, .preco_venda').forEach(input => {
            input.addEventListener('input', function() {
                formatCurrency(this);
                atualizarTotalNota();
            });
        });

        function atualizarTotalNota() {
            let totalNota = 0;
            document.querySelectorAll('.entrada').forEach(entrada => {
                const quantidade = entrada.querySelector('.quantidade').value;
                const precoCusto = parseFloat(entrada.querySelector('.preco_custo').value.replace(',', '.')) || 0;
                totalNota += quantidade * precoCusto;
            });
            document.getElementById('totalNota').textContent = totalNota.toFixed(2).replace('.', ',');
        }

        document.getElementById('addEntrada').addEventListener('click', function() {
            const entradaContainer = document.createElement('div');
            entradaContainer.classList.add('entrada', 'mb-3');
            entradaContainer.innerHTML = `
                <div class="form-row">
                    <div class="col-md-4">
                        <label for="produto_id">Produto</label>
                        <select name="entradas[${entradaIndex}][produto_id]" class="form-control produto_id" required>
                            <option value="">Selecione o Produto</option>
                            <?php foreach ($produtos as $produto): ?>
                                <option value="<?= $produto['id'] ?>" data-preco="<?= $produto['preco'] ?>" data-preco_custo="<?= $produto['preco_custo'] ?>" data-estoque="<?= $produto['estoque'] ?>">
                                    <?= $produto['nome'] ?> (Estoque: <?= $produto['estoque'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="quantidade">Quantidade</label>
                        <input type="number" name="entradas[${entradaIndex}][quantidade]" class="form-control quantidade" min="1" required>
                    </div>
                    <div class="col-md-3">
                        <label for="preco_custo">Preço de Custo</label>
                        <input type="text" name="entradas[${entradaIndex}][preco_custo]" class="form-control preco_custo" required>
                    </div>
                    <div class="col-md-3">
                        <label for="preco_venda">Preço de Venda</label>
                        <input type="text" name="entradas[${entradaIndex}][preco_venda]" class="form-control preco_venda" required>
                    </div>
                </div>
            `;
            document.getElementById('entradas').appendChild(entradaContainer);

            entradaContainer.querySelectorAll('.preco_custo, .preco_venda').forEach(input => {
                input.addEventListener('input', function() {
                    formatCurrency(this);
                    atualizarTotalNota();
                });
            });

            entradaIndex++;
        });

        document.querySelectorAll('.quantidade').forEach(input => {
            input.addEventListener('input', atualizarTotalNota);
        });
    </script>
</body>
</html>
