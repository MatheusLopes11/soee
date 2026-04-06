<?php
require_once __DIR__ . '/../include/conexao.php';
require_once __DIR__ . '/../auth/auth-home.php';

AuthHome::exigirTipo(['professor']);

$userId   = AuthHome::getId();
$userNome = AuthHome::getNome();


$stmtUser = $conn->prepare("
    SELECT u.nome_usuario, u.email_usuario, u.foto_perfil_usuario, u.genero_usuario
    FROM usuario u
    WHERE u.id_usuario = :id
");
$stmtUser->execute([':id' => $userId]);
$userData = $stmtUser->fetch(PDO::FETCH_ASSOC);

$stmtEdicoes = $conn->query("
    SELECT e.id_edicao, e.nome_edicao, e.ano_edicao, e.status_edicao,
           COUNT(DISTINCT em.id_edicao_modalidade) AS total_modalidades
    FROM edicao e
    LEFT JOIN edicao_modalidade em ON em.edicao_id_edicao = e.id_edicao
    GROUP BY e.id_edicao
    ORDER BY e.ano_edicao DESC, e.id_edicao DESC
    LIMIT 5
");
$edicoes = $stmtEdicoes->fetchAll(PDO::FETCH_ASSOC);

$stmtProximas = $conn->query("
    SELECT p.id_partida, p.data_partida, p.hora_partida, p.local_partida,
           p.fase_partida, p.status_partida,
           ta.nome_turma AS time_a, tb.nome_turma AS time_b,
           m.nome_modalidade
    FROM partida p
    INNER JOIN turma ta ON ta.id_turma = p.turma_id_time_a
    INNER JOIN turma tb ON tb.id_turma = p.turma_id_time_b
    INNER JOIN edicao_modalidade em ON em.id_edicao_modalidade = p.edicao_modalidade_id
    INNER JOIN modalidade m ON m.id_modalidade = em.modalidade_id_modalidade
    WHERE p.status_partida = 'agendada'
      AND p.data_partida >= CURDATE()
    ORDER BY p.data_partida ASC, p.hora_partida ASC
    LIMIT 8
");
$proximas = $stmtProximas->fetchAll(PDO::FETCH_ASSOC);

$stmtUltimos = $conn->query("
    SELECT p.id_partida, p.data_partida, p.fase_partida,
           ta.nome_turma AS time_a, tb.nome_turma AS time_b,
           r.placar_time_a, r.placar_time_b,
           tv.nome_turma AS vencedor,
           m.nome_modalidade
    FROM partida p
    INNER JOIN turma ta ON ta.id_turma = p.turma_id_time_a
    INNER JOIN turma tb ON tb.id_turma = p.turma_id_time_b
    INNER JOIN edicao_modalidade em ON em.id_edicao_modalidade = p.edicao_modalidade_id
    INNER JOIN modalidade m ON m.id_modalidade = em.modalidade_id_modalidade
    LEFT JOIN resultado r ON r.partida_id_partida = p.id_partida
    LEFT JOIN turma tv ON tv.id_turma = r.turma_id_vencedor
    WHERE p.status_partida = 'realizada'
    ORDER BY p.data_partida DESC
    LIMIT 6
");
$ultimos = $stmtUltimos->fetchAll(PDO::FETCH_ASSOC);

$stmtStats = $conn->query("
    SELECT
        (SELECT COUNT(*) FROM partida WHERE status_partida = 'agendada' AND data_partida >= CURDATE()) AS agendadas,
        (SELECT COUNT(*) FROM partida WHERE status_partida = 'realizada') AS realizadas,
        (SELECT COUNT(*) FROM modalidade WHERE ativo_modalidade = 1) AS modalidades,
        (SELECT COUNT(*) FROM usuario WHERE tipo_usuario = 'aluno' AND ativo_usuario = 1) AS alunos,
        (SELECT COUNT(*) FROM inscricao WHERE status_inscricao = 'ativa') AS inscricoes,
        (SELECT COUNT(*) FROM edicao WHERE status_edicao = 'em_andamento') AS edicoes_ativas
");
$stats = $stmtStats->fetch(PDO::FETCH_ASSOC);

$stmtSumulas = $conn->query("
    SELECT s.id_sumula, s.data_envio_sumula, s.status_sumula,
           s.nome_arquivo_sumula,
           ta.nome_turma AS time_a, tb.nome_turma AS time_b,
           p.data_partida
    FROM sumula s
    INNER JOIN partida p ON p.id_partida = s.partida_id_partida
    INNER JOIN turma ta ON ta.id_turma = p.turma_id_time_a
    INNER JOIN turma tb ON tb.id_turma = p.turma_id_time_b
    ORDER BY s.data_envio_sumula DESC
    LIMIT 5
");
$sumulas = $stmtSumulas->fetchAll(PDO::FETCH_ASSOC);

$faseLabel = [
    'grupos'         => 'Fase de Grupos',
    'oitavas'        => 'Oitavas de Final',
    'quartas'        => 'Quartas de Final',
    'semi'           => 'Semifinal',
    'final'          => 'Final',
    'terceiro_lugar' => '3º Lugar',
];
$statusLabel = [
    'planejamento' => 'Planejamento',
    'inscricoes'   => 'Inscrições',
    'em_andamento' => 'Em Andamento',
    'encerrado'    => 'Encerrado',
];
?>

<!-- ( HTML ) -->
<?php include __DIR__ . '/../include/doctype.php';?>
<head>
    <title>Dashboard — Professor | SOEE</title>
        <link rel="stylesheet" href="/soee/src/frontend/css/dash-prof.css">
    <?php include __DIR__ . '/../include/head-data.php';?>
</head>
<body>

<aside class="sidebar" id="sidebar">
    <div class="sidebar-logo">
        <a href="/soee/src/backend/php/pages/inicio.php">S<span>O</span>EE</a>
        <small>Painel do Professor</small>
    </div>

    <div class="sidebar-perfil">
        <div class="perfil-avatar">
            <?php if (!empty($userData['foto_perfil_usuario'])): ?>
                <img src="<?= htmlspecialchars($userData['foto_perfil_usuario']) ?>" alt="Foto">
            <?php else: ?>
                <i class="fa-solid fa-chalkboard-teacher"></i>
            <?php endif; ?>
        </div>
        <div class="perfil-info">
            <div class="perfil-nome"><?= htmlspecialchars($userNome) ?></div>
            <div class="perfil-cargo">Professor</div>
        </div>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-secao">Geral</div>
        <a href="#" class="nav-item ativo"><i class="fa-solid fa-gauge"></i> Painel</a>
        <a href="/soee/src/backend/php/pages/inicio.php" class="nav-item"><i class="fa-solid fa-house"></i> Início</a>

        <div class="nav-secao">Competição</div>
        <a href="#" class="nav-item"><i class="fa-solid fa-trophy"></i> Edições</a>
        <a href="#" class="nav-item"><i class="fa-solid fa-futbol"></i> Modalidades</a>
        <a href="#" class="nav-item"><i class="fa-solid fa-calendar-days"></i> Partidas</a>
        <a href="#" class="nav-item"><i class="fa-solid fa-ranking-star"></i> Classificação</a>

        <div class="nav-secao">Gestão</div>
        <a href="#" class="nav-item">
            <i class="fa-solid fa-file-lines"></i> Súmulas
            <?php if (count($sumulas) > 0): ?>
                <span class="nav-badge"><?= count($sumulas) ?></span>
            <?php endif; ?>
        </a>
        <a href="#" class="nav-item"><i class="fa-solid fa-users"></i> Alunos</a>
        <a href="/soee/src/backend/php/form/form-feedback.php" class="nav-item"><i class="fa-solid fa-comment-dots"></i> Feedback</a>
    </nav>

    <div class="sidebar-rodape">
        <a href="/soee/src/backend/php/auth/logout.php" class="btn-sair">
            <i class="fa-solid fa-right-from-bracket"></i> Sair da conta
        </a>
    </div>
</aside>

<div class="conteudo">

    <header class="topbar">
        <div class="topbar-titulo">Dashboard</div>
        <div class="topbar-acoes">
            <span class="data-atual" id="dataAtual"></span>
            <button class="btn-icone" id="toggleTema" aria-label="Tema">
                <i class="fa-solid fa-moon" id="iconeTema"></i>
            </button>
        </div>
    </header>

    <main class="pagina">

        <div class="boas-vindas">
            <div class="bv-texto">
                <h2>Olá, <?= htmlspecialchars(explode(' ', $userNome)[0]) ?>!</h2>
                <p>Aqui está um resumo do andamento dos interclasses da ETEC JK.</p>
            </div>
            <div class="bv-badge">
                <i class="fa-solid fa-chalkboard-teacher"></i>
                Área do Professor
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card azul">
                <div class="stat-icone"><i class="fa-solid fa-calendar-check"></i></div>
                <div class="stat-valor"><?= $stats['agendadas'] ?></div>
                <div class="stat-label">Partidas Agendadas</div>
            </div>
            <div class="stat-card verde">
                <div class="stat-icone"><i class="fa-solid fa-flag-checkered"></i></div>
                <div class="stat-valor"><?= $stats['realizadas'] ?></div>
                <div class="stat-label">Partidas Realizadas</div>
            </div>
            <div class="stat-card laranja">
                <div class="stat-icone"><i class="fa-solid fa-fire"></i></div>
                <div class="stat-valor"><?= $stats['edicoes_ativas'] ?></div>
                <div class="stat-label">Edições Ativas</div>
            </div>
            <div class="stat-card roxo">
                <div class="stat-icone"><i class="fa-solid fa-futbol"></i></div>
                <div class="stat-valor"><?= $stats['modalidades'] ?></div>
                <div class="stat-label">Modalidades</div>
            </div>
            <div class="stat-card amarelo">
                <div class="stat-icone"><i class="fa-solid fa-user-graduate"></i></div>
                <div class="stat-valor"><?= $stats['alunos'] ?></div>
                <div class="stat-label">Alunos Ativos</div>
            </div>
            <div class="stat-card vermelho">
                <div class="stat-icone"><i class="fa-solid fa-clipboard-list"></i></div>
                <div class="stat-valor"><?= $stats['inscricoes'] ?></div>
                <div class="stat-label">Inscrições Ativas</div>
            </div>
        </div>

        <div class="grid-3">
            <div class="card">
                <div class="card-header">
                    <div class="card-titulo"><i class="fa-solid fa-calendar"></i> Próximas Partidas</div>
                    <a href="#" class="card-link">Ver todas</a>
                </div>
                <?php if (empty($proximas)): ?>
                    <div class="empty-state"><i class="fa-solid fa-calendar-xmark"></i>Nenhuma partida agendada</div>
                <?php else: ?>
                    <?php foreach ($proximas as $p):
                        $d = new DateTime($p['data_partida']);
                    ?>
                    <div class="partida-item">
                        <div class="partida-data">
                            <div class="partida-dia"><?= $d->format('d') ?></div>
                            <div class="partida-mes"><?= strftime('%b', $d->getTimestamp()) ?></div>
                        </div>
                        <div class="partida-info">
                            <div class="partida-times"><?= htmlspecialchars($p['time_a']) ?> x <?= htmlspecialchars($p['time_b']) ?></div>
                            <div class="partida-detalhe">
                                <?= htmlspecialchars($p['nome_modalidade']) ?>
                                &middot; <?= $faseLabel[$p['fase_partida']] ?? $p['fase_partida'] ?>
                                <?php if ($p['local_partida']): ?>
                                    &middot; <?= htmlspecialchars($p['local_partida']) ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="partida-hora"><?= substr($p['hora_partida'], 0, 5) ?></div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div style="display:flex;flex-direction:column;gap:24px;">
                <div class="card">
                    <div class="card-header">
                        <div class="card-titulo"><i class="fa-solid fa-trophy"></i> Edições</div>
                    </div>
                    <?php if (empty($edicoes)): ?>
                        <div class="empty-state"><i class="fa-solid fa-trophy"></i>Nenhuma edição</div>
                    <?php else: ?>
                        <?php foreach ($edicoes as $ed): ?>
                        <div class="edicao-item">
                            <div class="edicao-icone"><i class="fa-solid fa-trophy"></i></div>
                            <div class="edicao-info">
                                <div class="edicao-nome"><?= htmlspecialchars($ed['nome_edicao']) ?></div>
                                <div class="edicao-detalhe"><?= $ed['ano_edicao'] ?> &middot; <?= $ed['total_modalidades'] ?> modalidade(s)</div>
                            </div>
                            <span class="badge-status <?= $ed['status_edicao'] ?>"><?= $statusLabel[$ed['status_edicao']] ?? $ed['status_edicao'] ?></span>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="grid-2">
            <div class="card">
                <div class="card-header">
                    <div class="card-titulo"><i class="fa-solid fa-check-circle"></i> Últimos Resultados</div>
                    <a href="#" class="card-link">Ver todos</a>
                </div>
                <?php if (empty($ultimos)): ?>
                    <div class="empty-state"><i class="fa-solid fa-circle-xmark"></i>Nenhum resultado ainda</div>
                <?php else: ?>
                    <?php foreach ($ultimos as $r): ?>
                    <div class="resultado-item">
                        <div class="res-placar">
                            <?= $r['placar_time_a'] ?? '-' ?> x <?= $r['placar_time_b'] ?? '-' ?>
                        </div>
                        <div class="res-info">
                            <div class="res-times"><?= htmlspecialchars($r['time_a']) ?> x <?= htmlspecialchars($r['time_b']) ?></div>
                            <div class="res-detalhe">
                                <?= htmlspecialchars($r['nome_modalidade']) ?>
                                &middot; <?= $faseLabel[$r['fase_partida']] ?? $r['fase_partida'] ?>
                                &middot; <?= date('d/m/Y', strtotime($r['data_partida'])) ?>
                            </div>
                        </div>
                        <?php if ($r['vencedor']): ?>
                            <div class="res-vencedor"><i class="fa-solid fa-crown"></i> <?= htmlspecialchars($r['vencedor']) ?></div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="card-titulo"><i class="fa-solid fa-file-lines"></i> Súmulas Recentes</div>
                    <a href="#" class="card-link">Ver todas</a>
                </div>
                <?php if (empty($sumulas)): ?>
                    <div class="empty-state"><i class="fa-solid fa-file-lines"></i>Nenhuma súmula enviada</div>
                <?php else: ?>
                    <?php foreach ($sumulas as $s): ?>
                    <div class="sumula-item">
                        <div class="sumula-icone" style="background:rgba(30,86,113,.08);color:var(--azul)">
                            <i class="fa-solid fa-file-pdf"></i>
                        </div>
                        <div class="sumula-info">
                            <div class="sumula-nome"><?= htmlspecialchars($s['time_a']) ?> x <?= htmlspecialchars($s['time_b']) ?></div>
                            <div class="sumula-detalhe"><?= date('d/m/Y', strtotime($s['data_partida'])) ?> &middot; Enviado <?= date('d/m H:i', strtotime($s['data_envio_sumula'])) ?></div>
                        </div>
                        <span class="badge-status <?= $s['status_sumula'] ?>"><?= ucfirst($s['status_sumula']) ?></span>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

    </main>
</div>

                    <!-- ( JS ) -->
    <script src="/soee/src/frontend/js/dash-prof.js"></script>
    <script>
        const _t = localStorage.getItem('theme');
        if (_t) document.documentElement.setAttribute('data-theme', _t);
    </script>

<?php include __DIR__ . '/../include/end.php';?>