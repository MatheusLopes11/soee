<!DOCTYPE html>
<html lang="pt-br" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Quem Somos | SOEE</title>

  <link rel="icon" type="image/png" href="/soee/src/images/logo-soee.png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;800&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous">
  <link rel="stylesheet" href="/soee/src/frontend/css/home.css">
  <link rel="stylesheet" href="/soee/src/frontend/css/quem-somos.css">
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
          <li><a href="/soee/home.php">Início</a></li>
          <li><a href="/soee/modalidades.php">Modalidades</a></li>
          <li><a href="/soee/quem-somos.php" aria-current="page">Quem Somos</a></li>
          <li><a href="/soee/sobre-etec.php">Sobre a ETEC</a></li>
          <li><a href="/soee/redes-sociais.php">Redes Sociais</a></li>
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
              <span class="timeline-tag">O pivô</span>
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
              <span class="membro-idade"><i class="fa-solid fa-cake-candles"></i> ?? anos</span>
              <!-- CIDADE: substitua pelo município/estado real -->
              <span class="membro-cidade"><i class="fa-solid fa-location-dot"></i> Cidade, SP</span>
              <!-- DESCRIÇÃO: escreva uma breve descrição sobre o integrante -->
              <p class="membro-descricao">
                Descrição breve sobre o Carlos Henrique — sua relação com tecnologia,
                o que gosta de fazer, sua contribuição no projeto, etc.
              </p>
              <div class="membro-links">
                <!-- GITHUB: substitua # pela URL real do GitHub -->
                <a href="#" target="_blank" class="membro-link" aria-label="GitHub de Carlos Henrique">
                  <i class="fa-brands fa-github"></i> GitHub
                </a>
                <!-- LINKEDIN: substitua # pela URL real do LinkedIn -->
                <a href="#" target="_blank" class="membro-link membro-link-linkedin" aria-label="LinkedIn de Carlos Henrique">
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
              <!-- <img src="/soee/src/images/equipe/miguel.jpg" alt="Miguel Lopes"> -->
              <div class="membro-iniciais">ML</div>
            </div>
            <div class="membro-info">
              <h3>Miguel Lopes</h3>
              <span class="membro-idade"><i class="fa-solid fa-cake-candles"></i> ?? anos</span>
              <span class="membro-cidade"><i class="fa-solid fa-location-dot"></i> Cidade, SP</span>
              <p class="membro-descricao">
                Descrição breve sobre o Miguel Lopes — sua relação com tecnologia,
                o que gosta de fazer, sua contribuição no projeto, etc.
              </p>
              <div class="membro-links">
                <a href="#" target="_blank" class="membro-link" aria-label="GitHub de Miguel Lopes">
                  <i class="fa-brands fa-github"></i> GitHub
                </a>
                <a href="#" target="_blank" class="membro-link membro-link-linkedin" aria-label="LinkedIn de Miguel Lopes">
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
              <!-- <img src="/soee/src/images/equipe/matheus.jpg" alt="Matheus Lopes"> -->
              <div class="membro-iniciais">ML</div>
            </div>
            <div class="membro-info">
              <h3>Matheus Lopes</h3>
              <span class="membro-idade"><i class="fa-solid fa-cake-candles"></i> ?? anos</span>
              <span class="membro-cidade"><i class="fa-solid fa-location-dot"></i> Cidade, SP</span>
              <p class="membro-descricao">
                Descrição breve sobre o Matheus Lopes — sua relação com tecnologia,
                o que gosta de fazer, sua contribuição no projeto, etc.
              </p>
              <div class="membro-links">
                <a href="#" target="_blank" class="membro-link" aria-label="GitHub de Matheus Lopes">
                  <i class="fa-brands fa-github"></i> GitHub
                </a>
                <a href="#" target="_blank" class="membro-link membro-link-linkedin" aria-label="LinkedIn de Matheus Lopes">
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
              <h3>Henrique Orlovas</h3>
              <span class="membro-idade"><i class="fa-solid fa-cake-candles"></i> ?? anos</span>
              <span class="membro-cidade"><i class="fa-solid fa-location-dot"></i> Cidade, SP</span>
              <p class="membro-descricao">
                Descrição breve sobre o Henrique Orlovas — sua relação com tecnologia,
                o que gosta de fazer, sua contribuição no projeto, etc.
              </p>
              <div class="membro-links">
                <a href="#" target="_blank" class="membro-link" aria-label="GitHub de Henrique Orlovas">
                  <i class="fa-brands fa-github"></i> GitHub
                </a>
                <a href="#" target="_blank" class="membro-link membro-link-linkedin" aria-label="LinkedIn de Henrique Orlovas">
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
              <span class="membro-idade"><i class="fa-solid fa-cake-candles"></i> ?? anos</span>
              <span class="membro-cidade"><i class="fa-solid fa-location-dot"></i> Cidade, SP</span>
              <p class="membro-descricao">
                Descrição breve sobre a Isabelly Barbosa — sua relação com tecnologia,
                o que gosta de fazer, sua contribuição no projeto, etc.
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

  <!-- ─── RODAPÉ ─── -->
  <footer class="rodape">
    <div class="rodape-conteudo">
      <div class="rodape-institucional">
        <h3>Etec Juscelino Kubitschek de Oliveira</h3>
        <p>Plataforma digital para organização dos interclasses e eventos esportivos da escola.</p>
        <a href="https://www.instagram.com/etecjko" class="rodape-rede-social" target="_blank" aria-label="Instagram">
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

  <script src="/soee/src/frontend/js/home.js"></script>

</body>
</html>