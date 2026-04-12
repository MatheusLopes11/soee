<?php
session_start();

// Limpa todas as variáveis de sessão
$_SESSION = [];

// Remove cookie de sessão do navegador
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Remove o cookie "lembrar de mim" — nome correto usado no auth-login.php
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/');
}

// Destrói a sessão no servidor
session_destroy();

header("Location: /soee/index.php");
exit;