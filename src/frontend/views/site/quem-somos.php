<?php include __DIR__ . '/../includes/doctype.php';?>
  <head>
    <title>SOEE | Quem Somos</title>
    <link rel="stylesheet" href="/soee/src/frontend/styles/inicio.css">
    <link rel="stylesheet" href="/soee/src/frontend/styles/quem-somos.css">
    <?php include __DIR__ . '/../includes/head.php';?> 
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
           <li><a href="/soee/src/frontend/views/site/modalidades.php">Modalidades</a></li>
           <li><a href="/soee/src/frontend/views/site/sobre-etec.php">ETEC</a></li>
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

    <section class="pagina">
      <div class="pagina-bg"></div>
      <div class="pagina-grid"></div>
      <div class="pagina-conteudo">
        <div class="badge">
          <i class="fa-solid fa-users"></i>
          O time por trás do SOEE
        </div>
        <h1>Quem <em>Somos</em></h1>
        <p>Cinco estudantes, uma ideia e muita determinação para transformar os interclasses da ETEC JK.</p>
      </div>
      <div class="pagina-onda">
        <svg viewBox="0 0 1440 80" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M0,40 C360,80 1080,0 1440,40 L1440,80 L0,80 Z" fill="var(--fundo-pagina)"/>
        </svg>
      </div>
    </section>

    <!-- ─── NOSSA HISTÓRIA ─── -->
    <section class="historia-section">
      <div class="historia-container">

        <header class="secao-cabecalho reveal">
          <div class="secao-tag">Nossa trajetória</div>
          <h2>Como o SOEE nasceu</h2>
          <p>De uma ideia para a cantina a um sistema completo de esportes escolares</p>
        </header>

        <div class="historia-timeline">

          <div class="timeline-item reveal reveal-delay-1">
            <div class="timeline-icone">
              <i class="fa-solid fa-school"></i>
            </div>
            <div class="timeline-conteudo">
              <span class="timeline-tag">1º Semestre</span>
              <h3>O começo de tudo</h3>
              <p>
                Desde as primeiras aulas de TCC, os cinco integrantes do grupo já tinham
                um objetivo em comum: desenvolver algo relevante <strong>para a escola e pelos alunos</strong>.
                A vontade de criar um projeto com impacto real na ETEC JK sempre foi o fio condutor do time.
              </p>
            </div>
          </div>

          <div class="timeline-item reveal reveal-delay-2">
            <div class="timeline-icone">
              <i class="fa-solid fa-utensils"></i>
            </div>
            <div class="timeline-conteudo">
              <span class="timeline-tag">Primeira ideia</span>
              <h3>O aplicativo da cantina</h3>
              <p>
                A primeira proposta foi um aplicativo para a cantina da ETEC — uma solução
                para modernizar pedidos e agilizar o atendimento. No entanto, problemas
                institucionais relacionados à <strong>logística e distribuição de alimentos</strong>
                tornaram inviável dar continuidade ao projeto nessa direção.
              </p>
            </div>
          </div>

          <div class="timeline-item reveal reveal-delay-3">
            <div class="timeline-icone">
              <i class="fa-solid fa-lightbulb"></i>
            </div>
            <div class="timeline-conteudo">
              <span class="timeline-tag">A "luz no fim do túnel"</span>
              <h3>Uma nova direção</h3>
              <p>
                Mantendo o espírito de <strong>"de aluno para aluno"</strong>, o grupo buscou
                outro problema real da escola para resolver. A desorganização dos interclasses
                — com conflitos de horário, falta de comunicação e processos manuais — saltou
                aos olhos de quem vivia isso todo semestre.
              </p>
            </div>
          </div>

          <div class="timeline-item reveal reveal-delay-4">
            <div class="timeline-icone">
              <i class="fa-solid fa-trophy"></i>
            </div>
            <div class="timeline-conteudo">
              <span class="timeline-tag">Hoje</span>
              <h3>Nasce o SOEE</h3>
              <p>
                Surgiu então o <strong>SOEE — Sistema de Organização de Esportes Escolares</strong>:
                uma plataforma web completa para digitalizar, organizar e modernizar toda a gestão
                dos interclasses da ETEC JK. Um projeto que uniu tecnologia, paixão pelo esporte
                e o desejo genuíno de melhorar a experiência escolar.
              </p>
            </div>
          </div>

        </div>
      </div>
    </section>

    <!-- ─── EQUIPE ─── -->
    <section class="equipe-section">
      <div class="equipe-container">

        <header class="secao-cabecalho reveal">
          <div class="secao-tag">O time</div>
          <h2>Conheça a Equipe</h2>
          <p>As pessoas que tornaram o SOEE possível</p>
        </header>

        <div class="equipe-grid">

          <!-- ─────────────────────────────
               INTEGRANTE 1 — Carlos Henrique
               ───────────────────────────── -->
          <article class="membro-card reveal reveal-delay-1">
            <div class="membro-avatar">
              <!-- FOTO: descomente a linha abaixo e substitua pelo caminho da imagem -->
              <!-- <img src="/soee/src/images/equipe/carlos.jpg" alt="Carlos Henrique"> -->

              <!-- Iniciais (remova este bloco quando adicionar a foto) -->
              <div class="membro-iniciais">CH</div>
            </div>
            <div class="membro-info">
              <h3>Carlos Henrique</h3>
              <!-- IDADE: substitua o ?? pela idade real -->
              <span class="membro-idade"><i class="fa-solid fa-cake-candles"></i> 17 anos</span>
              <!-- CIDADE: substitua pelo município/estado real -->
              <span class="membro-cidade"><i class="fa-solid fa-location-dot"></i> Diadema, SP</span>
              <!-- DESCRIÇÃO: escreva uma breve descrição sobre o integrante -->
              <p class="membro-descricao">
                Cursando desenvolvimento de sistemas na Etec JK sou interessado em tecnologia
              </p>
              <div class="membro-links">
                <!-- GITHUB: substitua # pela URL real do GitHub -->
                <a href="https://github.com/CarlosHenriqueValentim" target="_blank" class="membro-link" aria-label="GitHub de Carlos Henrique">
                  <i class="fa-brands fa-github"></i> GitHub
                </a>
                <!-- LINKEDIN: substitua # pela URL real do LinkedIn -->
                <a href="https://www.linkedin.com/in/carlos-henrique-57a87b274/" target="_blank" class="membro-link membro-link-linkedin" aria-label="LinkedIn de Carlos Henrique">
                  <i class="fa-brands fa-linkedin"></i> LinkedIn
                </a>
              </div>
            </div>
          </article>

          <!-- ─────────────────────────────
               INTEGRANTE 2 — Miguel Lopes
               ───────────────────────────── -->
          <article class="membro-card reveal reveal-delay-2">
            <div class="membro-avatar">
               <img src="/soee/src/frontend/assets/images/foto_miguel.jpeg" alt="Miguel Lopes"> 
              <div class="membro-iniciais">ML</div>
            </div>
            <div class="membro-info">
              <h3>Miguel Lopes</h3>
              <span class="membro-idade"><i class="fa-solid fa-cake-candles"></i> 17 anos</span>
              <span class="membro-cidade"><i class="fa-solid fa-location-dot"></i> Diadema, SP</span>
              <p class="membro-descricao">
                Cursando desenvolvimento de sistemas na Etec JK e interessado em engenharia de circuito elétrico apreciador de arte em geral.
              </p>
              <div class="membro-links">
                <a href="https://github.com/Dark34521" target="_blank" class="membro-link" aria-label="GitHub de Miguel Lopes">
                  <i class="fa-brands fa-github"></i> GitHub
                </a>
                <a href="https://www.linkedin.com/in/miguel-lopes-b768a03b6/" target="_blank" class="membro-link membro-link-linkedin" aria-label="LinkedIn de Miguel Lopes">
                  <i class="fa-brands fa-linkedin"></i> LinkedIn
                </a>
              </div>
            </div>
          </article>

          <!-- ─────────────────────────────
               INTEGRANTE 3 — Matheus Lopes
               ───────────────────────────── -->
          <article class="membro-card reveal reveal-delay-3">
            <div class="membro-avatar">
              <img src="/soee/src/frontend/assets/images/foto_matheus.jpeg" alt="Matheus Lopes"> 
              <div class="membro-iniciais">ML</div>
            </div>
            <div class="membro-info">
              <h3>Matheus Ferreira Lopes</h3>
              <span class="membro-idade"><i class="fa-solid fa-cake-candles"></i> 18 anos</span>
              <span class="membro-cidade"><i class="fa-solid fa-location-dot"></i> Diadema, SP</span>
              <p class="membro-descricao">
                Cursando Técnologo em Desenvolvimento de Software Multiplataforma na FATEC DIADEMA.
                Amante de gatinhos, jogos e música.
              </p>
              <div class="membro-links">
                <a href="https://github.com/MatheusLopes167" target="_blank" class="membro-link" aria-label="GitHub de Matheus Lopes">
                  <i class="fa-brands fa-github"></i> GitHub
                </a>
                <a href="https://www.linkedin.com/in/matheusflopes/" target="_blank" class="membro-link membro-link-linkedin" aria-label="LinkedIn de Matheus Lopes">
                  <i class="fa-brands fa-linkedin"></i> LinkedIn
                </a>
              </div>
            </div>
          </article>

          <!-- ─────────────────────────────
               INTEGRANTE 4 — Henrique Orlovas
               ───────────────────────────── -->
          <article class="membro-card reveal reveal-delay-4">
            <div class="membro-avatar">
              <!-- <img src="/soee/src/images/equipe/henrique.jpg" alt="Henrique Orlovas"> -->
              <div class="membro-iniciais">HO</div>
            </div>
            <div class="membro-info">
              <h3>Henrique Batista Orlovas</h3>
              <span class="membro-idade"><i class="fa-solid fa-cake-candles"></i> 17 anos</span>
              <span class="membro-cidade"><i class="fa-solid fa-location-dot"></i> São Paulo, SP</span>
              <p class="membro-descricao">
                Cursando o ensino médio com tecnico em administração e Desesnvolvimento de Sistemas na Etec JK
                interessado em cursar analise de dados e engenharia de software 
              </p>
              <div class="membro-links">
                <a href="https://github.com/HenriqueBOrlovas" target="_blank" class="membro-link" aria-label="GitHub de Henrique Orlovas">
                  <i class="fa-brands fa-github"></i> GitHub
                </a>
                <a href="https://www.linkedin.com/in/henriqueorlovas/" target="_blank" class="membro-link membro-link-linkedin" aria-label="LinkedIn de Henrique Orlovas">
                  <i class="fa-brands fa-linkedin"></i> LinkedIn
                </a>
              </div>
            </div>
          </article>

          <!-- ─────────────────────────────
               INTEGRANTE 5 — Isabelly Barbosa
               ───────────────────────────── -->
          <article class="membro-card reveal reveal-delay-1">
            <div class="membro-avatar">
              <!-- <img src="/soee/src/images/equipe/isabelly.jpg" alt="Isabelly Barbosa"> -->
              <div class="membro-iniciais">IB</div>
            </div>
            <div class="membro-info">
              <h3>Isabelly Barbosa</h3>
              <span class="membro-idade"><i class="fa-solid fa-cake-candles"></i> 17 anos</span>
              <span class="membro-cidade"><i class="fa-solid fa-location-dot"></i> Diadema, SP</span>
              <p class="membro-descricao">
                Cursando desenvolvimento de sistemas na Etec JK e cursando administração, gosto de desenhar, jogar, musica e passear.
              </p>
              <div class="membro-links">
                <a href="#" target="_blank" class="membro-link" aria-label="GitHub de Isabelly Barbosa">
                  <i class="fa-brands fa-github"></i> GitHub
                </a>
                <a href="#" target="_blank" class="membro-link membro-link-linkedin" aria-label="LinkedIn de Isabelly Barbosa">
                  <i class="fa-brands fa-linkedin"></i> LinkedIn
                </a>
              </div>
            </div>
          </article>

        </div>
      </div>
    </section>

    <!-- ─── CTA ─── -->
    <section class="chamada-sistema">
      <div class="chamada-sistema-inner">
        <h2>Feito com dedicação, de aluno para aluno.</h2>
        <p>Conheça o sistema que o grupo desenvolveu para transformar os interclasses da ETEC JK.</p>
        <a href="/soee/index.php" class="botao-primario">
          <i class="fa-solid fa-arrow-right"></i>
          Acessar o Sistema
        </a>
      </div>
    </section>

  </main>

  <?php include __DIR__ . '/../includes/footer.php';?>

  <script src="/soee/src/frontend/scripts/inicio.js"></script>

<?php include __DIR__ . '/../includes/end.php';?>
