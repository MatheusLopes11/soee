<?php
ob_start();
session_start();
include __DIR__ . '/../include/conexao.php';

$erro = "";

/* ==========================================
   FUNÇÃO AUXILIAR — REDIRECIONA POR TIPO
========================================== */
function redirecionarPorTipo($tipo) {
    $rotas = [
        'adm_geral' => '/soee/src/backend/php/dashboard/dash-adm.php',
        'adm_sala'  => '/soee/src/backend/php/dashboard/dash-adm-sala.php',
        'professor' => '/soee/src/backend/php/dashboard/dash-prof.php',
        'aluno'     => '/soee/src/backend/php/dashboard/dash-user.php',
    ];

    $destino = isset($rotas[$tipo]) ? $rotas[$tipo] : '/soee/src/backend/php/pages/inicio.php';
    header('Location: ' . $destino);
    exit();
}

/* =========================
   LOGIN AUTOMÁTICO (COOKIE)
========================= */
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_token'])) {

    $token = $_COOKIE['remember_token'];

    $stmt = $conn->prepare("SELECT * FROM usuario WHERE remember_token = :token LIMIT 1");
    $stmt->bindValue(":token", $token);
    $stmt->execute();

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && $user['ativo_usuario'] == 1) {
        $_SESSION['user_id']   = $user['id_usuario'];
        $_SESSION['user_nome'] = $user['nome_usuario'];
        $_SESSION['user_tipo'] = $user['tipo_usuario'];

        redirecionarPorTipo($user['tipo_usuario']);
    }

    // Token inválido ou conta inativa — limpa o cookie
    setcookie("remember_token", "", time() - 3600, "/");
}

/* =========================
   LOGIN NORMAL
========================= */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['username'])) {

    $login    = trim($_POST['username']);
    $password = trim($_POST['password']);
    $remember = isset($_POST['remember']);

    if (empty($login) || empty($password)) {
        $erro = "Preencha todos os campos.";
    } else {

        $sql = "SELECT * FROM usuario 
                WHERE (nome_usuario = :login OR email_usuario = :login)
                  AND ativo_usuario = 1
                LIMIT 1";

        $stmt = $conn->prepare($sql);
        $stmt->bindValue(":login", $login);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $erro = "Usuário não cadastrado ou senha incorreta.";
        } else {

            /*
             * Verifica a senha com password_verify (usuários cadastrados pelo formulário)
             * OU comparação direta (usuários legados do seed com senha em texto puro).
             * Após todos os usuários legados trocarem a senha, remova a parte do "||".
             */
            $senhaCorreta = password_verify($password, $user['senha_usuario'])
                         || $password === $user['senha_usuario'];

            if (!$senhaCorreta) {
                $erro = "Usuário não cadastrado ou senha incorreta.";
            } else {

                /*
                 * Se o usuário legado ainda tem senha em texto puro,
                 * aproveita o login para migrar para hash automaticamente.
                 */
                if (!password_verify($password, $user['senha_usuario'])) {
                    $novoHash = password_hash($password, PASSWORD_DEFAULT);
                    $upd = $conn->prepare("UPDATE usuario SET senha_usuario = :hash WHERE id_usuario = :id");
                    $upd->execute([':hash' => $novoHash, ':id' => $user['id_usuario']]);
                }

                $_SESSION['user_id']   = $user['id_usuario'];
                $_SESSION['user_nome'] = $user['nome_usuario'];
                $_SESSION['user_tipo'] = $user['tipo_usuario'];

                if ($remember) {
                    $token = bin2hex(random_bytes(32));

                    $upd = $conn->prepare("UPDATE usuario SET remember_token = :token WHERE id_usuario = :id");
                    $upd->execute([":token" => $token, ":id" => $user['id_usuario']]);

                    setcookie("remember_token", $token, time() + (86400 * 30), "/", "", false, true);
                }

                redirecionarPorTipo($user['tipo_usuario']);
            }
        }
    }
}
?>