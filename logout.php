<?php
session_start();
session_destroy();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout</title>
    <script>
        // Redirecionar para login.php na janela inteira
        window.top.location.href = 'login.php';
    </script>
</head>
<body>
    <!-- Conteúdo do corpo -->
</body>
</html>
