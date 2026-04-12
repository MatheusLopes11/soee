<?php include __DIR__ . '/../includes/doctype.php';?>
  <head>
    <title>SOEE | home</title>

    <!-- ( LAYOUTS ) -->
    <link rel="stylesheet" href="/soee/src/frontend/styles/layouts/header.css">
    <link rel="stylesheet" href="/soee/src/frontend/styles/layouts/footer.css">

    <!-- ( SECTIONS ) -->
    <link rel="stylesheet" href="/soee/src/frontend/styles/sections/hero.css">
    <link rel="stylesheet" href="/soee/src/frontend/styles/sections/sobre.css">
    <link rel="stylesheet" href="/soee/src/frontend/styles/sections/cta.css">
    <link rel="stylesheet" href="/soee/src/frontend/styles/sections/gerais.css">

    <!-- ( COMPONENTS ) -->
    <link rel="stylesheet" href="/soee/src/frontend/styles/components/card-problemas.css">
    <link rel="stylesheet" href="/soee/src/frontend/styles/components/card-feacture.css">
    <link rel="stylesheet" href="/soee/src/frontend/styles/components/stats.css">
    <link rel="stylesheet" href="/soee/src/frontend/styles/components/badge.css">
    <link rel="stylesheet" href="/soee/src/frontend/styles/components/particles.css">

    <!-- ( BUTTONS ) -->
    <link rel="stylesheet" href="/soee/src/frontend/styles/buttons/primary.css">
    <link rel="stylesheet" href="/soee/src/frontend/styles/buttons/secondary.css">

    <!-- ( ANIMATIONS ) -->
    <link rel="stylesheet" href="/soee/src/frontend/styles/animations/keyframes.css">
    <link rel="stylesheet" href="/soee/src/frontend/styles/animations/scroll-behavior.css">
    <link rel="stylesheet" href="/soee/src/frontend/styles/animations/scroll-reveal.css">

    <?php include __DIR__ . '/../includes/head.php';?> 
  </head>

<body>
    <?php include __DIR__ . '/../includes/cursor.php'?>
    <?php include __DIR__ . '/../includes/loader.php'?>

  <!-- ─── HEADER ─── -->
  <header class="cabecalho">
    <div class="cabecalho-container">

      <!-- Esquerda: logos institucionais -->
      <div class="cabecalho-logos">
        <img src="/soee/src/frontend/assets/icons/logo-jk.png"  alt="ETEC Juscelino Kubitschek de Oliveira">
        <img src="/soee/src/frontend/assets/icons/logo-cps.png" alt="Centro Paula Souza">
      </div>

      <!-- Centro: nav (desktop) -->
      <nav class="menu-principal" aria-label="Menu principal">
        <ul class="menu-lista">
          <li><a href="/soee/src/frontend/views/site/modalidades.php">Modalidades</a></li>
          <li><a href="/soee/src/frontend/views/site/quem-somos.php">Quem Somos</a></li>
          <li><a href="/soee/src/frontend/views/site/sobre-etec.php">ETEC</a></li>
          <li><a href="/soee/src/frontend/views/site/portal.php">Portal</a></li>
          <li><a href="/soee/src/frontend/views/site/contato-redes.php">Contato Redes</a></li>
          <li><a href="/soee/src/frontend/views/forms/feedback.php">Feedback</a></li>
        </ul>
      </nav>

      <!-- Direita: ações -->
      <div class="cabecalho-acoes">
        <button id="toggle-theme" class="botao-icone" aria-label="Alternar tema">
          <i class="fa-solid fa-moon" id="icone-tema"></i>
        </button>

        <a href="/soee/index.php" class="botao-login">
          <i class="fa-solid fa-user"></i>
          <span>Entrar</span>
        </a>

        <img
          src="/soee/src/frontend/assets/icons/logo-soee.png"
          alt="SOEE"
          class="logo-sistema"
        >
      </div>

    </div>
  </header>
  
  <!-- ─── CONTEÚDO PRINCIPAL ─── -->
  <main id="pagina-inicial">

    <section class="intro-soee" aria-labelledby="titulo-soee">
      <div class="bg"></div>
      <div class="grid"></div>
      <div class="particles">
        <span></span><span></span><span></span><span></span><span></span>
      </div>

      <div class="intro-conteudo">
        <div class="badge">
          <i class="fa-solid fa-trophy"></i>
          ETEC Juscelino Kubitschek de Oliveira
        </div>

        <h1 id="titulo-soee">
          Sistema de Organização de<br>
          <em>Esportes Escolares</em>
        </h1>

        <p>
          O <strong>SOEE</strong> é uma plataforma digital desenvolvida para
          organizar e modernizar os interclasses e eventos esportivos da
          ETEC Juscelino Kubitschek de Oliveira.
        </p>

        <div class="intro-acoes">
          <a href="#sobre-soee" class="botao-primario">
            <i class="fa-solid fa-arrow-down"></i>
            Conheça o Projeto
          </a>
          <a href="#funcionalidades-soee" class="botao-secundario">
            <i class="fa-solid fa-star"></i>
            Funcionalidades
          </a>
        </div>
      </div>
    </section>

    <!-- Sobre -->
    <section id="sobre-soee" class="secao-conteudo" aria-labelledby="titulo-sobre">
      <header class="secao-cabecalho reveal">
        <div class="secao-tag">Sobre o sistema</div>
        <h2 id="titulo-sobre">Sobre o SOEE</h2>
        <p>Organização, tecnologia e inovação no esporte escolar</p>
      </header>

      <div class="sobre-grid">
        <div class="sobre-texto reveal reveal-delay-1">
          <p>
            O SOEE foi criado para resolver problemas recorrentes na organização
            manual dos interclasses, como falhas de comunicação, conflitos de
            horários e ausência de controle centralizado.
          </p>
          <p>
            A proposta é oferecer um sistema único, transparente e eficiente,
            facilitando a gestão do evento para alunos, professores e coordenação.
          </p>
        </div>
        <div class="sobre-destaque reveal reveal-delay-2">
          <blockquote>
            Um sistema único e eficiente para toda a comunidade escolar.
          </blockquote>
          <cite>— SOEE, 2026</cite>
        </div>
      </div>
    </section>

    <!-- Problemática -->
    <section class="secao-problemas" aria-labelledby="titulo-problemas">
      <header class="secao-cabecalho reveal">
        <div class="secao-tag">Problemática</div>
        <h2 id="titulo-problemas">Problemática Identificada</h2>
        <p>Principais dificuldades enfrentadas atualmente</p>
      </header>

      <div class="problemas-lista">
        <article class="problema-item reveal reveal-delay-1">
          <div class="problema-icone"><i class="fa-solid fa-triangle-exclamation"></i></div>
          <h3>Falta de Organização</h3>
          <p>Processos manuais causam erros, retrabalho e perda de informações.</p>
        </article>

        <article class="problema-item reveal reveal-delay-2">
          <div class="problema-icone"><i class="fa-solid fa-clock"></i></div>
          <h3>Conflitos de Horários</h3>
          <p>Partidas marcadas simultaneamente e dificuldade de ajustes.</p>
        </article>

        <article class="problema-item reveal reveal-delay-3">
          <div class="problema-icone"><i class="fa-solid fa-comment-slash"></i></div>
          <h3>Comunicação Ineficiente</h3>
          <p>Ausência de um canal oficial para avisos e resultados.</p>
        </article>

        <article class="problema-item reveal reveal-delay-4">
          <div class="problema-icone"><i class="fa-solid fa-face-frown"></i></div>
          <h3>Desmotivação dos Alunos</h3>
          <p>A desorganização compromete a experiência esportiva.</p>
        </article>
      </div>
    </section>

    <!-- Funcionalidades -->
    <section id="funcionalidades-soee" class="secao-funcionalidades" aria-labelledby="titulo-funcionalidades">
      <header class="secao-cabecalho reveal">
        <div class="secao-tag">O que oferecemos</div>
        <h2 id="titulo-funcionalidades">Funcionalidades do Sistema</h2>
        <p>Recursos desenvolvidos para facilitar a organização</p>
      </header>

      <div class="funcionalidades-lista">
        <article class="funcionalidade-item reveal reveal-delay-1">
          <div class="func-icone"><i class="fa-solid fa-user-plus"></i></div>
          <h3>Inscrição Online</h3>
          <p>Cadastro digital de participantes de forma rápida e segura.</p>
        </article>

        <article class="funcionalidade-item reveal reveal-delay-2">
          <div class="func-icone"><i class="fa-solid fa-users"></i></div>
          <h3>Gestão de Times</h3>
          <p>Controle completo de equipes, atletas e modalidades.</p>
        </article>

        <article class="funcionalidade-item reveal reveal-delay-3">
          <div class="func-icone"><i class="fa-solid fa-calendar-check"></i></div>
          <h3>Cronogramas Automatizados</h3>
          <p>Geração inteligente de tabelas e confrontos.</p>
        </article>

        <article class="funcionalidade-item reveal reveal-delay-4">
          <div class="func-icone"><i class="fa-solid fa-bolt"></i></div>
          <h3>Resultados em Tempo Real</h3>
          <p>Acompanhamento atualizado das partidas e classificações.</p>
        </article>
      </div>
    </section>

    <!-- CTA -->
    <section class="chamada-sistema">
      <div class="chamada-sistema-inner">
        <h2>SOEE — Tecnologia a favor do esporte escolar</h2>
        <p>Mais organização, transparência e eficiência para toda a comunidade escolar.</p>
        <a href="/soee/index.php" class="botao-primario">
          <i class="fa-solid fa-arrow-right"></i>
          Acessar o Sistema
        </a>
      </div>
    </section>

  </main>

<?php include __DIR__ . '/../includes/footer.php';?>

  <!-- ( JavaScript ) -->
  <script src="/soee/src/frontend/scripts/inicio.js"></script>
  <script>
    const _t = localStorage.getItem('theme');
    if (_t) document.documentElement.setAttribute('data-theme', _t);
  </script>

<?php include __DIR__ . '/../includes/end.php';?>