<?php
$servername = "localhost";
$username = "uniposbr_admin";
$password = "Deggnhff$323@@";
$dbname = "uniposbr_unipos";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
