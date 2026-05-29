<?php
session_start();

// login.php fica em /soee/src/backend/controllers/
// __DIR__ já é controllers/, então:
// '/../includes/conexao.php' → ../includes/conexao.php ✓
// '/home.php' → controllers/home.php  ← ESTAVA CORRETO, mas mantendo explícito
require __DIR__ . '/../includes/conexao.php';
require __DIR__ . '/home.php';

$login   = $_POST['username'] ?? '';
$senha   = $_POST['password'] ?? '';
$lembrar = isset($_POST['remember']);

$result = AuthHome::processarLogin($conn, $login, $senha, $lembrar);

if ($result['sucesso']) {
    // REMOVIDO: $_SESSION['id_usuario'] = 1 (sobrescrevia o ID real do usuário)
    // A sessão é definida dentro de AuthHome::processarLogin com o ID correto.
    $_SESSION['login_sucesso'] = "Bem-vindo!";
    header("Location: " . $result['redirect']);
    exit();
}

$_SESSION['login_erro'] = $result['erro'];
header("Location: /soee/index.php");
exit();