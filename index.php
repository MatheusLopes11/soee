<?php
ob_start();

include __DIR__ . '/src/backend/php/include/conexao.php';
include __DIR__ . '/src/backend/php/auth/auth-home.php';

AuthHome::tentarLoginPorCookie($conn);

if (AuthHome::estaLogado()) {
    AuthHome::redirecionarPorTipo();
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'])) {
    $login   = trim($_POST['username'] ?? '');
    $senha   = trim($_POST['password'] ?? '');
    $lembrar = isset($_POST['remember']);

    $resultado = AuthHome::processarLogin($conn, $login, $senha, $lembrar);

    if ($resultado['sucesso']) {
        header('Location: ' . $resultado['redirect']);
        exit();
    } else {
        $erro = $resultado['erro'];
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR" data-theme="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SOEE — Entrar</title>
<link rel="stylesheet" href="/soee/src/frontend/css/index.css">
<link rel="icon" type="image/png" href="/soee/src/images/logo-soee.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;800&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous">

<script>
    const _t = localStorage.getItem('theme');
    if (_t) document.documentElement.setAttribute('data-theme', _t);
</script>

</head>
<body>

<div class="cursor-dot" id="cursorDot"></div>
<div class="cursor-ring" id="cursorRing"></div>

<div id="loader">
    <div class="loader-inner">
        <div class="loader-logo-text">SOEE</div>
        <div class="loader-logo-sub">Carregando sistema</div>
        <div class="loader-bar"><div class="loader-bar-fill"></div></div>
    </div>
</div>

<div class="pagina-login">

    <div class="lado-esquerdo">
        <div class="grid"></div>
        <div class="particles"><span></span><span></span><span></span></div>

        <div class="esq-conteudo">
            <div class="esq-badge">
                <i class="fa-solid fa-circle fa-xs"></i>
                ETEC Juscelino Kubitschek de Oliveira
            </div>
            <h1>Sistema de<br><em>Esportes Escolares</em></h1>
            <p>
                Plataforma digital para organizar inscrições, partidas e
                classificações dos interclasses da ETEC JK com eficiência
                e transparência.
            </p>
            <div class="esq-stats">
                <div class="esq-stat">
                    <strong>9</strong>
                    <span>Turmas</span>
                </div>
                <div class="esq-stat">
                    <strong>300+</strong>
                    <span>Alunos</span>
                </div>
                <div class="esq-stat">
                    <strong>100%</strong>
                    <span>Digital</span>
                </div>
            </div>
        </div>
    </div>

    <div class="lado-direito">
        <div class="topbar-login">
            <a href="/soee/src/backend/php/pages/inicio.php" class="btn-inicio">
                <i class="fa-solid fa-house"></i> Início
            </a>
            <button class="btn-icone-header" id="toggleTema" aria-label="Tema">
            </button>
        </div>

        <div class="login-card">
            <div class="login-logo">S<span>O</span>EE</div>
            <p class="login-subtitulo">Entre com sua conta para acessar o sistema</p>

            <?php if ($erro): ?>
            <div class="alerta-erro">
                <i class="fa-solid fa-circle-xmark"></i>
                <?= htmlspecialchars($erro) ?>
            </div>
            <?php endif; ?>

            <form method="POST" action="" id="formLogin" novalidate>

                <div class="campo-grupo">
                    <label class="campo-label" for="username">Usuário ou E-mail</label>
                    <div class="campo-wrapper">
                        <i class="campo-icone fa-solid fa-user"></i>
                        <input
                            type="text"
                            id="username"
                            name="username"
                            class="campo-input"
                            placeholder="Seu nome ou e-mail"
                            value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                            autocomplete="username"
                        >
                    </div>
                </div>

                <div class="campo-grupo">
                    <label class="campo-label" for="password">Senha</label>
                    <div class="campo-wrapper">
                        <i class="campo-icone fa-solid fa-lock"></i>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="campo-input"
                            placeholder="Sua senha"
                            autocomplete="current-password"
                        >
                        <button type="button" class="campo-toggle-senha" id="toggleSenha" aria-label="Mostrar senha">
                            <i class="fa-solid fa-eye" id="iconeSenha"></i>
                        </button>
                    </div>
                </div>

                <div class="opcoes-login">
                    <label class="checkbox-label">
                        <input type="checkbox" name="remember" value="1"> Lembrar de mim
                    </label>
                    <a href="#" class="link-esqueci">Esqueci a senha</a>
                </div>

                <button type="submit" class="btn-entrar" id="btnEntrar">
                    <div class="btn-spinner"></div>
                    <span class="btn-texto"><i class="fa-solid fa-right-to-bracket"></i> &nbsp;Entrar</span>
                </button>

                <div class="divisor">ou</div>

                <a href="/soee/src/backend/php/pages/inicio.php" class="btn-inicio-soee">
                    <i class="fa-solid fa-globe"></i> Acessar como visitante
                </a>

            </form>
        </div>
    </div>

</div>

    <footer class="rodape-login">
        &copy; <?= date('Y') ?> <a href="#">SOEE</a> — ETEC Juscelino Kubitschek de Oliveira
    </footer>

    <script src="/soee/src/frontend/js/index.js"></script>
</body>
</html>