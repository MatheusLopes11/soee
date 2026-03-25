<!DOCTYPE html>
<html lang="pt-BR" data-theme="light">
<head>
  <title>SOEE — Dashboard</title>

  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>

  <link rel="stylesheet" href="/soee/src/frontend/css/dash-user.css">
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;800&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

</head>
<body>

<!-- CURSOR -->
<div class="cursor-dot"  id="cursorDot"></div>
<div class="cursor-ring" id="cursorRing"></div>

<!-- LOADER -->
<div id="loader">
  <div class="loader-inner">
    <div class="loader-logo-text">SOEE</div>
    <div class="loader-logo-sub">Plataforma Esportiva</div>
    <div class="loader-bar"><div class="loader-bar-fill"></div></div>
  </div>
</div>

<!-- LAYOUT -->
<div class="dash-wrapper">

  <!-- SIDEBAR -->
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-logo">
      <div class="sidebar-logo-icon"><i class="fa-solid fa-trophy"></i></div>
      <span class="sidebar-logo-text">SOEE</span>
    </div>

    <div class="sidebar-sport-badge">
      <div class="sport-icon" id="sportIcon"><i class="fa-solid fa-futbol"></i></div>
      <div class="sport-info">
        <div class="sport-label">Esporte ativo</div>
        <div class="sport-name" id="sportName">Futebol</div>
      </div>
    </div>

    <nav class="sidebar-nav">
      <div class="nav-section-label">Principal</div>
      <a class="nav-item active" data-page="overview" onclick="navigate('overview',this)">
        <i class="fa-solid fa-house"></i> Visão Geral
      </a>
      <a class="nav-item" data-page="times" onclick="navigate('times',this)">
        <i class="fa-solid fa-shield-halved"></i> Times
        <span class="nav-badge" id="teamsBadge">8</span>
      </a>
      <a class="nav-item" data-page="classificacao" onclick="navigate('classificacao',this)">
        <i class="fa-solid fa-ranking-star"></i> Classificação
      </a>
      <a class="nav-item" data-page="meutime" onclick="navigate('meutime',this)">
        <i class="fa-solid fa-people-group"></i> Meu Time
      </a>

      <div class="nav-section-label" style="margin-top:16px">Conta</div>
      <a class="nav-item" data-page="perfil" onclick="navigate('perfil',this)">
        <i class="fa-solid fa-user"></i> Perfil
      </a>
      <a class="nav-item" onclick="openSportPicker()">
        <i class="fa-solid fa-sliders"></i> Trocar Esporte
      </a>
    </nav>

    <div class="sidebar-user">
      <div class="user-avatar" id="userAvatarSidebar">M</div>
      <div class="user-info">
        <div class="user-name" id="userNameSidebar">Mariana Costa</div>
        <div class="user-role">Atleta</div>
      </div>
      <button class="user-menu-btn" id="toggle-theme" title="Alternar tema">
        <i class="fa-solid fa-moon"></i>
      </button>
    </div>
  </aside>

  <!-- MAIN -->
  <main class="dash-main">

    <!-- TOPBAR -->
    <header class="topbar">
      <div class="topbar-title" id="pageTitle">Visão <span>Geral</span></div>
      <div class="topbar-search">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" placeholder="Buscar times, jogadores…" />
      </div>
      <button class="botao-icone notif-btn" title="Notificações">
        <i class="fa-solid fa-bell"></i>
        <span class="notif-dot"></span>
      </button>
    </header>

    <!-- CONTENT -->
    <div class="dash-content">

      <!-- ──── OVERVIEW ──── -->
      <div class="page-view active" id="page-overview">

        <div class="welcome-banner reveal">
          <div class="welcome-content">
            <div class="welcome-greeting">Bem-vinda de volta 👋</div>
            <div class="welcome-name">Olá, <span id="heroName">Mariana</span>!</div>
            <div class="welcome-sub" id="heroSub">Acompanhe o desempenho do seu time e a classificação do campeonato.</div>
            <div class="welcome-stats">
              <div class="w-stat"><strong id="heroRank">3°</strong><span>Posição</span></div>
              <div class="w-stat"><strong id="heroGames">12</strong><span>Jogos</span></div>
              <div class="w-stat"><strong id="heroPoints">28</strong><span>Pontos</span></div>
            </div>
          </div>
        </div>

        <div class="stats-grid">
          <div class="stat-card reveal reveal-delay-1">
            <div class="stat-card-change change-up">▲ 2</div>
            <div class="stat-card-icon orange"><i class="fa-solid fa-fire"></i></div>
            <div class="stat-card-value" id="sc1">7</div>
            <div class="stat-card-label">Vitórias</div>
          </div>
          <div class="stat-card reveal reveal-delay-2">
            <div class="stat-card-change change-down">▼ 1</div>
            <div class="stat-card-icon blue"><i class="fa-solid fa-handshake"></i></div>
            <div class="stat-card-value" id="sc2">3</div>
            <div class="stat-card-label">Empates</div>
          </div>
          <div class="stat-card reveal reveal-delay-3">
            <div class="stat-card-change change-down">▼ 1</div>
            <div class="stat-card-icon green"><i class="fa-solid fa-bullseye"></i></div>
            <div class="stat-card-value" id="sc3">24</div>
            <div class="stat-card-label" id="sc3Label">Gols Marcados</div>
          </div>
          <div class="stat-card reveal reveal-delay-4">
            <div class="stat-card-change change-up">▲ 5%</div>
            <div class="stat-card-icon purple"><i class="fa-solid fa-chart-line"></i></div>
            <div class="stat-card-value">82%</div>
            <div class="stat-card-label">Aproveitamento</div>
          </div>
        </div>

        <div class="section-header reveal">
          <div>
            <span class="section-title">Times do Campeonato</span>
            <span class="section-tag" id="sportTagOverview">Futebol</span>
          </div>
          <a class="ver-mais" onclick="navigate('times',null)">Ver todos <i class="fa-solid fa-arrow-right"></i></a>
        </div>

        <div class="teams-grid" id="teamsGridOverview"></div>
      </div>

      <!-- ──── TIMES ──── -->
      <div class="page-view" id="page-times">
        <div class="section-header reveal">
          <div>
            <span class="section-title">Todos os Times</span>
            <span class="section-tag" id="sportTagTimes">Futebol</span>
          </div>
        </div>
        <div class="teams-grid" id="teamsGridFull"></div>
      </div>

      <!-- ──── CLASSIFICAÇÃO ──── -->
      <div class="page-view" id="page-classificacao">
        <div class="section-header reveal">
          <div>
            <span class="section-title">Classificação Geral</span>
            <span class="section-tag" id="sportTagClass">Futebol</span>
          </div>
        </div>
        <div class="ranking-card reveal">
          <table class="ranking-table">
            <thead>
              <tr>
                <th>#</th>
                <th>Time</th>
                <th>PJ</th>
                <th>V</th>
                <th>E</th>
                <th>D</th>
                <th>PTS</th>
                <th>Forma</th>
              </tr>
            </thead>
            <tbody id="rankingBody"></tbody>
          </table>
        </div>
      </div>

      <!-- ──── MEU TIME ──── -->
      <div class="page-view" id="page-meutime">
        <div class="my-team-hero reveal">
          <div class="my-team-hero-content">
            <div class="my-team-big-logo" id="myTeamBigLogo">⚽</div>
            <div class="my-team-info">
              <h2 id="myTeamName">Estrelas FC</h2>
              <p id="myTeamSport">Futebol • Campeonato Regional</p>
              <div class="my-team-stats">
                <div class="mt-stat"><strong id="mtPos">3°</strong><span>Posição</span></div>
                <div class="mt-stat"><strong id="mtPts">28</strong><span>Pontos</span></div>
                <div class="mt-stat"><strong id="mtPlayers">15</strong><span>Jogadores</span></div>
              </div>
            </div>
          </div>
        </div>

        <div class="section-header reveal">
          <span class="section-title">Elenco</span>
        </div>
        <div class="players-grid reveal" id="playersGrid"></div>
      </div>

    </div><!-- dash-content -->
  </main>
</div>

<!-- SPORT PICKER MODAL -->
<div id="sportModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:5000;display:none;align-items:center;justify-content:center;">
  <div style="background:var(--fundo-bloco);border-radius:var(--raio-grande);padding:40px;max-width:420px;width:90%;box-shadow:0 40px 80px rgba(0,0,0,0.25);">
    <h2 style="font-family:'Playfair Display',serif;font-size:1.5rem;color:var(--azul-principal);margin-bottom:8px;">Escolha seu esporte</h2>
    <p style="color:var(--texto-secundario);font-size:0.88rem;margin-bottom:24px;">O dashboard se adaptará ao esporte selecionado.</p>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;" id="sportPickerGrid"></div>
    <button onclick="closeSportPicker()" style="margin-top:20px;width:100%;background:none;border:1px solid var(--borda-sutil);border-radius:var(--raio-medio);padding:10px;cursor:pointer;color:var(--texto-secundario);font-family:'DM Sans',sans-serif;">Cancelar</button>
  </div>
</div>

    <script src="/soee/src/frontend/js/dash-user.js"></script>
  </body>
</html>