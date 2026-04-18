<?php include __DIR__ . '/../includes/doctype.php'; ?>
  <head>
    <title>Modalidades | SOEE</title>
    <link rel="stylesheet" href="/soee/src/frontend/styles/modalidades.css">
    <?php include __DIR__ . '/../includes/head.php'; ?>
  </head>

<body>

  <div class="cursor-dot" id="cursorDot"></div>
  <div class="cursor-ring" id="cursorRing"></div>

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
          <li><a href="/soee/src/frontend/views/site/modalidades.php" aria-current="page">Modalidades</a></li>
          <li><a href="/soee/src/frontend/views/site/sobre-etec.php">Sobre a ETEC</a></li>
       
          <li><a href="/soee/src/frontend/views/site/contato-redes.php">Contato & Redes</a></li>
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

    <!-- ─── HERO DA PÁGINA ─── -->
    <section class="pagina">
      <div class="pagina-bg"></div>
      <div class="pagina-grid"></div>
      <div class="pagina-conteudo">
        <div class="badge">
          <i class="fa-solid fa-medal"></i>
          Interclasses ETEC JK
        </div>
        <h1>Modalidades <em>Esportivas</em></h1>
        <p>Conheça todas as modalidades que fazem parte dos interclasses da ETEC Juscelino Kubitschek de Oliveira.</p>
      </div>
      <div class="pagina-onda">
        <svg viewBox="0 0 1440 80" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M0,40 C360,80 1080,0 1440,40 L1440,80 L0,80 Z" fill="var(--fundo-pagina)"/>
        </svg>
      </div>
    </section>

    <!-- ─── FILTRO ─── -->
    <section class="modalidades-filtro-section">
      <div class="modalidades-filtro">
        <button class="filtro-btn ativo" data-filtro="todos">
          <i class="fa-solid fa-border-all"></i> Todas
        </button>
        <button class="filtro-btn" data-filtro="coletivo">
          <i class="fa-solid fa-users"></i> Coletivas
        </button>
        <button class="filtro-btn" data-filtro="individual">
          <i class="fa-solid fa-user"></i> Individuais
        </button>
      </div>
    </section>

    <!-- ─── GRID DE MODALIDADES ─── -->
    <section class="modalidades-section">
      <div class="modalidades-grid">

        <article class="modalidade-card reveal reveal-delay-1" data-categoria="coletivo">
          <div class="card-icone-wrap"><div class="card-icone">⚽</div></div>
          <div class="card-numero">01</div>
          <div class="card-corpo">
            <div class="card-tag">Coletiva</div>
            <h2>Futsal</h2>
            <p>O futsal é uma das modalidades mais aguardadas do interclasse! Jogado em quadra com times de 5 jogadores, exige raciocínio rápido, trabalho em equipe e muita habilidade com a bola.</p>
            <div class="card-info">
              <span><i class="fa-solid fa-users"></i> 5 por time</span>
              <span><i class="fa-solid fa-clock"></i> 2 × 20 min</span>
              <span><i class="fa-solid fa-location-dot"></i> Quadra</span>
            </div>
          </div>
          <div class="card-hover-line"></div>
        </article>

        <article class="modalidade-card reveal reveal-delay-2" data-categoria="coletivo">
          <div class="card-icone-wrap"><div class="card-icone">🏐</div></div>
          <div class="card-numero">02</div>
          <div class="card-corpo">
            <div class="card-tag">Coletiva</div>
            <h2>Vôlei</h2>
            <p>O vôlei une força, precisão e sincronismo. Com 6 jogadores por lado, cada toque conta para garantir pontos e manter a bola no ar.</p>
            <div class="card-info">
              <span><i class="fa-solid fa-users"></i> 6 por time</span>
              <span><i class="fa-solid fa-trophy"></i> Sets de 25 pts</span>
              <span><i class="fa-solid fa-location-dot"></i> Quadra</span>
            </div>
          </div>
          <div class="card-hover-line"></div>
        </article>

        <article class="modalidade-card reveal reveal-delay-3" data-categoria="coletivo">
          <div class="card-icone-wrap"><div class="card-icone">🤾</div></div>
          <div class="card-numero">03</div>
          <div class="card-corpo">
            <div class="card-tag">Coletiva</div>
            <h2>Handebol</h2>
            <p>Velocidade e contato: o handebol é dinâmico e intenso. 7 jogadores por time se revezam em ataques e defesas rápidas.</p>
            <div class="card-info">
              <span><i class="fa-solid fa-users"></i> 7 por time</span>
              <span><i class="fa-solid fa-clock"></i> 2 × 30 min</span>
              <span><i class="fa-solid fa-location-dot"></i> Quadra</span>
            </div>
          </div>
          <div class="card-hover-line"></div>
        </article>

        <article class="modalidade-card reveal reveal-delay-4" data-categoria="coletivo">
          <div class="card-icone-wrap"><div class="card-icone">🎯</div></div>
          <div class="card-numero">04</div>
          <div class="card-corpo">
            <div class="card-tag">Coletiva</div>
            <h2>Queimada</h2>
            <p>Clássico das escolas brasileiras! A queimada resgata a nostalgia e a diversão em formato competitivo.</p>
            <div class="card-info">
              <span><i class="fa-solid fa-users"></i> Turma completa</span>
              <span><i class="fa-solid fa-fire"></i> Alta energia</span>
              <span><i class="fa-solid fa-location-dot"></i> Quadra</span>
            </div>
          </div>
          <div class="card-hover-line"></div>
        </article>

        <article class="modalidade-card reveal reveal-delay-1" data-categoria="coletivo">
          <div class="card-icone-wrap"><div class="card-icone">🏀</div></div>
          <div class="card-numero">05</div>
          <div class="card-corpo">
            <div class="card-tag">Coletiva</div>
            <h2>Basquete</h2>
            <p>Com cestas valendo 2 ou 3 pontos, o basquete exige agilidade, visão de jogo e pontaria. Os times de 5 jogadores disputam em quadra numa batalha de dribles e passes.</p>
            <div class="card-info">
              <span><i class="fa-solid fa-users"></i> 5 por time</span>
              <span><i class="fa-solid fa-clock"></i> 4 × 10 min</span>
              <span><i class="fa-solid fa-location-dot"></i> Quadra</span>
            </div>
          </div>
          <div class="card-hover-line"></div>
        </article>

        <article class="modalidade-card reveal reveal-delay-2" data-categoria="individual">
          <div class="card-icone-wrap"><div class="card-icone">♟️</div></div>
          <div class="card-numero">06</div>
          <div class="card-corpo">
            <div class="card-tag">Individual</div>
            <h2>Xadrez</h2>
            <p>O esporte da mente! O xadrez testa concentração, planejamento e inteligência estratégica. Cada movimento pode definir o destino da partida.</p>
            <div class="card-info">
              <span><i class="fa-solid fa-user"></i> 1 vs 1</span>
              <span><i class="fa-solid fa-brain"></i> Estratégia</span>
              <span><i class="fa-solid fa-location-dot"></i> Sala</span>
            </div>
          </div>
          <div class="card-hover-line"></div>
        </article>

        <article class="modalidade-card reveal reveal-delay-3" data-categoria="individual">
          <div class="card-icone-wrap"><div class="card-icone">🔴</div></div>
          <div class="card-numero">07</div>
          <div class="card-corpo">
            <div class="card-tag">Individual</div>
            <h2>Damas</h2>
            <p>Simples de aprender, difícil de dominar. O jogo de damas é pura lógica: capture as peças do adversário e leve sua dama ao outro lado do tabuleiro.</p>
            <div class="card-info">
              <span><i class="fa-solid fa-user"></i> 1 vs 1</span>
              <span><i class="fa-solid fa-chess-board"></i> Tabuleiro</span>
              <span><i class="fa-solid fa-location-dot"></i> Sala</span>
            </div>
          </div>
          <div class="card-hover-line"></div>
        </article>

        <article class="modalidade-card reveal reveal-delay-4" data-categoria="individual">
          <div class="card-icone-wrap"><div class="card-icone">🏓</div></div>
          <div class="card-numero">08</div>
          <div class="card-corpo">
            <div class="card-tag">Individual</div>
            <h2>Tênis de Mesa</h2>
            <p>Velocidade de reflexo e precisão milimétrica. O ping-pong coloca dois competidores frente a frente numa mesa em partidas eletrizantes.</p>
            <div class="card-info">
              <span><i class="fa-solid fa-user"></i> 1 vs 1</span>
              <span><i class="fa-solid fa-trophy"></i> Sets de 11 pts</span>
              <span><i class="fa-solid fa-location-dot"></i> Sala</span>
            </div>
          </div>
          <div class="card-hover-line"></div>
        </article>

      </div>
    </section>

    <!-- ─── CTA ─── -->
    <section class="chamada-sistema">
      <div class="chamada-sistema-inner">
        <h2>Pronto para competir?</h2>
        <p>Acesse o sistema, escolha sua modalidade e faça sua inscrição.</p>
        <a href="/soee/index.php" class="botao-primario">
          <i class="fa-solid fa-arrow-right"></i>
          Acessar o Sistema
        </a>
      </div>
    </section>

  </main>

  <?php include __DIR__ . '/../includes/footer.php';?>

  <script src="/soee/src/frontend/scripts/inicio.js"></script>
  <script src="/soee/src/frontend/scripts/modalidades.js"></script>
  <?php include __DIR__ . '/../includes/end.php'; ?>

</body>
</html>