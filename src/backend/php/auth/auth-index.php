<?php
ob_start();
session_start();
include __DIR__ . '/../include/conexao.php';

$erro = "";

/* =========================
   LOGIN AUTOMÁTICO (COOKIE)
========================= */
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_token'])) {

    $token = $_COOKIE['remember_token'];

    $sql = "SELECT * FROM usuario WHERE remember_token = :token LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(":token", $token);
    $stmt->execute();

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $_SESSION['user_id']   = $user['id_usuario'];
        $_SESSION['user_nome'] = $user['nome_usuario'];
        $_SESSION['user_tipo'] = $user['tipo_usuario'];

        if ($user['tipo_usuario'] === 'adm_geral') {
            header('Location: /soee/src/backend/php/dashboard/dash-adm.php');
        } else {
            header("Location: /soee/src/backend/php/pages/home.php");
        }
        exit();
    }

    // Token inválido/expirado — limpa o cookie
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
                WHERE nome_usuario = :login 
                OR email_usuario = :login 
                LIMIT 1";

        $stmt = $conn->prepare($sql);
        $stmt->bindValue(":login", $login);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && $password === $user['senha_usuario']) {

            if ($user['ativo_usuario'] == 0) {
                $erro = "Sua conta está desativada.";
            } else {

                $_SESSION['user_id']   = $user['id_usuario'];
                $_SESSION['user_nome'] = $user['nome_usuario'];
                $_SESSION['user_tipo'] = $user['tipo_usuario'];

                if ($remember) {
                    $token = bin2hex(random_bytes(32));

                    $sql = "UPDATE usuario 
                            SET remember_token = :token 
                            WHERE id_usuario = :id";

                    $stmt = $conn->prepare($sql);
                    $stmt->execute([
                        ":token" => $token,
                        ":id"    => $user['id_usuario']
                    ]);

                    setcookie(
                        "remember_token",
                        $token,
                        time() + (86400 * 30),
                        "/",
                        "",
                        false,
                        true
                    );
                }

                if ($user['tipo_usuario'] === 'adm_geral') {
                    header('Location: /soee/src/backend/php/dashboard/dash-adm.php');
                } else {
                    header("Location: /soee/src/backend/php/pages/home.php");
                }
                exit();
            }

        } else {
            $erro = "Usuário não cadastrado ou senha incorreta.";
        }
    }
}
?>