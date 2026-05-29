<?php
ob_start();
session_start();
require_once __DIR__ . '/../../../backend/includes/conexao.php';
require_once __DIR__ . '/../../../backend/controllers/home.php';

AuthHome::exigirLogin();

$userTipo     = AuthHome::getTipo();
$userId       = AuthHome::getId();
$dashboardUrl = AuthHome::getRota($userTipo);

$turmaId = isset($_GET['turma']) ? (int)$_GET['turma'] : 0;
$emId    = isset($_GET['em'])    ? (int)$_GET['em']    : 0;

if ($turmaId <= 0 || $emId <= 0) {
    header('Location: ' . $dashboardUrl);
    exit;
}

// ── Dados da turma ────────────────────────────────────────────────────────────
$stmtTurma = $conn->prepare("
    SELECT t.id_turma, t.nome_turma, t.periodo_turma,
           c.nome_curso, c.sigla_curso
    FROM turma t
    LEFT JOIN curso c ON c.id_curso = t.curso_id_curso
    WHERE t.id_turma = :id
");
$stmtTurma->execute([':id' => $turmaId]);
$turma = $stmtTurma->fetch(PDO::FETCH_ASSOC);

if (!$turma) {
    header('Location: ' . $dashboardUrl);
    exit;
}

// ── Dados da modalidade / edição ─────────────────────────────────────────────
$stmtMod = $conn->prepare("
    SELECT m.nome_modalidade, m.tipo_modalidade, m.tipo_participacao,
           m.genero_modalidade, e.nome_edicao, em.status_edicao_modalidade
    FROM edicao_modalidade em
    INNER JOIN modalidade m ON m.id_modalidade = em.modalidade_id_modalidade
    INNER JOIN edicao e     ON e.id_edicao     = em.edicao_id_edicao
    WHERE em.id_edicao_modalidade = :emid
");
$stmtMod->execute([':emid' => $emId]);
$modalidade = $stmtMod->fetch(PDO::FETCH_ASSOC);

if (!$modalidade) {
    header('Location: ' . $dashboardUrl);
    exit;
}

// ── Elenco do time ────────────────────────────────────────────────────────────
$stmtElenco = $conn->prepare("
    SELECT u.id_usuario, u.nome_usuario, u.foto_perfil_usuario,
           u.genero_usuario,
           i.numero_camisa_inscricao, i.nome_camisa_inscricao,
           i.posicao_inscricao, i.capitao_inscricao
    FROM inscricao i
    INNER JOIN usuario u ON u.id_usuario = i.usuario_id_usuario
    WHERE i.edicao_modalidade_id = :emid
      AND u.turma_id_turma       = :turma
      AND i.status_inscricao     = 'ativa'
    ORDER BY i.capitao_inscricao DESC,
             i.numero_camisa_inscricao ASC,
             u.nome_usuario ASC
");
$stmtElenco->execute([':emid' => $emId, ':turma' => $turmaId]);
$elenco = $stmtElenco->fetchAll(PDO::FETCH_ASSOC);

// ── Classificação do time nesta modalidade ────────────────────────────────────
$stmtCl = $conn->prepare("
    SELECT pontos, vitorias, derrotas, empates, jogos,
           saldo, pontos_pro, pontos_contra
    FROM classificacao
    WHERE edicao_modalidade_id = :emid AND turma_id_turma = :turma
    LIMIT 1
");
$stmtCl->execute([':emid' => $emId, ':turma' => $turmaId]);
$classificacao = $stmtCl->fetch(PDO::FETCH_ASSOC);

// ── Próxima partida do time ───────────────────────────────────────────────────
$stmtProx = $conn->prepare("
    SELECT p.data_partida, p.hora_partida, p.local_partida,
           p.fase_partida, ta.nome_turma AS time_a, tb.nome_turma AS time_b,
           p.turma_id_time_a, p.turma_id_time_b
    FROM partida p
    INNER JOIN turma ta ON ta.id_turma = p.turma_id_time_a
    INNER JOIN turma tb ON tb.id_turma = p.turma_id_time_b
    WHERE p.edicao_modalidade_id = :emid
      AND (p.turma_id_time_a = :t1 OR p.turma_id_time_b = :t2)
      AND p.status_partida = 'agendada'
      AND p.data_partida >= CURRENT_DATE
    ORDER BY p.data_partida ASC, p.hora_partida ASC
    LIMIT 1
");
// CURDATE() → CURRENT_DATE  (padrão SQL, funciona no PostgreSQL)
$stmtProx->execute([':emid' => $emId, ':t1' => $turmaId, ':t2' => $turmaId]);
$proximaPartida = $stmtProx->fetch(PDO::FETCH_ASSOC);

$tipoIcone = [
    'quadra' => 'fa-basketball',
    'mesa'   => 'fa-table-tennis-paddle-ball',
    'outro'  => 'fa-trophy',
];

$faseLabel = [
    'grupos'        => 'Fase de Grupos',
    'oitavas'       => 'Oitavas de Final',
    'quartas'       => 'Quartas de Final',
    'semi'          => 'Semifinal',
    'final'         => 'Final',
    'terceiro_lugar'=> 'Disputa de 3º Lugar',
];

$generoLabel = ['m' => 'Masculino', 'f' => 'Feminino', 'n' => ''];

$totalJogadores = count($elenco);
$capitao = null;
foreach ($elenco as $j) {
    if ($j['capitao_inscricao']) { $capitao = $j; break; }
}
?>
<?php include __DIR__ . '/../includes/doctype.php'; ?>
<head>
    <title>SOEE | <?= htmlspecialchars($turma['nome_turma']) ?> — <?= htmlspecialchars($modalidade['nome_modalidade']) ?></title>
    <!-- Reutiliza o mesmo CSS do perfil -->
    <link rel="stylesheet" href="/soee/src/frontend/styles/user-conta.css">
    <style>
        /* ── Layout principal ── */
        .team-main {
            max-width: 780px;
            margin: 0 auto;
            padding: 40px 24px 80px;
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        /* ── Stats bar ── */
        .stats-bar {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1px;
            background: var(--borda);
            border-radius: var(--rm);
            overflow: hidden;
            border: 1px solid var(--borda);
        }
        .stat-cell {
            background: var(--bloco);
            padding: 18px 12px;
            text-align: center;
            transition: background .2s;
        }
        .stat-cell:hover { background: var(--fundo); }
        .stat-cell .val {
            display: block;
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            font-weight: 800;
            line-height: 1;
            margin-bottom: 4px;
        }
        .stat-cell .lbl {
            font-size: .62rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .1em;
            color: var(--texto-2);
        }
        .stat-cell.v .val { color: #22c55e; }
        .stat-cell.e .val { color: #f59e0b; }
        .stat-cell.d .val { color: #ef4444; }
        .stat-cell.p .val { color: var(--azul-2); }

        /* ── Próxima partida ── */
        .proxima-banner {
            border-radius: var(--r);
            background: linear-gradient(135deg, #0a2233, #1e5671 55%, #2c7da3);
            padding: 24px 28px;
            color: white;
            position: relative;
            overflow: hidden;
        }
        .proxima-banner::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: linear-gradient(rgba(255,255,255,.03) 1px, transparent 1px),
                              linear-gradient(90deg, rgba(255,255,255,.03) 1px, transparent 1px);
            background-size: 40px 40px;
            pointer-events: none;
        }
        .proxima-banner .prx-label {
            font-size: .65rem;
            text-transform: uppercase;
            letter-spacing: .15em;
            opacity: .6;
            margin-bottom: 10px;
        }
        .proxima-banner .prx-times {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 1.15rem;
            font-weight: 800;
            margin-bottom: 14px;
            flex-wrap: wrap;
        }
        .proxima-banner .prx-vs {
            font-size: .8rem;
            opacity: .45;
            font-weight: 400;
        }
        .proxima-banner .prx-mine { color: #fbbf24; }
        .proxima-banner .prx-meta {
            display: flex;
            gap: 16px;
            font-size: .78rem;
            opacity: .7;
            flex-wrap: wrap;
        }
        .proxima-banner .prx-meta span {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        /* ── Elenco grid ── */
        .elenco-grid {
            padding: 16px 20px 20px;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(210px, 1fr));
            gap: 12px;
        }

        .jogador-card {
            background: var(--fundo);
            border: 1px solid var(--borda);
            border-radius: 14px;
            padding: 16px 14px;
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: var(--texto);
            transition: var(--tr);
            position: relative;
            overflow: hidden;
        }
        .jogador-card::after {
            content: '';
            position: absolute; bottom: 0; left: 0; right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--laranja), var(--azul-2));
            transform: scaleX(0);
            transform-origin: left;
            transition: transform .3s ease;
        }
        .jogador-card:hover {
            border-color: var(--azul-2);
            transform: translateY(-4px);
            box-shadow: 0 12px 32px rgba(0,0,0,.1);
            background: var(--bloco);
        }
        .jogador-card:hover::after { transform: scaleX(1); }
        .jogador-card.capitao-card {
            border-color: rgba(245,158,11,.4);
            background: rgba(245,158,11,.04);
        }
        .jogador-card.capitao-card::after {
            background: linear-gradient(90deg, #f59e0b, var(--laranja));
        }

        .jog-avatar {
            width: 44px; height: 44px;
            border-radius: 50%;
            flex-shrink: 0;
            background: linear-gradient(135deg, var(--azul), var(--azul-2));
            display: flex; align-items: center; justify-content: center;
            font-family: 'Playfair Display', serif;
            font-size: 1rem;
            font-weight: 800;
            color: white;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(30,86,113,.25);
        }
        .jog-avatar img { width:100%; height:100%; object-fit:cover; }

        .jog-info { flex: 1; min-width: 0; }
        .jog-nome {
            font-size: .85rem;
            font-weight: 700;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            color: var(--texto);
        }
        .jog-sub {
            font-size: .7rem;
            color: var(--texto-2);
            margin-top: 2px;
            display: flex;
            align-items: center;
            gap: 4px;
            flex-wrap: wrap;
        }

        .jog-camisa {
            flex-shrink: 0;
            width: 32px; height: 32px;
            border-radius: 8px;
            background: linear-gradient(135deg, var(--azul), var(--azul-2));
            display: flex; align-items: center; justify-content: center;
            font-family: 'Playfair Display', serif;
            font-size: .75rem;
            font-weight: 800;
            color: white;
        }

        .badge-cap {
            display: inline-flex;
            align-items: center;
            gap: 3px;
            font-size: .62rem;
            font-weight: 700;
            color: #f59e0b;
            letter-spacing: .04em;
        }

        /* ── Capitão destaque ── */
        .capitao-destaque {
            padding: 20px 24px;
            display: flex;
            align-items: center;
            gap: 16px;
            border-bottom: 1px solid var(--borda);
        }
        .cap-avatar {
            width: 56px; height: 56px;
            border-radius: 50%;
            background: linear-gradient(135deg, #f59e0b, var(--laranja));
            display: flex; align-items: center; justify-content: center;
            font-family: 'Playfair Display', serif;
            font-size: 1.3rem; font-weight: 800;
            color: white; overflow: hidden;
            box-shadow: 0 6px 20px rgba(245,158,11,.35);
            flex-shrink: 0;
        }
        .cap-avatar img { width:100%; height:100%; object-fit:cover; }
        .cap-info { flex: 1; }
        .cap-lbl {
            font-size: .62rem; text-transform: uppercase;
            letter-spacing: .12em; color: #f59e0b;
            font-weight: 700; margin-bottom: 3px;
        }
        .cap-nome {
            font-family: 'Playfair Display', serif;
            font-size: 1rem; font-weight: 800;
            color: var(--texto);
        }
        .cap-camisa {
            font-size: .75rem; color: var(--texto-2); margin-top: 2px;
        }

        /* ── Info do time (header badges) ── */
        .team-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            padding: 16px 24px;
            border-bottom: 1px solid var(--borda);
        }
        .team-badge {
            display: inline-flex; align-items: center; gap: 5px;
            font-size: .7rem; font-weight: 600;
            padding: 4px 12px; border-radius: 999px;
        }
        .team-badge.mod   { background: rgba(44,125,163,.1); color: var(--azul-2); border: 1px solid rgba(44,125,163,.2); }
        .team-badge.edicao { background: rgba(255,77,18,.08); color: var(--laranja); border: 1px solid rgba(255,77,18,.2); }
        .team-badge.total { background: rgba(34,197,94,.08); color: #16a34a; border: 1px solid rgba(34,197,94,.2); }

        /* ── Vazio ── */
        .vazio-time {
            padding: 48px 24px;
            text-align: center;
            color: var(--texto-2);
        }
        .vazio-time i { font-size: 2.5rem; display: block; margin-bottom: 14px; opacity: .25; }

        @media (max-width: 600px) {
            .team-main { padding: 24px 14px 60px; }
            .elenco-grid { grid-template-columns: 1fr; }
            .stats-bar { grid-template-columns: repeat(2, 1fr); }
        }
    </style>
    <?php include __DIR__ . '/../includes/head.php'; ?>
</head>
<body>

<div class="cursor-dot" id="cursorDot"></div>
<div class="cursor-ring" id="cursorRing"></div>

<!-- TOPBAR -->
<header class="conta-topbar">
    <a href="javascript:history.back()" class="topbar-back">
        <i class="fa-solid fa-arrow-left"></i> Voltar
    </a>
    <div class="topbar-logo">S<span>O</span>EE</div>
    <button class="btn-icone-topo" id="toggleTema" title="Alternar tema">
        <i class="fa-solid fa-moon" id="iconeTema"></i>
    </button>
</header>

<!-- HERO do time -->
<div class="conta-hero">
    <div class="hero-grid"></div>
    <div class="hero-particles"><span></span><span></span><span></span></div>
    <div class="hero-conteudo">
        <div class="hero-avatar-wrap">
            <div class="hero-avatar" style="font-size:2.8rem;">
                <i class="fa-solid <?= $tipoIcone[$modalidade['tipo_modalidade']] ?? 'fa-trophy' ?>"></i>
            </div>
        </div>
        <div class="hero-info">
            <div class="hero-tipo-badge">
                <i class="fa-solid fa-shield-halved"></i>
                <?= htmlspecialchars($modalidade['nome_modalidade']) ?>
            </div>
            <h1 class="hero-nome"><?= htmlspecialchars($turma['nome_turma']) ?></h1>
            <div style="display:flex;flex-wrap:wrap;gap:8px;margin-top:4px;">
                <?php if (!empty($turma['sigla_curso'])): ?>
                <div class="hero-turma" style="margin:0;">
                    <i class="fa-solid fa-book"></i>
                    <?= htmlspecialchars($turma['sigla_curso']) ?>
                    <?= !empty($turma['periodo_turma']) ? '· '.ucfirst($turma['periodo_turma']) : '' ?>
                </div>
                <?php endif; ?>
                <div class="hero-turma" style="margin:0;">
                    <i class="fa-solid fa-users"></i>
                    <?= $totalJogadores ?> jogador<?= $totalJogadores !== 1 ? 'es' : '' ?>
                </div>
            </div>
        </div>
    </div>
</div>

<main class="team-main">

    <!-- ══ Stats (só se tiver classificação) ══ -->
    <?php if ($classificacao): ?>
    <div class="stats-bar reveal reveal-delay-1">
        <div class="stat-cell v">
            <span class="val"><?= $classificacao['vitorias'] ?></span>
            <span class="lbl">Vitórias</span>
        </div>
        <div class="stat-cell e">
            <span class="val"><?= $classificacao['empates'] ?></span>
            <span class="lbl">Empates</span>
        </div>
        <div class="stat-cell d">
            <span class="val"><?= $classificacao['derrotas'] ?></span>
            <span class="lbl">Derrotas</span>
        </div>
        <div class="stat-cell p">
            <span class="val"><?= $classificacao['pontos'] ?></span>
            <span class="lbl">Pontos</span>
        </div>
    </div>
    <?php endif; ?>

    <!-- ══ Próxima partida ══ -->
    <?php if ($proximaPartida): ?>
    <div class="proxima-banner reveal reveal-delay-2">
        <div class="prx-label"><i class="fa-solid fa-clock"></i> Próxima Partida</div>
        <div class="prx-times">
            <span class="<?= $proximaPartida['turma_id_time_a'] == $turmaId ? 'prx-mine' : '' ?>">
                <?= htmlspecialchars($proximaPartida['time_a']) ?>
            </span>
            <span class="prx-vs">VS</span>
            <span class="<?= $proximaPartida['turma_id_time_b'] == $turmaId ? 'prx-mine' : '' ?>">
                <?= htmlspecialchars($proximaPartida['time_b']) ?>
            </span>
        </div>
        <div class="prx-meta">
            <span><i class="fa-solid fa-calendar"></i> <?= date('d/m/Y', strtotime($proximaPartida['data_partida'])) ?></span>
            <span><i class="fa-solid fa-clock"></i> <?= substr($proximaPartida['hora_partida'], 0, 5) ?></span>
            <?php if ($proximaPartida['local_partida']): ?>
            <span><i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars($proximaPartida['local_partida']) ?></span>
            <?php endif; ?>
            <span><i class="fa-solid fa-flag"></i> <?= $faseLabel[$proximaPartida['fase_partida']] ?? ucfirst($proximaPartida['fase_partida']) ?></span>
        </div>
    </div>
    <?php endif; ?>

    <!-- ══ Elenco ══ -->
    <section class="conta-secao reveal reveal-delay-3">
        <div class="secao-header">
            <i class="fa-solid fa-people-group"></i>
            <h2>Elenco</h2>
        </div>

        <!-- Badges de info -->
        <div class="team-badges">
            <span class="team-badge mod">
                <i class="fa-solid <?= $tipoIcone[$modalidade['tipo_modalidade']] ?? 'fa-trophy' ?>"></i>
                <?= htmlspecialchars($modalidade['nome_modalidade']) ?>
            </span>
            <span class="team-badge edicao">
                <i class="fa-solid fa-flag"></i>
                <?= htmlspecialchars($modalidade['nome_edicao']) ?>
            </span>
            <span class="team-badge total">
                <i class="fa-solid fa-users"></i>
                <?= $totalJogadores ?> jogador<?= $totalJogadores !== 1 ? 'es' : '' ?>
            </span>
        </div>

        <!-- Capitão em destaque -->
        <?php if ($capitao): ?>
        <a href="/soee/src/frontend/views/site/otherprofile.php?id=<?= $capitao['id_usuario'] ?>"
           class="capitao-destaque" style="text-decoration:none;display:flex;">
            <div class="cap-avatar">
                <?php if (!empty($capitao['foto_perfil_usuario'])): ?>
                    <img src="<?= htmlspecialchars($capitao['foto_perfil_usuario']) ?>" alt="">
                <?php else: ?>
                    <?= mb_strtoupper(mb_substr($capitao['nome_usuario'], 0, 2)) ?>
                <?php endif; ?>
            </div>
            <div class="cap-info">
                <div class="cap-lbl"><i class="fa-solid fa-star"></i> Capitão</div>
                <div class="cap-nome"><?= htmlspecialchars($capitao['nome_usuario']) ?></div>
                <div class="cap-camisa">
                    <?php if (!empty($capitao['nome_camisa_inscricao'])): ?>
                        <i class="fa-solid fa-shirt" style="font-size:.65rem"></i>
                        <?= htmlspecialchars(strtoupper($capitao['nome_camisa_inscricao'])) ?>
                    <?php endif; ?>
                    <?php if ($capitao['numero_camisa_inscricao']): ?>
                        &nbsp;#<?= $capitao['numero_camisa_inscricao'] ?>
                    <?php endif; ?>
                </div>
            </div>
            <div style="display:flex;align-items:center;color:var(--texto-2);font-size:.8rem;gap:4px;flex-shrink:0;">
                Ver perfil <i class="fa-solid fa-chevron-right" style="font-size:.7rem"></i>
            </div>
        </a>
        <?php endif; ?>

        <!-- Grid de jogadores -->
        <?php if (empty($elenco)): ?>
        <div class="vazio-time">
            <i class="fa-solid fa-user-slash"></i>
            Nenhum jogador inscrito neste time ainda.
        </div>
        <?php else: ?>
        <div class="elenco-grid">
            <?php foreach ($elenco as $j):
                $primeiroNome = explode(' ', $j['nome_usuario'])[0];
                $iniciais = mb_strtoupper(mb_substr($j['nome_usuario'], 0, 2));
            ?>
            <a href="/soee/src/frontend/views/site/otherprofile.php?id=<?= $j['id_usuario'] ?>"
               class="jogador-card <?= $j['capitao_inscricao'] ? 'capitao-card' : '' ?>">

                <div class="jog-avatar">
                    <?php if (!empty($j['foto_perfil_usuario'])): ?>
                        <img src="<?= htmlspecialchars($j['foto_perfil_usuario']) ?>" alt="">
                    <?php else: ?>
                        <?= $iniciais ?>
                    <?php endif; ?>
                </div>

                <div class="jog-info">
                    <div class="jog-nome"><?= htmlspecialchars($j['nome_usuario']) ?></div>
                    <div class="jog-sub">
                        <?php if ($j['capitao_inscricao']): ?>
                            <span class="badge-cap">
                                <i class="fa-solid fa-star"></i> Cap.
                            </span>
                        <?php endif; ?>
                        <?php if (!empty($j['posicao_inscricao'])): ?>
                            <span><?= htmlspecialchars($j['posicao_inscricao']) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($j['nome_camisa_inscricao'])): ?>
                            <span style="font-family:monospace;letter-spacing:.05em;font-size:.65rem;">
                                <?= htmlspecialchars(strtoupper($j['nome_camisa_inscricao'])) ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if ($j['numero_camisa_inscricao']): ?>
                <div class="jog-camisa">#<?= $j['numero_camisa_inscricao'] ?></div>
                <?php endif; ?>

            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </section>

</main>

<script src="/soee/src/frontend/scripts/user-conta.js"></script>
<script>
/* ── Tema ── */
const _t = localStorage.getItem('theme');
if (_t) document.documentElement.setAttribute('data-theme', _t);
</script>

<?php include __DIR__ . '/../includes/end.php'; ?>