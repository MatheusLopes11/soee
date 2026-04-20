<?php
session_start();
require_once __DIR__ . '/../../../backend/includes/conexao.php';
require_once __DIR__ . '/../../../backend/controllers/home.php';

AuthHome::exigirTipo(['aluno']);

$userId   = AuthHome::getId();
$userNome = AuthHome::getNome();

// ── DADOS DO USUÁRIO + TURMA + FOTO ──────────────────────
$stmtUser = $conn->prepare("
    SELECT u.id_usuario, u.nome_usuario, u.genero_usuario,
           t.id_turma, t.nome_turma, t.periodo_turma,
           c.nome_curso, c.sigla_curso,
           fp.caminho_foto
    FROM usuario u
    LEFT JOIN turma t ON t.id_turma = u.turma_id_turma
    LEFT JOIN curso c ON c.id_curso = t.curso_id_curso
    LEFT JOIN foto_perfil fp ON fp.usuario_id_usuario = u.id_usuario AND fp.atual_foto = 1
    WHERE u.id_usuario = :id
    LIMIT 1
");
$stmtUser->execute([':id' => $userId]);
$userData = $stmtUser->fetch(PDO::FETCH_ASSOC);

$nomeTurma  = $userData['nome_turma']   ?? 'Sem turma';
$siglaCurso = $userData['sigla_curso']  ?? '';
$turmaId    = $userData['id_turma']     ?? null;
$fotoPerfil = $userData['caminho_foto'] ?? null;
$inicial    = mb_strtoupper(mb_substr($userNome, 0, 1));

// ── INSCRIÇÕES ATIVAS DO ALUNO ────────────────────────────
$stmtInsc = $conn->prepare("
    SELECT i.id_inscricao, i.numero_camisa_inscricao, i.posicao_inscricao,
           i.capitao_inscricao, i.edicao_modalidade_id, i.status_inscricao,
           m.nome_modalidade, m.id_modalidade
    FROM inscricao i
    INNER JOIN edicao_modalidade em ON em.id_edicao_modalidade = i.edicao_modalidade_id
    INNER JOIN modalidade m ON m.id_modalidade = em.modalidade_id_modalidade
    WHERE i.usuario_id_usuario = :id AND i.status_inscricao = 'ativa'
    ORDER BY i.data_inscricao DESC
");
$stmtInsc->execute([':id' => $userId]);
$inscricoes = $stmtInsc->fetchAll(PDO::FETCH_ASSOC);
$modalidadesInscritas = array_column($inscricoes, 'edicao_modalidade_id');

// ── MODALIDADES DISPONÍVEIS PARA INSCRIÇÃO ────────────────
$stmtMod = $conn->query("
    SELECT m.id_modalidade, m.nome_modalidade, m.tipo_participacao,
        m.tipo_modalidade, m.genero_modalidade, em.id_edicao_modalidade,
           em.status_edicao_modalidade,
           em.data_inicio_inscricao, em.data_fim_inscricao,
           e.nome_edicao
    FROM modalidade m
    INNER JOIN edicao_modalidade em ON em.modalidade_id_modalidade = m.id_modalidade
    INNER JOIN edicao e ON e.id_edicao = em.edicao_id_edicao
    WHERE m.ativo_modalidade = 1
      AND em.status_edicao_modalidade = 'inscricoes'
      AND e.status_edicao != 'encerrado'
    ORDER BY m.nome_modalidade
");
$modalidades = $stmtMod->fetchAll(PDO::FETCH_ASSOC);

// ── PRÓXIMA PARTIDA DA TURMA ──────────────────────────────
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
        WHERE (p.turma_id_time_a = :t1 OR p.turma_id_time_b = :t2)
          AND p.status_partida = 'agendada'
          AND p.data_partida >= CURDATE()
        ORDER BY p.data_partida ASC, p.hora_partida ASC
        LIMIT 1
    ");
    $stmtProx->execute([':t1' => $turmaId, ':t2' => $turmaId]);
    $proximaPartida = $stmtProx->fetch(PDO::FETCH_ASSOC);
}

// ── TODAS AS PARTIDAS DA TURMA ────────────────────────────
$partidas = [];
if ($turmaId) {
    $stmtPart = $conn->prepare("
        SELECT p.id_partida, p.data_partida, p.hora_partida,
            p.local_partida, p.fase_partida, p.status_partida,
            p.turma_id_time_a, p.turma_id_time_b,
            ta.nome_turma AS time_a, tb.nome_turma AS time_b,
            m.nome_modalidade,
            r.placar_time_a, r.placar_time_b
        FROM partida p
        INNER JOIN turma ta ON ta.id_turma = p.turma_id_time_a
        INNER JOIN turma tb ON tb.id_turma = p.turma_id_time_b
        INNER JOIN edicao_modalidade em ON em.id_edicao_modalidade = p.edicao_modalidade_id
        INNER JOIN modalidade m ON m.id_modalidade = em.modalidade_id_modalidade
        LEFT JOIN resultado r ON r.partida_id_partida = p.id_partida
        WHERE (p.turma_id_time_a = :t1 OR p.turma_id_time_b = :t2)
        ORDER BY p.data_partida DESC, p.hora_partida DESC
    ");
    $stmtPart->execute([':t1' => $turmaId, ':t2' => $turmaId]);
    $partidas = $stmtPart->fetchAll(PDO::FETCH_ASSOC);
}

// ── CLASSIFICAÇÃO POR MODALIDADE ──────────────────────────
$classificacoes = [];
if ($turmaId && !empty($inscricoes)) {
    foreach ($inscricoes as $insc) {
        $emId = $insc['edicao_modalidade_id'];
        $stmtCl = $conn->prepare("
            SELECT cl.pontos, cl.vitorias, cl.derrotas, cl.empates,
                   cl.jogos, cl.saldo, cl.pontos_pro, cl.pontos_contra,
                   m.nome_modalidade
            FROM classificacao cl
            INNER JOIN edicao_modalidade em ON em.id_edicao_modalidade = cl.edicao_modalidade_id
            INNER JOIN modalidade m ON m.id_modalidade = em.modalidade_id_modalidade
            WHERE cl.edicao_modalidade_id = :emid AND cl.turma_id_turma = :turma
            LIMIT 1
        ");
        $stmtCl->execute([':emid' => $emId, ':turma' => $turmaId]);
        $row = $stmtCl->fetch(PDO::FETCH_ASSOC);
        if ($row) $classificacoes[$emId] = $row;
    }
}

// ── CLASSIFICAÇÃO GERAL (todas as turmas, por modalidade) ─
$rankingGeral = [];
if (!empty($inscricoes)) {
    foreach ($inscricoes as $insc) {
        $emId = $insc['edicao_modalidade_id'];
        $stmtRk = $conn->prepare("
            SELECT cl.pontos, cl.vitorias, cl.derrotas, cl.empates,
                   cl.jogos, cl.saldo, cl.pontos_pro, cl.pontos_contra,
                   t.nome_turma,
                   m.nome_modalidade
            FROM classificacao cl
            INNER JOIN turma t ON t.id_turma = cl.turma_id_turma
            INNER JOIN edicao_modalidade em ON em.id_edicao_modalidade = cl.edicao_modalidade_id
            INNER JOIN modalidade m ON m.id_modalidade = em.modalidade_id_modalidade
            WHERE cl.edicao_modalidade_id = :emid
            ORDER BY cl.pontos DESC, cl.saldo DESC, cl.vitorias DESC
        ");
        $stmtRk->execute([':emid' => $emId]);
        $rankingGeral[$emId] = $stmtRk->fetchAll(PDO::FETCH_ASSOC);
    }
}

// ── ELENCO DA TURMA (alunos inscritos) ───────────────────
$elenco = [];
if ($turmaId && !empty($inscricoes)) {
    foreach ($inscricoes as $insc) {
        $emId = $insc['edicao_modalidade_id'];
        $stmtEl = $conn->prepare("
            SELECT u.id_usuario, u.nome_usuario, u.foto_perfil_usuario,
                   i.numero_camisa_inscricao, i.posicao_inscricao, i.capitao_inscricao
            FROM inscricao i
            INNER JOIN usuario u ON u.id_usuario = i.usuario_id_usuario
            WHERE i.edicao_modalidade_id = :emid
              AND u.turma_id_turma = :turma
              AND i.status_inscricao = 'ativa'
            ORDER BY i.capitao_inscricao DESC, u.nome_usuario ASC
        ");
        $stmtEl->execute([':emid' => $emId, ':turma' => $turmaId]);
        $elenco[$emId] = $stmtEl->fetchAll(PDO::FETCH_ASSOC);
    }
}

// ── TODOS OS TIMES (todas turmas inscritas, por modalidade) ─
$todosOsTimes = [];
if (!empty($inscricoes)) {
    foreach ($inscricoes as $insc) {
        $emId = $insc['edicao_modalidade_id'];
        $stmtTimes = $conn->prepare("
            SELECT DISTINCT t.id_turma, t.nome_turma,
                   COUNT(i.id_inscricao) AS total_inscritos
            FROM inscricao i
            INNER JOIN usuario u ON u.id_usuario = i.usuario_id_usuario
            INNER JOIN turma t ON t.id_turma = u.turma_id_turma
            WHERE i.edicao_modalidade_id = :emid
              AND i.status_inscricao = 'ativa'
            GROUP BY t.id_turma, t.nome_turma
            ORDER BY t.nome_turma
        ");
        $stmtTimes->execute([':emid' => $emId]);
        $todosOsTimes[$emId] = $stmtTimes->fetchAll(PDO::FETCH_ASSOC);
    }
}

$faseLabel = [
    'grupos'=>'Grupos','oitavas'=>'Oitavas','quartas'=>'Quartas',
    'semi'=>'Semi','final'=>'Final','terceiro_lugar'=>'3º Lugar',
];
$statusLabel = [
    'agendada'=>'Agendada','realizada'=>'Realizada',
    'cancelada'=>'Cancelada','wo'=>'W.O.',
];
?>
<?php include __DIR__ . '/../includes/doctype.php'; ?>
<head>
    <title>SOEE | Dashboard Aluno</title>
    <link rel="stylesheet" href="/soee/src/frontend/styles/dash-user.css">
    <?php include __DIR__ . '/../includes/head.php'; ?>
    <style>
        .painel { display: none; }
        .painel.active { display: block; }

        .stat-resumo {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px; margin-bottom: 28px;
        }
        .stat-resumo-card {
            background: var(--bloco, #fff);
            border: 1px solid var(--borda, #e2e8f0);
            border-radius: 16px; padding: 20px;
            text-align: center;
        }
        .stat-resumo-card strong {
            display: block;
            font-size: 1.8rem; font-weight: 800;
            font-family: 'Playfair Display', serif;
        }
        .stat-resumo-card span { font-size: .75rem; color: var(--texto-2, #64748b); text-transform: uppercase; }

        .proxima-card {
            background: linear-gradient(135deg, #0f2d3d, #2c7da3);
            border-radius: 16px; padding: 24px; margin-bottom: 28px;
            color: white;
        }
        .proxima-card .pp-label { font-size: .75rem; text-transform: uppercase; letter-spacing: .1em; opacity: .7; margin-bottom: 12px; }
        .proxima-card .pp-times { display: flex; align-items: center; gap: 16px; font-size: 1.2rem; font-weight: 800; margin-bottom: 12px; }
        .proxima-card .pp-vs { opacity: .5; font-size: .9rem; }
        .proxima-card .pp-mine { color: #fbbf24; }
        .proxima-card .pp-meta { display: flex; gap: 16px; font-size: .82rem; opacity: .75; flex-wrap: wrap; }
        .proxima-card .pp-meta span { display: flex; align-items: center; gap: 6px; }

        .inscricao-badge {
            display: inline-flex; align-items: center; gap: 6px;
            background: rgba(34,197,94,.12); color: #15803d;
            border: 1px solid rgba(34,197,94,.25);
            border-radius: 999px; padding: 4px 12px;
            font-size: .75rem; font-weight: 700;
        }
        [data-theme="dark"] .inscricao-badge { color: #4ade80; }

        .modal-card {
            background: var(--bloco, #fff);
            border: 1px solid var(--borda, #e2e8f0);
            border-radius: 16px; padding: 20px; margin-bottom: 16px;
        }
        .modal-card h4 { margin-bottom: 12px; font-size: 1rem; }

        .player-item {
            display: flex; align-items: center; gap: 12px;
            padding: 10px 0; border-bottom: 1px solid var(--borda, #e2e8f0);
        }
        .player-item:last-child { border-bottom: none; }
        .player-avatar {
            width: 36px; height: 36px; border-radius: 50%;
            background: var(--fundo, #f0f4f8);
            display: flex; align-items: center; justify-content: center;
            font-size: .85rem; overflow: hidden; flex-shrink: 0;
        }
        .player-avatar img { width: 100%; height: 100%; object-fit: cover; }

        .time-item {
            display: flex; align-items: center; justify-content: space-between;
            padding: 12px 0; border-bottom: 1px solid var(--borda, #e2e8f0);
        }
        .time-item:last-child { border-bottom: none; }
        .time-mine { font-weight: 800; color: var(--laranja, #f97316); }

        .ranking-table { width: 100%; border-collapse: collapse; }
        .ranking-table th, .ranking-table td {
            padding: 10px 12px; text-align: center;
            border-bottom: 1px solid var(--borda, #e2e8f0);
            font-size: .82rem;
        }
        .ranking-table th { font-weight: 700; color: var(--texto-2, #64748b); text-transform: uppercase; font-size: .7rem; }
        .ranking-table tr.minha-turma td { background: rgba(255,77,18,.07); font-weight: 700; }

        .partida-row {
            display: flex; align-items: center; gap: 12px;
            padding: 14px 0; border-bottom: 1px solid var(--borda, #e2e8f0);
        }
        .partida-row:last-child { border-bottom: none; }
        .partida-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
        .partida-dot.agendada  { background: #f59e0b; }
        .partida-dot.realizada { background: #22c55e; }
        .partida-dot.cancelada, .partida-dot.wo { background: #ef4444; }
        .partida-info { flex: 1; }
        .partida-times { font-weight: 700; font-size: .9rem; }
        .partida-meta  { font-size: .75rem; color: var(--texto-2, #64748b); margin-top: 2px; }
        .partida-placar {
            font-weight: 800; font-size: .9rem;
            background: var(--fundo, #f0f4f8);
            padding: 4px 10px; border-radius: 8px;
            white-space: nowrap;
        }

        .insc-form { display: flex; flex-direction: column; gap: 12px; }
        .insc-form label { font-size: .82rem; font-weight: 600; margin-bottom: 4px; display: block; }
        .insc-form input, .insc-form select {
            width: 100%; padding: 9px 12px;
            border: 1px solid var(--borda, #e2e8f0);
            border-radius: 10px; background: var(--fundo, #f0f4f8);
            color: var(--texto, #1e293b); font-family: inherit; font-size: .88rem;
        }
        .btn-inscrever {
            background: #22c55e; color: white; border: none;
            padding: 10px 20px; border-radius: 10px;
            font-weight: 700; cursor: pointer; font-size: .88rem;
            transition: background .2s;
        }
        .btn-inscrever:hover { background: #16a34a; }
        .btn-cancelar-insc {
            background: rgba(239,68,68,.1); color: #ef4444;
            border: 1px solid rgba(239,68,68,.25);
            padding: 8px 16px; border-radius: 10px;
            font-weight: 700; cursor: pointer; font-size: .82rem;
            transition: background .2s;
        }
        .btn-cancelar-insc:hover { background: #ef4444; color: white; }

        .empty { padding: 32px; text-align: center; color: var(--texto-2, #64748b); }
        .empty i { font-size: 2rem; opacity: .3; display: block; margin-bottom: 8px; }

        @media (max-width: 768px) {
            .stat-resumo { grid-template-columns: repeat(2, 1fr); }
        }
    </style>
</head>
<body>

<div class="dash-wrapper">

    <!-- SIDEBAR -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-logo">
            <div class="sidebar-logo-icon"><i class="fa-solid fa-trophy"></i></div>
            <span class="sidebar-logo-text">SOEE</span>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-section-label">Principal</div>
            <a class="nav-item active" href="javascript:void(0)" data-painel="overview" onclick="trocarPainel(this)">
                <i class="fa-solid fa-house"></i> Visão Geral
            </a>
            <a class="nav-item" href="javascript:void(0)" data-painel="partidas" onclick="trocarPainel(this)">
                <i class="fa-solid fa-calendar-days"></i> Partidas
            </a>
            <a class="nav-item" href="javascript:void(0)" data-painel="times" onclick="trocarPainel(this)">
                <i class="fa-solid fa-shield-halved"></i> Times
            </a>
            <a class="nav-item" href="javascript:void(0)" data-painel="classificacao" onclick="trocarPainel(this)">
                <i class="fa-solid fa-ranking-star"></i> Classificação
            </a>
            <a class="nav-item" href="javascript:void(0)" data-painel="meutime" onclick="trocarPainel(this)">
                <i class="fa-solid fa-people-group"></i> Meu Time
            </a>
            <a class="nav-item" href="javascript:void(0)" data-painel="inscricoes" onclick="trocarPainel(this)">
                <i class="fa-solid fa-clipboard-list"></i> Inscrições
                <?php if(count($modalidades) > 0): ?>
                <span class="nav-badge"><?= count($modalidades) ?></span>
                <?php endif; ?>
            </a>

            <div class="nav-section-label" style="margin-top:16px">Conta</div>
            <a class="nav-item" href="/soee/src/frontend/views/site/profile.php">
                <i class="fa-solid fa-user"></i> Perfil
            </a>
        </nav>

        <div class="sidebar-user">
            <a href="/soee/src/frontend/views/site/profile.php"
               style="display:flex;align-items:center;gap:10px;text-decoration:none;flex:1;min-width:0;">
                <div class="user-avatar">
                    <?php if ($fotoPerfil): ?>
                        <img src="<?= htmlspecialchars($fotoPerfil) ?>"
                             alt="Foto" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">
                    <?php else: ?>
                        <?= $inicial ?>
                    <?php endif; ?>
                </div>
                <div class="user-info">
                    <div class="user-name"><?= htmlspecialchars(explode(' ', $userNome)[0]) ?></div>
                    <div class="user-role"><?= htmlspecialchars($nomeTurma) ?></div>
                </div>
            </a>
            <button id="toggle-theme" class="user-menu-btn" title="Alternar tema">
                <i class="fa-solid fa-moon" id="temaIcone"></i>
            </button>
        </div>
    </aside>

    <!-- MAIN -->
    <main class="dash-main">
        <header class="topbar">
            <button class="topbar-menu-btn" onclick="document.getElementById('sidebar').classList.toggle('open')" style="background:none;border:none;cursor:pointer;font-size:1.1rem;color:var(--texto,#1e293b);">
                <i class="fa-solid fa-bars"></i>
            </button>
            <div class="topbar-title" id="topbar-titulo">Visão <span>Geral</span></div>
            <a href="/soee/src/backend/includes/logout.php"
               style="margin-left:auto;color:#ef4444;display:flex;align-items:center;gap:6px;text-decoration:none;font-size:.88rem;font-weight:600;">
                <i class="fa-solid fa-right-from-bracket"></i> Sair
            </a>
        </header>

        <div class="dash-content">

            <!-- ══════ OVERVIEW ══════ -->
            <div class="painel active" id="painel-overview">

                <!-- Banner de boas-vindas -->
                <div class="proxima-card" style="background:linear-gradient(135deg,#0f2d3d,#1e5671,#2c7da3);margin-bottom:28px;">
                    <div style="font-size:.75rem;opacity:.6;text-transform:uppercase;letter-spacing:.1em;margin-bottom:6px;">
                        <?= $userData['genero_usuario'] === 'f' ? 'Bem-vinda de volta' : 'Bem-vindo de volta' ?>
                    </div>
                    <div style="font-family:'Playfair Display',serif;font-size:1.6rem;font-weight:800;margin-bottom:4px;">
                        Olá, <?= htmlspecialchars(explode(' ', $userNome)[0]) ?>!
                    </div>
                    <div style="opacity:.7;font-size:.88rem;">
                        <?= htmlspecialchars($nomeTurma) ?>
                        <?= $siglaCurso ? '— '.htmlspecialchars($siglaCurso) : '' ?>
                    </div>
                    <?php if(empty($inscricoes)): ?>
                    <div style="margin-top:16px;">
                        <a href="javascript:void(0)" onclick="trocarPainelById('inscricoes')"
                           style="background:#f97316;color:white;padding:8px 18px;border-radius:10px;text-decoration:none;font-weight:700;font-size:.85rem;">
                            <i class="fa-solid fa-plus"></i> Inscrever-se em um esporte
                        </a>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Stats da turma -->
                <?php if (!empty($classificacoes)): ?>
                <?php $cl = reset($classificacoes); ?>
                <div class="stat-resumo">
                    <div class="stat-resumo-card">
                        <strong style="color:#22c55e"><?= $cl['vitorias'] ?></strong>
                        <span>Vitórias</span>
                    </div>
                    <div class="stat-resumo-card">
                        <strong style="color:#f59e0b"><?= $cl['empates'] ?></strong>
                        <span>Empates</span>
                    </div>
                    <div class="stat-resumo-card">
                        <strong style="color:#ef4444"><?= $cl['derrotas'] ?></strong>
                        <span>Derrotas</span>
                    </div>
                    <div class="stat-resumo-card">
                        <strong style="color:#8b5cf6"><?= $cl['pontos'] ?></strong>
                        <span>Pontos</span>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Próxima partida -->
                <?php if ($proximaPartida): ?>
                <div class="proxima-card" style="margin-bottom:28px;">
                    <div class="pp-label"><i class="fa-solid fa-clock"></i> Próxima Partida</div>
                    <div class="pp-times">
                        <span class="<?= $proximaPartida['turma_id_time_a'] == $turmaId ? 'pp-mine' : '' ?>">
                            <?= htmlspecialchars($proximaPartida['nome_time_a']) ?>
                        </span>
                        <span class="pp-vs">VS</span>
                        <span class="<?= $proximaPartida['turma_id_time_b'] == $turmaId ? 'pp-mine' : '' ?>">
                            <?= htmlspecialchars($proximaPartida['nome_time_b']) ?>
                        </span>
                    </div>
                    <div class="pp-meta">
                        <span><i class="fa-solid fa-calendar"></i> <?= date('d/m/Y', strtotime($proximaPartida['data_partida'])) ?></span>
                        <span><i class="fa-solid fa-clock"></i> <?= substr($proximaPartida['hora_partida'], 0, 5) ?></span>
                        <?php if ($proximaPartida['local_partida']): ?>
                        <span><i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars($proximaPartida['local_partida']) ?></span>
                        <?php endif; ?>
                        <span><i class="fa-solid fa-futbol"></i> <?= htmlspecialchars($proximaPartida['nome_modalidade']) ?></span>
                    </div>
                </div>
                <?php else: ?>
                <div class="modal-card" style="margin-bottom:28px;text-align:center;opacity:.6;">
                    <i class="fa-solid fa-calendar-xmark" style="font-size:1.5rem;margin-bottom:8px;display:block;"></i>
                    Nenhuma partida agendada para sua turma.
                </div>
                <?php endif; ?>

                <!-- Inscrições resumo -->
                <?php if (!empty($inscricoes)): ?>
                <div class="modal-card">
                    <h4><i class="fa-solid fa-clipboard-check" style="color:#22c55e"></i> Suas Inscrições</h4>
                    <?php foreach ($inscricoes as $ins): ?>
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--borda,#e2e8f0);">
                        <div>
                            <strong style="font-size:.88rem"><?= htmlspecialchars($ins['nome_modalidade']) ?></strong>
                            <?php if ($ins['posicao_inscricao']): ?>
                            <span style="font-size:.75rem;color:var(--texto-2,#64748b);margin-left:8px"><?= htmlspecialchars($ins['posicao_inscricao']) ?></span>
                            <?php endif; ?>
                        </div>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <?php if ($ins['capitao_inscricao']): ?>
                            <span style="font-size:.72rem;color:#f59e0b;font-weight:700"><i class="fa-solid fa-star"></i> Capitão</span>
                            <?php endif; ?>
                            <?php if ($ins['numero_camisa_inscricao']): ?>
                            <span style="font-size:.75rem;background:var(--fundo,#f0f4f8);padding:2px 8px;border-radius:6px;">#<?= $ins['numero_camisa_inscricao'] ?></span>
                            <?php endif; ?>
                            <span class="inscricao-badge"><i class="fa-solid fa-check"></i> Ativo</span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

            </div>

            <!-- ══════ PARTIDAS ══════ -->
            <div class="painel" id="painel-partidas">
                <div class="modal-card">
                    <h4><i class="fa-solid fa-calendar-days" style="color:#f97316"></i> Partidas da Turma</h4>
                    <?php if (empty($partidas)): ?>
                        <div class="empty"><i class="fa-solid fa-calendar-xmark"></i>Nenhuma partida encontrada.</div>
                    <?php else: ?>
                        <?php foreach ($partidas as $p):
                            $temPlacar = $p['status_partida'] === 'realizada' && isset($p['placar_time_a']); ?>
                        <div class="partida-row">
                            <div class="partida-dot <?= $p['status_partida'] ?>"></div>
                            <div class="partida-info">
                                <div class="partida-times">
                                    <span <?= $p['turma_id_time_a'] == $turmaId ? 'style="color:var(--laranja,#f97316);font-weight:800"' : '' ?>>
                                        <?= htmlspecialchars($p['time_a']) ?>
                                    </span>
                                    <span style="opacity:.5;margin:0 6px">vs</span>
                                    <span <?= $p['turma_id_time_b'] == $turmaId ? 'style="color:var(--laranja,#f97316);font-weight:800"' : '' ?>>
                                        <?= htmlspecialchars($p['time_b']) ?>
                                    </span>
                                </div>
                                <div class="partida-meta">
                                    <?= htmlspecialchars($p['nome_modalidade']) ?>
                                    &middot; <?= $faseLabel[$p['fase_partida']] ?? $p['fase_partida'] ?>
                                    &middot; <?= date('d/m/Y', strtotime($p['data_partida'])) ?>
                                    <?php if ($p['hora_partida']): ?>
                                    &middot; <?= substr($p['hora_partida'], 0, 5) ?>
                                    <?php endif; ?>
                                    <?php if ($p['local_partida']): ?>
                                    &middot; <?= htmlspecialchars($p['local_partida']) ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="partida-placar">
                                <?= $temPlacar
                                    ? $p['placar_time_a'].' x '.$p['placar_time_b']
                                    : ($statusLabel[$p['status_partida']] ?? $p['status_partida']) ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- ══════ TIMES ══════ -->
            <div class="painel" id="painel-times">
                <?php if (empty($inscricoes)): ?>
                    <div class="empty"><i class="fa-solid fa-shield-halved"></i>Inscreva-se em uma modalidade para ver os times.</div>
                <?php else: ?>
                    <?php foreach ($inscricoes as $insc):
                        $emId = $insc['edicao_modalidade_id'];
                        $times = $todosOsTimes[$emId] ?? []; ?>
                    <div class="modal-card">
                        <h4><i class="fa-solid fa-shield-halved" style="color:#f97316"></i> <?= htmlspecialchars($insc['nome_modalidade']) ?></h4>
                        <?php if (empty($times)): ?>
                            <div class="empty"><i class="fa-solid fa-users-slash"></i>Nenhum time inscrito.</div>
                        <?php else: ?>
                            <?php foreach ($times as $time): ?>
                            <div class="time-item">
                                <span class="<?= $time['id_turma'] == $turmaId ? 'time-mine' : '' ?>">
                                    <?php if ($time['id_turma'] == $turmaId): ?>
                                    <i class="fa-solid fa-star" style="color:#f59e0b;font-size:.7rem;margin-right:4px"></i>
                                    <?php endif; ?>
                                    <?= htmlspecialchars($time['nome_turma']) ?>
                                    <?= $time['id_turma'] == $turmaId ? ' (seu time)' : '' ?>
                                </span>
                                <span style="font-size:.78rem;color:var(--texto-2,#64748b)">
                                    <?= $time['total_inscritos'] ?> inscritos
                                </span>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- ══════ CLASSIFICAÇÃO ══════ -->
            <div class="painel" id="painel-classificacao">
                <?php if (empty($inscricoes)): ?>
                    <div class="empty"><i class="fa-solid fa-ranking-star"></i>Inscreva-se em uma modalidade para ver a classificação.</div>
                <?php else: ?>
                    <?php foreach ($inscricoes as $insc):
                        $emId   = $insc['edicao_modalidade_id'];
                        $ranking = $rankingGeral[$emId] ?? []; ?>
                    <div class="modal-card">
                        <h4><i class="fa-solid fa-ranking-star" style="color:#f97316"></i> <?= htmlspecialchars($insc['nome_modalidade']) ?></h4>
                        <?php if (empty($ranking)): ?>
                            <div class="empty"><i class="fa-solid fa-ranking-star"></i>Sem dados de classificação.</div>
                        <?php else: ?>
                        <div style="overflow-x:auto;">
                            <table class="ranking-table">
                                <thead>
                                    <tr><th>#</th><th style="text-align:left">Turma</th><th>J</th><th>V</th><th>E</th><th>D</th><th>GP</th><th>GC</th><th>SG</th><th>PTS</th></tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($ranking as $pos => $r): ?>
                                    <tr class="<?= $r['nome_turma'] === $nomeTurma ? 'minha-turma' : '' ?>">
                                        <td><?= $pos + 1 ?>º</td>
                                        <td style="text-align:left;font-weight:600"><?= htmlspecialchars($r['nome_turma']) ?></td>
                                        <td><?= $r['jogos'] ?></td>
                                        <td><?= $r['vitorias'] ?></td>
                                        <td><?= $r['empates'] ?></td>
                                        <td><?= $r['derrotas'] ?></td>
                                        <td><?= $r['pontos_pro'] ?></td>
                                        <td><?= $r['pontos_contra'] ?></td>
                                        <td><?= $r['saldo'] >= 0 ? '+'.$r['saldo'] : $r['saldo'] ?></td>
                                        <td style="font-weight:800"><?= $r['pontos'] ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- ══════ MEU TIME ══════ -->
            <div class="painel" id="painel-meutime">
                <?php if (empty($inscricoes)): ?>
                    <div class="empty"><i class="fa-solid fa-people-group"></i>Inscreva-se em uma modalidade para ver seu elenco.</div>
                <?php else: ?>
                    <?php foreach ($inscricoes as $insc):
                        $emId    = $insc['edicao_modalidade_id'];
                        $jogadores = $elenco[$emId] ?? [];
                        $cl      = $classificacoes[$emId] ?? null; ?>
                    <div class="modal-card">
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;flex-wrap:wrap;gap:8px;">
                            <h4 style="margin:0"><i class="fa-solid fa-people-group" style="color:#f97316"></i> <?= htmlspecialchars($nomeTurma) ?> — <?= htmlspecialchars($insc['nome_modalidade']) ?></h4>
                            <?php if ($cl): ?>
                            <div style="display:flex;gap:12px;font-size:.8rem;">
                                <span><strong style="color:#22c55e"><?= $cl['vitorias'] ?>V</strong></span>
                                <span><strong style="color:#f59e0b"><?= $cl['empates'] ?>E</strong></span>
                                <span><strong style="color:#ef4444"><?= $cl['derrotas'] ?>D</strong></span>
                                <span><strong style="color:#8b5cf6"><?= $cl['pontos'] ?> pts</strong></span>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php if (empty($jogadores)): ?>
                            <div class="empty"><i class="fa-solid fa-user-slash"></i>Nenhum jogador inscrito ainda.</div>
                        <?php else: ?>
                            <?php foreach ($jogadores as $j): ?>
                            <div class="player-item">
                                <div class="player-avatar">
                                    <?php if (!empty($j['foto_perfil_usuario'])): ?>
                                        <img src="<?= htmlspecialchars($j['foto_perfil_usuario']) ?>" alt="">
                                    <?php else: ?>
                                        <?= mb_strtoupper(mb_substr($j['nome_usuario'], 0, 1)) ?>
                                    <?php endif; ?>
                                </div>
                                <div style="flex:1">
                                    <div style="font-weight:600;font-size:.88rem"><?= htmlspecialchars($j['nome_usuario']) ?></div>
                                    <div style="font-size:.72rem;color:var(--texto-2,#64748b)">
                                        <?= $j['posicao_inscricao'] ? htmlspecialchars($j['posicao_inscricao']) : 'Sem posição' ?>
                                    </div>
                                </div>
                                <div style="display:flex;align-items:center;gap:8px;">
                                    <?php if ($j['capitao_inscricao']): ?>
                                    <span style="font-size:.7rem;color:#f59e0b;font-weight:700"><i class="fa-solid fa-star"></i> Cap.</span>
                                    <?php endif; ?>
                                    <?php if ($j['numero_camisa_inscricao']): ?>
                                    <span style="font-size:.75rem;background:var(--fundo,#f0f4f8);padding:2px 8px;border-radius:6px;">#<?= $j['numero_camisa_inscricao'] ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- ══════ INSCRIÇÕES ══════ -->
            <div class="painel" id="painel-inscricoes">

                <!-- Inscrições atuais -->
                <?php if (!empty($inscricoes)): ?>
                <div class="modal-card">
                    <h4><i class="fa-solid fa-clipboard-check" style="color:#22c55e"></i> Minhas Inscrições Ativas</h4>
                    <?php foreach ($inscricoes as $ins): ?>
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 0;border-bottom:1px solid var(--borda,#e2e8f0);">
                        <div>
                            <strong style="font-size:.9rem"><?= htmlspecialchars($ins['nome_modalidade']) ?></strong>
                            <?php if ($ins['posicao_inscricao']): ?>
                            <div style="font-size:.75rem;color:var(--texto-2,#64748b)"><?= htmlspecialchars($ins['posicao_inscricao']) ?></div>
                            <?php endif; ?>
                        </div>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <?php if ($ins['capitao_inscricao']): ?>
                            <span style="font-size:.72rem;color:#f59e0b;font-weight:700"><i class="fa-solid fa-star"></i> Capitão</span>
                            <?php endif; ?>
                            <span class="inscricao-badge"><i class="fa-solid fa-check"></i> Ativo</span>
                            <button class="btn-cancelar-insc"
                                onclick="cancelarInscricao(<?= $ins['id_inscricao'] ?>, '<?= addslashes(htmlspecialchars($ins['nome_modalidade'])) ?>')">
                                <i class="fa-solid fa-times"></i> Cancelar
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <!-- Modalidades disponíveis -->
                <div class="modal-card">
                    <h4><i class="fa-solid fa-futbol" style="color:#f97316"></i> Modalidades Disponíveis para Inscrição</h4>
                    <?php if (empty($modalidades)): ?>
                        <div class="empty"><i class="fa-solid fa-futbol"></i>Nenhuma modalidade com inscrições abertas.</div>
                    <?php else: ?>
                        <?php foreach ($modalidades as $md):
                            $jaInscrito = in_array($md['id_edicao_modalidade'], $modalidadesInscritas); ?>
                        <div style="padding:16px 0;border-bottom:1px solid var(--borda,#e2e8f0);">
                            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:<?= $jaInscrito ? '0' : '12px' ?>;">
                                <div>
                                    <strong><?= htmlspecialchars($md['nome_modalidade']) ?></strong>
                                    <div style="font-size:.75rem;color:var(--texto-2,#64748b)">
                                        <?= htmlspecialchars($md['nome_edicao']) ?>
                                        &middot; <?= ucfirst($md['tipo_participacao']) ?>
                                        &middot; Inscrições até <?= date('d/m/Y', strtotime($md['data_fim_inscricao'])) ?>
                                    </div>
                                </div>
                                <?php if ($jaInscrito): ?>
                                <span class="inscricao-badge"><i class="fa-solid fa-check"></i> Inscrito</span>
                                <?php endif; ?>
                            </div>

                            <?php if (!$jaInscrito): ?>
                            <form onsubmit="enviarInscricao(event, <?= $md['id_edicao_modalidade'] ?>)">
                                <div style="display:grid;grid-template-columns:1fr 1fr auto;gap:8px;align-items:end;">
                                    <div>
                                        <label style="font-size:.75rem;font-weight:600;display:block;margin-bottom:4px;">Posição (opcional)</label>
                                        <input type="text" name="posicao" placeholder="Ex: Atacante" style="width:100%;padding:8px 10px;border:1px solid var(--borda,#e2e8f0);border-radius:8px;background:var(--fundo,#f0f4f8);color:var(--texto,#1e293b);font-family:inherit;font-size:.85rem;">
                                    </div>
                                    <div>
                                        <label style="font-size:.75rem;font-weight:600;display:block;margin-bottom:4px;">Nº Camisa (opcional)</label>
                                        <input type="number" name="camisa" min="1" max="99" placeholder="Ex: 10" style="width:100%;padding:8px 10px;border:1px solid var(--borda,#e2e8f0);border-radius:8px;background:var(--fundo,#f0f4f8);color:var(--texto,#1e293b);font-family:inherit;font-size:.85rem;">
                                    </div>
                                    <button type="submit" class="btn-inscrever">
                                        <i class="fa-solid fa-plus"></i> Inscrever
                                    </button>
                                </div>
                            </form>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

        </div><!-- dash-content -->
    </main>
</div>

<script>
// ── Tema ──
(function() {
    const t = localStorage.getItem('theme') || 'light';
    document.documentElement.setAttribute('data-theme', t);
    const ic = document.getElementById('temaIcone');
    if (ic) ic.className = t === 'dark' ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
})();

document.getElementById('toggle-theme')?.addEventListener('click', () => {
    const atual = document.documentElement.getAttribute('data-theme');
    const novo  = atual === 'dark' ? 'light' : 'dark';
    document.documentElement.setAttribute('data-theme', novo);
    localStorage.setItem('theme', novo);
    const ic = document.getElementById('temaIcone');
    if (ic) ic.className = novo === 'dark' ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
});

// ── Navegação entre painéis ──
function trocarPainel(el) {
    document.querySelectorAll('.nav-item').forEach(i => i.classList.remove('active'));
    el.classList.add('active');
    trocarPainelById(el.dataset.painel);
}

function trocarPainelById(id) {
    document.querySelectorAll('.painel').forEach(p => p.classList.remove('active'));
    const alvo = document.getElementById('painel-' + id);
    if (alvo) alvo.classList.add('active');

    const titulos = {
        overview:      'Visão Geral',
        partidas:      'Partidas',
        times:         'Times',
        classificacao: 'Classificação',
        meutime:       'Meu Time',
        inscricoes:    'Inscrições',
    };
    const el = document.getElementById('topbar-titulo');
    if (el) el.textContent = titulos[id] || 'Dashboard';

    document.querySelectorAll('.nav-item[data-painel]').forEach(i => {
        i.classList.toggle('active', i.dataset.painel === id);
    });
}

// ── Inscrição ──

function enviarInscricao(e, edicaoModalidadeId) {
    e.preventDefault();
    const form    = e.target;
    const posicao = form.posicao.value.trim();
    const camisa  = form.camisa.value.trim();

    fetch('/soee/src/backend/actions/inscrever-aluno.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `edicao_modalidade_id=${edicaoModalidadeId}&posicao=${encodeURIComponent(posicao)}&camisa=${encodeURIComponent(camisa)}`
    })
    .then(r => r.json())
    .then(d => {
        if (d.ok) {
            alert('Inscrição realizada com sucesso!');
            location.reload();
        } else {
            alert('Erro: ' + (d.erro || 'desconhecido'));
        }
    })
    .catch(() => alert('Erro de conexão.'));
}

// ── Cancelar inscrição ──
function cancelarInscricao(id, nome) {
    if (!confirm('Cancelar inscrição em "' + nome + '"?')) return;
    fetch('/soee/src/backend/actions/cancelar-inscricao.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `id_inscricao=${id}`
    })
    .then(r => r.json())
    .then(d => {
        if (d.ok) { alert('Inscrição cancelada.'); location.reload(); }
        else alert('Erro: ' + (d.erro || 'desconhecido'));
    })
    .catch(() => alert('Erro de conexão.'));
}
</script>

<?php include __DIR__ . '/../includes/end.php'; ?>  