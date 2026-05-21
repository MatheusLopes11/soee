<?php
AuthHome::tentarLoginPorCookie($conn);

if (AuthHome::estaLogado()) {
    AuthHome::redirecionarPorTipo();
    die();
}

$erro = '';
$sucesso = '';

if (!empty($_GET['cadastro']) && $_GET['cadastro'] === 'sucesso') {
    $sucesso = 'Conta criada com sucesso! Faça login para entrar.';
}

if (!empty($_SESSION['login_erro'])) {
    $erro = $_SESSION['login_erro'];
    unset($_SESSION['login_erro']);
}

if (!empty($_SESSION['login_sucesso'])) {
    $sucesso = $_SESSION['login_sucesso'];
    unset($_SESSION['login_sucesso']);
}
?>