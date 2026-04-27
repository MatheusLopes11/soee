<?php include __DIR__ . '/../includes/doctype.php';?>
  <head>
    <title>Contato & Redes | SOEE</title>

    <link rel="stylesheet" href="/soee/src/frontend/styles/contato-redes.css">
    <link rel="stylesheet" href="/soee/src/frontend/styles/inicio.css">

    <?php include __DIR__ . '/../includes/head.php';?>
  </head>

<body>
<?php
  include __DIR__ . '/../includes/cursor.php';
?>

  <!-- ─── HEADER ─── -->
  <header class="cabecalho">
    <div class="cabecalho-container">
      
      <div class="cabecalho-logos">
        <img src="/soee/src/frontend/assets/icons/logo-jk.png"  alt="ETEC Juscelino Kubitschek de Oliveira">
        <img src="/soee/src/frontend/assets/icons/logo-cps.png" alt="Centro Paula Souza">
      </div>

      <nav class="menu-principal" aria-label="Menu principal">
        <ul class="menu-lista">
          <li><a href="/soee/src/frontend/views/site/home.php">Início</a></li>
          <li><a href="/soee/src/frontend/views/site/modalidades.php">Modalidades</a></li>
          <li><a href="/soee/src/frontend/views/site/quem-somos.php">Quem Somos</a></li>
          <li><a href="/soee/src/frontend/views/site/sobre-etec.php">Sobre a ETEC</a></li>
          <li><a href="/soee/src/frontend/views/forms/feedback.php">Feedback</a></li>
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

        <img src="/soee/src/frontend/assets/icons/logo-soee.png" alt="SOEE" class="logo-sistema">

      </div>
    </div>
  </header>

  <main>


    <section class="pagina">
      <div class="pagina-bg"></div>
      <div class="pagina-grid"></div>
      <div class="pagina-conteudo">
        <div class="badge">
          <i class="fa-solid fa-satellite-dish"></i>
          Fique por dentro
        </div>
        <h1>Contato <em>& Redes</em></h1>
        <p>Acompanhe a ETEC JK e o SOEE nas redes sociais e entre em contato com a equipe.</p>
      </div>
      <div class="pagina-onda">
        <svg viewBox="0 0 1440 80" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M0,40 C360,80 1080,0 1440,40 L1440,80 L0,80 Z" fill="var(--fundo-pagina)"/>
        </svg>
      </div>
    </section>

    <!-- ─── CARDS DE REDES ─── -->
    <section class="redes-section">
      <div class="redes-container">

        <header class="secao-cabecalho reveal">
          <div class="secao-tag">Nos siga</div>
          <h2>Nossas Redes Sociais</h2>
          <p>Acompanhe tudo sobre a ETEC JK e o projeto SOEE</p>
        </header>

        <div class="redes-grid">

          <!-- Instagram ETEC JK -->
          <a
            href="https://www.instagram.com/etecjko"
            target="_blank"
            class="rede-card rede-instagram reveal reveal-delay-1"
            aria-label="Instagram da ETEC JK"
          >
            <div class="rede-icone-wrap">
              <div class="rede-icone">
                <i class="fa-brands fa-instagram"></i>
              </div>
              <div class="rede-brilho"></div>
            </div>
            <div class="rede-info">
              <span class="rede-plataforma">Instagram</span>
              <h3>@etecjko</h3>
              <p>Acompanhe as novidades, eventos e o dia a dia da ETEC Juscelino Kubitschek de Oliveira.</p>
            </div>
            <div class="rede-acao">
              Seguir <i class="fa-solid fa-arrow-up-right-from-square"></i>
            </div>
          </a>

          <!-- GitHub da equipe -->
          <a
            href="https://github.com/CarlosHenriqueValentim/soee"
            target="_blank"
            class="rede-card rede-github reveal reveal-delay-2"
            aria-label="GitHub da equipe SOEE"
          >
            <div class="rede-icone-wrap">
              <div class="rede-icone">
                <i class="fa-brands fa-github"></i>
              </div>
              <div class="rede-brilho"></div>
            </div>
            <div class="rede-info">
              <span class="rede-plataforma">GitHub</span>
              <h3>Equipe SOEE</h3>
              <p>Acesse o repositório do projeto, veja o código-fonte e acompanhe o desenvolvimento do SOEE.</p>
            </div>
            <div class="rede-acao">
              Ver repositório <i class="fa-solid fa-arrow-up-right-from-square"></i>
            </div>
          </a>

        </div>
      </div>
    </section>

    <!-- ─── FEED DO INSTAGRAM ─── -->
    <section class="feed-section">
      <div class="feed-container">

        <header class="secao-cabecalho reveal">
          <div class="secao-tag">
            <i class="fa-brands fa-instagram"></i> @etecjko
          </div>
          <h2>Feed do Instagram</h2>
          <p>As últimas publicações da ETEC JK</p>
        </header>

        <div class="feed-embed reveal">

      <!-- Elfsight Instagram Feed | Untitled Instagram Feed -->
<script src="https://elfsightcdn.com/platform.js" async></script>
<div class="elfsight-app-85c507c9-b53d-44db-8fe1-2fbd89634c9b" data-elfsight-app-lazy></div>

        </div>

        <div class="feed-cta reveal">
          <a href="https://www.instagram.com/etecjko" target="_blank" class="botao-instagram">
            <i class="fa-brands fa-instagram"></i>
            Ver perfil completo no Instagram
          </a>
        </div>

      </div>
    </section>

    <!-- ─── CTA ─── -->
    <section class="chamada-sistema">
      <div class="chamada-sistema-inner">
        <h2>Ficou com alguma dúvida?</h2>
        <p>Entre no sistema, explore as funcionalidades e faça parte dos interclasses da ETEC JK.</p>
        <a href="/soee/index.php" class="botao-primario">
          <i class="fa-solid fa-arrow-right"></i>
          Acessar o Sistema
        </a>
      </div>
    </section>

  </main>

  <?php include __DIR__ . '/../includes/footer.php';?>

  <script src="/soee/src/frontend/scripts/inicio.js"></script>
  <script>
    const _t = localStorage.getItem('theme');
    if (_t) document.documentElement.setAttribute('data-theme', _t);
  </script>

<?php include __DIR__ . '/../includes/end.php';?>