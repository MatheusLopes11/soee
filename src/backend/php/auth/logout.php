<?php
/**
 * Sistema de Logout SOEE
 * Encerra a sessão, limpa cookies e redireciona para a home.
 */

// 1. Inicia a sessão para poder manipulá-la
session_start();

// 2. Limpa todas as variáveis de sessão
$_SESSION = array();

// 3. Se desejar destruir a sessão completamente, apague também o cookie de sessão.
// Isso garante que o ID da sessão no navegador seja invalidado.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 4. Limpar o cookie de "Lembrar de mim" (se você usar um)
// Ajuste o nome 'lembrar_soee' para o nome real que você definiu no login
if (isset($_COOKIE['lembrar_soee'])) {
    setcookie('lembrar_soee', '', time() - 3600, '/');
}

// 5. Destrói a sessão no servidor
session_destroy();

// 6. Redireciona para a página inicial (ajuste o caminho se necessário)
// Considerando que o arquivo está em /src/backend/php/auth/
header("Location: /soee/index.php");
exit;