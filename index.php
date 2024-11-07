<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    header("Location: login.php");
    exit;
}

require 'db.php';

// Obter configurações atuais
$stmt = $pdo->query('SELECT * FROM configuracoes_loja WHERE id = 1');
$configuracoes = $stmt->fetch();

if (!$configuracoes) {
    // Inserir linha de configuração padrão se não existir
    $stmt = $pdo->prepare('INSERT INTO configuracoes_loja (nome_loja, cor_texto_menu, cor_div_menu) VALUES (?, ?, ?)');
    $stmt->execute(['Minha Loja', '#000000', '#ffffff']);
    // Obter a configuração recém inserida
    $stmt = $pdo->query('SELECT * FROM configuracoes_loja WHERE id = 1');
    $configuracoes = $stmt->fetch();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($configuracoes['nome_loja']) ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="styles.css"> <!-- Opcional, para customizações adicionais -->
    <style>
        #sidebarMenu {
            background-color: <?= $configuracoes['cor_div_menu'] ?? '#ffffff' ?>;
        }
        #sidebarMenu .nav-link {
            color: <?= $configuracoes['cor_texto_menu'] ?? '#000000' ?>;
        }
    </style>
    <?php if ($configuracoes['favicon']): ?>
        <link rel="icon" type="image/png" href="<?= $configuracoes['favicon'] ?>">
    <?php endif; ?>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Menu Lateral -->
            <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block sidebar collapse" style="background-color: <?= $configuracoes['cor_div_menu'] ?? '#ffffff' ?>;">
                <div class="sidebar-sticky pt-3">
                    <div class="text-center mb-4">
                        <?php if ($configuracoes['logo']): ?>
                            <img src="<?= $configuracoes['logo'] ?>" alt="Logo" class="img-fluid" id="logo-upload">
                        <?php else: ?>
                            <img src="default-logo.png" alt="Logo Padrão" class="img-fluid" id="logo-upload">
                        <?php endif; ?>
                    </div>
                    <?php include 'menu.php'; ?>
                </div>
            </nav>

            <!-- Conteúdo Principal -->
            <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-md-4">
                <iframe id="iframe-content" src="vendas.php" frameborder="0" style="width: 100%; height: 100vh;"></iframe>
            </main>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        function loadPage(page) {
            document.getElementById('iframe-content').src = page;
        }
    </script>
</body>
<?php require 'footer.php'; ?>

</html>
