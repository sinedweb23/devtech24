<?php
$host = 'localhost';
$db = 'uniposbr_unipos';
$user = 'uniposbr_admin';
$pass = 'Deggnhff$323@@';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Define o charset da conexão
    $pdo->exec("SET NAMES utf8mb4");
} catch (PDOException $e) {
    die("Erro na conexão com o banco de dados: " . $e->getMessage());
}
?>
