<?php
// ═══════════════════════════════════════════════════════════
//  classificacao.php — SOEE · Página de Campeonato
//  URL: classificacao.php?id={id_modalidade}
// ═══════════════════════════════════════════════════════════
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/soee/src/backend/includes/conexao.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/soee/src/backend/controllers/home.php';

// ── ID da modalidade via GET ──────────────────────────────
$modalidadeId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// ── MODALIDADES EM ANDAMENTO (sidebar) ───────────────────
$stmtEsportes = $conn->query("
    SELECT DISTINCT
        m.id_modalidade,
        m.nome_modalidade,
        m.tipo_modalidade,
        m.formato_modalidade,
        em.status_edicao_modalidade
    FROM modalidade m
    INNER JOIN edicao_modalidade em ON em.modalidade_id_modalidade = m.id_modalidade
    INNER JOIN edicao e            ON e.id_edicao = em.edicao_id_edicao
    WHERE m.ativo_modalidade = 1
      AND e.status_edicao = 'em_andamento'
    ORDER BY m.nome_modalidade ASC
");
$esportes = $stmtEsportes->fetchAll(PDO::FETCH_ASSOC);

// Se não veio ID, usa o primeiro disponível
if (!$modalidadeId && !empty($esportes)) {
    $modalidadeId = (int) $esportes[0]['id_modalidade'];
}

// ── DADOS DA MODALIDADE SELECIONADA ──────────────────────
$esporte = null;
$emId    = null;
$formato = null;

if ($modalidadeId) {
    $stmtEsporte = $conn->prepare("
        SELECT
            m.id_modalidade,
            m.nome_modalidade,
            m.descricao_modalidade,
            m.tipo_modalidade,
            m.formato_modalidade,
            m.tipo_participacao,
            m.qtd_min_jogadores,
            m.qtd_max_jogadores,
            em.id_edicao_modalidade,
            em.status_edicao_modalidade,
            em.data_inicio_inscricao,
            em.data_fim_inscricao,
            e.nome_edicao,
            e.ano_edicao
        FROM modalidade m
        INNER JOIN edicao_modalidade em ON em.modalidade_id_modalidade = m.id_modalidade
        INNER JOIN edicao e            ON e.id_edicao = em.edicao_id_edicao
        WHERE m.id_modalidade = :id
          AND e.status_edicao = 'em_andamento'
        ORDER BY em.id_edicao_modalidade DESC
        LIMIT 1
    ");
    $stmtEsporte->execute([':id' => $modalidadeId]);
    $esporte = $stmtEsporte->fetch(PDO::FETCH_ASSOC);

    if ($esporte) {
        $emId         = (int) $esporte['id_edicao_modalidade'];
        $formato      = $esporte['formato_modalidade'];
        $participacao = $esporte['tipo_participacao'];
    }
}

// ── CLASSIFICAÇÃO POR GRUPOS ─────────────────────────────
$grupos   = [];
$temGrupos = $formato && in_array($formato, ['grupos', 'grupos_mata_mata', 'todos_contra_todos']);

if ($emId && $temGrupos) {
    $stmtCl = $conn->prepare("
        SELECT
            cl.pontos,
            cl.vitorias,
            cl.derrotas,
            cl.empates,
            cl.jogos,
            cl.saldo,
            cl.pontos_pro,
            cl.pontos_contra,
            cl.grupo_classificacao,
            t.nome_turma,
            t.id_turma
        FROM classificacao cl
        INNER JOIN turma t ON t.id_turma = cl.turma_id_turma
        WHERE cl.edicao_modalidade_id = :emid
        ORDER BY
            cl.grupo_classificacao ASC,
            cl.pontos DESC,
            cl.saldo DESC,
            cl.vitorias DESC,
            cl.pontos_pro DESC
    ");
    $stmtCl->execute([':emid' => $emId]);
    foreach ($stmtCl->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $g = $row['grupo_classificacao'] ?: 'A';
        $grupos[$g][] = $row;
    }
}

// ── PARTIDAS POR FASE ────────────────────────────────────
$partidas_fase = [];
$todasPartidas = [];

if ($emId) {
    $stmtP = $conn->prepare("
        SELECT
            p.id_partida,
            p.data_partida,
            p.hora_partida,
            p.local_partida,
            p.fase_partida,
            p.status_partida,
            p.grupo_partida,
            p.turma_id_time_a,
            p.turma_id_time_b,
            ta.nome_turma  AS time_a,
            tb.nome_turma  AS time_b,
            r.placar_time_a,
            r.placar_time_b,
            tv.nome_turma  AS vencedor
        FROM partida p
        INNER JOIN turma ta ON ta.id_turma = p.turma_id_time_a
        INNER JOIN turma tb ON tb.id_turma = p.turma_id_time_b
        LEFT  JOIN resultado r  ON r.partida_id_partida = p.id_partida
        LEFT  JOIN turma tv     ON tv.id_turma = r.turma_id_vencedor
        WHERE p.edicao_modalidade_id = :emid
        ORDER BY
            FIELD(p.fase_partida,'grupos','oitavas','quartas','semi','terceiro_lugar','final'),
            p.data_partida ASC,
            p.hora_partida ASC
    ");
    $stmtP->execute([':emid' => $emId]);
    $todasPartidas = $stmtP->fetchAll(PDO::FETCH_ASSOC);
    foreach ($todasPartidas as $p) {
        $partidas_fase[$p['fase_partida']][] = $p;
    }
}

// ── SORTEIO JÁ GERADO? ───────────────────────────────────
$sorteioGerado = false;
if ($emId) {
    $stmtSg = $conn->prepare("
        SELECT id FROM sorteio_gerado
        WHERE edicao_modalidade_id = :emid
        LIMIT 1
    ");
    $stmtSg->execute([':emid' => $emId]);
    $sorteioGerado = (bool) $stmtSg->fetchColumn();
}

// ── HELPERS ──────────────────────────────────────────────
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

$participacaoLabel = [
    'solo'  => 'Individual',
    'dupla' => 'Dupla',
    'trio'  => 'Trio',
    'time'  => 'Times por Turma',
];

$faseOrdemMata    = ['oitavas', 'quartas', 'semi', 'terceiro_lugar', 'final'];
$temMataMata      = $formato && in_array($formato, ['mata_mata', 'grupos_mata_mata']);
$icone            = $tipoIcons[$esporte['tipo_modalidade'] ?? 'outro'] ?? 'fa-medal';
$participacao     = $esporte['tipo_participacao'] ?? 'time';
$ehIndividual     = in_array($participacao, ['solo', 'dupla', 'trio']);

// Quantos classificam por grupo para mata-mata
$classificamPorGrupo = 2;

function fmtData($d)     { return $d ? date('d/m', strtotime($d)) : '—'; }
function fmtDataLong($d) { return $d ? date('d/m/Y', strtotime($d)) : '—'; }
function fmtHora($h)     { return $h ? substr($h, 0, 5) : ''; }
function avatar($nome)   { return mb_strtoupper(mb_substr(trim($nome ?? '?'), 0, 2)); }

// ── AUTH ─────────────────────────────────────────────────
$logado = !empty($_SESSION['usuario_id']);
$tipo   = $_SESSION['usuario_tipo'] ?? '';
$destDash = match($tipo) {
    'professor' => '/soee/src/frontend/views/dashboards/professor.php',
    'adm_sala'  => '/soee/src/frontend/views/dashboards/adm-sala.php',
    'adm_geral' => '/soee/src/frontend/views/dashboards/adm.php',
    default     => '/soee/src/frontend/views/dashboards/aluno.php',
};
?>
<!DOCTYPE html>
<html lang="pt-BR" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SOEE | <?= htmlspecialchars($esporte['nome_modalidade'] ?? 'Campeonato') ?></title>
    <link rel="icon" type="image/png" href="/soee/src/frontend/assets/icons/logo-soee.png">

    <!-- Fontes -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;800&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600;9..40,700&display=swap" rel="stylesheet">

    <!-- Ícones -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- CSS principal -->
    <link rel="stylesheet" href="/soee/src/frontend/styles/classificacao.css">

    <!-- Tema imediato (sem flash) -->
    <script>
        (function () {
            const t = localStorage.getItem('theme');
            if (t) document.documentElement.setAttribute('data-theme', t);
        })();
    </script>
</head>
<body>

<!-- ── CURSOR ── -->
<div class="cursor-dot"  id="cursorDot"></div>
<div class="cursor-ring" id="cursorRing"></div>

<!-- ── LOADER ── -->
<div id="loader">
    <div class="loader-inner">
        <div class="loader-logo-text">SOEE</div>
        <div class="loader-logo-sub">Carregando campeonato</div>
        <div class="loader-bar"><div class="loader-bar-fill"></div></div>
    </div>
</div>

<!-- ══════════ TOPBAR ══════════ -->
<header class="topbar">
    <div class="topbar-inner">

        <a href="/soee/src/frontend/views/site/home.php" class="topbar-logo">
            S<span>O</span>EE
        </a>

        <div class="topbar-center">
            <?php if ($esporte): ?>
                <i class="fa-solid <?= $icone ?>"></i>
                <span><?= htmlspecialchars($esporte['nome_modalidade']) ?></span>
                <?php if (!empty($esporte['nome_edicao'])): ?>
                    <span class="topbar-edicao">
                        <?= htmlspecialchars($esporte['nome_edicao']) ?> · <?= $esporte['ano_edicao'] ?>
                    </span>
                <?php endif; ?>
            <?php else: ?>
                <span>Campeonatos</span>
            <?php endif; ?>
        </div>

        <div class="topbar-acoes">
            <button id="toggleTema" class="btn-icone-top" aria-label="Alternar tema">
                <i class="fa-solid fa-moon" id="iconeTema"></i>
            </button>

            <?php if ($logado): ?>
                <a href="<?= $destDash ?>" class="btn-dash-top">
                    <i class="fa-solid fa-gauge"></i> Dashboard
                </a>
            <?php else: ?>
                <a href="/soee/index.php" class="btn-dash-top">
                    <i class="fa-solid fa-right-to-bracket"></i> Voltar
                </a>
            <?php endif; ?>
        </div>

    </div>
</header>

<div class="app-layout">

    <!-- ══════════ SIDEBAR ══════════ -->
    <aside class="sidebar" id="sidebar">

        <div class="sidebar-titulo">
            <i class="fa-solid fa-layer-group"></i> Modalidades em Andamento
        </div>

        <nav class="sidebar-nav">
            <?php if (empty($esportes)): ?>
                <div class="sidebar-vazio">
                    <i class="fa-solid fa-clock"></i>
                    <span>Nenhuma modalidade em andamento no momento.</span>
                </div>
            <?php else: ?>
                <?php foreach ($esportes as $esp):
                    $ico      = $tipoIcons[$esp['tipo_modalidade'] ?? 'outro'] ?? 'fa-medal';
                    $ativo    = (int) $esp['id_modalidade'] === $modalidadeId;
                    $fmtBadge = [
                        'grupos'             => 'Grupos',
                        'mata_mata'          => 'Mata-Mata',
                        'grupos_mata_mata'   => 'G + MM',
                        'todos_contra_todos' => 'Todos × Todos',
                    ][$esp['formato_modalidade'] ?? ''] ?? '';
                ?>
                <a href="?id=<?= $esp['id_modalidade'] ?>"
                   class="sidebar-item <?= $ativo ? 'ativo' : '' ?>">

                    <div class="sidebar-item-icone">
                        <i class="fa-solid <?= $ico ?>"></i>
                    </div>

                    <div class="sidebar-item-info">
                        <span class="sidebar-item-nome">
                            <?= htmlspecialchars($esp['nome_modalidade']) ?>
                        </span>
                        <div class="sidebar-badges">
                            <span class="sidebar-badge andamento">
                                <i class="fa-solid fa-circle" style="font-size:.35rem;vertical-align:middle"></i>
                                Em Andamento
                            </span>
                            <?php if ($fmtBadge): ?>
                                <span class="sidebar-badge formato"><?= $fmtBadge ?></span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if ($ativo): ?>
                        <div class="sidebar-ativo-bar"></div>
                    <?php endif; ?>
                </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </nav>

    </aside>

    <!-- ══════════ CONTEÚDO ══════════ -->
    <main class="conteudo" id="conteudo">

        <?php if (!$esporte): ?>
        <!-- ── VAZIO ── -->
        <div class="empty-page">
            <div class="empty-page-inner">
                <i class="fa-solid fa-trophy"></i>
                <h2>Nenhum campeonato em andamento</h2>
                <p>Quando as inscrições fecharem e o campeonato iniciar, as classificações e partidas aparecerão aqui.</p>
                <?php if ($logado && in_array($tipo, ['professor', 'adm_geral'])): ?>
                    <a href="<?= $destDash ?>" class="btn-dash-top" style="margin-top:24px;display:inline-flex;">
                        <i class="fa-solid fa-gauge"></i> Ir para o Dashboard
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <?php else: ?>

        <!-- ── HERO ── -->
        <div class="esporte-hero">
            <div class="hero-bg"></div>
            <div class="hero-inner">
                <div class="hero-icone">
                    <i class="fa-solid <?= $icone ?>"></i>
                </div>
                <div class="hero-texto">
                    <h1><?= htmlspecialchars($esporte['nome_modalidade']) ?></h1>
                    <?php if (!empty($esporte['descricao_modalidade'])): ?>
                        <p><?= htmlspecialchars(mb_substr($esporte['descricao_modalidade'], 0, 160)) ?></p>
                    <?php endif; ?>
                    <div class="hero-meta">
                        <span>
                            <i class="fa-solid fa-sitemap"></i>
                            <?= $formatoLabel[$formato] ?? ucfirst($formato) ?>
                        </span>
                        <span>
                            <i class="fa-solid fa-user"></i>
                            <?= $participacaoLabel[$participacao] ?? ucfirst($participacao) ?>
                        </span>
                        <?php if (!empty($esporte['nome_edicao'])): ?>
                            <span>
                                <i class="fa-solid fa-trophy"></i>
                                <?= htmlspecialchars($esporte['nome_edicao']) ?>
                            </span>
                        <?php endif; ?>
                        <span class="hero-status em_andamento">
                            <i class="fa-solid fa-circle" style="font-size:.45rem;vertical-align:middle"></i>
                            Em Andamento
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── TABS ── -->
        <div class="tabs-bar">
            <?php if ($temGrupos): ?>
            <button class="tab ativo" data-tab="grupos" onclick="trocarTab(this,'grupos')">
                <i class="fa-solid fa-table-cells"></i>
                <?= $formato === 'todos_contra_todos' ? 'Classificação' : 'Fase de Grupos' ?>
            </button>
            <?php endif; ?>

            <?php if ($temMataMata): ?>
            <button class="tab <?= !$temGrupos ? 'ativo' : '' ?>"
                    data-tab="chaveamento"
                    onclick="trocarTab(this,'chaveamento')">
                <i class="fa-solid fa-trophy"></i> Mata-Mata
            </button>
            <?php endif; ?>

            <?php if (!$temGrupos && !$temMataMata): ?>
            <button class="tab ativo" data-tab="partidas" onclick="trocarTab(this,'partidas')">
                <i class="fa-solid fa-calendar-days"></i> Partidas
            </button>
            <?php else: ?>
            <button class="tab" data-tab="partidas" onclick="trocarTab(this,'partidas')">
                <i class="fa-solid fa-calendar-days"></i> Todas as Partidas
            </button>
            <?php endif; ?>
        </div>

        <!-- ══ TAB: GRUPOS / CLASSIFICAÇÃO GERAL ══ -->
        <?php if ($temGrupos): ?>
        <div class="tab-conteudo ativo" id="tab-grupos">

            <?php if (empty($grupos)): ?>
                <div class="empty-state">
                    <i class="fa-solid fa-table-cells"></i>
                    <p>O sorteio ainda não foi realizado ou nenhuma partida foi registrada.</p>
                </div>
            <?php else: ?>

                <?php if ($ehIndividual): ?>
                <div class="aviso-individual">
                    <i class="fa-solid fa-circle-info"></i>
                    <?php if ($participacao === 'solo'): ?>
                        Modalidade individual — cada jogador representa sua turma.
                    <?php elseif ($participacao === 'dupla'): ?>
                        Modalidade em duplas — as duplas representam sua turma.
                    <?php else: ?>
                        Modalidade em trios — os trios representam sua turma.
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <div class="grupos-grid">
                    <?php foreach ($grupos as $nomeGrupo => $times): ?>
                    <div class="grupo-card reveal">

                        <div class="grupo-header">
                            <div class="grupo-letra">
                                <?= $formato === 'todos_contra_todos' ? '#' : $nomeGrupo ?>
                            </div>
                            <div>
                                <div class="grupo-titulo">
                                    <?= $formato === 'todos_contra_todos'
                                        ? 'Classificação Geral'
                                        : 'Grupo ' . $nomeGrupo ?>
                                </div>
                                <div class="grupo-sub">
                                    <?= count($times) ?> time<?= count($times) > 1 ? 's' : '' ?>
                                </div>
                            </div>
                            <?php if ($formato !== 'todos_contra_todos'): ?>
                            <div class="grupo-qualifica">
                                <i class="fa-solid fa-arrow-up"></i>
                                Top <?= $classificamPorGrupo ?> avançam
                            </div>
                            <?php endif; ?>
                        </div>

                        <div class="grupo-tabela-wrap">
                            <table class="grupo-tabela">
                                <thead>
                                    <tr>
                                        <th class="th-pos">#</th>
                                        <th class="th-time">Time / Turma</th>
                                        <th title="Jogos realizados">J</th>
                                        <th title="Vitórias">V</th>
                                        <th title="Empates">E</th>
                                        <th title="Derrotas">D</th>
                                        <th title="Pontos a favor">GP</th>
                                        <th title="Pontos contra">GC</th>
                                        <th title="Saldo">SG</th>
                                        <th title="Pontos na tabela">PTS</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($times as $pos => $time):
                                        $classifica = $pos < $classificamPorGrupo
                                                      && $formato !== 'grupos'
                                                      && $formato !== 'todos_contra_todos';
                                        $lider  = $pos === 0;
                                        $saldo  = (int) $time['saldo'];
                                    ?>
                                    <tr class="<?= $classifica ? 'classificado' : '' ?> <?= $lider ? 'lider' : '' ?>">
                                        <td class="td-pos">
                                            <?php if ($pos === 0): ?>
                                                <span class="pos-badge ouro"  title="1º lugar">1</span>
                                            <?php elseif ($pos === 1): ?>
                                                <span class="pos-badge prata" title="2º lugar">2</span>
                                            <?php elseif ($pos === 2): ?>
                                                <span class="pos-badge bronze" title="3º lugar">3</span>
                                            <?php else: ?>
                                                <span class="pos-badge"><?= $pos + 1 ?></span>
                                            <?php endif; ?>
                                        </td>

                                        <td class="td-time">
                                            <div class="time-col">
                                                <div class="time-avatar">
                                                    <?= avatar($time['nome_turma']) ?>
                                                </div>
                                                <span class="time-nome">
                                                    <?= htmlspecialchars($time['nome_turma']) ?>
                                                </span>
                                                <?php if ($classifica): ?>
                                                    <span class="badge-classifica" title="Classificado">
                                                        <i class="fa-solid fa-check"></i>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </td>

                                        <td class="td-num"><?= (int) $time['jogos'] ?></td>
                                        <td class="td-num verde"><?= (int) $time['vitorias'] ?></td>
                                        <td class="td-num"><?= (int) $time['empates'] ?></td>
                                        <td class="td-num vermelho"><?= (int) $time['derrotas'] ?></td>
                                        <td class="td-num"><?= (int) $time['pontos_pro'] ?></td>
                                        <td class="td-num"><?= (int) $time['pontos_contra'] ?></td>
                                        <td class="td-num <?= $saldo > 0 ? 'verde' : ($saldo < 0 ? 'vermelho' : '') ?>">
                                            <?= $saldo > 0 ? '+' . $saldo : $saldo ?>
                                        </td>
                                        <td class="td-pts">
                                            <span class="pts" data-target="<?= (int) $time['pontos'] ?>">0</span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Jogos do grupo -->
                        <?php
                        $jogosGrupo = array_filter(
                            $partidas_fase['grupos'] ?? [],
                            fn($p) => $p['grupo_partida'] === $nomeGrupo
                        );
                        if (!empty($jogosGrupo)):
                        ?>
                        <div class="grupo-jogos">
                            <div class="grupo-jogos-titulo">
                                <i class="fa-solid fa-futbol"></i>
                                Jogos do Grupo <?= $nomeGrupo ?>
                            </div>
                            <?php foreach ($jogosGrupo as $jogo):
                                $realizada = $jogo['status_partida'] === 'realizada';
                            ?>
                            <div class="jogo-mini <?= $realizada ? 'realizada' : '' ?>">
                                <span class="jogo-time <?= (!empty($jogo['vencedor']) && $jogo['vencedor'] === $jogo['time_a']) ? 'vencedor' : '' ?>">
                                    <?= htmlspecialchars($jogo['time_a']) ?>
                                </span>
                                <div class="jogo-placar">
                                    <?php if ($realizada): ?>
                                        <strong><?= $jogo['placar_time_a'] ?></strong>
                                        <span class="jogo-x">×</span>
                                        <strong><?= $jogo['placar_time_b'] ?></strong>
                                    <?php else: ?>
                                        <span class="jogo-data"><?= fmtData($jogo['data_partida']) ?></span>
                                        <?php if ($jogo['hora_partida']): ?>
                                            <span class="jogo-hora"><?= fmtHora($jogo['hora_partida']) ?></span>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                                <span class="jogo-time direita <?= (!empty($jogo['vencedor']) && $jogo['vencedor'] === $jogo['time_b']) ? 'vencedor' : '' ?>">
                                    <?= htmlspecialchars($jogo['time_b']) ?>
                                </span>
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

        <!-- ══ TAB: MATA-MATA / CHAVEAMENTO ══ -->
        <?php if ($temMataMata): ?>
        <div class="tab-conteudo <?= !$temGrupos ? 'ativo' : '' ?>" id="tab-chaveamento">

            <?php
            $temFases = false;
            foreach ($faseOrdemMata as $f) {
                if (!empty($partidas_fase[$f])) { $temFases = true; break; }
            }
            ?>

            <?php if (!$temFases): ?>
                <div class="empty-state">
                    <i class="fa-solid fa-trophy"></i>
                    <p>O mata-mata ainda não começou. Aguarde o fim da fase de grupos.</p>
                </div>
            <?php else: ?>

            <div class="chaveamento-wrap">
                <div class="bracket" id="bracket-root">
                    <?php foreach ($faseOrdemMata as $fase):
                        if (empty($partidas_fase[$fase])) continue;
                        $isFinal = $fase === 'final';
                        $is3o    = $fase === 'terceiro_lugar';
                    ?>
                    <div class="bracket-coluna <?= $isFinal ? 'coluna-final' : '' ?> <?= $is3o ? 'coluna-3o' : '' ?>">

                        <div class="bracket-fase-label">
                            <?= $isFinal ? '<i class="fa-solid fa-crown"></i>' : '' ?>
                            <?= $faseLabel[$fase] ?? ucfirst($fase) ?>
                        </div>

                        <div class="bracket-jogos">
                            <?php foreach ($partidas_fase[$fase] as $jogo):
                                $realizada = $jogo['status_partida'] === 'realizada';
                                $wo        = $jogo['status_partida'] === 'wo';
                                $winA      = !empty($jogo['vencedor']) && $jogo['vencedor'] === $jogo['time_a'];
                                $winB      = !empty($jogo['vencedor']) && $jogo['vencedor'] === $jogo['time_b'];
                            ?>
                            <div class="bracket-jogo <?= $isFinal ? 'jogo-final' : '' ?> <?= $realizada ? 'realizada' : '' ?> reveal"
                                 data-partida="<?= $jogo['id_partida'] ?>">

                                <!-- Time A -->
                                <div class="bracket-time <?= $winA ? 'vencedor' : ($realizada && !$winA ? 'perdedor' : '') ?>">
                                    <div class="bracket-avatar">
                                        <?= avatar($jogo['time_a'] ?? '?') ?>
                                    </div>
                                    <span><?= htmlspecialchars($jogo['time_a'] ?: 'A definir') ?></span>
                                    <?php if ($realizada): ?>
                                        <strong class="bracket-placar"><?= $jogo['placar_time_a'] ?></strong>
                                    <?php endif; ?>
                                    <?php if ($winA): ?>
                                        <i class="fa-solid fa-check bracket-check"></i>
                                    <?php endif; ?>
                                </div>

                                <!-- Separador central -->
                                <div class="bracket-sep">
                                    <?php if ($wo): ?>
                                        <span class="bracket-wo">W.O.</span>
                                    <?php elseif (!$realizada): ?>
                                        <span class="bracket-data">
                                            <?= fmtData($jogo['data_partida']) ?>
                                            <?php if ($jogo['hora_partida']): ?>
                                                <?= fmtHora($jogo['hora_partida']) ?>
                                            <?php endif; ?>
                                        </span>
                                    <?php else: ?>
                                        <span>×</span>
                                    <?php endif; ?>
                                </div>

                                <!-- Time B -->
                                <div class="bracket-time <?= $winB ? 'vencedor' : ($realizada && !$winB ? 'perdedor' : '') ?>">
                                    <div class="bracket-avatar">
                                        <?= avatar($jogo['time_b'] ?? '?') ?>
                                    </div>
                                    <span><?= htmlspecialchars($jogo['time_b'] ?: 'A definir') ?></span>
                                    <?php if ($realizada): ?>
                                        <strong class="bracket-placar"><?= $jogo['placar_time_b'] ?></strong>
                                    <?php endif; ?>
                                    <?php if ($winB): ?>
                                        <i class="fa-solid fa-check bracket-check"></i>
                                    <?php endif; ?>
                                </div>

                                <?php if (!empty($jogo['local_partida'])): ?>
                                <div class="bracket-local">
                                    <i class="fa-solid fa-location-dot"></i>
                                    <?= htmlspecialchars($jogo['local_partida']) ?>
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

        <!-- ══ TAB: TODAS AS PARTIDAS ══ -->
        <div class="tab-conteudo <?= !$temGrupos && !$temMataMata ? 'ativo' : '' ?>"
             id="tab-partidas">

            <?php if (empty($todasPartidas)): ?>
                <div class="empty-state">
                    <i class="fa-solid fa-calendar-xmark"></i>
                    <p>Nenhuma partida cadastrada ainda.</p>
                </div>
            <?php else: ?>
            <div class="partidas-lista">
                <?php $fasesExibir = ['grupos','oitavas','quartas','semi','terceiro_lugar','final'];
                foreach ($fasesExibir as $fase):
                    if (empty($partidas_fase[$fase])) continue;
                ?>
                <div class="partidas-secao reveal">
                    <div class="partidas-secao-titulo">
                        <?php if ($fase === 'final'): ?>
                            <i class="fa-solid fa-crown"></i>
                        <?php elseif ($fase === 'grupos'): ?>
                            <i class="fa-solid fa-table-cells"></i>
                        <?php else: ?>
                            <i class="fa-solid fa-trophy"></i>
                        <?php endif; ?>
                        <?= $faseLabel[$fase] ?>
                        <span class="partidas-count">
                            <?= count($partidas_fase[$fase]) ?> jogo(s)
                        </span>
                    </div>

                    <?php foreach ($partidas_fase[$fase] as $p):
                        $realizada = $p['status_partida'] === 'realizada';
                        $wo        = $p['status_partida'] === 'wo';
                        $winA      = !empty($p['vencedor']) && $p['vencedor'] === $p['time_a'];
                        $winB      = !empty($p['vencedor']) && $p['vencedor'] === $p['time_b'];
                    ?>
                    <div class="partida-row <?= $realizada ? 'realizada' : '' ?> <?= $fase === 'final' ? 'partida-final' : '' ?>">

                        <div class="pr-times">
                            <span class="pr-time <?= $winA ? 'vencedor' : '' ?>">
                                <span class="pr-avatar"><?= avatar($p['time_a']) ?></span>
                                <?= htmlspecialchars($p['time_a']) ?>
                            </span>

                            <div class="pr-placar">
                                <?php if ($realizada): ?>
                                    <strong><?= $p['placar_time_a'] ?></strong>
                                    <span class="pr-x">×</span>
                                    <strong><?= $p['placar_time_b'] ?></strong>
                                <?php elseif ($wo): ?>
                                    <span class="pr-wo">W.O.</span>
                                <?php else: ?>
                                    <span class="pr-vs">VS</span>
                                <?php endif; ?>
                            </div>

                            <span class="pr-time direita <?= $winB ? 'vencedor' : '' ?>">
                                <?= htmlspecialchars($p['time_b']) ?>
                                <span class="pr-avatar"><?= avatar($p['time_b']) ?></span>
                            </span>
                        </div>

                        <div class="pr-meta">
                            <span>
                                <i class="fa-solid fa-calendar"></i>
                                <?= fmtDataLong($p['data_partida']) ?>
                            </span>
                            <?php if ($p['hora_partida']): ?>
                                <span>
                                    <i class="fa-solid fa-clock"></i>
                                    <?= fmtHora($p['hora_partida']) ?>
                                </span>
                            <?php endif; ?>
                            <?php if ($p['local_partida']): ?>
                                <span>
                                    <i class="fa-solid fa-location-dot"></i>
                                    <?= htmlspecialchars($p['local_partida']) ?>
                                </span>
                            <?php endif; ?>
                            <?php if (!empty($p['grupo_partida'])): ?>
                                <span>
                                    <i class="fa-solid fa-layer-group"></i>
                                    Grupo <?= htmlspecialchars($p['grupo_partida']) ?>
                                </span>
                            <?php endif; ?>
                            <span class="pr-badge <?= $p['status_partida'] ?>">
                                <?= [
                                    'agendada'  => 'Agendada',
                                    'realizada' => 'Realizada',
                                    'cancelada' => 'Cancelada',
                                    'wo'        => 'W.O.',
                                ][$p['status_partida']] ?? ucfirst($p['status_partida']) ?>
                            </span>
                        </div>

                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <?php endif; // fim $esporte ?>
    </main>
</div>

<!-- ── BTN SIDEBAR MOBILE ── -->
<button class="sidebar-toggle-mobile" id="sidebarToggle" aria-label="Abrir menu">
    <i class="fa-solid fa-bars"></i>
</button>

<!-- ── SCRIPTS ── -->
<script src="/soee/src/frontend/scripts/classificacao.js"></script>

<?php include __DIR__ . '/../includes/end.php'; ?>
</body>
</html>