<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . "/soee/src/backend/php/include/db.php";

$erro = "";

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
            exit;

        } else {
            $erro = "Usuário ou senha inválidos.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>SOEE — Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="icon" type="image/png" href="/soee/src/imgs/logo-soee.png">

    <!-- Fontes -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- CSS -->
    <link rel="stylesheet" href="/soee/src/frontend/css/login.css">
</head>
<body>

<div class="bg-blur"></div>
<canvas id="particles"></canvas>

<div class="wrapper">
    <div class="card">

        <!-- ── Lado Esquerdo ── -->
        <div class="left">
            <div class="left-content">
                <img class="left-img" src="/soee/src/images/soee-login.png" alt="SOEE">
                <div class="left-title">S<span>.</span>O<span>.</span>E<span>.</span>E<span>.</span></div>
                <div class="left-sub">Sistema de Organização Esportiva Escolar</div>
                <div class="sport-chips">
                    <span class="chip">⚽ Futsal</span>
                    <span class="chip">🏐 Vôlei</span>
                    <span class="chip">🏀 Basquete</span>
                    <span class="chip">♟ Xadrez</span>
                    <span class="chip">🏓 Tênis de Mesa</span>
                </div>
            </div>
        </div>

        <!-- ── Lado Direito ── -->
        <div class="right">

            <div class="avatar-wrap">
                <div class="avatar" id="avatarIcon">
                    <i class="fa-solid fa-user"></i>
                </div>
            </div>

            <div class="right-header">
                <h2>Bem-vindo de volta</h2>
                <p>Faça login para acessar o sistema</p>
            </div>

            <?php if (!empty($erro)): ?>
            <div class="error-msg">
                <i class="fa-solid fa-circle-exclamation"></i>
                <?= htmlspecialchars($erro) ?>
            </div>
            <?php endif; ?>

            <form method="POST" action="" id="loginForm" novalidate>

                <div class="form-group">
                    <label for="username">Usuário ou E-mail</label>
                    <div class="input-wrap">
                        <i class="fa-solid fa-user"></i>
                        <input
                            type="text"
                            id="username"
                            name="username"
                            placeholder="Digite seu usuário"
                            value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                            autocomplete="username"
                        >
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Senha</label>
                    <div class="input-wrap">
                        <i class="fa-solid fa-lock"></i>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Digite sua senha"
                            autocomplete="current-password"
                        >
                        <i class="fa-solid fa-eye toggle-pw" id="togglePw" title="Mostrar senha"></i>
                    </div>
                </div>

                <div class="options">
                    <label>
                        <input type="checkbox" name="remember"> Lembrar-me
                    </label>
                </div>

                <button type="submit" class="btn-login" id="btnLogin">
                    ENTRAR
                </button>

                <div class="links-row">
                    <a href="/soee/src/backend/php/pages/home.php">
                        <i class="fa-solid fa-arrow-left"></i> Voltar
                    </a>
                    <span class="register-link">
                        Esqueceu a Senha? <a href="/soee/src/backend/php/form/form-cadastro.php">Cadastrar-se</a>
                    </span>
                </div>

            </form>
        </div>

    </div>
</div>

<!-- JS -->
<script src="/soee/src/frontend/js/login.js"></script>

</body>
</html>