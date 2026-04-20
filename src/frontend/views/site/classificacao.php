<?php
session_start();
require_once __DIR__ . '/../../../backend/includes/conexao.php';
require_once __DIR__ . '/../../../backend/controllers/home.php';

// ── ID do esporte via GET ─────────────────────────────────
$esporteId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// ── LISTA DE ESPORTES NA SIDEBAR ──────────────────────────
$stmtEsportes = $conn->query("
    SELECT m.id_modalidade, m.nome_modalidade, m.tipo_modalidade,
           em.status_edicao_modalidade
    FROM modalidade m
    LEFT JOIN edicao_modalidade em ON em.modalidade_id_modalidade = m.id_modalidade
        AND em.status_edicao_modalidade IN ('inscricoes','em_andamento')
    WHERE m.ativo_modalidade = 1
    GROUP BY m.id_modalidade
    ORDER BY m.nome_modalidade ASC
");
$esportes = $stmtEsportes->fetchAll(PDO::FETCH_ASSOC);

// Se não veio ID, usa o primeiro da lista
if (!$esporteId && !empty($esportes)) {
    $esporteId = (int) $esportes[0]['id_modalidade'];
}

// ── DADOS DO ESPORTE SELECIONADO ──────────────────────────
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

$emId = $esporte['id_edicao_modalidade'] ?? null;

// ── CLASSIFICAÇÃO / GRUPOS ────────────────────────────────
$grupos = [];
if ($emId) {
    $stmtClass = $conn->prepare("
        SELECT cl.pontos, cl.vitorias, cl.derrotas, cl.empates,
               cl.jogos, cl.saldo, cl.pontos_pro, cl.pontos_contra,
               cl.grupo_classificacao,
               t.nome_turma, t.id_turma
        FROM classificacao cl
        INNER JOIN turma t ON t.id_turma = cl.turma_id_turma
        WHERE cl.edicao_modalidade_id = :emid
        ORDER BY cl.grupo_classificacao ASC, cl.pontos DESC,
                 cl.saldo DESC, cl.vitorias DESC
    ");
    $stmtClass->execute([':emid' => $emId]);
    $classificacoes = $stmtClass->fetchAll(PDO::FETCH_ASSOC);

    foreach ($classificacoes as $row) {
        $grupo = $row['grupo_classificacao'] ?: 'A';
        $grupos[$grupo][] = $row;
    }
}

// ── PARTIDAS POR FASE ─────────────────────────────────────
$partidas_fase = [];
if ($emId) {
    $stmtPartidas = $conn->prepare("
        SELECT p.id_partida, p.data_partida, p.hora_partida,
               p.local_partida, p.fase_partida, p.status_partida,
               p.grupo_partida,
               ta.nome_turma AS time_a, tb.nome_turma AS time_b,
               p.turma_id_time_a, p.turma_id_time_b,
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
    $stmtPartidas->execute([':emid' => $emId]);
    $todasPartidas = $stmtPartidas->fetchAll(PDO::FETCH_ASSOC);

    foreach ($todasPartidas as $p) {
        $partidas_fase[$p['fase_partida']][] = $p;
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
$faseOrdem = ['oitavas', 'quartas', 'semi', 'terceiro_lugar', 'final'];

function fmtData($d) { return $d ? date('d/m', strtotime($d)) : '—'; }
function fmtHora($h) { return $h ? substr($h, 0, 5) : ''; }

$icone = $tipoIcons[$esporte['tipo_modalidade'] ?? 'outro'] ?? 'fa-medal';
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
</head>
<body>

<!-- CURSOR -->
<div class="cursor-dot" id="cursorDot"></div>
<div class="cursor-ring" id="cursorRing"></div>

<!-- LOADER -->
<div id="loader">
    <div class="loader-inner">
        <div class="loader-logo-text">SOEE</div>
        <div class="loader-logo-sub">Carregando campeonato</div>
        <div class="loader-bar"><div class="loader-bar-fill"></div></div>
    </div>
</div>

<!-- TOPBAR -->
<header class="topbar">
    <div class="topbar-inner">
        <a href="/soee/src/frontend/views/site/home.php" class="topbar-logo">
            S<span>O</span>EE
        </a>
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
            <?php if (isset($_SESSION['usuario_id'])): ?>
            <a href="<?php
                $tipo = $_SESSION['usuario_tipo'] ?? '';
                if ($tipo === 'professor') echo '/soee/src/frontend/views/dashboards/professor.php';
                elseif ($tipo === 'adm_sala') echo '/soee/src/frontend/views/dashboards/adm-sala.php';
                else echo '/soee/src/frontend/views/dashboards/aluno.php';
            ?>" class="btn-dash-top">
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

<!-- LAYOUT PRINCIPAL -->
<div class="app-layout">

    <!-- ══ SIDEBAR ══ -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-titulo">
            <i class="fa-solid fa-layer-group"></i>
            Modalidades
        </div>
        <nav class="sidebar-nav">
            <?php foreach ($esportes as $esp):
                $ico  = $tipoIcons[$esp['tipo_modalidade']] ?? 'fa-medal';
                $ativo = $esp['id_modalidade'] == $esporteId;
                $status = $esp['status_edicao_modalidade'];
            ?>
            <a href="?id=<?= $esp['id_modalidade'] ?>"
               class="sidebar-item <?= $ativo ? 'ativo' : '' ?>">
                <div class="sidebar-item-icone">
                    <i class="fa-solid <?= $ico ?>"></i>
                </div>
                <div class="sidebar-item-info">
                    <span class="sidebar-item-nome"><?= htmlspecialchars($esp['nome_modalidade']) ?></span>
                    <?php if ($status === 'em_andamento'): ?>
                        <span class="sidebar-badge andamento">Em andamento</span>
                    <?php elseif ($status === 'inscricoes'): ?>
                        <span class="sidebar-badge inscricoes">Inscrições</span>
                    <?php else: ?>
                        <span class="sidebar-badge encerrado">Encerrado</span>
                    <?php endif; ?>
                </div>
                <?php if ($ativo): ?>
                    <div class="sidebar-ativo-bar"></div>
                <?php endif; ?>
            </a>
            <?php endforeach; ?>
        </nav>
    </aside>

    <!-- ══ CONTEÚDO ══ -->
    <main class="conteudo" id="conteudo">

        <?php if (!$esporte): ?>
        <div class="empty-page">
            <i class="fa-solid fa-futbol"></i>
            <p>Nenhum esporte encontrado.</p>
        </div>
        <?php else: ?>

        <!-- HERO DO ESPORTE -->
        <div class="esporte-hero">
            <div class="hero-bg"></div>
            <div class="hero-inner">
                <div class="hero-icone">
                    <i class="fa-solid <?= $icone ?>"></i>
                </div>
                <div class="hero-texto">
                    <h1><?= htmlspecialchars($esporte['nome_modalidade']) ?></h1>
                    <?php if (!empty($esporte['descricao_modalidade'])): ?>
                        <p><?= htmlspecialchars($esporte['descricao_modalidade']) ?></p>
                    <?php endif; ?>
                    <div class="hero-meta">
                        <span><i class="fa-solid fa-users"></i> <?= $esporte['qtd_min_jogadores'] ?>–<?= $esporte['qtd_max_jogadores'] ?> jogadores</span>
                        <span><i class="fa-solid fa-sitemap"></i> <?= ucfirst(str_replace('_', ' ', $esporte['formato_modalidade'] ?? '')) ?></span>
                        <?php if (!empty($esporte['status_edicao_modalidade'])): ?>
                        <span class="hero-status <?= $esporte['status_edicao_modalidade'] ?>">
                            <i class="fa-solid fa-circle"></i>
                            <?= $esporte['status_edicao_modalidade'] === 'em_andamento' ? 'Em Andamento' : 'Inscrições Abertas' ?>
                        </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- ══ TABS ══ -->
        <div class="tabs-bar">
            <button class="tab ativo" data-tab="grupos" onclick="trocarTab(this, 'grupos')">
                <i class="fa-solid fa-table-cells"></i> Fase de Grupos
            </button>
            <button class="tab" data-tab="chaveamento" onclick="trocarTab(this, 'chaveamento')">
                <i class="fa-solid fa-trophy"></i> Mata-Mata
            </button>
            <button class="tab" data-tab="partidas" onclick="trocarTab(this, 'partidas')">
                <i class="fa-solid fa-calendar-days"></i> Partidas
            </button>
        </div>

        <!-- ══ TAB: GRUPOS ══ -->
        <div class="tab-conteudo ativo" id="tab-grupos">
            <?php if (empty($grupos)): ?>
            <div class="empty-state">
                <i class="fa-solid fa-table-cells"></i>
                <p>Nenhum dado de classificação disponível ainda.</p>
            </div>
            <?php else: ?>
            <div class="grupos-grid">
                <?php foreach ($grupos as $nomeGrupo => $times): ?>
                <div class="grupo-card reveal">
                    <div class="grupo-header">
                        <div class="grupo-letra"><?= htmlspecialchars($nomeGrupo) ?></div>
                        <span class="grupo-titulo">Grupo <?= htmlspecialchars($nomeGrupo) ?></span>
                    </div>
                    <table class="grupo-tabela">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Time</th>
                                <th title="Jogos">J</th>
                                <th title="Vitórias">V</th>
                                <th title="Empates">E</th>
                                <th title="Derrotas">D</th>
                                <th title="Saldo">SG</th>
                                <th title="Pontos">PTS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($times as $pos => $time): ?>
                            <tr class="<?= $pos < 2 ? 'classificado' : '' ?> <?= $pos === 0 ? 'lider' : '' ?>">
                                <td class="pos-col">
                                    <?php if ($pos === 0): ?>
                                        <span class="pos-badge ouro">1</span>
                                    <?php elseif ($pos === 1): ?>
                                        <span class="pos-badge prata">2</span>
                                    <?php else: ?>
                                        <span class="pos-badge"><?= $pos + 1 ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="time-col">
                                    <div class="time-avatar"><?= mb_strtoupper(mb_substr($time['nome_turma'], 0, 2)) ?></div>
                                    <span><?= htmlspecialchars($time['nome_turma']) ?></span>
                                </td>
                                <td><?= $time['jogos'] ?></td>
                                <td class="verde"><?= $time['vitorias'] ?></td>
                                <td><?= $time['empates'] ?></td>
                                <td class="vermelho"><?= $time['derrotas'] ?></td>
                                <td><?= $time['saldo'] >= 0 ? '+'.$time['saldo'] : $time['saldo'] ?></td>
                                <td class="pts"><?= $time['pontos'] ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <!-- Jogos do grupo -->
                    <?php if (!empty($partidas_fase['grupos'])): ?>
                    <div class="grupo-jogos">
                        <?php
                        $jogos_grupo = array_filter($partidas_fase['grupos'], fn($p) => $p['grupo_partida'] === $nomeGrupo);
                        foreach ($jogos_grupo as $jogo):
                            $realizada = $jogo['status_partida'] === 'realizada';
                        ?>
                        <div class="jogo-mini <?= $realizada ? 'realizada' : '' ?>">
                            <span class="jogo-time <?= $jogo['vencedor'] === $jogo['time_a'] ? 'vencedor' : '' ?>"><?= htmlspecialchars($jogo['time_a']) ?></span>
                            <div class="jogo-placar">
                                <?php if ($realizada): ?>
                                    <strong><?= $jogo['placar_time_a'] ?></strong>
                                    <span>×</span>
                                    <strong><?= $jogo['placar_time_b'] ?></strong>
                                <?php else: ?>
                                    <span class="jogo-data"><?= fmtData($jogo['data_partida']) ?></span>
                                    <?php if ($jogo['hora_partida']): ?>
                                        <span class="jogo-hora"><?= fmtHora($jogo['hora_partida']) ?></span>
                                    <?php endif; ?>
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

        <!-- ══ TAB: MATA-MATA ══ -->
        <div class="tab-conteudo" id="tab-chaveamento">
            <?php
            $temMata = false;
            foreach ($faseOrdem as $f) {
                if (!empty($partidas_fase[$f])) { $temMata = true; break; }
            }
            ?>
            <?php if (!$temMata): ?>
            <div class="empty-state">
                <i class="fa-solid fa-trophy"></i>
                <p>O mata-mata ainda não começou.</p>
            </div>
            <?php else: ?>
            <div class="chaveamento-wrap">
                <div class="bracket">
                    <?php foreach ($faseOrdem as $fase):
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
                            <div class="bracket-jogo <?= $isFinal ? 'jogo-final' : '' ?> <?= $realizada ? 'realizada' : '' ?>">
                                <div class="bracket-time <?= $jogo['vencedor'] === $jogo['time_a'] ? 'vencedor' : '' ?> <?= (!$realizada && !$jogo['time_a']) ? 'vazio' : '' ?>">
                                    <div class="bracket-time-avatar">
                                        <?= $jogo['time_a'] ? mb_strtoupper(mb_substr($jogo['time_a'], 0, 2)) : '?' ?>
                                    </div>
                                    <span><?= htmlspecialchars($jogo['time_a'] ?: 'A definir') ?></span>
                                    <?php if ($realizada): ?>
                                        <strong class="bracket-placar"><?= $jogo['placar_time_a'] ?></strong>
                                    <?php endif; ?>
                                    <?php if ($jogo['vencedor'] === $jogo['time_a']): ?>
                                        <i class="fa-solid fa-check bracket-check"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="bracket-vs">
                                    <?php if (!$realizada && !$wo): ?>
                                        <span class="bracket-data-hora">
                                            <?= fmtData($jogo['data_partida']) ?>
                                            <?= fmtHora($jogo['hora_partida']) ?>
                                        </span>
                                    <?php elseif ($wo): ?>
                                        <span class="bracket-wo">W.O.</span>
                                    <?php else: ?>
                                        <span>×</span>
                                    <?php endif; ?>
                                </div>
                                <div class="bracket-time <?= $jogo['vencedor'] === $jogo['time_b'] ? 'vencedor' : '' ?> <?= (!$realizada && !$jogo['time_b']) ? 'vazio' : '' ?>">
                                    <div class="bracket-time-avatar">
                                        <?= $jogo['time_b'] ? mb_strtoupper(mb_substr($jogo['time_b'], 0, 2)) : '?' ?>
                                    </div>
                                    <span><?= htmlspecialchars($jogo['time_b'] ?: 'A definir') ?></span>
                                    <?php if ($realizada): ?>
                                        <strong class="bracket-placar"><?= $jogo['placar_time_b'] ?></strong>
                                    <?php endif; ?>
                                    <?php if ($jogo['vencedor'] === $jogo['time_b']): ?>
                                        <i class="fa-solid fa-check bracket-check"></i>
                                    <?php endif; ?>
                                </div>
                                <?php if ($jogo['local_partida']): ?>
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

        <!-- ══ TAB: TODAS AS PARTIDAS ══ -->
        <div class="tab-conteudo" id="tab-partidas">
            <?php if (empty($todasPartidas ?? [])): ?>
            <div class="empty-state">
                <i class="fa-solid fa-calendar-xmark"></i>
                <p>Nenhuma partida cadastrada ainda.</p>
            </div>
            <?php else: ?>
            <div class="partidas-lista">
                <?php
                // Agrupa por fase para exibição
                $fasesExibir = ['grupos', 'oitavas', 'quartas', 'semi', 'terceiro_lugar', 'final'];
                foreach ($fasesExibir as $fase):
                    if (empty($partidas_fase[$fase])) continue;
                ?>
                <div class="partidas-secao reveal">
                    <div class="partidas-secao-titulo">
                        <?= $fase === 'final' ? '<i class="fa-solid fa-crown"></i>' : '<i class="fa-solid fa-circle-dot"></i>' ?>
                        <?= $faseLabel[$fase] ?? ucfirst($fase) ?>
                    </div>
                    <?php foreach ($partidas_fase[$fase] as $p):
                        $realizada = $p['status_partida'] === 'realizada';
                        $agendada  = $p['status_partida'] === 'agendada';
                        $wo        = $p['status_partida'] === 'wo';
                    ?>
                    <div class="partida-row <?= $realizada ? 'realizada' : '' ?> <?= $fase === 'final' ? 'partida-final' : '' ?>">
                        <div class="pr-status-dot <?= $p['status_partida'] ?>"></div>
                        <div class="pr-times">
                            <span class="pr-time <?= $p['vencedor'] === $p['time_a'] ? 'vencedor' : '' ?>">
                                <?= htmlspecialchars($p['time_a']) ?>
                            </span>
                            <div class="pr-placar">
                                <?php if ($realizada): ?>
                                    <strong><?= $p['placar_time_a'] ?></strong>
                                    <span>×</span>
                                    <strong><?= $p['placar_time_b'] ?></strong>
                                <?php elseif ($wo): ?>
                                    <span class="pr-wo">W.O.</span>
                                <?php else: ?>
                                    <span class="pr-vs">VS</span>
                                <?php endif; ?>
                            </div>
                            <span class="pr-time direita <?= $p['vencedor'] === $p['time_b'] ? 'vencedor' : '' ?>">
                                <?= htmlspecialchars($p['time_b']) ?>
                            </span>
                        </div>
                        <div class="pr-meta">
                            <span><i class="fa-solid fa-calendar"></i> <?= date('d/m/Y', strtotime($p['data_partida'])) ?></span>
                            <?php if ($p['hora_partida']): ?>
                                <span><i class="fa-solid fa-clock"></i> <?= fmtHora($p['hora_partida']) ?></span>
                            <?php endif; ?>
                            <?php if ($p['local_partida']): ?>
                                <span><i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars($p['local_partida']) ?></span>
                            <?php endif; ?>
                            <?php if ($p['grupo_partida']): ?>
                                <span><i class="fa-solid fa-layer-group"></i> Grupo <?= htmlspecialchars($p['grupo_partida']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <?php endif; ?>
    </main>
</div>

<!-- BTN TOGGLE SIDEBAR MOBILE -->
<button class="sidebar-toggle-mobile" id="sidebarToggle" onclick="document.getElementById('sidebar').classList.toggle('aberta')" aria-label="Menu">
    <i class="fa-solid fa-bars"></i>
</button>

<script src="/soee/src/frontend/scripts/classificacao.js"></script>
</body>
</html>