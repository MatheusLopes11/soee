<!DOCTYPE html>
<html lang="pt-br" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Início</title>

  <link rel="icon" type="image/png" href="/soee/src/images/logo-soee.png">

  <!-- Fontes -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;800&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous">

  <!-- CSS da home -->
  <link rel="stylesheet" href="/soee/src/frontend/css/home.css">
</head>

<body>

  <!-- Cursor personalizado -->
  <div class="cursor-dot" id="cursorDot"></div>
  <div class="cursor-ring" id="cursorRing"></div>

  <!-- Loader -->
  <div id="loader">
    <div class="loader-inner">
      <div class="loader-logo-text">SOEE</div>
      <div class="loader-logo-sub">Carregando sistema</div>
      <div class="loader-bar">
        <div class="loader-bar-fill"></div>
      </div>
    </div>
  </div>

  <!-- ─── HEADER ─── -->
  <header class="cabecalho">
    <div class="cabecalho-container">

      <div class="cabecalho-logos">
        <img src="/soee/src/images/logo-jk.png"  alt="ETEC Juscelino Kubitschek de Oliveira">
        <img src="/soee/src/images/logo-cps.png" alt="Centro Paula Souza">
      </div>

      <nav class="menu-principal" aria-label="Menu principal">
        <ul class="menu-lista">
<<<<<<< HEAD
          <li><a href="#"                   aria-current="page">Início</a></li>
          <li><a href="">Modalidades</a></li>
          <li><a href="#">Quem Somos</a></li>
          <li><a href="#">Sobre a ETEC</a></li>
          <li><a href="#">Redes Sociais</a></li>
=======
          <li><a href="/soee/src/backend/php/pages/home.php" aria-current="page">Início</a></li>
          <li><a href="/soee/src/backend/php/pages/mds.php">Modalidades</a></li>
          <li><a href="/soee/src/backend/php/pages/qs.php">Quem Somos</a></li>
          <li><a href="/soee/src/backend/php/pages/se.php">Sobre a ETEC</a></li
          <li><a href="/soee/src/backend/php/pages/rs.php">Redes Sociais</a></li>
>>>>>>> 44f00acd55cda3f521e8b8fece07ef25ca276199
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

      <div class="stats">
        <div class="stat">
          <strong>4+</strong>
          <span>Modalidades</span>
        </div>
        <div class="stat-sep"></div>
        <div class="stat">
          <strong>100+</strong>
          <span>Online</span>
        </div>
        <div class="stat-sep"></div>
        <div class="stat">
          <strong>Real</strong>
          <span>Tempo Real</span>
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

  <!-- ─── RODAPÉ ─── -->
  <footer class="rodape">
    <div class="rodape-conteudo">

      <div class="rodape-institucional">
        <h3>Etec Juscelino Kubitschek de Oliveira</h3>
        <p>Plataforma digital para organização dos interclasses e eventos esportivos da escola.</p>
        <a
          href="https://www.instagram.com/etecjko"
          class="rodape-rede-social"
          target="_blank"
          aria-label="Instagram da Etec JK"
        >
          <i class="fa-brands fa-instagram"></i>
        </a>
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

  <!-- JS da home -->
  <script src="/soee/src/frontend/js/home.js"></script>

</body>
</html>