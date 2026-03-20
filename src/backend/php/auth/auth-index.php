<?php
session_start();
require_once __DIR__ . '/../include/conexao.php'; // Verifique se o caminho está correto

$erro = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['username'])) {
    $login = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($login) || empty($password)) {
        $erro = "Preencha todos os campos.";
    } else {
        // Busca por nome_usuario OU email_usuario
        $sql = "SELECT * FROM usuario WHERE nome_usuario = :login OR email_usuario = :login LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(":login", $login);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verifica se usuário existe e se a senha confere
        if ($user && $password === $user['senha_usuario']) {
            
            // Verifica se o usuário está ativo
            if ($user['ativo_usuario'] == 0) {
                $erro = "Sua conta está desativada.";
            } else {
                // Sucesso: Cria a sessão
                $_SESSION['user_id']   = $user['id_usuario'];
                $_SESSION['user_nome'] = $user['nome_usuario'];
                $_SESSION['user_tipo'] = $user['tipo_usuario'];

                header("Location: /soee/src/backend/php/pages/home.php");
                exit();
            }
        } else {
            $erro = "Usuário não cadastrado ou senha incorreta.";
        }
    }
}
?>