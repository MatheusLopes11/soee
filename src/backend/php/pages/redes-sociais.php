<?php include __DIR__ . '/../include/doctype.php';?>
<head>
  <title>Redes Socias</title>
    <link rel="stylesheet" href="/soee/src/frontend/css/inicio.css">
  <?php include __DIR__ . '/../include/head-data.php';?>
</head>
    <body>

        <header class="cabecalho">
    <div class="cabecalho-container">

      <div class="cabecalho-logos">
        <img src="/soee/src/images/logo-jk.png"  alt="ETEC Juscelino Kubitschek de Oliveira">
        <img src="/soee/src/images/logo-cps.png" alt="Centro Paula Souza">
      </div>

      <nav class="menu-principal" aria-label="Menu principal">
        <ul class="menu-lista">
          <li><a href="/soee/src/backend/php/pages/inicio.php">Início</a></li>
          <li><a href="/soee/src/backend/php/pages/modalidades.php">Modalidades</a></li>
          <li><a href="/soee/src/backend/php/pages/quem-somos.php">Quem Somos</a></li>
          <li><a href="/soee/src/backend/php/pages/sobre-etec.php">Sobre a ETEC</a></li>
        </ul>
      </nav>

      <div class="cabecalho-acoes">
        <button id="toggle-theme" class="botao-icone" aria-label="Alternar tema">
          <i class="fa-solid fa-moon"></i>
        </button>

        <a href="/soee/index.php" class="botao-login">
          <i class="fa-solid fa-user"></i>
          Entrar
        </a>

        <img
          src="/soee/src/images/logo-soee.png"
          alt="SOEE - Sistema de Organização de Esportes Escolares"
          class="logo-sistema"
        >
      </div>

    </div>
  </header>

<?php include __DIR__ . '/../include/end.php';?>