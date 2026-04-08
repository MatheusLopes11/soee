<?php
session_start();
require_once __DIR__ . '/../include/conexao.php';
require_once __DIR__ . '/../auth/auth-home.php';

AuthHome::exigirTipo(['adm_sala']);

$userId   = AuthHome::getId();
$userNome = AuthHome::getNome();

$stmtUser = $conn->prepare("
    SELECT u.nome_usuario, u.email_usuario, u.foto_perfil_usuario,
           u.genero_usuario, u.turma_id_turma,
           t.nome_turma, t.ano_serie_turma, t.periodo_turma,
           c.nome_curso, c.sigla_curso
    FROM usuario u
    LEFT JOIN turma t ON t.id_turma = u.turma_id_turma
    LEFT JOIN curso c ON c.id_curso = t.curso_id_curso
    WHERE u.id_usuario = :id
");
$stmtUser->execute([':id' => $userId]);
$userData = $stmtUser->fetch(PDO::FETCH_ASSOC);

$turmaId = (int) ($userData['turma_id_turma'] ?? 0);

$stmtAlunos = $conn->prepare("
    SELECT u.id_usuario, u.nome_usuario, u.email_usuario, u.foto_perfil_usuario
    FROM usuario u
    WHERE u.turma_id_turma = :turma
      AND u.tipo_usuario = 'aluno'
      AND u.ativo_usuario = 1
    ORDER BY u.nome_usuario ASC
");
$stmtAlunos->execute([':turma' => $turmaId]);
$alunos = $stmtAlunos->fetchAll(PDO::FETCH_ASSOC);

$stmtInscricoes = $conn->prepare("
    SELECT i.id_inscricao, i.numero_camisa_inscricao, i.posicao_inscricao,
           i.capitao_inscricao, i.data_inscricao, i.status_inscricao,
           u.nome_usuario,
           m.nome_modalidade,
           e.nome_edicao
    FROM inscricao i
    INNER JOIN usuario u ON u.id_usuario = i.usuario_id_usuario
    INNER JOIN edicao_modalidade em ON em.id_edicao_modalidade = i.edicao_modalidade_id
    INNER JOIN modalidade m ON m.id_modalidade = em.modalidade_id_modalidade
    INNER JOIN edicao e ON e.id_edicao = em.edicao_id_edicao
    WHERE u.turma_id_turma = :turma
    ORDER BY i.data_inscricao DESC
    LIMIT 10
");
$stmtInscricoes->execute([':turma' => $turmaId]);
$inscricoes = $stmtInscricoes->fetchAll(PDO::FETCH_ASSOC);

$stmtPartidas = $conn->prepare("
    SELECT p.id_partida, p.data_partida, p.hora_partida,
           p.local_partida, p.fase_partida, p.status_partida,
           ta.nome_turma AS time_a, tb.nome_turma AS time_b,
           m.nome_modalidade,
           r.placar_time_a, r.placar_time_b
    FROM partida p
    INNER JOIN turma ta ON ta.id_turma = p.turma_id_time_a
    INNER JOIN turma tb ON tb.id_turma = p.turma_id_time_b
    INNER JOIN edicao_modalidade em ON em.id_edicao_modalidade = p.edicao_modalidade_id
    INNER JOIN modalidade m ON m.id_modalidade = em.modalidade_id_modalidade
    LEFT JOIN resultado r ON r.partida_id_partida = p.id_partida
    WHERE (p.turma_id_time_a = :turma1 OR p.turma_id_time_b = :turma2)
    ORDER BY p.data_partida DESC, p.hora_partida DESC
    LIMIT 8
");
$stmtPartidas->execute([':turma1' => $turmaId, ':turma2' => $turmaId]);
$partidas = $stmtPartidas->fetchAll(PDO::FETCH_ASSOC);

$stmtClassif = $conn->prepare("
    SELECT cl.pontos, cl.vitorias, cl.derrotas, cl.empates,
           cl.pontos_pro, cl.pontos_contra, cl.saldo, cl.jogos,
           m.nome_modalidade, e.nome_edicao
    FROM classificacao cl
    INNER JOIN edicao_modalidade em ON em.id_edicao_modalidade = cl.edicao_modalidade_id
    INNER JOIN modalidade m ON m.id_modalidade = em.modalidade_id_modalidade
    INNER JOIN edicao e ON e.id_edicao = em.edicao_id_edicao
    WHERE cl.turma_id_turma = :turma
    ORDER BY cl.pontos DESC
");
$stmtClassif->execute([':turma' => $turmaId]);
$classificacoes = $stmtClassif->fetchAll(PDO::FETCH_ASSOC);

$stmtStats = $conn->prepare("
    SELECT
        (SELECT COUNT(*) FROM usuario
         WHERE turma_id_turma = :t1 AND tipo_usuario = 'aluno' AND ativo_usuario = 1) AS total_alunos,
        (SELECT COUNT(*) FROM inscricao i
         INNER JOIN usuario u ON u.id_usuario = i.usuario_id_usuario
         WHERE u.turma_id_turma = :t2 AND i.status_inscricao = 'ativa') AS total_inscricoes,
        (SELECT COUNT(*) FROM partida
         WHERE (turma_id_time_a = :t3 OR turma_id_time_b = :t3b)
           AND status_partida = 'realizada') AS partidas_realizadas,
        (SELECT COUNT(*) FROM partida
         WHERE (turma_id_time_a = :t4 OR turma_id_time_b = :t4b)
           AND status_partida = 'agendada'
           AND data_partida >= CURDATE()) AS proximas_partidas
");
$stmtStats->execute([
    ':t1'  => $turmaId, ':t2'  => $turmaId,
    ':t3'  => $turmaId, ':t3b' => $turmaId,
    ':t4'  => $turmaId, ':t4b' => $turmaId,
]);
$stats = $stmtStats->fetch(PDO::FETCH_ASSOC);

$stmtModalidades = $conn->query("
    SELECT m.id_modalidade, m.nome_modalidade, m.tipo_participacao,
           em.id_edicao_modalidade, em.data_inicio_inscricao, em.data_fim_inscricao,
           em.status_edicao_modalidade, e.nome_edicao
    FROM edicao_modalidade em
    INNER JOIN modalidade m ON m.id_modalidade = em.modalidade_id_modalidade
    INNER JOIN edicao e ON e.id_edicao = em.edicao_id_edicao
    WHERE em.status_edicao_modalidade IN ('inscricoes', 'em_andamento')
      AND e.status_edicao != 'encerrado'
    ORDER BY em.data_fim_inscricao ASC
    LIMIT 6
");
$modalidades = $stmtModalidades->fetchAll(PDO::FETCH_ASSOC);

$faseLabel = [
    'grupos' => 'Grupos', 'oitavas' => 'Oitavas',
    'quartas' => 'Quartas', 'semi' => 'Semi', 'final' => 'Final',
    'terceiro_lugar' => '3º Lugar',
];
$statusPartidaLabel = [
    'agendada' => 'Agendada', 'realizada' => 'Realizada',
    'cancelada' => 'Cancelada', 'wo' => 'W.O.',
];
?>
<?php include __DIR__ . '/../include/doctype.php'; ?>
<head>
    <title>Dashboard — ADM Sala | SOEE</title>
    <link rel="stylesheet" href="/soee/src/frontend/css/dash-adm-sala.css">
    <?php include __DIR__ . '/../include/head-data.php'; ?>
</head>
<body>

<aside class="sidebar" id="sidebar">
    <div class="sidebar-logo">
        <a href="/soee/src/backend/php/pages/inicio.php">S<span>O</span>EE</a>
        <small>ADM de Sala</small>
    </div>

    <?php if ($turmaId): ?>
    <div class="sidebar-turma">
        <div class="turma-label">Sua Turma</div>
        <div class="turma-nome"><?= htmlspecialchars($userData['nome_turma'] ?? '—') ?></div>
        <div class="turma-curso"><?= htmlspecialchars($userData['sigla_curso'] ?? '') ?> &middot; <?= ucfirst($userData['periodo_turma'] ?? '') ?></div>
    </div>
    <?php endif; ?>

    <a href="/soee/src/backend/php/pages/user-conta.php" class="sidebar-perfil"
       style="text-decoration:none;display:flex;align-items:center;gap:12px;padding:16px;border-bottom:1px solid rgba(255,255,255,.08);margin-top:12px;transition:background .2s;"
       onmouseover="this.style.background='rgba(255,255,255,0.05)'"
       onmouseout="this.style.background='none'">
        <div class="perfil-avatar">
            <?php if (!empty($userData['foto_perfil_usuario'])): ?>
                <img src="<?= htmlspecialchars($userData['foto_perfil_usuario']) ?>" alt="">
            <?php else: ?>
                <i class="fa-solid fa-user-shield"></i>
            <?php endif; ?>
        </div>
        <div class="perfil-info">
            <div class="perfil-nome"><?= htmlspecialchars($userNome) ?></div>
            <div class="perfil-cargo">ADM de Sala</div>
        </div>
    </a>

    <nav class="sidebar-nav">
        <div class="nav-secao">Painel</div>
        <a href="#" class="nav-item ativo"><i class="fa-solid fa-gauge"></i> Dashboard</a>
        <a href="/soee/src/backend/php/pages/inicio.php" class="nav-item"><i class="fa-solid fa-house"></i> Início</a>

        <div class="nav-secao">Minha Sala</div>
        <a href="#" class="nav-item">
            <i class="fa-solid fa-users"></i> Alunos
            <span class="nav-badge"><?= count($alunos) ?></span>
        </a>
        <a href="#" class="nav-item">
            <i class="fa-solid fa-clipboard-list"></i> Inscrições
            <span class="nav-badge"><?= $stats['total_inscricoes'] ?></span>
        </a>
        <a href="#" class="nav-item"><i class="fa-solid fa-calendar-days"></i> Partidas</a>
        <a href="#" class="nav-item"><i class="fa-solid fa-ranking-star"></i> Classificação</a>

        <div class="nav-secao">Outros</div>
        <a href="#" class="nav-item"><i class="fa-solid fa-futbol"></i> Modalidades</a>
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
        <button class="btn-icone" id="toggleSidebar" aria-label="Menu">
            <i class="fa-solid fa-bars"></i>
        </button>
        <div class="topbar-titulo">Dashboard — ADM de Sala</div>
        <?php if ($turmaId): ?>
            <span class="topbar-turma"><?= htmlspecialchars($userData['nome_turma'] ?? '') ?></span>
        <?php endif; ?>
        <div class="topbar-acoes">
            <button class="btn-icone" id="toggleTema" aria-label="Tema">
                <i class="fa-solid fa-moon" id="iconeTema"></i>
            </button>
        </div>
    </header>

    <main class="pagina">

        <div class="boas-vindas">
            <div class="bv-esq">
                <?php if (!empty($userData['nome_turma'])): ?>
                <div class="bv-turma-badge">
                    <div class="bv-turma-sigla"><?= htmlspecialchars($userData['nome_turma']) ?></div>
                    <div class="bv-turma-periodo"><?= ucfirst($userData['periodo_turma'] ?? '') ?></div>
                </div>
                <?php endif; ?>
                <div class="bv-texto">
                    <h2>Olá, <?= htmlspecialchars(explode(' ', $userNome)[0]) ?>!</h2>
                    <p>Gerencie sua sala, inscrições e acompanhe o desempenho nos interclasses.</p>
                </div>
            </div>
            <div class="bv-acoes">
                <a href="#" class="btn-acesso-rapido">
                    <i class="fa-solid fa-user-plus"></i> Inscrever Aluno
                </a>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card azul">
                <div class="stat-icone"><i class="fa-solid fa-users"></i></div>
                <div class="stat-valor"><?= $stats['total_alunos'] ?></div>
                <div class="stat-label">Alunos na Sala</div>
            </div>
            <div class="stat-card laranja">
                <div class="stat-icone"><i class="fa-solid fa-clipboard-check"></i></div>
                <div class="stat-valor"><?= $stats['total_inscricoes'] ?></div>
                <div class="stat-label">Inscrições Ativas</div>
            </div>
            <div class="stat-card verde">
                <div class="stat-icone"><i class="fa-solid fa-flag-checkered"></i></div>
                <div class="stat-valor"><?= $stats['partidas_realizadas'] ?></div>
                <div class="stat-label">Partidas Jogadas</div>
            </div>
            <div class="stat-card amarelo">
                <div class="stat-icone"><i class="fa-solid fa-calendar-check"></i></div>
                <div class="stat-valor"><?= $stats['proximas_partidas'] ?></div>
                <div class="stat-label">Próximas Partidas</div>
            </div>
        </div>

        <div class="grid-2" style="margin-bottom:24px;">

            <div class="card">
                <div class="card-header">
                    <div class="card-titulo"><i class="fa-solid fa-calendar"></i> Partidas da Turma</div>
                    <a href="#" class="card-link">Ver todas</a>
                </div>
                <?php if (empty($partidas)): ?>
                    <div class="empty-state"><i class="fa-solid fa-calendar-xmark"></i>Nenhuma partida encontrada</div>
                <?php else: ?>
                    <?php foreach ($partidas as $p):
                        $temPlacar = isset($p['placar_time_a']) && $p['status_partida'] === 'realizada';
                    ?>
                    <div class="partida-item">
                        <div class="partida-status-dot <?= $p['status_partida'] ?>"></div>
                        <div class="partida-info">
                            <div class="partida-times"><?= htmlspecialchars($p['time_a']) ?> x <?= htmlspecialchars($p['time_b']) ?></div>
                            <div class="partida-detalhe">
                                <?= htmlspecialchars($p['nome_modalidade']) ?>
                                &middot; <?= $faseLabel[$p['fase_partida']] ?? $p['fase_partida'] ?>
                                &middot; <?= date('d/m/Y', strtotime($p['data_partida'])) ?>
                                <?php if ($p['hora_partida'] && $p['status_partida'] === 'agendada'): ?>
                                    &middot; <?= substr($p['hora_partida'], 0, 5) ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="partida-placar <?= !$temPlacar ? 'agendada' : '' ?>">
                            <?= $temPlacar
                                ? $p['placar_time_a'].' x '.$p['placar_time_b']
                                : ($statusPartidaLabel[$p['status_partida']] ?? $p['status_partida']) ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div style="display:flex;flex-direction:column;gap:24px;">
                <div class="card">
                    <div class="card-header">
                        <div class="card-titulo"><i class="fa-solid fa-ranking-star"></i> Classificação</div>
                    </div>
                    <?php if (empty($classificacoes)): ?>
                        <div class="empty-state"><i class="fa-solid fa-ranking-star"></i>Sem classificação</div>
                    <?php else: ?>
                        <?php foreach ($classificacoes as $cl): ?>
                        <div class="classif-item">
                            <div class="classif-info">
                                <div class="classif-modalidade"><?= htmlspecialchars($cl['nome_modalidade']) ?></div>
                                <div class="classif-edicao"><?= htmlspecialchars($cl['nome_edicao']) ?></div>
                            </div>
                            <div class="classif-stats">
                                <div class="cstat">
                                    <div class="cstat-val" style="color:var(--laranja)"><?= $cl['pontos'] ?></div>
                                    <div class="cstat-label">Pts</div>
                                </div>
                                <div class="cstat">
                                    <div class="cstat-val" style="color:var(--verde)"><?= $cl['vitorias'] ?></div>
                                    <div class="cstat-label">V</div>
                                </div>
                                <div class="cstat">
                                    <div class="cstat-val" style="color:var(--vermelho)"><?= $cl['derrotas'] ?></div>
                                    <div class="cstat-label">D</div>
                                </div>
                                <div class="cstat">
                                    <div class="cstat-val"><?= $cl['saldo'] >= 0 ? '+'.$cl['saldo'] : $cl['saldo'] ?></div>
                                    <div class="cstat-label">Saldo</div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="card">
                    <div class="card-header">
                        <div class="card-titulo"><i class="fa-solid fa-futbol"></i> Modalidades Abertas</div>
                    </div>
                    <?php if (empty($modalidades)): ?>
                        <div class="empty-state"><i class="fa-solid fa-futbol"></i>Nenhuma aberta</div>
                    <?php else: ?>
                        <?php foreach ($modalidades as $md):
                            $fim    = new DateTime($md['data_fim_inscricao']);
                            $hoje   = new DateTime();
                            $diff   = (int) $hoje->diff($fim)->days;
                            $urgente = $diff <= 3;
                        ?>
                        <div class="modal-inscricao-item">
                            <div class="mi-topo">
                                <span class="mi-nome"><?= htmlspecialchars($md['nome_modalidade']) ?></span>
                                <span class="badge-status <?= $md['status_edicao_modalidade'] ?>">
                                    <?= $md['status_edicao_modalidade'] === 'inscricoes' ? 'Inscrições' : 'Em Andamento' ?>
                                </span>
                            </div>
                            <div class="mi-prazo <?= $urgente ? 'urgente' : '' ?>">
                                <?php if ($md['status_edicao_modalidade'] === 'inscricoes'): ?>
                                    Inscrições até <?= $fim->format('d/m/Y') ?>
                                    <?= $urgente ? ' — '.$diff.' dia(s)!' : '' ?>
                                <?php else: ?>
                                    <?= ucfirst($md['tipo_participacao']) ?> &middot; <?= htmlspecialchars($md['nome_edicao']) ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="grid-2">
            <div class="card">
                <div class="card-header">
                    <div class="card-titulo"><i class="fa-solid fa-users"></i> Alunos da Turma</div>
                    <span style="font-size:.8rem;color:var(--texto-2)"><?= count($alunos) ?> aluno(s)</span>
                </div>
                <div class="busca-aluno">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" id="buscaAluno" placeholder="Buscar aluno..." autocomplete="off">
                </div>
                <div class="tabela-alunos-lista" id="listaAlunos">
                    <?php if (empty($alunos)): ?>
                        <div class="empty-state"><i class="fa-solid fa-user-slash"></i>Nenhum aluno na turma</div>
                    <?php else: ?>
                        <?php foreach ($alunos as $a): ?>
                        <div class="aluno-item" data-nome="<?= strtolower(htmlspecialchars($a['nome_usuario'])) ?>">
                            <div class="aluno-avatar">
                                <?php if (!empty($a['foto_perfil_usuario'])): ?>
                                    <img src="<?= htmlspecialchars($a['foto_perfil_usuario']) ?>" alt="">
                                <?php else: ?>
                                    <i class="fa-solid fa-user"></i>
                                <?php endif; ?>
                            </div>
                            <div>
                                <div class="aluno-nome"><?= htmlspecialchars($a['nome_usuario']) ?></div>
                                <div class="aluno-email"><?= htmlspecialchars($a['email_usuario']) ?></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="card-titulo"><i class="fa-solid fa-clipboard-list"></i> Inscrições Recentes</div>
                    <a href="#" class="card-link">Ver todas</a>
                </div>
                <?php if (empty($inscricoes)): ?>
                    <div class="empty-state"><i class="fa-solid fa-clipboard-list"></i>Nenhuma inscrição</div>
                <?php else: ?>
                    <?php foreach ($inscricoes as $ins): ?>
                    <div class="inscricao-item">
                        <div class="ins-info">
                            <div class="ins-nome"><?= htmlspecialchars($ins['nome_usuario']) ?></div>
                            <div class="ins-detalhe">
                                <?= htmlspecialchars($ins['nome_modalidade']) ?>
                                <?php if ($ins['posicao_inscricao']): ?>
                                    &middot; <?= htmlspecialchars($ins['posicao_inscricao']) ?>
                                <?php endif; ?>
                                <?php if ($ins['capitao_inscricao']): ?>
                                    &middot; <span style="color:var(--laranja);font-weight:700">
                                        <i class="fa-solid fa-star" style="font-size:.65rem"></i> Capitão
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php if ($ins['numero_camisa_inscricao']): ?>
                            <div class="ins-camisa">#<?= $ins['numero_camisa_inscricao'] ?></div>
                        <?php endif; ?>
                        <span class="badge-status <?= $ins['status_inscricao'] ?>"><?= ucfirst($ins['status_inscricao']) ?></span>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

    </main>
</div>

<script src="/soee/src/frontend/js/dash-adm-sala.js"></script>
<script>
const _t = localStorage.getItem('theme');
if (_t) document.documentElement.setAttribute('data-theme', _t);
</script>

<?php include __DIR__ . '/../include/end.php'; ?>