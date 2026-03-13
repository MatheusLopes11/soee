<?php
$host = "localhost"; $port = "3306"; $dbname = "soee"; $user = "root"; $password = "teteu 110607"; // Verificar se necessita de senha ou não juvencio.
try {
    $conn = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage();
    die();
} 
?>