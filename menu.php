<?php
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require 'db.php';

// Verificar se a linha de configuração existe
$stmt = $pdo->query('SELECT * FROM configuracoes_loja WHERE id = 1');
$configuracoes = $stmt->fetch();

if (!$configuracoes) {
    // Inserir linha de configuração padrão se não existir
    $stmt = $pdo->prepare('INSERT INTO configuracoes_loja (cor_texto_menu, cor_div_menu) VALUES (?, ?)');
    $stmt->execute(['#000000', '#ffffff']);
    // Obter a configuração recém inserida
    $stmt = $pdo->query('SELECT * FROM configuracoes_loja WHERE id = 1');
    $configuracoes = $stmt->fetch();
}

$nivel_acesso = $_SESSION['nivel_acesso'];

$menu = [
    'usuario' => [
        'Vender' => ['url' => 'vendas.php', 'icon' => 'fas fa-shopping-cart'],
        'Gerar Pedido' => ['url' => 'entrega/gerar_pedido.php', 'icon' => 'fas fa-cart-plus'],
        'Pedidos em Andamento' => ['url' => 'entrega/gerenciar_pedidos.php', 'icon' => 'fas fa-list'],
        'Produtos' => ['url' => 'produtos.php', 'icon' => 'fas fa-boxes'],
        'Vendas' => ['url' => 'lista_vendas.php', 'icon' => 'fas fa-list'],
        'Minha Conta' => ['url' => 'minha_conta.php', 'icon' => 'fas fa-user'],
        'Abertura de Caixa' => ['url' => 'abrir_caixa.php', 'icon' => 'fas fa-cash-register'],
        'Movimentação de Caixa' => ['url' => 'movimentacao_caixa.php', 'icon' => 'fas fa-exchange-alt'],
        'Fechamento de Caixa' => ['url' => 'fechar_caixa.php', 'icon' => 'fas fa-door-closed'],
        'Cadastrar Cliente' => ['url' => 'cadastro_cliente.php', 'icon' => 'fas fa-user-tie'],
        'Extrato Convênio' => ['url' => 'clientes.php', 'icon' => 'fas fa-file-alt'],
        'Suporte' => ['url' => 'javascript:void(0);', 'onclick' => "openSupportWindow()", 'icon' => 'fas fa-life-ring'], // Função para abrir popup
        'Logout' => ['url' => 'logout.php', 'icon' => 'fas fa-sign-out-alt']
    ],
    'admin' => [
        'Cadastrar Produto' => ['url' => 'cadastrar_produto.php', 'icon' => 'fas fa-plus'],
        'Entrada de Produtos' => ['url' => 'entrada_produtos.php', 'icon' => 'fas fa-plus-circle'],
        'Cadastrar Categoria' => ['url' => 'cadastrar_categoria.php', 'icon' => 'fas fa-tags'],
        'Cadastrar Usuário' => ['url' => 'cadastro_usuario.php', 'icon' => 'fas fa-user-plus'],
        'Cadastrar Entregador' => ['url' => 'entrega/cadastrar_entregador.php', 'icon' => 'fas fa-motorcycle'],
        'Relatórios' => ['url' => 'relatorios.php', 'icon' => 'fas fa-chart-line'],
        'Faturas' => ['url' => 'faturas', 'icon' => 'fas fa-file-invoice'],
        'Editar Loja' => ['url' => 'editar_loja.php', 'icon' => 'fas fa-edit']
        
    ]
];
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        #sidebarMenu {
            background-color: <?= $configuracoes['cor_div_menu'] ?? '#ffffff' ?> !important;
        }
        #sidebarMenu .nav-link {
            color: <?= $configuracoes['cor_texto_menu'] ?? '#000000' ?>;
        }
    </style>
</head>
<body>
    <ul class="nav flex-column">
        <?php foreach ($menu['usuario'] as $name => $page): ?>
            <li class="nav-item">
                <a class="nav-link" href="javascript:void(0);" onclick="<?= $page['onclick'] ?? "loadPage('{$page['url']}')" ?>">
                    <i class="<?= $page['icon'] ?>"></i> <?= $name ?>
                </a>
            </li>
        <?php endforeach; ?>
        <?php if ($nivel_acesso == 'admin'): ?>
            <hr>
            <?php foreach ($menu['admin'] as $name => $page): ?>
                <li class="nav-item">
                    <a class="nav-link" href="javascript:void(0);" onclick="loadPage('<?= $page['url'] ?>')">
                        <i class="<?= $page['icon'] ?>"></i> <?= $name ?>
                    </a>
                </li>
            <?php endforeach; ?>
        <?php endif; ?>
    </ul>

    <script>
        function loadPage(page) {
            window.parent.location.href = page;
        }

        function openSupportWindow() {
            // Número do WhatsApp do suporte e mensagem pré-definida
            var supportURL = 'https://api.whatsapp.com/send?phone=+5511964718868&text=' + encodeURIComponent('Olá, preciso de suporte ao sistema fastpdv');
            // Abrir nova janela com o link do WhatsApp
            window.open(supportURL, '_blank', 'width=600,height=400');
        }
    </script>
</body>
</html>
