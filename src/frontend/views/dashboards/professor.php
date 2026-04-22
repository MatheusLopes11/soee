<?php
session_start();
require_once __DIR__ . '/../../../backend/includes/conexao.php';
require_once __DIR__ . '/../../../backend/controllers/home.php';

AuthHome::exigirTipo(['professor']);

$usuario_logado = AuthHome::getNome();
$userId         = AuthHome::getId();

/* ── FOTO DE PERFIL ─────────────────────────────── */
$stmtFoto = $conn->prepare("
    SELECT fp.caminho_foto
    FROM foto_perfil fp
    WHERE fp.usuario_id_usuario = :id
      AND fp.atual_foto = 1
    LIMIT 1
");
$stmtFoto->execute([':id' => $userId]);
$fotoPerfil = $stmtFoto->fetchColumn();

/* ── KPIs ────────────────────────────────────────── */
$kpi_alunos      = $conn->query("SELECT COUNT(*) FROM usuario WHERE tipo_usuario = 'aluno' AND ativo_usuario = 1")->fetchColumn();
$kpi_partidas    = $conn->query("SELECT COUNT(*) FROM partida WHERE status_partida = 'agendada'")->fetchColumn();
$kpi_realizadas  = $conn->query("SELECT COUNT(*) FROM partida WHERE status_partida = 'realizada'")->fetchColumn();
$kpi_modalidades = $conn->query("SELECT COUNT(*) FROM modalidade WHERE ativo_modalidade = 1")->fetchColumn();

/* ── AGENDA ──────────────────────────────────────── */
$agenda = $conn->query("
    SELECT p.id_partida, m.nome_modalidade,
           ta.nome_turma AS time_a, tb.nome_turma AS time_b,
           p.data_partida, p.hora_partida, p.local_partida, p.fase_partida
    FROM partida p
    JOIN edicao_modalidade em ON em.id_edicao_modalidade = p.edicao_modalidade_id
    JOIN modalidade m  ON m.id_modalidade  = em.modalidade_id_modalidade
    JOIN turma ta ON ta.id_turma = p.turma_id_time_a
    JOIN turma tb ON tb.id_turma = p.turma_id_time_b
    WHERE p.status_partida = 'agendada'
    ORDER BY p.data_partida, p.hora_partida
    LIMIT 30
")->fetchAll(PDO::FETCH_ASSOC);

/* ── SÚMULAS ─────────────────────────────────────── */
$sumulas = $conn->query("
    SELECT s.id_sumula,
           ta.nome_turma AS time_a, tb.nome_turma AS time_b,
           m.nome_modalidade,
           u.nome_usuario AS enviado_por,
           s.nome_arquivo_sumula, s.tipo_arquivo_sumula,
           s.data_envio_sumula, s.status_sumula
    FROM sumula s
    JOIN partida p  ON p.id_partida  = s.partida_id_partida
    JOIN edicao_modalidade em ON em.id_edicao_modalidade = p.edicao_modalidade_id
    JOIN modalidade m  ON m.id_modalidade  = em.modalidade_id_modalidade
    JOIN turma ta ON ta.id_turma = p.turma_id_time_a
    JOIN turma tb ON tb.id_turma = p.turma_id_time_b
    JOIN usuario u ON u.id_usuario = s.usuario_id_enviou
    ORDER BY s.data_envio_sumula DESC
")->fetchAll(PDO::FETCH_ASSOC);
$sumulas_pendentes = array_filter($sumulas, fn($s) => $s['status_sumula'] === 'pendente');

/* ── PARTIDAS ────────────────────────────────────── */
$partidas = $conn->query("
    SELECT p.id_partida, m.nome_modalidade,
           ta.nome_turma AS time_a, tb.nome_turma AS time_b,
           p.data_partida, p.hora_partida, p.local_partida,
           p.fase_partida, p.status_partida
    FROM partida p
    JOIN edicao_modalidade em ON em.id_edicao_modalidade = p.edicao_modalidade_id
    JOIN modalidade m  ON m.id_modalidade  = em.modalidade_id_modalidade
    JOIN turma ta ON ta.id_turma = p.turma_id_time_a
    JOIN turma tb ON tb.id_turma = p.turma_id_time_b
    ORDER BY p.data_partida DESC, p.hora_partida DESC
")->fetchAll(PDO::FETCH_ASSOC);

/* ── RESULTADOS ──────────────────────────────────── */
/*
   Busca TODAS as partidas já realizadas (com ou sem resultado),
   para exibir tanto placares registrados quanto o formulário inline.
*/
$resultados = $conn->query("
    SELECT p.id_partida,
           p.data_partida, p.status_partida,
           ta.nome_turma AS time_a, tb.nome_turma AS time_b,
           m.nome_modalidade,
           r.id_resultado,
           r.placar_time_a, r.placar_time_b,
           tv.nome_turma AS vencedor
    FROM partida p
    JOIN edicao_modalidade em ON em.id_edicao_modalidade = p.edicao_modalidade_id
    JOIN modalidade m  ON m.id_modalidade  = em.modalidade_id_modalidade
    JOIN turma ta ON ta.id_turma = p.turma_id_time_a
    JOIN turma tb ON tb.id_turma = p.turma_id_time_b
    LEFT JOIN resultado r ON r.partida_id_partida = p.id_partida
    LEFT JOIN turma tv   ON tv.id_turma = r.turma_id_vencedor
    WHERE p.status_partida IN ('realizada','agendada')
    ORDER BY p.data_partida DESC, p.hora_partida DESC
")->fetchAll(PDO::FETCH_ASSOC);

/* ── EDIÇÕES ─────────────────────────────────────── */
$edicoes = $conn->query("
    SELECT id_edicao, nome_edicao, ano_edicao,
           data_inicio_edicao, data_fim_edicao, status_edicao
    FROM edicao ORDER BY id_edicao DESC
")->fetchAll(PDO::FETCH_ASSOC);

/* ── ALUNOS ──────────────────────────────────────── */
$alunos = $conn->query("
    SELECT u.id_usuario, u.nome_usuario, u.email_usuario,
           t.nome_turma, u.tipo_usuario, u.genero_usuario, u.ativo_usuario
    FROM usuario u
    LEFT JOIN turma t ON t.id_turma = u.turma_id_turma
    WHERE u.tipo_usuario IN ('aluno','adm_sala')
    ORDER BY t.nome_turma, u.nome_usuario
")->fetchAll(PDO::FETCH_ASSOC);

/* ── SELECTs para modais ─────────────────────────── */
$turmas_select = $conn->query("SELECT id_turma, nome_turma FROM turma ORDER BY nome_turma")->fetchAll(PDO::FETCH_ASSOC);

$partidas_select = $conn->query("
    SELECT p.id_partida,
           CONCAT(m.nome_modalidade,' — ',ta.nome_turma,' vs ',tb.nome_turma,
                  ' (',DATE_FORMAT(p.data_partida,'%d/%m'),')') AS label
    FROM partida p
    JOIN edicao_modalidade em ON em.id_edicao_modalidade = p.edicao_modalidade_id
    JOIN modalidade m  ON m.id_modalidade  = em.modalidade_id_modalidade
    JOIN turma ta ON ta.id_turma = p.turma_id_time_a
    JOIN turma tb ON tb.id_turma = p.turma_id_time_b
    ORDER BY p.data_partida DESC
")->fetchAll(PDO::FETCH_ASSOC);

$edicoes_modal_select = $conn->query("
    SELECT em.id_edicao_modalidade,
           CONCAT(e.nome_edicao,' — ',m.nome_modalidade) AS label
    FROM edicao_modalidade em
    JOIN edicao e ON e.id_edicao = em.edicao_id_edicao
    JOIN modalidade m ON m.id_modalidade = em.modalidade_id_modalidade
    ORDER BY e.ano_edicao DESC, m.nome_modalidade
")->fetchAll(PDO::FETCH_ASSOC);

/* ── SORTEIOS JÁ GERADOS ─────────────────────────── */
$sorteiosGerados = [];
try {
    foreach ($conn->query("SELECT edicao_modalidade_id FROM sorteio_gerado")->fetchAll(PDO::FETCH_COLUMN) as $emId) {
        $sorteiosGerados[$emId] = true;
    }
} catch (Exception $e) { /* tabela ainda não existe */ }

/* ── MODALIDADES POR EDIÇÃO ──────────────────────── */
$modalidadesPorEdicao = [];
$stmtMpe = $conn->query("
    SELECT em.id_edicao_modalidade, em.edicao_id_edicao,
           em.status_edicao_modalidade,
           m.nome_modalidade,
           COUNT(DISTINCT u.turma_id_turma) AS turmas_inscritas
    FROM edicao_modalidade em
    INNER JOIN modalidade m ON m.id_modalidade = em.modalidade_id_modalidade
    LEFT JOIN inscricao i ON i.edicao_modalidade_id = em.id_edicao_modalidade
        AND i.status_inscricao = 'ativa'
    LEFT JOIN usuario u ON u.id_usuario = i.usuario_id_usuario
    GROUP BY em.id_edicao_modalidade
    ORDER BY m.nome_modalidade ASC
");
foreach ($stmtMpe->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $modalidadesPorEdicao[$row['edicao_id_edicao']][] = $row;
}

/* ── FLASH ───────────────────────────────────────── */
$flashMsg  = $_SESSION['flash_msg']  ?? '';
$flashTipo = $_SESSION['flash_tipo'] ?? '';
unset($_SESSION['flash_msg'], $_SESSION['flash_tipo']);

/* ── HELPERS ─────────────────────────────────────── */
function badgeStatus(string $s): string {
    $map = [
        'agendada'    => 'ativo',    'realizada'  => 'verde',
        'ativo'       => 'ativo',    'inativo'    => 'inativo',
        'em_andamento'=> 'ativo',    'encerrado'  => 'encerrado',
        'pendente'    => 'pendente', 'validada'   => 'ativo',
        'rejeitada'   => 'inativo',  'inscricoes' => 'pendente',
        'planejamento'=> 'pendente', 'cancelada'  => 'inativo',
        'wo'          => 'inativo',
    ];
    $labels = [
        'agendada'    => 'Agendada',    'realizada'   => 'Realizada',
        'ativo'       => 'Ativo',       'inativo'     => 'Inativo',
        'em_andamento'=> 'Em Andamento','encerrado'   => 'Encerrado',
        'pendente'    => 'Pendente',    'validada'    => 'Validada',
        'rejeitada'   => 'Rejeitada',   'inscricoes'  => 'Inscrições',
        'planejamento'=> 'Planejamento','cancelada'   => 'Cancelada',
        'wo'          => 'W.O.',
    ];
    return '<span class="badge-status '.($map[$s] ?? 'pendente').'">'.($labels[$s] ?? ucfirst($s)).'</span>';
}
function fmtData(?string $d): string { return $d ? date('d/m/Y', strtotime($d)) : '—'; }
function fmtHora(?string $h): string { return $h ? substr($h, 0, 5) : '—'; }

$dias_pt  = ['Sunday'=>'Dom','Monday'=>'Seg','Tuesday'=>'Ter','Wednesday'=>'Qua',
             'Thursday'=>'Qui','Friday'=>'Sex','Saturday'=>'Sáb'];
$meses_pt = ['January'=>'Janeiro','February'=>'Fevereiro','March'=>'Março',
             'April'=>'Abril','May'=>'Maio','June'=>'Junho','July'=>'Julho',
             'August'=>'Agosto','September'=>'Setembro','October'=>'Outubro',
             'November'=>'Novembro','December'=>'Dezembro'];
$faseLabel = ['grupos'=>'Grupos','oitavas'=>'Oitavas','quartas'=>'Quartas',
              'semi'=>'Semifinal','final'=>'Final','terceiro_lugar'=>'3º Lugar'];
?>
<?php include __DIR__ . '/../includes/doctype.php'; ?>
<head>
    <title>SOEE | Dashboard Professor</title>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="/soee/src/frontend/styles/dash-adm.css">
    <link rel="stylesheet" href="/soee/src/frontend/styles/dash-prof.css">
    <link rel="icon" type="image/png" href="/soee/src/images/logo-soee.png">
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;800&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>
<body>

<?php if ($flashMsg): ?>
<div class="toast-container" id="toast-container" style="position:fixed;bottom:28px;right:28px;z-index:9999;">
    <div class="toast <?= htmlspecialchars($flashTipo) ?>" style="display:flex;">
        <i class="fas <?= $flashTipo === 'sucesso' ? 'fa-check-circle' : 'fa-times-circle' ?>"></i>
        <span><?= htmlspecialchars($flashMsg) ?></span>
    </div>
</div>
<?php endif; ?>

<!-- ══════ SIDEBAR ══════ -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-logo">
        <div class="sidebar-logo-text">SOEE<span class="sidebar-logo-dot"></span></div>
        <div class="sidebar-logo-sub">Painel do Professor</div>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-group-label">Visão Geral</div>
        <a class="nav-item active" href="javascript:void(0)" data-painel="overview" onclick="trocarPainel(this)">
            <i class="fas fa-chart-line"></i> Dashboard
        </a>
        <a class="nav-item" href="javascript:void(0)" data-painel="agenda" onclick="trocarPainel(this)">
            <i class="fas fa-calendar-alt"></i> Agenda de Partidas
            <?php if (count($agenda)): ?>
                <span class="nav-badge"><?= count($agenda) ?></span>
            <?php endif; ?>
        </a>

        <div class="nav-group-label">Competições</div>
        <a class="nav-item" href="javascript:void(0)" data-painel="edicoes" onclick="trocarPainel(this)">
            <i class="fas fa-trophy"></i> Edições / Eventos
            <span class="nav-badge"><?= count($edicoes) ?></span>
        </a>
        <a class="nav-item" href="javascript:void(0)" data-painel="partidas" onclick="trocarPainel(this)">
            <i class="fas fa-calendar-days"></i> Partidas
        </a>
        <a class="nav-item" href="javascript:void(0)" data-painel="resultados" onclick="trocarPainel(this)">
            <i class="fas fa-flag-checkered"></i> Resultados
        </a>

        <div class="nav-group-label">Gestão</div>
        <a class="nav-item" href="javascript:void(0)" data-painel="sumulas" onclick="trocarPainel(this)">
            <i class="fas fa-file-alt"></i> Súmulas
            <?php if (count($sumulas_pendentes)): ?>
                <span class="nav-badge"><?= count($sumulas_pendentes) ?></span>
            <?php endif; ?>
        </a>
        <a class="nav-item" href="javascript:void(0)" data-painel="alunos" onclick="trocarPainel(this)">
            <i class="fas fa-users"></i> Alunos
        </a>
        <a class="nav-item" href="/soee/src/frontend/views/forms/feedback.php">
            <i class="fas fa-comment-dots"></i> Feedback
        </a>
    </nav>

    <div class="sidebar-footer">
        <a href="/soee/src/frontend/views/site/profile.php" class="user-card"
           style="text-decoration:none;display:flex;align-items:center;gap:12px;padding:8px;border-radius:var(--raio-medio);transition:background .2s;"
           onmouseover="this.style.background='rgba(255,255,255,0.07)'"
           onmouseout="this.style.background='none'">
            <div class="user-avatar">
                <?php if (!empty($fotoPerfil)): ?>
                    <img src="<?= htmlspecialchars($fotoPerfil) ?>" alt="Foto de perfil"
                         onerror="this.style.display='none';this.parentNode.innerHTML='<?= strtoupper(substr($usuario_logado, 0, 2)) ?>';"
                         style="width:100%;height:100%;object-fit:cover;border-radius:50%;">
                <?php else: ?>
                    <?= strtoupper(substr($usuario_logado, 0, 2)) ?>
                <?php endif; ?>
            </div>
            <div class="user-info">
                <strong><?= htmlspecialchars($usuario_logado) ?></strong>
                <span>Professor</span>
            </div>
        </a>
        <a href="/soee/src/backend/includes/logout.php"
           style="display:flex;align-items:center;gap:8px;margin-top:10px;padding:8px 10px;border-radius:var(--raio-medio);color:rgba(255,255,255,.4);font-size:.8rem;text-decoration:none;transition:color .2s;"
           onmouseover="this.style.color='#fca5a5'" onmouseout="this.style.color='rgba(255,255,255,.4)'">
            <i class="fas fa-right-from-bracket"></i> Sair da conta
        </a>
    </div>
</aside>

<!-- ══════ MAIN ══════ -->
<div class="main">

    <header class="topbar">
        <div class="topbar-title" id="topbar-titulo">Dashboard</div>
        <div class="topbar-search">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="Buscar aluno, turma, partida…" />
        </div>
        <button class="botao-icone" onclick="alternarTema()" title="Tema">
            <i class="fas fa-moon" id="tema-icone"></i>
        </button>
        <a href="/soee/src/frontend/views/site/home.php" class="botao-icone" title="Início">
            <i class="fas fa-home"></i>
        </a>
    </header>

    <div class="content">

        <!-- ════════════════════════════════════
             PAINEL: OVERVIEW
        ════════════════════════════════════ -->
        <div class="painel active" id="painel-overview">
            <div class="kpi-grid">
                <div class="kpi-card azul">
                    <div class="kpi-icon"><i class="fas fa-users"></i></div>
                    <div class="kpi-num"><?= (int)$kpi_alunos ?></div>
                    <div class="kpi-label">Alunos Cadastrados</div>
                </div>
                <div class="kpi-card laranja">
                    <div class="kpi-icon"><i class="fas fa-futbol"></i></div>
                    <div class="kpi-num"><?= (int)$kpi_partidas ?></div>
                    <div class="kpi-label">Partidas Agendadas</div>
                </div>
                <div class="kpi-card verde">
                    <div class="kpi-icon"><i class="fas fa-check-circle"></i></div>
                    <div class="kpi-num"><?= (int)$kpi_realizadas ?></div>
                    <div class="kpi-label">Partidas Realizadas</div>
                </div>
                <div class="kpi-card roxo">
                    <div class="kpi-icon"><i class="fas fa-layer-group"></i></div>
                    <div class="kpi-num"><?= (int)$kpi_modalidades ?></div>
                    <div class="kpi-label">Modalidades Ativas</div>
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">
                <!-- Próximas Partidas -->
                <div class="secao-card">
                    <div class="secao-card-header">
                        <h3>Próximas Partidas</h3>
                        <button class="btn btn-primario btn-sm" onclick="trocarPainelById('agenda')">
                            <i class="fas fa-arrow-right"></i> Ver todas
                        </button>
                    </div>
                    <?php if (empty($agenda)): ?>
                        <p style="padding:16px;opacity:.6;">Nenhuma partida agendada.</p>
                    <?php else: ?>
                    <div class="agenda-lista">
                        <?php foreach (array_slice($agenda, 0, 4) as $p):
                            $dt = new DateTime($p['data_partida']); ?>
                        <div class="agenda-item">
                            <div class="agenda-data">
                                <strong><?= $dt->format('d') ?></strong>
                                <span><?= $dt->format('M') ?></span>
                            </div>
                            <div class="agenda-info">
                                <strong><?= htmlspecialchars($p['time_a'] . ' vs ' . $p['time_b'] . ' — ' . $p['nome_modalidade']) ?></strong>
                                <?php if ($p['local_partida']): ?>
                                    <span><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($p['local_partida']) ?></span>
                                <?php endif; ?>
                            </div>
                            <span class="agenda-hora"><?= fmtHora($p['hora_partida']) ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Súmulas Pendentes -->
                <div class="secao-card">
                    <div class="secao-card-header">
                        <h3>Súmulas Pendentes</h3>
                        <span class="secao-tag-mini"><?= count($sumulas_pendentes) ?> pendentes</span>
                    </div>
                    <?php if (empty($sumulas_pendentes)): ?>
                        <p style="padding:16px;opacity:.6;">Nenhuma súmula pendente.</p>
                    <?php else: ?>
                    <div class="tabela-wrap">
                        <table>
                            <thead>
                                <tr><th>Partida</th><th>Enviado por</th><th>Status</th><th>Ações</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice(array_values($sumulas_pendentes), 0, 3) as $s): ?>
                                <tr>
                                    <td><?= htmlspecialchars($s['nome_modalidade'] . ' — ' . $s['time_a'] . ' vs ' . $s['time_b']) ?></td>
                                    <td><?= htmlspecialchars($s['enviado_por']) ?></td>
                                    <td><?= badgeStatus($s['status_sumula']) ?></td>
                                    <td class="td-acoes">
                                        <button class="btn btn-ok btn-sm" onclick="validarSumula(<?= $s['id_sumula'] ?>, 'validada')"><i class="fas fa-check"></i></button>
                                        <button class="btn btn-perigo btn-sm" onclick="validarSumula(<?= $s['id_sumula'] ?>, 'rejeitada')"><i class="fas fa-times"></i></button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- ════════════════════════════════════
             PAINEL: AGENDA
        ════════════════════════════════════ -->
        <div class="painel" id="painel-agenda">
            <div class="secao-card">
                <div class="secao-card-header">
                    <h3>Agenda Completa de Partidas</h3>
                    <button class="btn btn-primario btn-sm" onclick="abrirModal('modal-partida')">
                        <i class="fas fa-plus"></i> Agendar
                    </button>
                </div>
                <?php if (empty($agenda)): ?>
                    <p style="padding:24px;opacity:.6;">Nenhuma partida agendada no momento.</p>
                <?php else:
                    $por_dia = [];
                    foreach ($agenda as $p) { $por_dia[$p['data_partida']][] = $p; }
                    ksort($por_dia);
                ?>
                <div class="agenda-tabela-wrap">
                    <div class="agenda-colunas">
                        <?php foreach ($por_dia as $data => $pd):
                            $dt = new DateTime($data); ?>
                        <div class="agenda-coluna">
                            <div class="agenda-coluna-header">
                                <span class="agenda-col-diaSemana"><?= $dias_pt[$dt->format('l')] ?></span>
                                <span class="agenda-col-dia"><?= $dt->format('d') ?></span>
                                <span class="agenda-col-mes"><?= $meses_pt[$dt->format('F')] ?></span>
                            </div>
                            <div class="agenda-coluna-body">
                                <?php foreach ($pd as $p): ?>
                                <div class="agenda-slot">
                                    <span class="agenda-slot-hora"><?= fmtHora($p['hora_partida']) ?></span>
                                    <div class="agenda-slot-info">
                                        <strong><?= htmlspecialchars($p['time_a'] . ' vs ' . $p['time_b']) ?></strong>
                                        <span class="agenda-slot-mod"><?= htmlspecialchars($p['nome_modalidade']) ?></span>
                                        <?php if ($p['local_partida']): ?>
                                            <span class="agenda-slot-local">
                                                <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($p['local_partida']) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- ════════════════════════════════════
             PAINEL: EDIÇÕES
        ════════════════════════════════════ -->
        <div class="painel" id="painel-edicoes">
            <div class="secao-card">
                <div class="secao-card-header">
                    <h3>Edições / Eventos</h3>
                    <div style="display:flex;gap:10px;align-items:center;">
                        <span class="secao-tag-mini"><?= count($edicoes) ?> registros</span>
                        <button class="btn btn-primario btn-sm" onclick="abrirModal('modal-edicao')">
                            <i class="fas fa-plus"></i> Nova Edição
                        </button>
                    </div>
                </div>
                <div class="tabela-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>#</th><th>Nome</th><th>Ano</th>
                                <th>Início</th><th>Fim</th><th>Status</th>
                                <th>Alterar Status</th><th>Sorteio</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($edicoes)): ?>
                            <tr><td colspan="8" style="text-align:center;opacity:.6;padding:20px;">Nenhuma edição cadastrada.</td></tr>
                        <?php else: foreach ($edicoes as $e): ?>
                            <tr>
                                <td><?= $e['id_edicao'] ?></td>
                                <td><?= htmlspecialchars($e['nome_edicao']) ?></td>
                                <td><?= $e['ano_edicao'] ?></td>
                                <td><?= fmtData($e['data_inicio_edicao']) ?></td>
                                <td><?= fmtData($e['data_fim_edicao']) ?></td>
                                <td><?= badgeStatus($e['status_edicao']) ?></td>
                                <td>
                                    <select class="status-select"
                                            onchange="alterarStatusEdicao(<?= $e['id_edicao'] ?>, this.value, this)">
                                        <option value="planejamento" <?= $e['status_edicao']==='planejamento'  ? 'selected':'' ?>>Planejamento</option>
                                        <option value="inscricoes"   <?= $e['status_edicao']==='inscricoes'    ? 'selected':'' ?>>Inscrições Abertas</option>
                                        <option value="em_andamento" <?= $e['status_edicao']==='em_andamento'  ? 'selected':'' ?>>Em Andamento</option>
                                        <option value="encerrado"    <?= $e['status_edicao']==='encerrado'     ? 'selected':'' ?>>Encerrado</option>
                                    </select>
                                </td>
                                <td>
                                    <?php
                                    $mods = $modalidadesPorEdicao[$e['id_edicao']] ?? [];
                                    if (empty($mods)): ?>
                                        <span class="sorteio-sem-ins">Nenhuma modalidade</span>
                                    <?php else: ?>
                                    <div class="sorteio-cell">
                                        <?php foreach ($mods as $mod):
                                            $jaFeito = !empty($sorteiosGerados[$mod['id_edicao_modalidade']]); ?>
                                        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                                            <span class="sorteio-mod-nome">
                                                <i class="fas fa-futbol" style="font-size:.65rem;color:var(--laranja)"></i>
                                                <?= htmlspecialchars($mod['nome_modalidade']) ?>
                                                <span style="color:var(--texto-secundario);font-size:.68rem;">
                                                    (<?= $mod['turmas_inscritas'] ?> turma<?= $mod['turmas_inscritas'] != 1 ? 's' : '' ?>)
                                                </span>
                                            </span>
                                            <?php if ($jaFeito): ?>
                                                <span class="badge-sorteado"><i class="fas fa-check"></i> Sorteado</span>
                                            <?php elseif ($mod['turmas_inscritas'] >= 2): ?>
                                                <button class="btn-sortear"
                                                        id="btn-sortear-<?= $mod['id_edicao_modalidade'] ?>"
                                                        onclick="gerarSorteio(<?= $mod['id_edicao_modalidade'] ?>, '<?= addslashes(htmlspecialchars($mod['nome_modalidade'])) ?>', this)">
                                                    <i class="fas fa-shuffle"></i> Sortear
                                                </button>
                                            <?php else: ?>
                                                <span class="sorteio-sem-ins">Inscritos insuficientes</span>
                                            <?php endif; ?>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ════════════════════════════════════
             PAINEL: PARTIDAS
             (com botão de editar data/hora/local)
        ════════════════════════════════════ -->
        <div class="painel" id="painel-partidas">
            <div class="secao-card">
                <div class="secao-card-header">
                    <h3>Partidas</h3>
                    <span class="secao-tag-mini"><?= count($partidas) ?> registros</span>
                    <button class="btn btn-primario btn-sm" onclick="abrirModal('modal-partida')">
                        <i class="fas fa-plus"></i> Agendar Partida
                    </button>
                </div>
                <div class="tabela-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>#</th><th>Modalidade</th><th>Time A</th><th>Time B</th>
                                <th>Data</th><th>Hora</th><th>Local</th>
                                <th>Fase</th><th>Status</th><th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($partidas)): ?>
                            <tr><td colspan="10" style="text-align:center;opacity:.6;padding:20px;">Nenhuma partida cadastrada.</td></tr>
                        <?php else: foreach ($partidas as $p): ?>
                            <tr>
                                <td><?= $p['id_partida'] ?></td>
                                <td><?= htmlspecialchars($p['nome_modalidade']) ?></td>
                                <td><?= htmlspecialchars($p['time_a']) ?></td>
                                <td><?= htmlspecialchars($p['time_b']) ?></td>
                                <td><?= fmtData($p['data_partida']) ?></td>
                                <td><?= fmtHora($p['hora_partida']) ?></td>
                                <td><?= htmlspecialchars($p['local_partida'] ?? '—') ?></td>
                                <td>
                                    <span class="badge-status pendente">
                                        <?= $faseLabel[$p['fase_partida']] ?? ucfirst($p['fase_partida']) ?>
                                    </span>
                                </td>
                                <td><?= badgeStatus($p['status_partida']) ?></td>
                                <td class="td-acoes">
                                    <button class="btn-editar-partida"
                                            onclick="abrirEditarPartida(
                                                <?= $p['id_partida'] ?>,
                                                '<?= $p['data_partida'] ?>',
                                                '<?= substr($p['hora_partida'], 0, 5) ?>',
                                                '<?= addslashes(htmlspecialchars($p['local_partida'] ?? '')) ?>'
                                            )">
                                        <i class="fas fa-pen"></i> Editar
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ════════════════════════════════════
             PAINEL: RESULTADOS
             (mostra a partida + inputs inline)
        ════════════════════════════════════ -->
        <div class="painel" id="painel-resultados">
            <div class="secao-card">
                <div class="secao-card-header">
                    <h3>Resultados</h3>
                    <span class="secao-tag-mini"><?= count($resultados) ?> partidas</span>
                </div>
                <div class="tabela-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>#</th><th>Partida</th><th>Data</th>
                                <th>Status</th><th class="td-resultado">Resultado</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($resultados)): ?>
                            <tr><td colspan="5" style="text-align:center;opacity:.6;padding:20px;">Nenhuma partida encontrada.</td></tr>
                        <?php else: foreach ($resultados as $r): ?>
                            <tr>
                                <td><?= $r['id_partida'] ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($r['time_a'] . ' vs ' . $r['time_b']) ?></strong><br>
                                    <small style="color:var(--texto-secundario)"><?= htmlspecialchars($r['nome_modalidade']) ?></small>
                                </td>
                                <td><?= fmtData($r['data_partida'] ?? '') ?></td>
                                <td><?= badgeStatus($r['status_partida'] ?? 'agendada') ?></td>
                                <td class="td-resultado">
                                    <?php if (!is_null($r['id_resultado'])): ?>
                                        <!-- Resultado já registrado -->
                                        <span class="resultado-vencedor-badge">
                                            <i class="fas fa-trophy"></i>
                                            <?php
                                                if ($r['vencedor']) {
                                                    echo htmlspecialchars($r['vencedor']);
                                                } elseif ($r['placar_time_a'] == $r['placar_time_b']) {
                                                    echo 'Empate';
                                                } else {
                                                    echo $r['placar_time_a'] > $r['placar_time_b']
                                                        ? htmlspecialchars($r['time_a'])
                                                        : htmlspecialchars($r['time_b']);
                                                }
                                            ?>
                                            (<?= $r['placar_time_a'] ?>–<?= $r['placar_time_b'] ?>)
                                        </span>
                                    <?php else: ?>
                                        <!-- Formulário inline para registrar -->
                                        <div class="resultado-inline-form">
                                            <span style="font-size:.78rem;font-weight:600;color:var(--texto-secundario);max-width:80px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?= htmlspecialchars($r['time_a']) ?>">
                                                <?= htmlspecialchars($r['time_a']) ?>
                                            </span>
                                            <input type="number" class="placar-input placar-a"
                                                   min="0" max="99" placeholder="0"
                                                   aria-label="Placar <?= htmlspecialchars($r['time_a']) ?>">
                                            <span class="placar-x">×</span>
                                            <input type="number" class="placar-input placar-b"
                                                   min="0" max="99" placeholder="0"
                                                   aria-label="Placar <?= htmlspecialchars($r['time_b']) ?>">
                                            <span style="font-size:.78rem;font-weight:600;color:var(--texto-secundario);max-width:80px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?= htmlspecialchars($r['time_b']) ?>">
                                                <?= htmlspecialchars($r['time_b']) ?>
                                            </span>
                                            <button class="btn-salvar-placar"
                                                    onclick="salvarResultadoInline(
                                                        <?= $r['id_partida'] ?>,
                                                        '<?= addslashes(htmlspecialchars($r['time_a'])) ?>',
                                                        '<?= addslashes(htmlspecialchars($r['time_b'])) ?>',
                                                        this
                                                    )">
                                                <i class="fas fa-check"></i> Salvar
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ════════════════════════════════════
             PAINEL: SÚMULAS
        ════════════════════════════════════ -->
        <div class="painel" id="painel-sumulas">
            <div class="secao-card">
                <div class="secao-card-header">
                    <h3>Súmulas</h3>
                    <span class="secao-tag-mini"><?= count($sumulas) ?> registros</span>
                    <button class="btn btn-primario btn-sm" onclick="abrirModal('modal-sumula')">
                        <i class="fas fa-upload"></i> Enviar Súmula
                    </button>
                </div>
                <div class="tabela-wrap">
                    <table>
                        <thead>
                            <tr><th>#</th><th>Partida</th><th>Enviado por</th><th>Arquivo</th><th>Tipo</th><th>Data Envio</th><th>Status</th><th>Ações</th></tr>
                        </thead>
                        <tbody>
                        <?php if (empty($sumulas)): ?>
                            <tr><td colspan="8" style="text-align:center;opacity:.6;padding:20px;">Nenhuma súmula enviada.</td></tr>
                        <?php else: foreach ($sumulas as $s): ?>
                            <tr>
                                <td><?= $s['id_sumula'] ?></td>
                                <td><?= htmlspecialchars($s['nome_modalidade'] . ' — ' . $s['time_a'] . ' vs ' . $s['time_b']) ?></td>
                                <td><?= htmlspecialchars($s['enviado_por']) ?></td>
                                <td>
                                    <a href="#" style="color:var(--azul-secundario)">
                                        <i class="fas fa-file-<?= strtolower($s['tipo_arquivo_sumula']) === 'pdf' ? 'pdf' : 'image' ?>"></i>
                                        <?= htmlspecialchars($s['nome_arquivo_sumula']) ?>
                                    </a>
                                </td>
                                <td><?= strtoupper($s['tipo_arquivo_sumula']) ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($s['data_envio_sumula'])) ?></td>
                                <td><?= badgeStatus($s['status_sumula']) ?></td>
                                <td class="td-acoes">
                                    <?php if ($s['status_sumula'] === 'pendente'): ?>
                                        <button class="btn btn-ok btn-sm" onclick="validarSumula(<?= $s['id_sumula'] ?>, 'validada')"><i class="fas fa-check"></i> Validar</button>
                                        <button class="btn btn-perigo btn-sm" onclick="validarSumula(<?= $s['id_sumula'] ?>, 'rejeitada')"><i class="fas fa-times"></i></button>
                                    <?php else: ?>
                                        <button class="btn btn-perigo btn-sm" onclick="excluirRegistro('sumula', <?= $s['id_sumula'] ?>)"><i class="fas fa-trash"></i></button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ════════════════════════════════════
             PAINEL: ALUNOS
        ════════════════════════════════════ -->
        <div class="painel" id="painel-alunos">
            <div class="secao-card">
                <div class="secao-card-header">
                    <h3>Alunos &amp; Turmas</h3>
                    <span class="secao-tag-mini"><?= count($alunos) ?> registros</span>
                </div>
                <div class="tabela-wrap">
                    <table>
                        <thead>
                            <tr><th>#</th><th>Nome</th><th>E-mail</th><th>Turma</th><th>Gênero</th><th>Cargo</th><th>Status</th><th>Ações</th></tr>
                        </thead>
                        <tbody>
                        <?php if (empty($alunos)): ?>
                            <tr><td colspan="8" style="text-align:center;opacity:.6;padding:20px;">Nenhum aluno encontrado.</td></tr>
                        <?php else:
                            $generoLabel = ['m' => 'Masc.', 'f' => 'Fem.', 'n' => '—'];
                            foreach ($alunos as $a):
                                $isAdm = $a['tipo_usuario'] === 'adm_sala'; ?>
                            <tr>
                                <td><?= $a['id_usuario'] ?></td>
                                <td style="font-weight:600;"><?= htmlspecialchars($a['nome_usuario']) ?></td>
                                <td style="color:var(--texto-secundario);font-size:.82rem;"><?= htmlspecialchars($a['email_usuario']) ?></td>
                                <td><?= htmlspecialchars($a['nome_turma'] ?? '—') ?></td>
                                <td><?= $generoLabel[$a['genero_usuario']] ?? '—' ?></td>
                                <td>
                                    <?php if ($isAdm): ?>
                                        <span class="badge-cargo adm-sala"><i class="fas fa-star"></i> Adm. Sala</span>
                                    <?php else: ?>
                                        <span class="badge-cargo aluno"><i class="fas fa-user-graduate"></i> Aluno</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= $a['ativo_usuario'] ? badgeStatus('ativo') : badgeStatus('inativo') ?></td>
                                <td class="td-acoes">
                                    <?php if ($isAdm): ?>
                                        <button class="btn-acao remover" title="Remover cargo"
                                                onclick="elegerAdmSala(<?= $a['id_usuario'] ?>, '<?= addslashes(htmlspecialchars($a['nome_usuario'])) ?>')">
                                            <i class="fas fa-undo"></i>
                                        </button>
                                    <?php else: ?>
                                        <button class="btn-acao eleger" title="Eleger como Adm. de Sala"
                                                onclick="elegerAdmSala(<?= $a['id_usuario'] ?>, '<?= addslashes(htmlspecialchars($a['nome_usuario'])) ?>')">
                                            <i class="fas fa-star"></i>
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div><!-- /content -->
</div><!-- /main -->


<!-- ════════════════════════════════════
     MODAL: Nova Edição
════════════════════════════════════ -->
<div class="modal-overlay" id="modal-edicao">
    <div class="modal modal-edicao">
        <div class="modal-header">
            <h4><i class="fas fa-trophy" style="color:var(--laranja);margin-right:6px;"></i> Nova Edição / Evento</h4>
            <button class="modal-close" onclick="fecharModal('modal-edicao')"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <form action="/soee/src/backend/actions/salvar-edicao.php" method="POST" id="form-edicao">
                <div class="form-grid">
                    <div class="form-grupo span2">
                        <label class="form-label">Nome do Evento <span style="color:var(--laranja)">*</span></label>
                        <input class="form-input" type="text" name="nome_edicao"
                               placeholder="Ex.: Interclasse 2026 — 1º Semestre" maxlength="80" required />
                    </div>
                    <div class="form-grupo">
                        <label class="form-label">Ano <span style="color:var(--laranja)">*</span></label>
                        <input class="form-input" type="number" name="ano_edicao"
                               value="<?= date('Y') ?>" min="2020" max="2099" required />
                    </div>
                    <div class="form-grupo">
                        <label class="form-label">Status <span style="color:var(--laranja)">*</span></label>
                        <select class="form-select" name="status_edicao" required>
                            <option value="planejamento">Planejamento</option>
                            <option value="inscricoes">Inscrições Abertas</option>
                            <option value="em_andamento">Em Andamento</option>
                            <option value="encerrado">Encerrado</option>
                        </select>
                    </div>
                    <div class="form-grupo">
                        <label class="form-label">Data de Início <span style="color:var(--laranja)">*</span></label>
                        <input class="form-input" type="date" name="data_inicio_edicao" required />
                    </div>
                    <div class="form-grupo">
                        <label class="form-label">Data de Fim (opcional)</label>
                        <input class="form-input" type="date" name="data_fim_edicao" />
                    </div>
                    <div class="form-grupo span2">
                        <label class="form-label">Descrição (opcional)</label>
                        <textarea class="form-textarea" name="descricao_edicao"
                                  placeholder="Descreva o evento, regras gerais, observações…" rows="3"></textarea>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secundario" onclick="fecharModal('modal-edicao')">Cancelar</button>
            <button class="btn btn-primario" onclick="document.getElementById('form-edicao').submit()">
                <i class="fas fa-save"></i> Criar Edição
            </button>
        </div>
    </div>
</div>

<!-- ════════════════════════════════════
     MODAL: Agendar Partida
════════════════════════════════════ -->
<div class="modal-overlay" id="modal-partida">
    <div class="modal">
        <div class="modal-header">
            <h4>Agendar Partida</h4>
            <button class="modal-close" onclick="fecharModal('modal-partida')"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <form action="/soee/src/backend/actions/salvar-partida.php" method="POST" id="form-partida">
                <div class="form-grid">
                    <div class="form-grupo span2">
                        <label class="form-label">Edição / Modalidade</label>
                        <select class="form-select" name="edicao_modalidade_id" required>
                            <option value="">Selecionar…</option>
                            <?php foreach ($edicoes_modal_select as $em): ?>
                            <option value="<?= $em['id_edicao_modalidade'] ?>"><?= htmlspecialchars($em['label']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-grupo">
                        <label class="form-label">Time A</label>
                        <select class="form-select" name="turma_id_time_a" required>
                            <?php foreach ($turmas_select as $t): ?>
                            <option value="<?= $t['id_turma'] ?>"><?= htmlspecialchars($t['nome_turma']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-grupo">
                        <label class="form-label">Time B</label>
                        <select class="form-select" name="turma_id_time_b" required>
                            <?php foreach ($turmas_select as $t): ?>
                            <option value="<?= $t['id_turma'] ?>"><?= htmlspecialchars($t['nome_turma']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-grupo">
                        <label class="form-label">Data</label>
                        <input class="form-input" type="date" name="data_partida" required />
                    </div>
                    <div class="form-grupo">
                        <label class="form-label">Hora</label>
                        <input class="form-input" type="time" name="hora_partida" required />
                    </div>
                    <div class="form-grupo">
                        <label class="form-label">Local</label>
                        <input class="form-input" type="text" name="local_partida" placeholder="Ex.: Quadra A" />
                    </div>
                    <div class="form-grupo">
                        <label class="form-label">Fase</label>
                        <select class="form-select" name="fase_partida" required>
                            <option value="grupos">Grupos</option>
                            <option value="oitavas">Oitavas</option>
                            <option value="quartas">Quartas</option>
                            <option value="semi">Semifinal</option>
                            <option value="final">Final</option>
                            <option value="terceiro_lugar">3º Lugar</option>
                        </select>
                    </div>
                    <div class="form-grupo span2">
                        <label class="form-label">Observações</label>
                        <textarea class="form-textarea" name="observacoes_partida" placeholder="Notas adicionais…"></textarea>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secundario" onclick="fecharModal('modal-partida')">Cancelar</button>
            <button class="btn btn-primario" onclick="document.getElementById('form-partida').submit()">
                <i class="fas fa-save"></i> Salvar
            </button>
        </div>
    </div>
</div>

<!-- ════════════════════════════════════
     MODAL: Editar Partida (data/hora/local)
════════════════════════════════════ -->
<div class="modal-overlay" id="modal-editar-partida">
    <div class="modal modal-editar-partida">
        <div class="modal-header">
            <h4><i class="fas fa-pen" style="color:var(--laranja);margin-right:6px;"></i> Editar Partida</h4>
            <button class="modal-close" onclick="fecharModal('modal-editar-partida')"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="edit-partida-id">
            <div class="form-grid">
                <div class="form-grupo">
                    <label class="form-label">Data <span style="color:var(--laranja)">*</span></label>
                    <input class="form-input" type="date" id="edit-partida-data" required />
                </div>
                <div class="form-grupo">
                    <label class="form-label">Hora <span style="color:var(--laranja)">*</span></label>
                    <input class="form-input" type="time" id="edit-partida-hora" required />
                </div>
                <div class="form-grupo span2">
                    <label class="form-label">Local</label>
                    <input class="form-input" type="text" id="edit-partida-local" placeholder="Ex.: Quadra A" />
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secundario" onclick="fecharModal('modal-editar-partida')">Cancelar</button>
            <button class="btn btn-primario" id="btn-salvar-edicao-partida">
                <i class="fas fa-save"></i> Salvar Alterações
            </button>
        </div>
    </div>
</div>

<!-- ════════════════════════════════════
     MODAL: Enviar Súmula
════════════════════════════════════ -->
<div class="modal-overlay" id="modal-sumula">
    <div class="modal">
        <div class="modal-header">
            <h4>Enviar Súmula</h4>
            <button class="modal-close" onclick="fecharModal('modal-sumula')"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <form action="/soee/src/backend/php/actions/salvar-sumula.php" method="POST" enctype="multipart/form-data" id="form-sumula">
                <div class="form-grid">
                    <div class="form-grupo span2">
                        <label class="form-label">Partida</label>
                        <select class="form-select" name="partida_id_partida" required>
                            <option value="">Selecionar…</option>
                            <?php foreach ($partidas_select as $ps): ?>
                            <option value="<?= $ps['id_partida'] ?>"><?= htmlspecialchars($ps['label']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-grupo span2">
                        <label class="form-label">Arquivo (PDF / JPG / PNG)</label>
                        <input class="form-input" type="file" name="arquivo_sumula" accept=".pdf,.jpg,.jpeg,.png" required />
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secundario" onclick="fecharModal('modal-sumula')">Cancelar</button>
            <button class="btn btn-primario" onclick="document.getElementById('form-sumula').submit()">
                <i class="fas fa-upload"></i> Enviar
            </button>
        </div>
    </div>
</div>

<!-- ════════════════════════════════════
     MODAL: Resultado do Sorteio
════════════════════════════════════ -->
<div class="modal-sorteio-overlay" id="modal-sorteio">
    <div class="modal-sorteio-box">
        <div class="modal-sorteio-header">
            <div class="modal-sorteio-titulo">
                <i class="fas fa-shuffle"></i> Resultado do Sorteio
            </div>
            <button class="modal-sorteio-fechar" onclick="fecharModalSorteio()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div id="sorteio-loading" style="text-align:center;padding:28px;color:var(--texto-secundario);">
            <i class="fas fa-spinner fa-spin" style="font-size:1.6rem;display:block;margin-bottom:12px;color:var(--laranja)"></i>
            Sorteando partidas aleatoriamente…
        </div>
        <div id="sorteio-resultado" style="display:none;"></div>
        <div id="sorteio-footer" style="display:none;margin-top:20px;text-align:right;">
            <button class="btn btn-primario" onclick="fecharModalSorteio()">
                <i class="fas fa-check"></i> Fechar
            </button>
        </div>
    </div>
</div>

<div class="toast-container" id="toast-container"></div>

<!-- Scripts: base primeiro, depois específico da página -->
<script src="/soee/src/frontend/scripts/adm.js"></script>
<script src="/soee/src/frontend/scripts/dash-prof.js"></script>

<?php include __DIR__ . '/../includes/end.php'; ?>