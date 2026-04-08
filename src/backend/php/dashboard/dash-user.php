<?php
session_start();
require_once __DIR__ . '/../include/conexao.php';
require_once __DIR__ . '/../auth/auth-home.php';

AuthHome::exigirTipo(['aluno']);

$userId   = AuthHome::getId();
$userNome = AuthHome::getNome();

/* ══════════════════════════════════════════
   1. DADOS DO USUÁRIO LOGADO + TURMA
══════════════════════════════════════════ */
$stmtUser = $conn->prepare("
    SELECT u.id_usuario, u.nome_usuario, u.genero_usuario,
           t.id_turma, t.nome_turma,
           c.nome_curso, c.sigla_curso,
           t.periodo_turma
    FROM usuario u
    LEFT JOIN turma t ON t.id_turma = u.turma_id_turma
    LEFT JOIN curso c ON c.id_curso = t.curso_id_curso
    WHERE u.id_usuario = :id
    LIMIT 1
");
$stmtUser->execute([':id' => $userId]);
$userData = $stmtUser->fetch(PDO::FETCH_ASSOC);

$nomeTurma  = $userData['nome_turma']  ?? 'Sem turma';
$siglaCurso = $userData['sigla_curso'] ?? '';
$turmaId    = $userData['id_turma']    ?? null;

/* ══════════════════════════════════════════
   2. MODALIDADES ATIVAS (para o seletor de esporte)
══════════════════════════════════════════ */
$stmtMod = $conn->query("
    SELECT m.id_modalidade, m.nome_modalidade, m.tipo_modalidade,
           m.tipo_participacao, em.id_edicao_modalidade,
           em.status_edicao_modalidade
    FROM modalidade m
    INNER JOIN edicao_modalidade em ON em.modalidade_id_modalidade = m.id_modalidade
    INNER JOIN edicao e ON e.id_edicao = em.edicao_id_edicao
    WHERE m.ativo_modalidade = 1
      AND e.status_edicao IN ('inscricoes','em_andamento')
    ORDER BY m.nome_modalidade
");
$modalidades = $stmtMod->fetchAll(PDO::FETCH_ASSOC);

/* ══════════════════════════════════════════
   3. INSCRIÇÃO DO USUÁRIO (em qual modalidade/time está)
══════════════════════════════════════════ */
$stmtInsc = $conn->prepare("
    SELECT i.id_inscricao, i.numero_camisa_inscricao, i.posicao_inscricao,
           i.capitao_inscricao, i.edicao_modalidade_id,
           m.nome_modalidade, m.id_modalidade
    FROM inscricao i
    INNER JOIN edicao_modalidade em ON em.id_edicao_modalidade = i.edicao_modalidade_id
    INNER JOIN modalidade m ON m.id_modalidade = em.modalidade_id_modalidade
    WHERE i.usuario_id_usuario = :id
      AND i.status_inscricao = 'ativa'
    ORDER BY i.data_inscricao DESC
");
$stmtInsc->execute([':id' => $userId]);
$inscricoes = $stmtInsc->fetchAll(PDO::FETCH_ASSOC);

/* ══════════════════════════════════════════
   4. CLASSIFICAÇÃO DA TURMA DO USUÁRIO
     (para cada modalidade em que está inscrito)
══════════════════════════════════════════ */
$classificacaoPorModalidade = [];
if ($turmaId && !empty($inscricoes)) {
    foreach ($inscricoes as $insc) {
        $emId = $insc['edicao_modalidade_id'];
        $stmt = $conn->prepare("
            SELECT cl.*, t.nome_turma,
                   ROW_NUMBER() OVER (ORDER BY cl.pontos DESC, cl.saldo DESC, cl.vitorias DESC) AS posicao
            FROM classificacao cl
            INNER JOIN turma t ON t.id_turma = cl.turma_id_turma
            WHERE cl.edicao_modalidade_id = :emid
              AND cl.turma_id_turma = :turma
            LIMIT 1
        ");
        $stmt->execute([':emid' => $emId, ':turma' => $turmaId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) $classificacaoPorModalidade[$emId] = $row;
    }
}

/* ══════════════════════════════════════════
   5. PRÓXIMA PARTIDA DA TURMA
══════════════════════════════════════════ */
$proximaPartida = null;
if ($turmaId) {
    $stmtProx = $conn->prepare("
        SELECT p.*, m.nome_modalidade,
               ta.nome_turma AS nome_time_a,
               tb.nome_turma AS nome_time_b
        FROM partida p
        INNER JOIN edicao_modalidade em ON em.id_edicao_modalidade = p.edicao_modalidade_id
        INNER JOIN modalidade m ON m.id_modalidade = em.modalidade_id_modalidade
        INNER JOIN turma ta ON ta.id_turma = p.turma_id_time_a
        INNER JOIN turma tb ON tb.id_turma = p.turma_id_time_b
        WHERE (p.turma_id_time_a = :turma OR p.turma_id_time_b = :turma2)
          AND p.status_partida = 'agendada'
          AND p.data_partida >= CURDATE()
        ORDER BY p.data_partida ASC, p.hora_partida ASC
        LIMIT 1
    ");
    $stmtProx->execute([':turma' => $turmaId, ':turma2' => $turmaId]);
    $proximaPartida = $stmtProx->fetch(PDO::FETCH_ASSOC);
}

/* ══════════════════════════════════════════
   6. DADOS PARA O JS (injetados inline)
══════════════════════════════════════════ */
$jsData = [
    'userId'      => $userId,
    'userNome'    => $userNome,
    'userGenero'  => $userData['genero_usuario'] ?? 'n',
    'turmaId'     => $turmaId,
    'nomeTurma'   => $nomeTurma,
    'siglaCurso'  => $siglaCurso,
    'modalidades' => $modalidades,
    'inscricoes'  => $inscricoes,
    'classificacao' => $classificacaoPorModalidade,
    'proximaPartida' => $proximaPartida,
];
?>

<?php include __DIR__ . '/../include/doctype.php';?>
<head>
    <title>SOEE — Dashboard</title>
    <link rel="stylesheet" href="/soee/src/frontend/css/dash-user.css">
    <?php include __DIR__ . '/../include/head-data.php';?>
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

        <div class="sidebar-sport-badge" id="sportBadge">
            <div class="sport-icon" id="sportIcon"><i class="fa-solid fa-medal"></i></div>
            <div class="sport-info">
                <div class="sport-label">Esporte ativo</div>
                <div class="sport-name" id="sportName">—</div>
            </div>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-section-label">Principal</div>
            <a class="nav-item active" data-page="overview" onclick="navigate('overview',this)">
                <i class="fa-solid fa-house"></i> Visão Geral
            </a>
            <a class="nav-item" data-page="times" onclick="navigate('times',this)">
                <i class="fa-solid fa-shield-halved"></i> Times
            </a>
            <a class="nav-item" data-page="classificacao" onclick="navigate('classificacao',this)">
                <i class="fa-solid fa-ranking-star"></i> Classificação
            </a>
            <a class="nav-item" data-page="partidas" onclick="navigate('partidas',this)">
                <i class="fa-solid fa-calendar-days"></i> Partidas
            </a>
            <a class="nav-item" data-page="meutime" onclick="navigate('meutime',this)">
                <i class="fa-solid fa-people-group"></i> Meu Time
            </a>

            <div class="nav-section-label" style="margin-top:16px">Conta</div>
            <a class="nav-item" data-page="perfil" onclick="navigate('perfil',this)">
                <i class="fa-solid fa-user"></i> Perfil
            </a>
            <?php if (count($modalidades) > 1): ?>
            <a class="nav-item" onclick="openSportPicker()">
                <i class="fa-solid fa-sliders"></i> Trocar Esporte
            </a>
            <?php endif; ?>
            <a class="nav-item" href="/soee/src/backend/php/auth/logout.php" style="color:var(--laranja-destaque)">
                <i class="fa-solid fa-right-from-bracket"></i> Sair
            </a>
        </nav>

        <div class="sidebar-user">
            <div class="user-avatar" id="userAvatarSidebar">
                <?= mb_strtoupper(mb_substr($userNome, 0, 1)) ?>
            </div>
            <div class="user-info">
                <div class="user-name" id="userNameSidebar">
                    <?= htmlspecialchars(explode(' ', $userNome)[0]) ?>
                </div>
                <div class="user-role"><?= htmlspecialchars($nomeTurma) ?></div>
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
        </header>

        <!-- CONTENT -->
        <div class="dash-content">

            <!-- ──── OVERVIEW ──── -->
            <div class="page-view active" id="page-overview">

                <div class="welcome-banner reveal">
                    <div class="welcome-content">
                        <div class="welcome-greeting">
                            <?= $userData['genero_usuario'] === 'f' ? 'Bem-vinda de volta' : 'Bem-vindo de volta' ?>
                        </div>
                        <div class="welcome-name">
                            Olá, <span id="heroName"><?= htmlspecialchars(explode(' ', $userNome)[0]) ?></span>!
                        </div>
                        <div class="welcome-sub" id="heroSub">
                            <?= htmlspecialchars($nomeTurma) ?>
                            <?= $siglaCurso ? '— ' . htmlspecialchars($siglaCurso) : '' ?>
                        </div>
                        <div class="welcome-stats" id="heroStats">
                            <div class="w-stat"><strong id="heroRank">—</strong><span>Posição</span></div>
                            <div class="w-stat"><strong id="heroGames">—</strong><span>Jogos</span></div>
                            <div class="w-stat"><strong id="heroPoints">—</strong><span>Pontos</span></div>
                        </div>
                    </div>
                </div>

                <!-- Próxima partida -->
                <?php if ($proximaPartida): ?>
                <div class="proxima-partida-card reveal">
                    <div class="pp-label"><i class="fa-solid fa-clock"></i> Próxima Partida</div>
                    <div class="pp-times">
                        <span class="pp-time <?= $proximaPartida['turma_id_time_a'] == $turmaId ? 'pp-mine' : '' ?>">
                            <?= htmlspecialchars($proximaPartida['nome_time_a']) ?>
                        </span>
                        <span class="pp-vs">VS</span>
                        <span class="pp-time <?= $proximaPartida['turma_id_time_b'] == $turmaId ? 'pp-mine' : '' ?>">
                            <?= htmlspecialchars($proximaPartida['nome_time_b']) ?>
                        </span>
                    </div>
                    <div class="pp-info">
                        <span><i class="fa-solid fa-calendar"></i>
                            <?= date('d/m/Y', strtotime($proximaPartida['data_partida'])) ?>
                        </span>
                        <span><i class="fa-solid fa-clock"></i>
                            <?= date('H:i', strtotime($proximaPartida['hora_partida'])) ?>
                        </span>
                        <?php if ($proximaPartida['local_partida']): ?>
                        <span><i class="fa-solid fa-location-dot"></i>
                            <?= htmlspecialchars($proximaPartida['local_partida']) ?>
                        </span>
                        <?php endif; ?>
                        <span><i class="fa-solid fa-futbol"></i>
                            <?= htmlspecialchars($proximaPartida['nome_modalidade']) ?>
                        </span>
                    </div>
                </div>
                <?php endif; ?>

                <div class="stats-grid" id="statsGrid">
                    <!-- preenchido pelo JS com dados da classificação -->
                    <div class="stat-card reveal reveal-delay-1">
                        <div class="stat-card-change change-up" id="sc-vic-change"></div>
                        <div class="stat-card-icon orange"><i class="fa-solid fa-fire"></i></div>
                        <div class="stat-card-value" id="sc1">—</div>
                        <div class="stat-card-label">Vitórias</div>
                    </div>
                    <div class="stat-card reveal reveal-delay-2">
                        <div class="stat-card-icon blue"><i class="fa-solid fa-handshake"></i></div>
                        <div class="stat-card-value" id="sc2">—</div>
                        <div class="stat-card-label">Empates</div>
                    </div>
                    <div class="stat-card reveal reveal-delay-3">
                        <div class="stat-card-icon green"><i class="fa-solid fa-shield"></i></div>
                        <div class="stat-card-value" id="sc3">—</div>
                        <div class="stat-card-label">Jogos</div>
                    </div>
                    <div class="stat-card reveal reveal-delay-4">
                        <div class="stat-card-icon purple"><i class="fa-solid fa-chart-line"></i></div>
                        <div class="stat-card-value" id="sc4">—</div>
                        <div class="stat-card-label">Pontos</div>
                    </div>
                </div>

                <div class="section-header reveal">
                    <div>
                        <span class="section-title">Times do Campeonato</span>
                        <span class="section-tag" id="sportTagOverview">—</span>
                    </div>
                    <a class="ver-mais" onclick="navigate('times',null)">
                        Ver todos <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>

                <div class="teams-grid" id="teamsGridOverview"></div>
            </div>

            <!-- ──── TIMES ──── -->
            <div class="page-view" id="page-times">
                <div class="section-header reveal">
                    <div>
                        <span class="section-title">Todos os Times</span>
                        <span class="section-tag" id="sportTagTimes">—</span>
                    </div>
                </div>
                <div class="teams-grid" id="teamsGridFull"></div>
            </div>

            <!-- ──── CLASSIFICAÇÃO ──── -->
            <div class="page-view" id="page-classificacao">
                <div class="section-header reveal">
                    <div>
                        <span class="section-title">Classificação Geral</span>
                        <span class="section-tag" id="sportTagClass">—</span>
                    </div>
                </div>
                <div class="ranking-card reveal">
                    <table class="ranking-table">
                        <thead>
                            <tr>
                                <th>#</th><th>Turma</th>
                                <th>PJ</th><th>V</th><th>E</th><th>D</th>
                                <th>GP</th><th>GC</th><th>SG</th><th>PTS</th>
                            </tr>
                        </thead>
                        <tbody id="rankingBody">
                            <tr><td colspan="10" style="text-align:center;padding:24px;color:var(--texto-secundario)">Carregando…</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ──── PARTIDAS ──── -->
            <div class="page-view" id="page-partidas">
                <div class="section-header reveal">
                    <div>
                        <span class="section-title">Partidas</span>
                        <span class="section-tag" id="sportTagPartidas">—</span>
                    </div>
                </div>
                <div id="partidasLista" class="partidas-lista">
                    <p style="color:var(--texto-secundario);text-align:center;padding:32px">Carregando partidas…</p>
                </div>
            </div>

            <!-- ──── MEU TIME ──── -->
            <div class="page-view" id="page-meutime">
                <div class="my-team-hero reveal">
                    <div class="my-team-hero-content">
                        <div class="my-team-big-logo" id="myTeamBigLogo">🏅</div>
                        <div class="my-team-info">
                            <h2 id="myTeamName"><?= htmlspecialchars($nomeTurma) ?></h2>
                            <p id="myTeamSport">—</p>
                            <div class="my-team-stats">
                                <div class="mt-stat"><strong id="mtPos">—</strong><span>Posição</span></div>
                                <div class="mt-stat"><strong id="mtPts">—</strong><span>Pontos</span></div>
                                <div class="mt-stat"><strong id="mtPlayers">—</strong><span>Inscritos</span></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="section-header reveal">
                    <span class="section-title">Elenco</span>
                    <span class="section-tag" id="sportTagMeuTime">—</span>
                </div>
                <div class="players-grid reveal" id="playersGrid">
                    <p style="color:var(--texto-secundario)">Carregando elenco…</p>
                </div>
            </div>

            <!-- ──── PERFIL ──── -->
            <div class="page-view" id="page-perfil">
                <div class="perfil-card reveal">
                    <div class="perfil-avatar">
                        <?= mb_strtoupper(mb_substr($userNome, 0, 1)) ?>
                    </div>
                    <h2 class="perfil-nome"><?= htmlspecialchars($userNome) ?></h2>
                    <p class="perfil-turma"><?= htmlspecialchars($nomeTurma . ($siglaCurso ? ' — ' . $siglaCurso : '')) ?></p>
                    <div class="perfil-info-grid">
                        <div class="perfil-info-item">
                            <span class="perfil-info-label"><i class="fa-solid fa-school"></i> Turma</span>
                            <span class="perfil-info-value"><?= htmlspecialchars($nomeTurma) ?></span>
                        </div>
                        <div class="perfil-info-item">
                            <span class="perfil-info-label"><i class="fa-solid fa-book-open"></i> Curso</span>
                            <span class="perfil-info-value"><?= htmlspecialchars($userData['nome_curso'] ?? '—') ?></span>
                        </div>
                        <div class="perfil-info-item">
                            <span class="perfil-info-label"><i class="fa-solid fa-futbol"></i> Modalidades</span>
                            <span class="perfil-info-value"><?= count($inscricoes) ?: '0' ?> inscrição(ões)</span>
                        </div>
                        <div class="perfil-info-item">
                            <span class="perfil-info-label"><i class="fa-solid fa-id-badge"></i> Tipo</span>
                            <span class="perfil-info-value">Aluno</span>
                        </div>
                    </div>
                    <?php if (!empty($inscricoes)): ?>
                    <div class="perfil-inscricoes">
                        <div class="section-title" style="font-size:1rem;margin-bottom:12px">Minhas inscrições</div>
                        <?php foreach ($inscricoes as $insc): ?>
                        <div class="perfil-inscricao-item">
                            <i class="fa-solid fa-medal"></i>
                            <span><?= htmlspecialchars($insc['nome_modalidade']) ?></span>
                            <?php if ($insc['posicao_inscricao']): ?>
                            <span class="perfil-posicao"><?= htmlspecialchars($insc['posicao_inscricao']) ?></span>
                            <?php endif; ?>
                            <?php if ($insc['numero_camisa_inscricao']): ?>
                            <span class="perfil-camisa">#<?= $insc['numero_camisa_inscricao'] ?></span>
                            <?php endif; ?>
                            <?php if ($insc['capitao_inscricao']): ?>
                            <span class="captain-badge"><i class="fa-solid fa-star"></i> Capitão</span>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

        </div><!-- dash-content -->
    </main>
</div>

<!-- SPORT PICKER MODAL -->
<div id="sportModal" class="sport-modal-overlay" style="display:none">
    <div class="sport-modal-box">
        <h2>Escolha o esporte</h2>
        <p>O dashboard se adaptará à modalidade selecionada.</p>
        <div id="sportPickerGrid" class="sport-picker-grid"></div>
        <button onclick="closeSportPicker()" class="sport-modal-cancel">Cancelar</button>
    </div>
</div>

<!-- DADOS DO PHP PARA O JS -->
<script>
const PHP_DATA = <?= json_encode($jsData, JSON_UNESCAPED_UNICODE) ?>;
</script>
<script src="/soee/src/frontend/js/dash-user.js"></script>

<?php include __DIR__ . '/../include/end.php';?>