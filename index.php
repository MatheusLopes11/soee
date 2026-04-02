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
    $login    = trim($_POST['username']  ?? '');
    $senha    = trim($_POST['password']  ?? '');
    $lembrar  = isset($_POST['remember']);

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
<link rel="icon" type="image/png" href="/soee/src/images/logo-soee.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;800&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous">
<script>
    const _t = localStorage.getItem('theme');
    if (_t) document.documentElement.setAttribute('data-theme', _t);
</script>
<style>
:root {
    --azul: #1e5671;
    --azul-2: #2c7da3;
    --laranja: #ff4d12;
    --laranja-s: rgba(255,77,18,.22);
    --fundo: #f8fafc;
    --bloco: #ffffff;
    --texto: #1e293b;
    --texto-2: #64748b;
    --branco: #ffffff;
    --borda: rgba(30,86,113,.09);
    --sombra: 0 10px 40px -10px rgba(0,0,0,.1);
    --r: 20px;
    --rm: 10px;
    --tr: all .32s cubic-bezier(.4,0,.2,1);
    --erro: #ef4444;
}
[data-theme="dark"] {
    --fundo: #060d14;
    --bloco: #0c1825;
    --texto: #e2e8f0;
    --texto-2: #8da8bc;
    --borda: rgba(44,125,163,.12);
}
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box}
html{height:100%;scroll-behavior:smooth}
body{
    font-family:'DM Sans',sans-serif;
    background:var(--fundo);color:var(--texto);
    min-height:100vh;display:flex;flex-direction:column;
    overflow-x:hidden;
}

.cursor-dot{
    position:fixed;width:8px;height:8px;
    background:var(--laranja);border-radius:50%;
    pointer-events:none;z-index:9999;
    transform:translate(-50%,-50%);transition:transform .1s;
}
.cursor-ring{
    position:fixed;width:36px;height:36px;
    border:2px solid rgba(255,77,18,.4);border-radius:50%;
    pointer-events:none;z-index:9998;
    transform:translate(-50%,-50%);
    transition:width .25s,height .25s,border-color .25s;
}
body:has(a:hover) .cursor-ring,
body:has(button:hover) .cursor-ring{width:52px;height:52px;border-color:rgba(255,77,18,.7)}

#loader{
    position:fixed;inset:0;background:var(--azul);
    display:flex;align-items:center;justify-content:center;
    z-index:10000;transition:opacity .6s,visibility .6s;
}
#loader.hide{opacity:0;visibility:hidden}
.loader-inner{text-align:center;color:white}
.loader-logo-text{font-family:'Playfair Display',serif;font-size:2.2rem;font-weight:800;letter-spacing:.08em}
.loader-logo-sub{font-size:.75rem;letter-spacing:.18em;text-transform:uppercase;opacity:.6;margin-top:4px}
.loader-bar{width:200px;height:3px;background:rgba(255,255,255,.2);border-radius:999px;margin:16px auto 0;overflow:hidden}
.loader-bar-fill{height:100%;background:var(--laranja);border-radius:999px;animation:loadBar 1.4s cubic-bezier(.4,0,.2,1) forwards}
@keyframes loadBar{from{width:0}to{width:100%}}

.pagina-login{
    flex:1;display:grid;
    grid-template-columns:1fr 1fr;
    min-height:100vh;
}

.lado-esquerdo{
    background:linear-gradient(160deg,#0a2d3d 0%,#1e5671 45%,#0f3447 100%);
    position:relative;overflow:hidden;
    display:flex;flex-direction:column;
    align-items:flex-start;justify-content:center;
    padding:60px 56px;
    color:white;
}
.lado-esquerdo .grid{
    position:absolute;inset:0;
    background-image:
        linear-gradient(rgba(255,255,255,.025) 1px,transparent 1px),
        linear-gradient(90deg,rgba(255,255,255,.025) 1px,transparent 1px);
    background-size:60px 60px;
    mask-image:radial-gradient(ellipse 80% 80% at 50% 50%,black 40%,transparent 100%);
}
.lado-esquerdo .particles{position:absolute;inset:0;overflow:hidden;pointer-events:none}
.lado-esquerdo .particles span{
    position:absolute;border-radius:50%;
    background:rgba(255,255,255,.05);
    animation:float linear infinite;
}
.lado-esquerdo .particles span:nth-child(1){width:300px;height:300px;top:-60px;left:-80px;animation-duration:18s}
.lado-esquerdo .particles span:nth-child(2){width:160px;height:160px;top:25%;right:5%;animation-duration:13s;animation-delay:-5s}
.lado-esquerdo .particles span:nth-child(3){width:80px;height:80px;bottom:20%;left:20%;animation-duration:9s;animation-delay:-3s;background:rgba(255,77,18,.08)}
@keyframes float{0%{transform:translate(0,0) scale(1);opacity:.6}50%{transform:translate(15px,-25px) scale(1.04);opacity:1}100%{transform:translate(0,0) scale(1);opacity:.6}}

.esq-conteudo{position:relative;z-index:2;max-width:460px}
.esq-badge{
    display:inline-flex;align-items:center;gap:8px;
    background:rgba(255,255,255,.1);
    border:1px solid rgba(255,255,255,.18);
    padding:6px 16px;border-radius:999px;
    font-size:.75rem;font-weight:600;letter-spacing:.1em;text-transform:uppercase;
    color:rgba(255,255,255,.85);margin-bottom:28px;
}
.esq-badge i{color:var(--laranja);font-size:.7rem}
.esq-conteudo h1{
    font-family:'Playfair Display',serif;
    font-size:clamp(2rem,3.5vw,3rem);font-weight:800;
    line-height:1.15;margin-bottom:20px;
}
.esq-conteudo h1 em{font-style:normal;color:var(--laranja);position:relative}
.esq-conteudo h1 em::after{
    content:'';position:absolute;bottom:2px;left:0;right:0;
    height:3px;background:var(--laranja);border-radius:2px;opacity:.5
}
.esq-conteudo p{color:rgba(255,255,255,.65);font-size:.95rem;line-height:1.7;margin-bottom:36px}
.esq-stats{display:flex;gap:28px;padding-top:24px;border-top:1px solid rgba(255,255,255,.1)}
.esq-stat strong{
    display:block;font-family:'Playfair Display',serif;
    font-size:1.6rem;font-weight:800;color:var(--laranja)
}
.esq-stat span{font-size:.7rem;letter-spacing:.1em;text-transform:uppercase;opacity:.55}

.lado-direito{
    display:flex;flex-direction:column;
    align-items:center;justify-content:center;
    padding:40px 32px;
    background:var(--fundo);
    position:relative;
}
.topbar-login{
    position:absolute;top:0;left:0;right:0;
    height:60px;
    display:flex;align-items:center;justify-content:flex-end;
    padding:0 28px;gap:12px;
}
.btn-inicio{
    display:flex;align-items:center;gap:8px;
    text-decoration:none;color:var(--texto-2);
    font-size:.82rem;font-weight:600;
    padding:7px 14px;border-radius:var(--rm);
    border:1px solid var(--borda);
    transition:var(--tr);
}
.btn-inicio:hover{color:var(--laranja);border-color:var(--laranja);background:rgba(255,77,18,.04)}
.btn-icone-header{
    width:34px;height:34px;background:none;
    border:1px solid var(--borda);border-radius:var(--rm);
    display:flex;align-items:center;justify-content:center;
    cursor:pointer;color:var(--texto-2);font-size:.85rem;
    transition:var(--tr);
}
.btn-icone-header:hover{border-color:var(--laranja);color:var(--laranja)}

.login-card{
    width:100%;max-width:420px;
    background:var(--bloco);
    border:1px solid var(--borda);
    border-radius:var(--r);
    box-shadow:var(--sombra);
    padding:44px 40px;
    position:relative;overflow:hidden;
}
.login-card::before{
    content:'';position:absolute;top:0;left:0;right:0;height:4px;
    background:linear-gradient(90deg,var(--laranja),var(--azul-2));
}
.login-logo{
    font-family:'Playfair Display',serif;
    font-size:1.8rem;font-weight:800;
    color:var(--azul);margin-bottom:4px;
    letter-spacing:.05em;
}
.login-logo span{color:var(--laranja)}
.login-subtitulo{font-size:.82rem;color:var(--texto-2);margin-bottom:32px}

.alerta-erro{
    display:flex;align-items:center;gap:10px;
    background:#fef2f2;border:1px solid #fca5a5;
    border-radius:var(--rm);padding:12px 14px;
    font-size:.85rem;color:#b91c1c;margin-bottom:22px;
}
.alerta-erro i{font-size:.9rem;flex-shrink:0}

.campo-grupo{margin-bottom:18px}
.campo-label{
    display:block;font-size:.78rem;font-weight:700;
    letter-spacing:.07em;text-transform:uppercase;
    color:var(--texto-2);margin-bottom:7px;
}
.campo-wrapper{position:relative}
.campo-icone{
    position:absolute;left:14px;top:50%;transform:translateY(-50%);
    color:var(--texto-2);font-size:.85rem;pointer-events:none;
}
.campo-input{
    width:100%;padding:12px 14px 12px 40px;
    background:var(--fundo);
    border:1.5px solid var(--borda);
    border-radius:var(--rm);
    font-family:'DM Sans',sans-serif;
    font-size:.9rem;color:var(--texto);
    outline:none;transition:var(--tr);
    -webkit-appearance:none;
}
.campo-input:focus{
    border-color:var(--azul-2);
    box-shadow:0 0 0 3px rgba(44,125,163,.1);
    background:var(--bloco);
}
.campo-input.invalido{border-color:var(--erro);box-shadow:0 0 0 3px rgba(239,68,68,.09)}
.campo-toggle-senha{
    position:absolute;right:14px;top:50%;transform:translateY(-50%);
    background:none;border:none;cursor:pointer;
    color:var(--texto-2);font-size:.85rem;transition:var(--tr);
    padding:4px;
}
.campo-toggle-senha:hover{color:var(--azul)}

.opcoes-login{
    display:flex;align-items:center;justify-content:space-between;
    margin-bottom:22px;
}
.checkbox-label{
    display:flex;align-items:center;gap:8px;
    font-size:.83rem;color:var(--texto-2);cursor:pointer;
    user-select:none;
}
.checkbox-label input{
    width:16px;height:16px;
    accent-color:var(--laranja);cursor:pointer;
}
.link-esqueci{
    font-size:.82rem;color:var(--azul-2);
    text-decoration:none;font-weight:600;
    transition:var(--tr);
}
.link-esqueci:hover{color:var(--laranja)}

.btn-entrar{
    width:100%;padding:14px;
    background:var(--laranja);color:white;border:none;
    border-radius:var(--rm);font-family:'DM Sans',sans-serif;
    font-size:.95rem;font-weight:700;cursor:pointer;
    transition:var(--tr);box-shadow:0 6px 20px var(--laranja-s);
    display:flex;align-items:center;justify-content:center;gap:10px;
    position:relative;overflow:hidden;
}
.btn-entrar::before{content:'';position:absolute;inset:0;background:rgba(255,255,255,0);transition:background .3s}
.btn-entrar:hover{transform:translateY(-2px);box-shadow:0 12px 32px var(--laranja-s)}
.btn-entrar:hover::before{background:rgba(255,255,255,.07)}
.btn-entrar:active{transform:translateY(0)}
.btn-entrar.loading{pointer-events:none;opacity:.8}
.btn-spinner{display:none;width:18px;height:18px;border:2px solid rgba(255,255,255,.4);border-top-color:white;border-radius:50%;animation:spin .7s linear infinite}
.btn-entrar.loading .btn-spinner{display:block}
.btn-entrar.loading .btn-texto{opacity:.7}
@keyframes spin{to{transform:rotate(360deg)}}

.divisor{
    display:flex;align-items:center;gap:12px;
    margin:20px 0;color:var(--texto-2);font-size:.78rem;
}
.divisor::before,.divisor::after{content:'';flex:1;height:1px;background:var(--borda)}

.btn-inicio-soee{
    width:100%;padding:12px;
    background:var(--fundo);color:var(--texto-2);
    border:1.5px solid var(--borda);border-radius:var(--rm);
    font-family:'DM Sans',sans-serif;font-size:.88rem;font-weight:600;
    cursor:pointer;transition:var(--tr);text-decoration:none;
    display:flex;align-items:center;justify-content:center;gap:8px;
}
.btn-inicio-soee:hover{border-color:var(--azul-2);color:var(--azul);background:var(--bloco)}

.rodape-login{
    background:#060d14;color:#94a3b8;
    text-align:center;padding:18px;
    font-size:.75rem;border-top:1px solid rgba(255,255,255,.04);
}
.rodape-login a{color:var(--laranja);text-decoration:none}

@media(max-width:900px){
    .pagina-login{grid-template-columns:1fr}
    .lado-esquerdo{display:none}
    .lado-direito{min-height:100vh;padding:28px 20px}
    .cursor-dot,.cursor-ring{display:none}
}
</style>
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
                <i class="fa-solid fa-moon" id="iconeTema"></i>
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

<script>
    window.addEventListener('load', () => {
        setTimeout(() => document.getElementById('loader').classList.add('hide'), 1500);
    });

    const dot  = document.getElementById('cursorDot');
    const ring = document.getElementById('cursorRing');
    document.addEventListener('mousemove', e => {
        dot.style.left  = ring.style.left  = e.clientX + 'px';
        dot.style.top   = ring.style.top   = e.clientY + 'px';
    });

    const html      = document.documentElement;
    const btnTema   = document.getElementById('toggleTema');
    const iconeTema = document.getElementById('iconeTema');
    const temaSalvo = localStorage.getItem('theme') || 'light';
    html.setAttribute('data-theme', temaSalvo);
    iconeTema.className = temaSalvo === 'dark' ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
    btnTema.addEventListener('click', () => {
        const atual = html.getAttribute('data-theme');
        const novo  = atual === 'dark' ? 'light' : 'dark';
        html.setAttribute('data-theme', novo);
        localStorage.setItem('theme', novo);
        iconeTema.className = novo === 'dark' ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
    });

    const toggleSenha = document.getElementById('toggleSenha');
    const campoPwd    = document.getElementById('password');
    const iconeSenha  = document.getElementById('iconeSenha');
    toggleSenha.addEventListener('click', () => {
        const visivel = campoPwd.type === 'text';
        campoPwd.type = visivel ? 'password' : 'text';
        iconeSenha.className = visivel ? 'fa-solid fa-eye' : 'fa-solid fa-eye-slash';
    });

    document.getElementById('formLogin').addEventListener('submit', function (e) {
        const user = document.getElementById('username').value.trim();
        const pwd  = document.getElementById('password').value.trim();
        let valido = true;

        if (!user) {
            document.getElementById('username').classList.add('invalido');
            valido = false;
        } else {
            document.getElementById('username').classList.remove('invalido');
        }

        if (!pwd) {
            document.getElementById('password').classList.add('invalido');
            valido = false;
        } else {
            document.getElementById('password').classList.remove('invalido');
        }

        if (!valido) {
            e.preventDefault();
            return;
        }

        document.getElementById('btnEntrar').classList.add('loading');
    });
</script>
</body>
</html>