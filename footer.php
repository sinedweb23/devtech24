<?php
require 'db.php';

// Obter configurações atuais
$stmt = $pdo->query('SELECT * FROM configuracoes_loja WHERE id = 1');
$configuracoes = $stmt->fetch();

if (!$configuracoes) {
    // Inserir linha de configuração padrão se não existir
    $stmt = $pdo->prepare('INSERT INTO configuracoes_loja (nome_loja, cor_texto_menu, cor_div_menu, cor_fundo_footer) VALUES (?, ?, ?, ?)');
    $stmt->execute(['Minha Loja', '#000000', '#ffffff', '#cccccc']);
    // Obter a configuração recém inserida
    $stmt = $pdo->query('SELECT * FROM configuracoes_loja WHERE id = 1');
    $configuracoes = $stmt->fetch();
}
?>

<footer style="background-color: <?= $configuracoes['cor_fundo_footer'] ?? '#cccccc' ?>; color: <?= $configuracoes['cor_texto_menu'] ?? '#000000' ?>;">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 text-center">
                <p>&copy; <?= date('Y') ?> UniPOS Simplificando Vendas, Conectando Negócios.
</p>
            </div>
        </div>
    </div>
</footer>
