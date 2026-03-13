<!DOCTYPE html>
<html lang="pt-br" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Contato & Redes | SOEE</title>

  <link rel="icon" type="image/png" href="/soee/src/images/logo-soee.png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;800&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous">
  <link rel="stylesheet" href="/soee/src/frontend/css/inicio.css">
  <link rel="stylesheet" href="/soee/src/frontend/css/contato-redes.css">
</head>

<body>

  <div class="cursor-dot" id="cursorDot"></div>
  <div class="cursor-ring" id="cursorRing"></div>

  <!-- ─── HEADER ─── -->
  <header class="cabecalho">
    <div class="cabecalho-container">
      <div class="cabecalho-logos">
        <img src="/soee/src/images/logo-jk.png"  alt="ETEC Juscelino Kubitschek de Oliveira">
        <img src="/soee/src/images/logo-cps.png" alt="Centro Paula Souza">
      </div>

      <nav class="menu-principal" aria-label="Menu principal">
        <ul class="menu-lista">
          <li><a href="/soee/inicio.php">Início</a></li>
          <li><a href="/soee/modalidades.php">Modalidades</a></li>
          <li><a href="/soee/quem-somos.php">Quem Somos</a></li>
          <li><a href="/soee/sobre-etec.php">Sobre a ETEC</a></li>
          <li><a href="/soee/contato-redes.php" aria-current="page">Contato & Redes</a></li>
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
        <img src="/soee/src/images/logo-soee.png" alt="SOEE" class="logo-sistema">
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

  <!-- ─── RODAPÉ ─── -->
  <footer class="rodape">
    <div class="rodape-conteudo">
      <div class="rodape-institucional">
        <h3>Etec Juscelino Kubitschek de Oliveira</h3>
        <p>Plataforma digital para organização dos interclasses e eventos esportivos da escola.</p>
      </div>
      <ul class="rodape-lista">
        <li><h3>Comunicação</h3></li>
        <li>(11) 4053-9400</li>
        <li><a href="#">Contato</a></li>
      </ul>
      <ul class="rodape-lista">
        <li><h3>Institucional</h3></li>
        <li><a href="#">ETEC</a></li>
        <li><a href="#">Centro Paula Souza</a></li>
      </ul>
    </div>
    <div class="rodape-direitos">
      © 2026 — SOEE | Todos os direitos reservados
    </div>
  </footer>

  <script src="/soee/src/frontend/js/inicio.js"></script>

</body>
</html>