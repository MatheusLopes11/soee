<!DOCTYPE html>
<html lang="pt-br" data-theme="light">
<head>

<!-- (Título Guia) -->
  <title>Portal — SOEE</title>

<!-- (Meta Dados) -->
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

<!-- (Links) -->
  <link rel="stylesheet" href="/soee/src/frontend/css/inicio.css">
  <link rel="stylesheet" href="/soee/src/frontend/css/portal.css">
  <link rel="icon" type="image/png" href="/soee/src/images/logo-soee.png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;800&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous">

<!-- Tema persistido — deve ser o PRIMEIRO script -->
  <script>
    const _t = localStorage.getItem('theme');
    if (_t) document.documentElement.setAttribute('data-theme', _t);
  </script>

</head>
<body>

  <!-- Cursor personalizado (mesmo do inicio.css) -->
  <div class="cursor-dot" id="cursorDot"></div>
  <div class="cursor-ring" id="cursorRing"></div>

  <!-- Loader (mesmo do inicio.css) -->
  <div id="loader">
    <div class="loader-inner">
      <div class="loader-logo-text">SOEE</div>
      <div class="loader-logo-sub">Carregando portal</div>
      <div class="loader-bar">
        <div class="loader-bar-fill"></div>
      </div>
    </div>
  </div>

  <!-- ─── HEADER (mesmo do inicio.php) ─── -->
  <header class="cabecalho">
    <div class="cabecalho-container">

      <div class="cabecalho-logos">
        <img src="/soee/src/images/logo-jk.png"  alt="ETEC Juscelino Kubitschek de Oliveira">
        <img src="/soee/src/images/logo-cps.png" alt="Centro Paula Souza">
      </div>

      <nav class="menu-principal" aria-label="Menu principal">
        <ul class="menu-lista">
          <li><a href="/soee/src/backend/php/pages/inicio.php">Home</a></li>
          <li><a href="/soee/src/backend/php/pages/modalidades.php">Modalidades</a></li>
          <li><a href="/soee/src/backend/php/pages/quem-somos.php">Quem Somos</a></li>
          <li><a href="/soee/src/backend/php/pages/contato-redes.php">Redes Sociais</a></li>
        </ul>
      </nav>

      <div class="cabecalho-acoes">
        <button id="toggle-theme" class="botao-icone" aria-label="Alternar tema">
          <i class="fa-solid fa-moon" id="icone-tema"></i>
        </button>

        <a href="/soee/index.php" class="botao-login">
          <i class="fa-solid fa-user"></i>
          <span>Entrar</span>
        </a>

        <img
          src="/soee/src/images/logo-soee.png"
          alt="SOEE"
          class="logo-sistema"
        >
      </div>

    </div>
  </header>

  <!-- ─── HERO ─── -->
  <section class="hero-portal">
    <div class="bg"></div>
    <div class="grid"></div>
    <div class="particles">
      <span></span><span></span><span></span><span></span><span></span>
    </div>

    <div class="hero-portal-container">

      <!-- Texto -->
      <div class="hero-portal-texto">
        <div class="badge">
          <i class="fa-solid fa-trophy"></i>
          Portal de Eventos Esportivos
        </div>

        <h1>Bem-vindo ao<br><em>Portal SOEE</em></h1>

        <p>
          Inscreva-se em eventos esportivos, acompanhe resultados
          em tempo real e fique por dentro de todas as novidades
          dos interclasses da ETEC JK.
        </p>

        <div class="intro-acoes">
          <a href="#eventos" class="botao-primario">
            <i class="fa-solid fa-medal"></i>
            Inscreva-se Agora
          </a>
          <a href="#noticias" class="botao-secundario">
            <i class="fa-solid fa-newspaper"></i>
            Ver Notícias
          </a>
        </div>

        <div class="hero-portal-stats">
          <div class="hp-stat">
            <strong id="stat-modalidades">0+</strong>
            <span>Modalidades</span>
          </div>
          <div class="hp-sep"></div>
          <div class="hp-stat">
            <strong id="stat-times">0</strong>
            <span>Times</span>
          </div>
          <div class="hp-sep"></div>
          <div class="hp-stat">
            <strong id="stat-digital">0%</strong>
            <span>Digital</span>
          </div>
        </div>
      </div>

      <!-- Card inscrição rápida -->
      <div class="hero-portal-card">
        <h3><i class="fa-solid fa-person-running"></i> Inscreva-se em um Evento</h3>
        <p>Escolha a modalidade e garanta sua vaga</p>

        <div class="card-eventos-mini">

          <div class="ev-mini">
            <div class="ev-mini-ico ev-azul"><i class="fa-solid fa-futbol"></i></div>
            <div class="ev-mini-info">
              <strong>Torneio de Futebol</strong>
              <span>25 Mai 2026 · Campo Principal</span>
            </div>
            <span class="ev-badge badge-aberto">Aberto</span>
          </div>

          <div class="ev-mini">
            <div class="ev-mini-ico ev-laranja"><i class="fa-solid fa-volleyball"></i></div>
            <div class="ev-mini-info">
              <strong>Campeonato de Vôlei</strong>
              <span>01 Jun 2026 · Ginásio</span>
            </div>
            <span class="ev-badge badge-aberto">Aberto</span>
          </div>

          <div class="ev-mini">
            <div class="ev-mini-ico ev-azul"><i class="fa-solid fa-basketball"></i></div>
            <div class="ev-mini-info">
              <strong>Copa de Basquete</strong>
              <span>10 Jun 2026 · Quadra</span>
            </div>
            <span class="ev-badge badge-breve">Em breve</span>
          </div>

        </div>

        <a href="#eventos" class="botao-primario" style="width:100%;justify-content:center">
          <i class="fa-solid fa-arrow-right"></i>
          Ver Todos os Eventos
        </a>
      </div>

    </div>
  </section>

  <!-- ─── CONTEÚDO PRINCIPAL ─── -->
  <div class="portal-grid">

    <!-- COLUNA PRINCIPAL -->
    <main>

      <!-- PRÓXIMOS EVENTOS -->
      <section id="eventos" aria-labelledby="titulo-eventos">

        <header class="secao-cabecalho reveal">
          <div class="secao-tag">Agenda</div>
          <h2 id="titulo-eventos">Próximos Eventos</h2>
          <p>Inscreva-se e participe dos interclasses da ETEC JK</p>
        </header>

        <div class="eventos-grid">

          <article class="evento-card reveal reveal-delay-1">
            <div class="evento-img evento-futebol">
              <span class="evento-emoji">⚽</span>
              <span class="evento-data-badge">25 Mai</span>
            </div>
            <div class="evento-corpo">
              <strong>Torneio de Futebol</strong>
              <p>Campeonato entre turmas no campo principal da escola.</p>
              <button class="botao-primario btn-ev">
                <i class="fa-solid fa-user-plus"></i> Inscreva-se
              </button>
            </div>
          </article>

          <article class="evento-card reveal reveal-delay-2">
            <div class="evento-img evento-volei">
              <span class="evento-emoji">🏐</span>
              <span class="evento-data-badge">01 Jun</span>
            </div>
            <div class="evento-corpo">
              <strong>Campeonato de Vôlei</strong>
              <p>Disputa no ginásio com árbitros oficiais.</p>
              <button class="botao-primario btn-ev">
                <i class="fa-solid fa-user-plus"></i> Inscreva-se
              </button>
            </div>
          </article>

          <article class="evento-card reveal reveal-delay-3">
            <div class="evento-img evento-basquete">
              <span class="evento-emoji">🏀</span>
              <span class="evento-data-badge">10 Jun</span>
            </div>
            <div class="evento-corpo">
              <strong>Copa de Basquete</strong>
              <p>3×3 e 5×5 na quadra coberta da ETEC.</p>
              <button class="botao-primario btn-ev">
                <i class="fa-solid fa-user-plus"></i> Inscreva-se
              </button>
            </div>
          </article>

          <article class="evento-card reveal reveal-delay-4">
            <div class="evento-img evento-natacao">
              <span class="evento-emoji">🏊</span>
              <span class="evento-data-badge">18 Jun</span>
            </div>
            <div class="evento-corpo">
              <strong>Torneio de Natação</strong>
              <p>Provas de livre e estilo no complexo aquático.</p>
              <button class="botao-primario btn-ev">
                <i class="fa-solid fa-user-plus"></i> Inscreva-se
              </button>
            </div>
          </article>

        </div>

        <button class="btn-ver-todos reveal">
          <i class="fa-solid fa-calendar"></i>
          Ver Todos os Eventos
        </button>

      </section>

      <!-- PLACAR -->
      <section aria-labelledby="titulo-placar" style="margin-top:96px">

        <header class="secao-cabecalho reveal">
          <div class="secao-tag">Resultados</div>
          <h2 id="titulo-placar">Placar de Eventos Recentes</h2>
          <p>Acompanhe os resultados das últimas partidas</p>
        </header>

        <div class="placar-grid">

          <div class="placar-card reveal reveal-delay-1">
            <div class="placar-esporte">Futebol · Fase de Grupos</div>
            <div class="placar-times">
              <div class="placar-time"><span>3DS</span><strong>1</strong></div>
              <div class="placar-sep">×</div>
              <div class="placar-time"><span>1INFO</span><strong>1</strong></div>
            </div>
            <div class="placar-status status-fim">Encerrado</div>
            <div class="placar-emoji">⚽</div>
          </div>

          <div class="placar-card reveal reveal-delay-2">
            <div class="placar-esporte">Futebol · Fase de Grupos</div>
            <div class="placar-times">
              <div class="placar-time"><span>2DS</span><strong>3</strong></div>
              <div class="placar-sep">×</div>
              <div class="placar-time"><span>2INFO</span><strong>2</strong></div>
            </div>
            <div class="placar-status status-fim">Encerrado</div>
            <div class="placar-emoji">⚽</div>
          </div>

          <div class="placar-card reveal reveal-delay-3">
            <div class="placar-esporte">Vôlei · Semifinal</div>
            <div class="placar-times">
              <div class="placar-time"><span>1DS</span><strong>2</strong></div>
              <div class="placar-sep">×</div>
              <div class="placar-time"><span>3INFO</span><strong>1</strong></div>
            </div>
            <div class="placar-status status-live">● Ao Vivo</div>
            <div class="placar-emoji">🏐</div>
          </div>

          <div class="placar-card reveal reveal-delay-4">
            <div class="placar-esporte">Basquete · Quartas</div>
            <div class="placar-times">
              <div class="placar-time"><span>2DS</span><strong>2</strong></div>
              <div class="placar-sep">×</div>
              <div class="placar-time"><span>1INFO</span><strong>1</strong></div>
            </div>
            <div class="placar-status status-fim">Encerrado</div>
            <div class="placar-emoji">🏀</div>
          </div>

        </div>

      </section>



    </main>

    <!-- SIDEBAR -->
    <aside class="portal-sidebar">

      <!-- NOTÍCIAS -->
      <div id="noticias" class="sidebar-card reveal">
        <div class="sidebar-card-header">
          <h3>Últimas Notícias</h3>
          <span class="secao-tag" style="margin:0">Novo</span>
        </div>

        <div class="noticias-lista">

          <a href="#" class="noticia-item">
            <div class="noticia-thumb nt-verde">⚽</div>
            <div class="noticia-info">
              <strong>Chave do Torneio de Futebol divulgada</strong>
              <p>Confira os confrontos da fase de grupos.</p>
              <span class="noticia-meta"><i class="fa-regular fa-clock"></i> há 2 horas</span>
            </div>
          </a>

          <a href="#" class="noticia-item">
            <div class="noticia-thumb nt-azul">🏐</div>
            <div class="noticia-info">
              <strong>Vôlei: inscrições encerram amanhã</strong>
              <p>Última chance para garantir a vaga da sua turma.</p>
              <span class="noticia-meta"><i class="fa-regular fa-clock"></i> há 5 horas</span>
            </div>
          </a>

          <a href="#" class="noticia-item">
            <div class="noticia-thumb nt-laranja">🏀</div>
            <div class="noticia-info">
              <strong>2DS vence 3INFO no basquete</strong>
              <p>Partida encerrada com diferença de 1 ponto.</p>
              <span class="noticia-meta"><i class="fa-regular fa-clock"></i> há 1 dia</span>
            </div>
          </a>

        </div>

        <button class="btn-ver-todos" style="margin-top:4px">
          <i class="fa-solid fa-newspaper"></i>
          Ver Todas as Notícias
        </button>

      </div>

    </aside>

  </div><!-- /portal-grid -->

  <!-- ─── RODAPÉ ─── -->
  <footer class="rodape">
    <div class="rodape-conteudo">

      <div class="rodape-institucional">
        <h3>Etec Juscelino Kubitschek de Oliveira</h3>
        <p>Plataforma digital para organização dos interclasses e eventos esportivos da escola.</p>
        <div class="rodape-redes">
          <a href="#" class="rodape-rede-social"><i class="fa-brands fa-facebook-f"></i></a>
          <a href="#" class="rodape-rede-social"><i class="fa-brands fa-instagram"></i></a>
          <a href="#" class="rodape-rede-social"><i class="fa-brands fa-youtube"></i></a>
        </div>
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

  <!-- JS base (cursor, loader, tema, reveal) -->
  <script src="/soee/src/frontend/js/inicio.js"></script>
  <!-- JS específico do portal -->
  <script src="/soee/src/frontend/js/portal.js"></script>

</body>
</html>
