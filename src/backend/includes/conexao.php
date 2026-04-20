<?php
$host = "localhost"; 
$port = "3306"; 
$dbname = "soee"; 
$user = "root"; 
$password = "root";

try {
    $conn = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("ERRO conexão: " . $e->getMessage());
}
?>