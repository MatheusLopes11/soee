<?php
session_start();
include __DIR__ . '/../../../../pages/conexao.php';

$erro;

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $erro = "Preencha todos os campos.";
    } else {

        $sql = "SELECT * FROM usuario WHERE nome_usuario = :username LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(":username", $username);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && $password === $user['senha_usuario']) {

            $_SESSION['user_id']   = $user['id_usuario'];
            $_SESSION['user_nome'] = $user['nome_usuario'];
            $_SESSION['user_tipo'] = $user['tipo_usuario'];

            header("Location: /soee/src/backend/php/pages/home.php");
            die();
            
        } else {
            $erro = "Usuário ou senha inválidos.";
        }
    }
}
?>