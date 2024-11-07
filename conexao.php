<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "unipos";

$conn = mysqli_connect($servername, $username, $password, $database);

if (!$conn) {
    die("Conexão falhou: " . mysqli_connect_error());
}
?>
