<?php
session_start();

require __DIR__ . '/../includes/conexao.php';
require __DIR__ . '/home.php';

$login = $_POST['username'] ?? '';
$senha = $_POST['password'] ?? '';
$lembrar = isset($_POST['remember']);

$result = AuthHome::processarLogin($conn, $login, $senha, $lembrar);

if ($result['sucesso']) {
    $_SESSION['login_sucesso'] = "Bem-vindo!";
    header("Location: " . $result['redirect']);
    exit();
}

$_SESSION['login_erro'] = $result['erro'];
header("Location: /soee/index.php");
exit();