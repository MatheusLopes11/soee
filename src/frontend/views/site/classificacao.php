<?php
session_start();
require_once __DIR__ . '/../../../backend/includes/conexao.php';
require_once __DIR__ . '/../../../backend/controllers/home.php';

// ══════════════════════════════════════════════════════════
//  MODO MOCK — troque para false quando o banco tiver dados
// ══════════════════════════════════════════════════════════
$MOCK = true;

// ── ID do esporte via GET ─────────────────────────────────
$esporteId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($MOCK) {
    // ── DADOS SIMULADOS ───────────────────────────────────
    $esportes = [
        ['id_modalidade'=>1,'nome_modalidade'=>'Futsal','tipo_modalidade'=>'quadra',
         'formato_modalidade'=>'grupos_mata_mata','status_edicao_modalidade'=>'em_andamento'],
        ['id_modalidade'=>2,'nome_modalidade'=>'Vôlei','tipo_modalidade'=>'quadra',
         'formato_modalidade'=>'grupos','status_edicao_modalidade'=>'em_andamento'],
        ['id_modalidade'=>3,'nome_modalidade'=>'Tênis de Mesa','tipo_modalidade'=>'mesa',
         'formato_modalidade'=>'mata_mata','status_edicao_modalidade'=>'inscricoes'],
        ['id_modalidade'=>4,'nome_modalidade'=>'Basquete','tipo_modalidade'=>'quadra',
         'formato_modalidade'=>'todos_contra_todos','status_edicao_modalidade'=>'em_andamento'],
    ];

    if (!$esporteId) $esporteId = 1;

    // Encontra o esporte atual
    $esporte = null;
    foreach ($esportes as $e) {
        if ($e['id_modalidade'] == $esporteId) { $esporte = $e; break; }
    }
    if (!$esporte) $esporte = $esportes[0];

    $esporte = array_merge($esporte, [
        'descricao_modalidade' => 'Modalidade oficial do interclasse. Confira a classificação e o chaveamento.',
        'qtd_min_jogadores'    => 5,
        'qtd_max_jogadores'    => 7,
        'id_edicao_modalidade' => $esporteId,
        'nome_edicao'          => 'Interclasse 2026',
        'ano_edicao'           => 2026,
    ]);

    $formato = $esporte['formato_modalidade'];

    // ── MOCK: GRUPOS ──────────────────────────────────────
    $grupos = [];
    if (in_array($formato, ['grupos', 'grupos_mata_mata', 'todos_contra_todos'])) {
        $grupos = [
            'A' => [
                ['nome_turma'=>'1MTIA','jogos'=>3,'vitorias'=>3,'empates'=>0,'derrotas'=>0,'pontos_pro'=>9,'pontos_contra'=>2,'saldo'=>7,'pontos'=>9],
                ['nome_turma'=>'2MTIC','jogos'=>3,'vitorias'=>2,'empates'=>0,'derrotas'=>1,'pontos_pro'=>6,'pontos_contra'=>4,'saldo'=>2,'pontos'=>6],
                ['nome_turma'=>'3DSGN','jogos'=>3,'vitorias'=>1,'empates'=>0,'derrotas'=>2,'pontos_pro'=>4,'pontos_contra'=>6,'saldo'=>-2,'pontos'=>3],
                ['nome_turma'=>'1EMIF','jogos'=>3,'vitorias'=>0,'empates'=>0,'derrotas'=>3,'pontos_pro'=>1,'pontos_contra'=>8,'saldo'=>-7,'pontos'=>0],
            ],
            'B' => [
                ['nome_turma'=>'2MTEC','jogos'=>3,'vitorias'=>2,'empates'=>1,'derrotas'=>0,'pontos_pro'=>7,'pontos_contra'=>3,'saldo'=>4,'pontos'=>7],
                ['nome_turma'=>'1LOGB','jogos'=>3,'vitorias'=>2,'empates'=>0,'derrotas'=>1,'pontos_pro'=>6,'pontos_contra'=>4,'saldo'=>2,'pontos'=>6],
                ['nome_turma'=>'3ADMN','jogos'=>3,'vitorias'=>0,'empates'=>1,'derrotas'=>2,'pontos_pro'=>3,'pontos_contra'=>7,'saldo'=>-4,'pontos'=>1],
                ['nome_turma'=>'2DSGB','jogos'=>3,'vitorias'=>0,'empates'=>0,'derrotas'=>3,'pontos_pro'=>2,'pontos_contra'=>9,'saldo'=>-7,'pontos'=>0],
            ],
        ];
    }

    // ── MOCK: PARTIDAS POR FASE ───────────────────────────
    $partidas_fase = [];
    $todasPartidas = [];

    if (in_array($formato, ['grupos', 'grupos_mata_mata', 'todos_contra_todos'])) {
        $partidas_fase['grupos'] = [
            ['id_partida'=>1,'fase_partida'=>'grupos','grupo_partida'=>'A','data_partida'=>'2026-05-10','hora_partida'=>'09:00:00','local_partida'=>'Quadra A','status_partida'=>'realizada','time_a'=>'1MTIA','time_b'=>'2MTIC','turma_id_time_a'=>1,'turma_id_time_b'=>2,'placar_time_a'=>3,'placar_time_b'=>1,'vencedor'=>'1MTIA'],
            ['id_partida'=>2,'fase_partida'=>'grupos','grupo_partida'=>'A','data_partida'=>'2026-05-10','hora_partida'=>'10:00:00','local_partida'=>'Quadra A','status_partida'=>'realizada','time_a'=>'3DSGN','time_b'=>'1EMIF','turma_id_time_a'=>3,'turma_id_time_b'=>4,'placar_time_a'=>2,'placar_time_b'=>0,'vencedor'=>'3DSGN'],
            ['id_partida'=>3,'fase_partida'=>'grupos','grupo_partida'=>'A','data_partida'=>'2026-05-12','hora_partida'=>'09:00:00','local_partida'=>'Quadra B','status_partida'=>'realizada','time_a'=>'1MTIA','time_b'=>'3DSGN','turma_id_time_a'=>1,'turma_id_time_b'=>3,'placar_time_a'=>4,'placar_time_b'=>1,'vencedor'=>'1MTIA'],
            ['id_partida'=>4,'fase_partida'=>'grupos','grupo_partida'=>'A','data_partida'=>'2026-05-12','hora_partida'=>'10:00:00','local_partida'=>'Quadra B','status_partida'=>'realizada','time_a'=>'2MTIC','time_b'=>'1EMIF','turma_id_time_a'=>2,'turma_id_time_b'=>4,'placar_time_a'=>3,'placar_time_b'=>0,'vencedor'=>'2MTIC'],
            ['id_partida'=>5,'fase_partida'=>'grupos','grupo_partida'=>'B','data_partida'=>'2026-05-10','hora_partida'=>'11:00:00','local_partida'=>'Quadra A','status_partida'=>'realizada','time_a'=>'2MTEC','time_b'=>'1LOGB','turma_id_time_a'=>5,'turma_id_time_b'=>6,'placar_time_a'=>2,'placar_time_b'=>2,'vencedor'=>null],
            ['id_partida'=>6,'fase_partida'=>'grupos','grupo_partida'=>'B','data_partida'=>'2026-05-14','hora_partida'=>'09:00:00','local_partida'=>'Quadra A','status_partida'=>'agendada','time_a'=>'3ADMN','time_b'=>'2DSGB','turma_id_time_a'=>7,'turma_id_time_b'=>8,'placar_time_a'=>null,'placar_time_b'=>null,'vencedor'=>null],
        ];
    }

    if (in_array($formato, ['mata_mata', 'grupos_mata_mata'])) {
        $partidas_fase['semi'] = [
            ['id_partida'=>10,'fase_partida'=>'semi','grupo_partida'=>null,'data_partida'=>'2026-05-20','hora_partida'=>'09:00:00','local_partida'=>'Quadra A','status_partida'=>'realizada','time_a'=>'1MTIA','time_b'=>'2MTEC','turma_id_time_a'=>1,'turma_id_time_b'=>5,'placar_time_a'=>3,'placar_time_b'=>1,'vencedor'=>'1MTIA'],
            ['id_partida'=>11,'fase_partida'=>'semi','grupo_partida'=>null,'data_partida'=>'2026-05-20','hora_partida'=>'10:30:00','local_partida'=>'Quadra B','status_partida'=>'realizada','time_a'=>'2MTIC','time_b'=>'1LOGB','turma_id_time_a'=>2,'turma_id_time_b'=>6,'placar_time_a'=>2,'placar_time_b'=>3,'vencedor'=>'1LOGB'],
        ];
        $partidas_fase['terceiro_lugar'] = [
            ['id_partida'=>12,'fase_partida'=>'terceiro_lugar','grupo_partida'=>null,'data_partida'=>'2026-05-24','hora_partida'=>'09:00:00','local_partida'=>'Quadra A','status_partida'=>'agendada','time_a'=>'2MTIC','time_b'=>'2MTEC','turma_id_time_a'=>2,'turma_id_time_b'=>5,'placar_time_a'=>null,'placar_time_b'=>null,'vencedor'=>null],
        ];
        $partidas_fase['final'] = [
            ['id_partida'=>13,'fase_partida'=>'final','grupo_partida'=>null,'data_partida'=>'2026-05-24','hora_partida'=>'11:00:00','local_partida'=>'Quadra Principal','status_partida'=>'agendada','time_a'=>'1MTIA','time_b'=>'1LOGB','turma_id_time_a'=>1,'turma_id_time_b'=>6,'placar_time_a'=>null,'placar_time_b'=>null,'vencedor'=>null],
        ];
    }

    if ($formato === 'mata_mata') {
        $partidas_fase['oitavas'] = [
            ['id_partida'=>20,'fase_partida'=>'oitavas','grupo_partida'=>null,'data_partida'=>'2026-05-15','hora_partida'=>'09:00:00','local_partida'=>'Quadra A','status_partida'=>'realizada','time_a'=>'1MTIA','time_b'=>'3DSGN','turma_id_time_a'=>1,'turma_id_time_b'=>3,'placar_time_a'=>4,'placar_time_b'=>1,'vencedor'=>'1MTIA'],
            ['id_partida'=>21,'fase_partida'=>'oitavas','grupo_partida'=>null,'data_partida'=>'2026-05-15','hora_partida'=>'10:00:00','local_partida'=>'Quadra B','status_partida'=>'realizada','time_a'=>'2MTIC','time_b'=>'1EMIF','turma_id_time_a'=>2,'turma_id_time_b'=>4,'placar_time_a'=>3,'placar_time_b'=>0,'vencedor'=>'2MTIC'],
        ];
        $partidas_fase['quartas'] = [
            ['id_partida'=>25,'fase_partida'=>'quartas','grupo_partida'=>null,'data_partida'=>'2026-05-17','hora_partida'=>'09:00:00','local_partida'=>'Quadra A','status_partida'=>'agendada','time_a'=>'1MTIA','time_b'=>'2MTEC','turma_id_time_a'=>1,'turma_id_time_b'=>5,'placar_time_a'=>null,'placar_time_b'=>null,'vencedor'=>null],
            ['id_partida'=>26,'fase_partida'=>'quartas','grupo_partida'=>null,'data_partida'=>'2026-05-17','hora_partida'=>'10:30:00','local_partida'=>'Quadra B','status_partida'=>'agendada','time_a'=>'2MTIC','time_b'=>'1LOGB','turma_id_time_a'=>2,'turma_id_time_b'=>6,'placar_time_a'=>null,'placar_time_b'=>null,'vencedor'=>null],
        ];
    }

    // Junta todas as partidas
    foreach ($partidas_fase as $fase => $lista) {
        foreach ($lista as $p) $todasPartidas[] = $p;
    }

} else {
    // ══════════════════════════════════════════════════════
    //  DADOS REAIS DO BANCO
    // ══════════════════════════════════════════════════════

    $stmtEsportes = $conn->query("
        SELECT m.id_modalidade, m.nome_modalidade, m.tipo_modalidade,
               m.formato_modalidade,
               em.status_edicao_modalidade
        FROM modalidade m
        LEFT JOIN edicao_modalidade em ON em.modalidade_id_modalidade = m.id_modalidade
            AND em.status_edicao_modalidade IN ('inscricoes','em_andamento')
        WHERE m.ativo_modalidade = 1
        GROUP BY m.id_modalidade
        ORDER BY m.nome_modalidade ASC
    ");
    $esportes = $stmtEsportes->fetchAll(PDO::FETCH_ASSOC);

    if (!$esporteId && !empty($esportes)) {
        $esporteId = (int) $esportes[0]['id_modalidade'];
    }

    $stmtEsporte = $conn->prepare("
        SELECT m.id_modalidade, m.nome_modalidade, m.descricao_modalidade,
               m.tipo_modalidade, m.formato_modalidade, m.tipo_participacao,
               m.qtd_min_jogadores, m.qtd_max_jogadores,
               em.id_edicao_modalidade, em.status_edicao_modalidade,
               em.data_inicio_inscricao, em.data_fim_inscricao,
               e.nome_edicao, e.ano_edicao
        FROM modalidade m
        LEFT JOIN edicao_modalidade em ON em.modalidade_id_modalidade = m.id_modalidade
            AND em.status_edicao_modalidade IN ('inscricoes','em_andamento')
        LEFT JOIN edicao e ON e.id_edicao = em.edicao_id_edicao
        WHERE m.id_modalidade = :id
        LIMIT 1
    ");
    $stmtEsporte->execute([':id' => $esporteId]);
    $esporte = $stmtEsporte->fetch(PDO::FETCH_ASSOC);
    $emId    = $esporte['id_edicao_modalidade'] ?? null;
    $formato = $esporte['formato_modalidade'] ?? 'grupos_mata_mata';

    // Grupos
    $grupos = [];
    if ($emId && in_array($formato, ['grupos','grupos_mata_mata','todos_contra_todos'])) {
        $stmtCl = $conn->prepare("
            SELECT cl.pontos, cl.vitorias, cl.derrotas, cl.empates,
                   cl.jogos, cl.saldo, cl.pontos_pro, cl.pontos_contra,
                   cl.grupo_classificacao,
                   t.nome_turma, t.id_turma
            FROM classificacao cl
            INNER JOIN turma t ON t.id_turma = cl.turma_id_turma
            WHERE cl.edicao_modalidade_id = :emid
            ORDER BY cl.grupo_classificacao ASC, cl.pontos DESC, cl.saldo DESC
        ");
        $stmtCl->execute([':emid' => $emId]);
        foreach ($stmtCl->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $g = $row['grupo_classificacao'] ?: 'A';
            $grupos[$g][] = $row;
        }
    }

    // Partidas
    $partidas_fase = [];
    $todasPartidas = [];
    if ($emId) {
        $stmtP = $conn->prepare("
            SELECT p.id_partida, p.data_partida, p.hora_partida,
                   p.local_partida, p.fase_partida, p.status_partida,
                   p.grupo_partida,
                   p.turma_id_time_a, p.turma_id_time_b,
                   ta.nome_turma AS time_a, tb.nome_turma AS time_b,
                   r.placar_time_a, r.placar_time_b,
                   tv.nome_turma AS vencedor
            FROM partida p
            INNER JOIN turma ta ON ta.id_turma = p.turma_id_time_a
            INNER JOIN turma tb ON tb.id_turma = p.turma_id_time_b
            LEFT JOIN resultado r ON r.partida_id_partida = p.id_partida
            LEFT JOIN turma tv ON tv.id_turma = r.turma_id_vencedor
            WHERE p.edicao_modalidade_id = :emid
            ORDER BY FIELD(p.fase_partida,'grupos','oitavas','quartas','semi','terceiro_lugar','final'),
                     p.data_partida ASC, p.hora_partida ASC
        ");
        $stmtP->execute([':emid' => $emId]);
        $todasPartidas = $stmtP->fetchAll(PDO::FETCH_ASSOC);
        foreach ($todasPartidas as $p) {
            $partidas_fase[$p['fase_partida']][] = $p;
        }
    }
}

// ── HELPERS ───────────────────────────────────────────────
$tipoIcons = [
    'quadra' => 'fa-basketball',
    'mesa'   => 'fa-table-tennis-paddle-ball',
    'campo'  => 'fa-futbol',
    'outro'  => 'fa-medal',
];
$faseLabel = [
    'grupos'         => 'Fase de Grupos',
    'oitavas'        => 'Oitavas de Final',
    'quartas'        => 'Quartas de Final',
    'semi'           => 'Semifinais',
    'terceiro_lugar' => '3º Lugar',
    'final'          => 'Grande Final',
];
$formatoLabel = [
    'grupos'             => 'Somente Grupos',
    'mata_mata'          => 'Somente Mata-Mata',
    'grupos_mata_mata'   => 'Grupos + Mata-Mata',
    'todos_contra_todos' => 'Todos Contra Todos',
];
$faseOrdemMata = ['oitavas', 'quartas', 'semi', 'terceiro_lugar', 'final'];

$formato      = $esporte['formato_modalidade'] ?? 'grupos_mata_mata';
$temGrupos    = in_array($formato, ['grupos', 'grupos_mata_mata', 'todos_contra_todos']);
$temMataMata  = in_array($formato, ['mata_mata', 'grupos_mata_mata']);
$icone        = $tipoIcons[$esporte['tipo_modalidade'] ?? 'outro'] ?? 'fa-medal';

function fmtData($d)  { return $d ? date('d/m', strtotime($d)) : '—'; }
function fmtDataLong($d) { return $d ? date('d/m/Y', strtotime($d)) : '—'; }
function fmtHora($h)  { return $h ? substr($h, 0, 5) : ''; }
function avatarLetras($nome) { return mb_strtoupper(mb_substr($nome ?? '?', 0, 2)); }
?>
<!DOCTYPE html>
<html lang="pt-BR" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SOEE | <?= htmlspecialchars($esporte['nome_modalidade'] ?? 'Esporte') ?></title>
    <link rel="icon" type="image/png" href="/soee/src/images/logo-soee.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;800&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="/soee/src/frontend/styles/classificacao.css">
    <script>
        // Aplica tema salvo antes do render (evita flash)
        (function(){ const t = localStorage.getItem('theme'); if(t) document.documentElement.setAttribute('data-theme',t); })();
    </script>
</head>
<body>
<div class="cursor-dot" id="cursorDot"></div>
<div class="cursor-ring" id="cursorRing"></div>

<div id="loader">
    <div class="loader-inner">
        <div class="loader-logo-text">SOEE</div>
        <div class="loader-logo-sub">Carregando campeonato</div>
        <div class="loader-bar"><div class="loader-bar-fill"></div></div>
    </div>
</div>

<!-- ══ TOPBAR ══ -->
<header class="topbar">
    <div class="topbar-inner">
        <a href="/soee/src/frontend/views/site/home.php" class="topbar-logo">S<span>O</span>EE</a>
        <div class="topbar-center">
            <i class="fa-solid <?= $icone ?>"></i>
            <span id="topbar-esporte-nome"><?= htmlspecialchars($esporte['nome_modalidade'] ?? '—') ?></span>
            <?php if (!empty($esporte['nome_edicao'])): ?>
                <span class="topbar-edicao"><?= htmlspecialchars($esporte['nome_edicao']) ?> · <?= $esporte['ano_edicao'] ?></span>
            <?php endif; ?>
        </div>
        <div class="topbar-acoes">
            <button id="toggleTema" class="btn-icone-top" aria-label="Tema">
                <i class="fa-solid fa-moon" id="iconeTema"></i>
            </button>
            <?php if (!empty($_SESSION['usuario_id'])): ?>
                <?php
                $tipo = $_SESSION['usuario_tipo'] ?? '';
                $destDash = $tipo === 'professor' ? '/soee/src/frontend/views/dashboards/professor.php'
                          : ($tipo === 'adm_sala'  ? '/soee/src/frontend/views/dashboards/adm-sala.php'
                                                   : '/soee/src/frontend/views/dashboards/aluno.php');
                ?>
                <a href="<?= $destDash ?>" class="btn-dash-top">
                    <i class="fa-solid fa-gauge"></i> Dashboard
                </a>
            <?php else: ?>
                <a href="/soee/index.php" class="btn-dash-top">
                    <i class="fa-solid fa-right-to-bracket"></i> Entrar
                </a>
            <?php endif; ?>
        </div>
    </div>
</header>

<!-- ══ LAYOUT ══ -->
<div class="app-layout">

    <!-- ══ SIDEBAR ══ -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-titulo">
            <i class="fa-solid fa-layer-group"></i> Modalidades
        </div>
        <nav class="sidebar-nav">
            <?php foreach ($esportes as $esp):
                $ico   = $tipoIcons[$esp['tipo_modalidade'] ?? 'outro'] ?? 'fa-medal';
                $ativo = (int)$esp['id_modalidade'] === $esporteId;
                $st    = $esp['status_edicao_modalidade'] ?? '';
                $fmtBadge = $esp['formato_modalidade'] ?? '';
                $badgeFmt = [
                    'grupos'           => 'Grupos',
                    'mata_mata'        => 'Mata-Mata',
                    'grupos_mata_mata' => 'G + MM',
                    'todos_contra_todos' => 'Todos x Todos',
                ][$fmtBadge] ?? '';
            ?>
            <a href="?id=<?= $esp['id_modalidade'] ?>" class="sidebar-item <?= $ativo ? 'ativo' : '' ?>">
                <div class="sidebar-item-icone">
                    <i class="fa-solid <?= $ico ?>"></i>
                </div>
                <div class="sidebar-item-info">
                    <span class="sidebar-item-nome"><?= htmlspecialchars($esp['nome_modalidade']) ?></span>
                    <div style="display:flex;gap:4px;flex-wrap:wrap;margin-top:2px;">
                        <?php if ($st === 'em_andamento'): ?>
                            <span class="sidebar-badge andamento"><i class="fa-solid fa-circle" style="font-size:.4rem"></i> Andamento</span>
                        <?php elseif ($st === 'inscricoes'): ?>
                            <span class="sidebar-badge inscricoes"><i class="fa-solid fa-circle" style="font-size:.4rem"></i> Inscrições</span>
                        <?php else: ?>
                            <span class="sidebar-badge encerrado">Encerrado</span>
                        <?php endif; ?>
                        <?php if ($badgeFmt): ?>
                            <span class="sidebar-badge formato"><?= $badgeFmt ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php if ($ativo): ?><div class="sidebar-ativo-bar"></div><?php endif; ?>
            </a>
            <?php endforeach; ?>
        </nav>
    </aside>

    <!-- ══ CONTEÚDO PRINCIPAL ══ -->
    <main class="conteudo" id="conteudo">

        <?php if (!$esporte): ?>
        <div class="empty-page"><i class="fa-solid fa-futbol"></i><p>Nenhum esporte encontrado.</p></div>
        <?php else: ?>

        <!-- HERO -->
        <div class="esporte-hero">
            <div class="hero-bg"></div>
            <div class="hero-inner">
                <div class="hero-icone"><i class="fa-solid <?= $icone ?>"></i></div>
                <div class="hero-texto">
                    <h1><?= htmlspecialchars($esporte['nome_modalidade']) ?></h1>
                    <?php if (!empty($esporte['descricao_modalidade'])): ?>
                        <p><?= htmlspecialchars($esporte['descricao_modalidade']) ?></p>
                    <?php endif; ?>
                    <div class="hero-meta">
                        <?php if (!empty($esporte['qtd_min_jogadores'])): ?>
                        <span><i class="fa-solid fa-users"></i> <?= $esporte['qtd_min_jogadores'] ?>–<?= $esporte['qtd_max_jogadores'] ?> jog.</span>
                        <?php endif; ?>
                        <span><i class="fa-solid fa-sitemap"></i> <?= $formatoLabel[$formato] ?? ucfirst($formato) ?></span>
                        <?php if (!empty($esporte['nome_edicao'])): ?>
                        <span><i class="fa-solid fa-trophy"></i> <?= htmlspecialchars($esporte['nome_edicao']) ?></span>
                        <?php endif; ?>
                        <?php $st = $esporte['status_edicao_modalidade'] ?? ''; if ($st): ?>
                        <span class="hero-status <?= $st ?>">
                            <i class="fa-solid fa-circle"></i>
                            <?= $st === 'em_andamento' ? 'Em Andamento' : ($st === 'inscricoes' ? 'Inscrições Abertas' : 'Encerrado') ?>
                        </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- TABS — adaptadas ao formato -->
        <div class="tabs-bar" id="tabsBar">
            <?php if ($temGrupos): ?>
            <button class="tab ativo" data-tab="grupos" onclick="trocarTab(this,'grupos')">
                <i class="fa-solid fa-table-cells"></i> Grupos
            </button>
            <?php endif; ?>
            <?php if ($temMataMata): ?>
            <button class="tab <?= !$temGrupos ? 'ativo' : '' ?>" data-tab="chaveamento" onclick="trocarTab(this,'chaveamento')">
                <i class="fa-solid fa-trophy"></i> Mata-Mata
            </button>
            <?php endif; ?>
            <?php if ($formato === 'todos_contra_todos'): ?>
            <button class="tab" data-tab="tabela-geral" onclick="trocarTab(this,'tabela-geral')">
                <i class="fa-solid fa-ranking-star"></i> Classificação
            </button>
            <?php endif; ?>
            <button class="tab <?= !$temGrupos && !$temMataMata ? 'ativo' : '' ?>" data-tab="partidas" onclick="trocarTab(this,'partidas')">
                <i class="fa-solid fa-calendar-days"></i> Partidas
            </button>
        </div>

        <!-- ══ TAB: GRUPOS ══ -->
        <?php if ($temGrupos): ?>
        <div class="tab-conteudo ativo" id="tab-grupos">
            <?php if (empty($grupos)): ?>
                <div class="empty-state"><i class="fa-solid fa-table-cells"></i><p>Nenhuma classificação disponível ainda.</p></div>
            <?php else: ?>
            <div class="grupos-grid">
                <?php foreach ($grupos as $nomeGrupo => $times): ?>
                <div class="grupo-card reveal">
                    <div class="grupo-header">
                        <div class="grupo-letra"><?= htmlspecialchars($nomeGrupo) ?></div>
                        <div>
                            <div class="grupo-titulo">Grupo <?= htmlspecialchars($nomeGrupo) ?></div>
                            <div style="font-size:.72rem;color:var(--texto-2);margin-top:2px;"><?= count($times) ?> times</div>
                        </div>
                        <div class="grupo-qualifica-info">↑ Top 2 avançam</div>
                    </div>
                    <table class="grupo-tabela">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th style="text-align:left">Time</th>
                                <th title="Jogos">J</th>
                                <th title="Vitórias">V</th>
                                <th title="Empates">E</th>
                                <th title="Derrotas">D</th>
                                <th title="Gols Pró">GP</th>
                                <th title="Gols Contra">GC</th>
                                <th title="Saldo de Gols">SG</th>
                                <th title="Pontos">PTS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($times as $pos => $time): ?>
                            <tr class="<?= $pos < 2 ? 'classificado' : '' ?> <?= $pos === 0 ? 'lider' : '' ?>">
                                <td class="pos-col">
                                    <?php if ($pos === 0): ?><span class="pos-badge ouro">1</span>
                                    <?php elseif ($pos === 1): ?><span class="pos-badge prata">2</span>
                                    <?php else: ?><span class="pos-badge"><?= $pos+1 ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="time-col">
                                        <div class="time-avatar"><?= avatarLetras($time['nome_turma']) ?></div>
                                        <span><?= htmlspecialchars($time['nome_turma']) ?></span>
                                    </div>
                                </td>
                                <td><?= $time['jogos'] ?></td>
                                <td class="verde"><?= $time['vitorias'] ?></td>
                                <td><?= $time['empates'] ?></td>
                                <td class="vermelho"><?= $time['derrotas'] ?></td>
                                <td><?= $time['pontos_pro'] ?></td>
                                <td><?= $time['pontos_contra'] ?></td>
                                <td><?= $time['saldo'] >= 0 ? '+'.$time['saldo'] : $time['saldo'] ?></td>
                                <td><span class="pts" data-target="<?= $time['pontos'] ?>">0</span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <!-- Jogos deste grupo -->
                    <?php
                    $jogosGrupo = array_filter($partidas_fase['grupos'] ?? [], fn($p) => $p['grupo_partida'] === $nomeGrupo);
                    if (!empty($jogosGrupo)):
                    ?>
                    <div class="grupo-jogos">
                        <div class="grupo-jogos-titulo"><i class="fa-solid fa-futbol"></i> Jogos</div>
                        <?php foreach ($jogosGrupo as $jogo):
                            $r = $jogo['status_partida'] === 'realizada';
                        ?>
                        <div class="jogo-mini <?= $r ? 'realizada' : '' ?>">
                            <span class="jogo-time <?= $jogo['vencedor'] === $jogo['time_a'] ? 'vencedor' : '' ?>"><?= htmlspecialchars($jogo['time_a']) ?></span>
                            <div class="jogo-placar">
                                <?php if ($r): ?>
                                    <strong><?= $jogo['placar_time_a'] ?></strong><span>×</span><strong><?= $jogo['placar_time_b'] ?></strong>
                                <?php else: ?>
                                    <span class="jogo-data"><?= fmtData($jogo['data_partida']) ?></span>
                                    <?php if ($jogo['hora_partida']): ?><span class="jogo-hora"><?= fmtHora($jogo['hora_partida']) ?></span><?php endif; ?>
                                <?php endif; ?>
                            </div>
                            <span class="jogo-time direita <?= $jogo['vencedor'] === $jogo['time_b'] ? 'vencedor' : '' ?>"><?= htmlspecialchars($jogo['time_b']) ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- ══ TAB: MATA-MATA ══ -->
        <?php if ($temMataMata): ?>
        <div class="tab-conteudo <?= !$temGrupos ? 'ativo' : '' ?>" id="tab-chaveamento">
            <?php
            $temFases = false;
            foreach ($faseOrdemMata as $f) { if (!empty($partidas_fase[$f])) { $temFases = true; break; } }
            ?>
            <?php if (!$temFases): ?>
                <div class="empty-state"><i class="fa-solid fa-trophy"></i><p>O mata-mata ainda não começou.</p></div>
            <?php else: ?>
            <div class="chaveamento-wrap">
                <div class="bracket" id="bracket">
                    <?php foreach ($faseOrdemMata as $fase):
                        if (empty($partidas_fase[$fase])) continue;
                        $isFinal = $fase === 'final';
                        $is3o    = $fase === 'terceiro_lugar';
                    ?>
                    <div class="bracket-coluna <?= $isFinal ? 'coluna-final' : '' ?> <?= $is3o ? 'coluna-3o' : '' ?>">
                        <div class="bracket-fase-label">
                            <?php if ($isFinal): ?><i class="fa-solid fa-crown"></i><?php endif; ?>
                            <?= $faseLabel[$fase] ?? ucfirst($fase) ?>
                        </div>
                        <div class="bracket-jogos">
                            <?php foreach ($partidas_fase[$fase] as $jogo):
                                $realizada = $jogo['status_partida'] === 'realizada';
                                $wo        = $jogo['status_partida'] === 'wo';
                            ?>
                            <div class="bracket-jogo <?= $isFinal ? 'jogo-final' : '' ?> <?= $realizada ? 'realizada' : '' ?> reveal">
                                <!-- Time A -->
                                <div class="bracket-time <?= $jogo['vencedor'] === $jogo['time_a'] ? 'vencedor' : '' ?>">
                                    <div class="bracket-time-avatar"><?= avatarLetras($jogo['time_a'] ?? '?') ?></div>
                                    <span><?= htmlspecialchars($jogo['time_a'] ?: 'A definir') ?></span>
                                    <?php if ($realizada): ?>
                                        <strong class="bracket-placar"><?= $jogo['placar_time_a'] ?></strong>
                                    <?php endif; ?>
                                    <?php if ($jogo['vencedor'] === $jogo['time_a']): ?>
                                        <i class="fa-solid fa-check bracket-check"></i>
                                    <?php endif; ?>
                                </div>
                                <!-- VS / Placar / Data -->
                                <div class="bracket-vs">
                                    <?php if ($wo): ?>
                                        <span class="bracket-wo">W.O.</span>
                                    <?php elseif (!$realizada): ?>
                                        <span class="bracket-data-hora"><?= fmtData($jogo['data_partida']) ?> <?= fmtHora($jogo['hora_partida']) ?></span>
                                    <?php else: ?>
                                        <span>×</span>
                                    <?php endif; ?>
                                </div>
                                <!-- Time B -->
                                <div class="bracket-time <?= $jogo['vencedor'] === $jogo['time_b'] ? 'vencedor' : '' ?>">
                                    <div class="bracket-time-avatar"><?= avatarLetras($jogo['time_b'] ?? '?') ?></div>
                                    <span><?= htmlspecialchars($jogo['time_b'] ?: 'A definir') ?></span>
                                    <?php if ($realizada): ?>
                                        <strong class="bracket-placar"><?= $jogo['placar_time_b'] ?></strong>
                                    <?php endif; ?>
                                    <?php if ($jogo['vencedor'] === $jogo['time_b']): ?>
                                        <i class="fa-solid fa-check bracket-check"></i>
                                    <?php endif; ?>
                                </div>
                                <?php if (!empty($jogo['local_partida'])): ?>
                                <div class="bracket-local">
                                    <i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars($jogo['local_partida']) ?>
                                </div>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- ══ TAB: TODOS CONTRA TODOS (tabela geral) ══ -->
        <?php if ($formato === 'todos_contra_todos'): ?>
        <div class="tab-conteudo" id="tab-tabela-geral">
            <?php $todosGrupo = reset($grupos) ?: []; ?>
            <?php if (empty($todosGrupo)): ?>
                <div class="empty-state"><i class="fa-solid fa-ranking-star"></i><p>Sem dados de classificação ainda.</p></div>
            <?php else: ?>
            <div class="tabela-geral-wrap reveal">
                <table class="grupo-tabela tabela-geral">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th style="text-align:left">Time</th>
                            <th title="Jogos">J</th>
                            <th title="Vitórias">V</th>
                            <th title="Empates">E</th>
                            <th title="Derrotas">D</th>
                            <th title="Gols Pró">GP</th>
                            <th title="Gols Contra">GC</th>
                            <th title="Saldo">SG</th>
                            <th title="Pontos">PTS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($todosGrupo as $pos => $time): ?>
                        <tr class="<?= $pos === 0 ? 'lider' : '' ?>">
                            <td class="pos-col">
                                <?php if ($pos === 0): ?><span class="pos-badge ouro">1</span>
                                <?php elseif ($pos === 1): ?><span class="pos-badge prata">2</span>
                                <?php elseif ($pos === 2): ?><span class="pos-badge bronze">3</span>
                                <?php else: ?><span class="pos-badge"><?= $pos+1 ?></span>
                                <?php endif; ?>
                            </td>
                            <td><div class="time-col"><div class="time-avatar"><?= avatarLetras($time['nome_turma']) ?></div><span><?= htmlspecialchars($time['nome_turma']) ?></span></div></td>
                            <td><?= $time['jogos'] ?></td>
                            <td class="verde"><?= $time['vitorias'] ?></td>
                            <td><?= $time['empates'] ?></td>
                            <td class="vermelho"><?= $time['derrotas'] ?></td>
                            <td><?= $time['pontos_pro'] ?></td>
                            <td><?= $time['pontos_contra'] ?></td>
                            <td><?= $time['saldo'] >= 0 ? '+'.$time['saldo'] : $time['saldo'] ?></td>
                            <td><span class="pts" data-target="<?= $time['pontos'] ?>">0</span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- ══ TAB: PARTIDAS ══ -->
        <div class="tab-conteudo <?= !$temGrupos && !$temMataMata ? 'ativo' : '' ?>" id="tab-partidas">
            <?php if (empty($todasPartidas)): ?>
                <div class="empty-state"><i class="fa-solid fa-calendar-xmark"></i><p>Nenhuma partida cadastrada ainda.</p></div>
            <?php else: ?>
            <div class="partidas-lista">
                <?php $fasesExibir = ['grupos','oitavas','quartas','semi','terceiro_lugar','final'];
                foreach ($fasesExibir as $fase):
                    if (empty($partidas_fase[$fase])) continue; ?>
                <div class="partidas-secao reveal">
                    <div class="partidas-secao-titulo">
                        <?php if ($fase === 'final'): ?><i class="fa-solid fa-crown"></i>
                        <?php elseif ($fase === 'grupos'): ?><i class="fa-solid fa-table-cells"></i>
                        <?php else: ?><i class="fa-solid fa-trophy"></i><?php endif; ?>
                        <?= $faseLabel[$fase] ?? ucfirst($fase) ?>
                        <span class="partidas-count"><?= count($partidas_fase[$fase]) ?> jogo(s)</span>
                    </div>
                    <?php foreach ($partidas_fase[$fase] as $p):
                        $realizada = $p['status_partida'] === 'realizada';
                        $wo        = $p['status_partida'] === 'wo';
                    ?>
                    <div class="partida-row <?= $realizada ? 'realizada' : '' ?> <?= $fase === 'final' ? 'partida-final' : '' ?>">
                        <div class="pr-times">
                            <span class="pr-time <?= $p['vencedor'] === $p['time_a'] ? 'vencedor' : '' ?>">
                                <span class="pr-avatar"><?= avatarLetras($p['time_a']) ?></span>
                                <?= htmlspecialchars($p['time_a']) ?>
                            </span>
                            <div class="pr-placar">
                                <?php if ($realizada): ?>
                                    <strong><?= $p['placar_time_a'] ?></strong><span class="pr-x">×</span><strong><?= $p['placar_time_b'] ?></strong>
                                <?php elseif ($wo): ?>
                                    <span class="pr-wo">W.O.</span>
                                <?php else: ?>
                                    <span class="pr-vs">VS</span>
                                <?php endif; ?>
                            </div>
                            <span class="pr-time direita <?= $p['vencedor'] === $p['time_b'] ? 'vencedor' : '' ?>">
                                <?= htmlspecialchars($p['time_b']) ?>
                                <span class="pr-avatar"><?= avatarLetras($p['time_b']) ?></span>
                            </span>
                        </div>
                        <div class="pr-meta">
                            <span><i class="fa-solid fa-calendar"></i> <?= fmtDataLong($p['data_partida']) ?></span>
                            <?php if ($p['hora_partida']): ?>
                                <span><i class="fa-solid fa-clock"></i> <?= fmtHora($p['hora_partida']) ?></span>
                            <?php endif; ?>
                            <?php if ($p['local_partida']): ?>
                                <span><i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars($p['local_partida']) ?></span>
                            <?php endif; ?>
                            <?php if (!empty($p['grupo_partida'])): ?>
                                <span><i class="fa-solid fa-layer-group"></i> Grupo <?= htmlspecialchars($p['grupo_partida']) ?></span>
                            <?php endif; ?>
                            <span class="pr-status-badge <?= $p['status_partida'] ?>">
                                <?= ['agendada'=>'Agendada','realizada'=>'Realizada','cancelada'=>'Cancelada','wo'=>'W.O.'][$p['status_partida']] ?? ucfirst($p['status_partida']) ?>
                            </span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <?php endif; // fim if esporte ?>
    </main>
</div>

<!-- BOTÃO SIDEBAR MOBILE -->
<button class="sidebar-toggle-mobile" id="sidebarToggle" aria-label="Menu">
    <i class="fa-solid fa-bars"></i>
</button>

<script src="/soee/src/frontend/scripts/classificacao.js"></script>
</body>
</html>