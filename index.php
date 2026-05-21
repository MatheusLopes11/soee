<?php
ob_start();
session_start();

require __DIR__ . '/src/backend/includes/conexao.php';
require __DIR__ . '/src/backend/controllers/home.php';
include __DIR__ . '/src/backend/auth/index.php';

include __DIR__ . '/src/frontend/views/includes/doctype.php';?>
    <head>
        <title>SOEE | Entrar</title>
        <link rel="stylesheet" href="/soee/src/frontend/styles/index.css">
        <?php include __DIR__ . '/src/frontend/views/includes/head.php';?>
    </head>
<body>
    <?php
        include __DIR__ . '/src/frontend/views/includes/cursor.php';
        include __DIR__ . '/src/frontend/views/includes/loader.php';
    ?>
<div class="pagina-login">

    <!-- LADO ESQUERDO -->
    <div class="lado-esquerdo">
        <div class="grid"></div>

        <div class="particles">
            <span></span><span></span><span></span>
        </div>

        <div class="esq-conteudo">

            <div class="esq-badge">
                <i class="fa-solid fa-circle fa-xs"></i>
                ETEC Juscelino Kubitschek de Oliveira
            </div>

            <h1>
                Sistema de<br>
                <em>Esportes Escolares</em>
            </h1>

            <p>
                Plataforma digital para organizar inscrições, partidas e
                classificações dos interclasses da ETEC JK com eficiência e transparência.
            </p>

            <div class="esq-stats">
                <div class="esq-stat"><strong>9</strong><span>Turmas</span></div>
                <div class="esq-stat"><strong>300+</strong><span>Alunos</span></div>
                <div class="esq-stat"><strong>100%</strong><span>Digital</span></div>
            </div>

        </div>
    </div>

    <!-- LADO DIREITO -->
    <div class="lado-direito">

        <div class="login-card">

            <div class="login-logo">S<span>O</span>EE</div>

            <p class="login-subtitulo">
                Entre com sua conta para acessar o sistema
            </p>

            <!-- ALERTA SUCESSO -->
            <?php if (!empty($sucesso)): ?>
                <div class="alerta-sucesso">
                    <i class="fa-solid fa-circle-check"></i>
                    <?= htmlspecialchars($sucesso) ?>
                </div>
            <?php endif; ?>

            <!-- ALERTA ERRO -->
            <?php if (!empty($erro)): ?>
                <div class="alerta-erro">
                    <i class="fa-solid fa-circle-xmark"></i>
                    <?= htmlspecialchars($erro) ?>
                </div>
            <?php endif; ?>

            <!-- FORMULÁRIO (AGORA VAI PRO BACKEND) -->
            <form method="POST" action="/soee/src/backend/controllers/login.php" id="formLogin" novalidate>

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
                            autocomplete="username"
                            required
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
                            required
                        >

                        <button type="button" class="campo-toggle-senha" id="toggleSenha">
                            <i class="fa-solid fa-eye" id="iconeSenha"></i>
                        </button>
                    </div>
                </div>

                <div class="opcoes-login">
                    <label class="checkbox-label">
                        <input type="checkbox" name="remember" value="1">
                        Lembrar de mim
                    </label>

                    <a href="/soee/src/frontend/views/forms/cadastrar.php" class="link-esqueci">
                        Criar conta
                    </a>
                </div>

                <button type="submit" class="btn-entrar" id="btnEntrar">
                    <div class="btn-spinner"></div>
                    <span class="btn-texto">
                        <i class="fa-solid fa-right-to-bracket"></i>
                        Entrar
                    </span>
                </button>

                <div class="divisor">ou</div>

                <a href="/soee/src/frontend/views/site/home.php" class="btn-inicio-soee">
                    <i class="fa-solid fa-globe"></i>
                    Acessar como visitante
                </a>
            </form>
        </div>
    </div>
</div>

<script src="/soee/src/frontend/scripts/index.js"></script>

<script>
const theme = localStorage.getItem('theme');
if (theme) {
    document.documentElement.setAttribute('data-theme', theme);
}
</script>

<?php include __DIR__ . '/src/frontend/views/includes/end.php'; ?>