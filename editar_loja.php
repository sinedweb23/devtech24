<?php
session_start();
if ($_SESSION['nivel_acesso'] !== 'admin') {
    header("Location: index.php");
    exit;
}

require 'db.php';

// Verificar se a linha de configuração existe
$stmt = $pdo->query('SELECT * FROM configuracoes_loja WHERE id = 1');
$configuracoes = $stmt->fetch();

if (!$configuracoes) {
    // Inserir linha de configuração padrão
    $stmt = $pdo->prepare('INSERT INTO configuracoes_loja (nome_loja, cor_texto_menu, cor_div_menu, cor_fundo_footer, endereco, cnpj, ie, telefone) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute(['Minha Loja', '#000000', '#ffffff', '#cccccc', '', '**.***.***/****-**', '', '']);
    // Obter a configuração recém inserida
    $stmt = $pdo->query('SELECT * FROM configuracoes_loja WHERE id = 1');
    $configuracoes = $stmt->fetch();
}

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome_loja = $_POST['nome_loja'];
    $endereco = $_POST['endereco'];
    $cnpj = $_POST['cnpj'] ?: '**.***.***/****-**';
    $ie = $_POST['ie'];
    $telefone = $_POST['telefone'];
    $cor_texto_menu = $_POST['cor_texto_menu'];
    $cor_div_menu = $_POST['cor_div_menu'];
    $cor_fundo_footer = $_POST['cor_fundo_footer']; // Nova variável para a cor do rodapé

    // Upload da logo
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
        $extensao = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
        $novo_nome = uniqid() . "." . $extensao;
        $diretorio = 'uploads/';
        move_uploaded_file($_FILES['logo']['tmp_name'], $diretorio . $novo_nome);

        // Atualizar o caminho da logo no banco de dados
        $stmt = $pdo->prepare('UPDATE configuracoes_loja SET logo = ? WHERE id = 1');
        $stmt->execute([$diretorio . $novo_nome]);
    }

    // Upload do favicon
    if (isset($_FILES['favicon']) && $_FILES['favicon']['error'] == 0) {
        $extensao_favicon = pathinfo($_FILES['favicon']['name'], PATHINFO_EXTENSION);
        $novo_nome_favicon = uniqid() . "." . $extensao_favicon;
        $diretorio_favicon = 'uploads/';
        move_uploaded_file($_FILES['favicon']['tmp_name'], $diretorio_favicon . $novo_nome_favicon);

        // Atualizar o caminho do favicon no banco de dados
        $stmt = $pdo->prepare('UPDATE configuracoes_loja SET favicon = ? WHERE id = 1');
        $stmt->execute([$diretorio_favicon . $novo_nome_favicon]);
    }

    // Atualizar as configurações da loja no banco de dados
    $stmt = $pdo->prepare('UPDATE configuracoes_loja SET nome_loja = ?, endereco = ?, cnpj = ?, ie = ?, telefone = ?, cor_texto_menu = ?, cor_div_menu = ?, cor_fundo_footer = ? WHERE id = 1');
    if ($stmt->execute([$nome_loja, $endereco, $cnpj, $ie, $telefone, $cor_texto_menu, $cor_div_menu, $cor_fundo_footer])) {
        // Redirecionar para index.php na janela principal após salvar
        echo "<script>
                window.parent.location.href = 'index.php';
              </script>";
        exit;
    } else {
        $error = "Erro ao atualizar configurações.";
    }

    // Recarregar configurações atualizadas
    $stmt = $pdo->query('SELECT * FROM configuracoes_loja WHERE id = 1');
    $configuracoes = $stmt->fetch();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Loja</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <?php if ($configuracoes['favicon']): ?>
        <link rel="icon" type="image/png" href="<?= $configuracoes['favicon'] ?>">
    <?php endif; ?>
</head>
<body>
    <div class="container">
        <h2 class="mt-5">Editar Loja</h2>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>
        <form method="POST" action="editar_loja.php" enctype="multipart/form-data">
            <div class="form-group">
                <label for="nome_loja">Nome da Loja</label>
                <input type="text" name="nome_loja" class="form-control" id="nome_loja" value="<?= $configuracoes['nome_loja'] ?? 'Minha Loja' ?>" required>
            </div>
            <div class="form-group">
                <label for="endereco">Endereço</label>
                <input type="text" name="endereco" class="form-control" id="endereco" value="<?= $configuracoes['endereco'] ?? '' ?>" placeholder="Rua, Bairro, CEP" required>
            </div>
            <div class="form-group">
                <label for="cnpj">CNPJ</label>
                <input type="text" name="cnpj" class="form-control" id="cnpj" value="<?= $configuracoes['cnpj'] ?? '**.***.***/****-**' ?>" placeholder="**.***.***/****-**">
            </div>
            <div class="form-group">
                <label for="ie">IE</label>
                <input type="text" name="ie" class="form-control" id="ie" value="<?= $configuracoes['ie'] ?? '' ?>">
            </div>
            <div class="form-group">
                <label for="telefone">Telefone</label>
                <input type="text" name="telefone" class="form-control" id="telefone" value="<?= $configuracoes['telefone'] ?? '' ?>" required>
            </div>
            <div class="form-group">
            <p><b>Adicione sua Logomarca</b></p>
                <label for="logo">Logo</label>
                <input type="file" name="logo" class="form-control-file" id="logo">
                <?php if ($configuracoes['logo']): ?>
                    <img src="<?= $configuracoes['logo'] ?>" alt="Logo atual" class="img-fluid mt-2" style="max-height: 100px;">
                <?php endif; ?>
            </div>
            <div class="form-group">
                <hr>
                <p><b>Adicione um Favicon 48 x 48 px</b></p>
                <label for="favicon">Favicon</label>
                <input type="file" name="favicon" class="form-control-file" id="favicon">
                <?php if ($configuracoes['favicon']): ?>
                    <img src="<?= $configuracoes['favicon'] ?>" alt="Favicon atual" class="img-fluid mt-2" style="max-height: 32px;">
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label for="cor_texto_menu">Cor do Texto do Menu</label>
                <input type="color" name="cor_texto_menu" class="form-control" id="cor_texto_menu" value="<?= $configuracoes['cor_texto_menu'] ?? '#000000' ?>">
            </div>
            <div class="form-group">
                <label for="cor_div_menu">Cor da Div do Menu</label>
                <input type="color" name="cor_div_menu" class="form-control" id="cor_div_menu" value="<?= $configuracoes['cor_div_menu'] ?? '#ffffff' ?>">
            </div>
            <div class="form-group">
                <label for="cor_fundo_footer">Cor do Rodapé</label>
                <input type="color" name="cor_fundo_footer" class="form-control" id="cor_fundo_footer" value="<?= $configuracoes['cor_fundo_footer'] ?? '#cccccc' ?>">
            </div>
            <button type="submit" class="btn btn-primary">Salvar</button>
        </form>
    </div>
</body>
</html>
