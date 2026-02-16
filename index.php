<?php
session_start();

$erro = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($sword)) {
        $erro = "Preencha todos os campos.";
    } else {

        if ($username === "admin" && $password === "1234") {
            $_SESSION['user'] = $username;
            header("Location: dashboard.php");
            exit;
        } else {
            $erro = "Usuário ou senha inválidos.";
        }
    }
     if(!empty($erro)): ?>
        <div class="error-msg"><?= $erro ?></div>
        <?php endif;

}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>SOEE Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="/soee/src/imgs/logo-soee.png">

    <link rel="stylesheet" href="/soee/src/frontend/css/login.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">

        <div class="left">
            <img src="/soee/src/frontend/images/soee-login.png" alt="Imagem">
        </div>

        <div class="right">
            <div class="avatar"></div>

            <h2>SOEE LOGIN</h2>
            <p style="color:white; margin-bottom:20px;">Bem-vindo de volta</p>

            <div class="input-box">
                <input type="text" placeholder="Email ou Usuário">
            </div>

            <div class="input-box">
                <input type="password" placeholder="Senha">
            </div>

            <div class="options">
                <label>
                    <input type="checkbox"> Lembre-se
                </label>
                <a href="#" style="color:white;">Esqueceu a senha?</a>
            </div>

            <button>LOGIN</button>

            <div class="register">
                Não tem conta?
                <a href="#">Cadastrar-se</a>
            </div>

        </div>

    </div>
</body>

</html>
