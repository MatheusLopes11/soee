<?php
session_start();
require_once __DIR__ . '/../../../backend/includes/conexao.php';
require_once __DIR__ . '/../../../backend/controllers/home.php';

AuthHome::exigirTipo(['adm_sala']);

$userId   = AuthHome::getId();
$userNome = AuthHome::getNome();

$stmtUser = $conn->prepare("
    SELECT 
        u.id_usuario, u.nome_usuario, u.email_usuario,
        u.foto_perfil_usuario, u.genero_usuario,
        u.turma_id_turma AS turma_id,
        t.nome_turma, t.ano_serie_turma, t.periodo_turma,
        c.nome_curso, c.sigla_curso
    FROM usuario u
    LEFT JOIN turma t ON t.id_turma = u.turma_id_turma
    LEFT JOIN curso c ON c.id_curso = t.curso_id_curso
    WHERE u.id_usuario = :id
    LIMIT 1
");
$stmtUser->execute([':id' => $userId]);
$userData = $stmtUser->fetch(PDO::FETCH_ASSOC);
if (!$userData) die("Usuário não encontrado.");

$turmaId = (int) ($userData['turma_id'] ?? 0);

// ── ALUNOS DA TURMA ──────────────────────────────────────────────
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

// ── INSCRIÇÕES ────────────────────────────────────────────────────
$stmtInscricoes = $conn->prepare("
    SELECT i.id_inscricao, i.numero_camisa_inscricao, i.posicao_inscricao,
           i.capitao_inscricao, i.data_inscricao, i.status_inscricao,
           u.nome_usuario, m.nome_modalidade, e.nome_edicao
    FROM inscricao i
    INNER JOIN usuario u ON u.id_usuario = i.usuario_id_usuario
    INNER JOIN edicao_modalidade em ON em.id_edicao_modalidade = i.edicao_modalidade_id
    INNER JOIN modalidade m ON m.id_modalidade = em.modalidade_id_modalidade
    INNER JOIN edicao e ON e.id_edicao = em.edicao_id_edicao
    WHERE u.turma_id_turma = :turma
    ORDER BY i.data_inscricao DESC
");
$stmtInscricoes->execute([':turma' => $turmaId]);
$inscricoes = $stmtInscricoes->fetchAll(PDO::FETCH_ASSOC);

// ── PARTIDAS ──────────────────────────────────────────────────────
$stmtPartidas = $conn->prepare("
    SELECT p.id_partida, p.data_partida, p.hora_partida,
           p.local_partida, p.fase_partida, p.status_partida,
           ta.nome_turma AS time_a, tb.nome_turma AS time_b,
           m.nome_modalidade, r.placar_time_a, r.placar_time_b
    FROM partida p
    INNER JOIN turma ta ON ta.id_turma = p.turma_id_time_a
    INNER JOIN turma tb ON tb.id_turma = p.turma_id_time_b
    INNER JOIN edicao_modalidade em ON em.id_edicao_modalidade = p.edicao_modalidade_id
    INNER JOIN modalidade m ON m.id_modalidade = em.modalidade_id_modalidade
    LEFT JOIN resultado r ON r.partida_id_partida = p.id_partida
    WHERE (p.turma_id_time_a = :turma1 OR p.turma_id_time_b = :turma2)
    ORDER BY p.data_partida DESC, p.hora_partida DESC
");
$stmtPartidas->execute([':turma1' => $turmaId, ':turma2' => $turmaId]);
$partidas = $stmtPartidas->fetchAll(PDO::FETCH_ASSOC);

// ── CLASSIFICAÇÃO ─────────────────────────────────────────────────
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

// ── STATS ─────────────────────────────────────────────────────────
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
    ':t1' => $turmaId, ':t2'  => $turmaId,
    ':t3' => $turmaId, ':t3b' => $turmaId,
    ':t4' => $turmaId, ':t4b' => $turmaId,
]);
$stats = $stmtStats->fetch(PDO::FETCH_ASSOC);

// ── MODALIDADES COM INSCRIÇÕES ABERTAS (para o overview/widget) ───
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

// ── TODAS AS MODALIDADES (painel completo) ────────────────────────
$stmtTodasModalidades = $conn->query("
    SELECT m.id_modalidade, m.nome_modalidade, m.descricao_modalidade,
           m.tipo_modalidade, m.formato_modalidade, m.tipo_participacao,
           m.qtd_min_jogadores, m.qtd_max_jogadores, m.ativo_modalidade,
           m.tipo_duracao, m.duracao_minutos, m.duracao_pontos,
           m.regulamento_modalidade
    FROM modalidade m
    ORDER BY m.nome_modalidade ASC
");
$todasModalidades = $stmtTodasModalidades->fetchAll(PDO::FETCH_ASSOC);

// ── EDIÇÕES ATIVAS (para vincular modal em edição) ────────────────
$stmtEdicoes = $conn->query("
    SELECT id_edicao, nome_edicao, ano_edicao
    FROM edicao
    WHERE status_edicao != 'encerrado'
    ORDER BY ano_edicao DESC, id_edicao DESC
");
$edicoesAtivas = $stmtEdicoes->fetchAll(PDO::FETCH_ASSOC);

// ── HELPERS ───────────────────────────────────────────────────────
$faseLabel = [
    'grupos'        => 'Grupos',
    'oitavas'       => 'Oitavas',
    'quartas'       => 'Quartas',
    'semi'          => 'Semi',
    'final'         => 'Final',
    'terceiro_lugar'=> '3º Lugar',
];
$statusPartidaLabel = [
    'agendada'  => 'Agendada',
    'realizada' => 'Realizada',
    'cancelada' => 'Cancelada',
    'wo'        => 'W.O.',
];
$tipoIcons = [
    'quadra' => 'fa-basketball',
    'mesa'   => 'fa-table-tennis-paddle-ball',
    'campo'  => 'fa-futbol',
    'outro'  => 'fa-star',
];
$formatoLabel = [
    'mata_mata'          => 'Mata-mata',
    'grupos'             => 'Grupos',
    'grupos_mata_mata'   => 'Grupos + Mata-mata',
    'todos_contra_todos' => 'Todos contra todos',
];
$participacaoLabel = [
    'solo'  => 'Individual',
    'dupla' => 'Dupla',
    'trio'  => 'Trio',
    'time'  => 'Time',
];

// ── FLASH MESSAGE ─────────────────────────────────────────────────
$flashMsg  = $_SESSION['flash_msg']  ?? '';
$flashTipo = $_SESSION['flash_tipo'] ?? '';
unset($_SESSION['flash_msg'], $_SESSION['flash_tipo']);
?>
<?php include __DIR__ . '/../includes/doctype.php'; ?>
<head>
    <title>SOEE | Dashboard — ADM Sala</title>
    <link rel="stylesheet" href="/soee/src/frontend/styles/dash-adm-sala.css">
    <?php include __DIR__ . '/../includes/head.php'; ?>
    <style>
        /* ════════════════════════════════════════════
           PAINEL MODALIDADES — estilos exclusivos
        ════════════════════════════════════════════ */

        /* Botão nova modalidade */
        .btn-nova-modalidade {
            display: inline-flex; align-items: center; gap: 7px;
            background: var(--laranja); color: #fff;
            border: none; border-radius: var(--raio-medio);
            padding: 8px 16px; font-size: .82rem; font-weight: 700;
            cursor: pointer; transition: var(--transicao);
            white-space: nowrap;
        }
        .btn-nova-modalidade:hover { filter: brightness(1.12); transform: translateY(-1px); }

        /* Grid de cards */
        .modalidades-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(255px, 1fr));
            gap: 16px;
            padding: 4px 0 8px;
        }

        /* Card individual */
        .modalidade-card {
            background: var(--bg-card);
            border: 1px solid var(--borda-sutil);
            border-radius: var(--raio-grande);
            padding: 18px;
            display: flex; flex-direction: column; gap: 10px;
            transition: var(--transicao);
        }
        .modalidade-card:hover {
            border-color: var(--laranja);
            box-shadow: 0 4px 20px rgba(0,0,0,.08);
        }
        .modalidade-card.inativa { opacity: .5; }

        .mc-topo { display: flex; align-items: center; justify-content: space-between; }
        .mc-icone {
            width: 40px; height: 40px; border-radius: 10px;
            background: rgba(234,88,12,.1);
            display: flex; align-items: center; justify-content: center;
            color: var(--laranja); font-size: 1.1rem;
            flex-shrink: 0;
        }
        .mc-nome { font-size: .95rem; font-weight: 700; color: var(--texto-1); }
        .mc-desc { font-size: .76rem; color: var(--texto-2); line-height: 1.45; }
        .mc-meta {
            display: flex; flex-wrap: wrap; gap: 5px;
            font-size: .71rem; color: var(--texto-2);
        }
        .mc-meta span {
            display: flex; align-items: center; gap: 4px;
            background: var(--borda-sutil);
            padding: 3px 8px; border-radius: 99px;
        }
        .mc-rodape { display: flex; gap: 8px; margin-top: auto; padding-top: 4px; }
        .mc-btn-editar, .mc-btn-inscricao {
            flex: 1; padding: 7px 6px; border-radius: var(--raio-medio);
            font-size: .73rem; font-weight: 600; cursor: pointer;
            border: 1px solid; transition: var(--transicao);
            display: inline-flex; align-items: center; justify-content: center; gap: 5px;
        }
        .mc-btn-editar {
            background: transparent; color: var(--texto-2);
            border-color: var(--borda-sutil);
        }
        .mc-btn-editar:hover { background: var(--borda-sutil); color: var(--texto-1); }
        .mc-btn-inscricao {
            background: var(--laranja); color: #fff; border-color: var(--laranja);
        }
        .mc-btn-inscricao:hover { filter: brightness(1.1); }

        /* Badge ativa */
        .badge-ativa {
            font-size: .68rem; font-weight: 700; padding: 3px 9px;
            border-radius: 99px; white-space: nowrap;
            background: rgba(34,197,94,.12); color: #16a34a;
            border: 1px solid rgba(34,197,94,.25);
        }
        .badge-inativa {
            font-size: .68rem; font-weight: 700; padding: 3px 9px;
            border-radius: 99px; white-space: nowrap;
            background: rgba(100,116,139,.12); color: var(--texto-2);
            border: 1px solid var(--borda-sutil);
        }

        /* ── Overlay / Modal ── */
        .modal-overlay-sala {
            position: fixed; inset: 0; z-index: 9999;
            background: rgba(0,0,0,.6); backdrop-filter: blur(4px);
            display: none; align-items: center; justify-content: center;
            padding: 16px;
        }
        .modal-overlay-sala.open { display: flex; }

        .modal-sala {
            background: var(--bg-card);
            border: 1px solid var(--borda-sutil);
            border-radius: var(--raio-grande);
            width: 100%; max-width: 680px;
            max-height: 90vh; overflow-y: auto;
            box-shadow: 0 24px 64px rgba(0,0,0,.28);
            animation: msSlideUp .22s ease;
        }
        @keyframes msSlideUp {
            from { transform: translateY(20px); opacity: 0; }
            to   { transform: translateY(0);    opacity: 1; }
        }
        .modal-sala-sm { max-width: 500px; }

        .modal-sala-header {
            display: flex; align-items: center; justify-content: space-between;
            padding: 20px 24px; border-bottom: 1px solid var(--borda-sutil);
            position: sticky; top: 0; background: var(--bg-card); z-index: 2;
        }
        .modal-sala-header h3 {
            font-size: .95rem; font-weight: 700;
            display: flex; align-items: center; gap: 8px;
            margin: 0;
        }
        .modal-sala-fechar {
            width: 32px; height: 32px; border-radius: 8px;
            border: 1px solid var(--borda-sutil); background: none;
            color: var(--texto-2); cursor: pointer; transition: var(--transicao);
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .modal-sala-fechar:hover {
            background: #ef4444; color: #fff; border-color: #ef4444;
        }

        .modal-sala-body { padding: 24px; }

        /* Grid de formulário */
        .form-sala-grid {
            display: grid; grid-template-columns: 1fr 1fr; gap: 14px;
        }
        .form-sala-grupo { display: flex; flex-direction: column; gap: 6px; }
        .form-sala-grupo.span2 { grid-column: 1 / -1; }
        .form-sala-label { font-size: .77rem; font-weight: 600; color: var(--texto-2); }
        .form-sala-label .obrig { color: var(--laranja); }
        .form-sala-input,
        .form-sala-select,
        .form-sala-textarea {
            width: 100%; padding: 9px 12px;
            background: var(--bg-input, rgba(0,0,0,.03));
            border: 1px solid var(--borda-sutil);
            border-radius: var(--raio-medio);
            color: var(--texto-1); font-size: .84rem;
            transition: var(--transicao); font-family: inherit;
            box-sizing: border-box;
        }
        .form-sala-input:focus,
        .form-sala-select:focus,
        .form-sala-textarea:focus {
            outline: none; border-color: var(--laranja);
            box-shadow: 0 0 0 3px rgba(234,88,12,.12);
        }
        .form-sala-textarea { resize: vertical; min-height: 80px; }
        .form-sala-input:disabled { opacity: .6; cursor: not-allowed; }

        /* Toggle */
        .toggle-label {
            display: flex; align-items: center; gap: 10px;
            cursor: pointer; font-size: .84rem; color: var(--texto-1);
            user-select: none;
        }
        .toggle-label input[type="checkbox"] { display: none; }
        .toggle-track {
            width: 40px; height: 22px; border-radius: 99px;
            background: var(--borda-sutil); position: relative;
            transition: background .2s; flex-shrink: 0;
        }
        .toggle-track::after {
            content: ''; position: absolute; top: 3px; left: 3px;
            width: 16px; height: 16px; border-radius: 50%;
            background: #fff; transition: transform .2s;
            box-shadow: 0 1px 3px rgba(0,0,0,.2);
        }
        .toggle-label input:checked + .toggle-track { background: #22c55e; }
        .toggle-label input:checked + .toggle-track::after { transform: translateX(18px); }

        /* Footer do modal */
        .modal-sala-footer {
            display: flex; justify-content: flex-end; gap: 10px;
            padding: 16px 24px; border-top: 1px solid var(--borda-sutil);
            position: sticky; bottom: 0; background: var(--bg-card); z-index: 2;
        }
        .btn-modal-cancelar {
            padding: 9px 18px; border-radius: var(--raio-medio);
            border: 1px solid var(--borda-sutil); background: none;
            color: var(--texto-2); font-size: .84rem; cursor: pointer;
            transition: var(--transicao);
        }
        .btn-modal-cancelar:hover { background: var(--borda-sutil); color: var(--texto-1); }
        .btn-modal-salvar {
            padding: 9px 20px; border-radius: var(--raio-medio);
            background: var(--laranja); color: #fff;
            border: none; font-size: .84rem; font-weight: 700;
            cursor: pointer; display: flex; align-items: center; gap: 7px;
            transition: var(--transicao);
        }
        .btn-modal-salvar:hover { filter: brightness(1.1); transform: translateY(-1px); }

        /* Toast flash */
        .flash-toast {
            position: fixed; bottom: 24px; right: 24px; z-index: 99999;
            padding: 12px 20px; border-radius: var(--raio-medio);
            font-size: .84rem; font-weight: 600; color: #fff;
            box-shadow: 0 8px 24px rgba(0,0,0,.2);
            animation: msSlideUp .3s ease;
            display: flex; align-items: center; gap: 10px;
        }
        .flash-toast.sucesso { background: #22c55e; }
        .flash-toast.erro    { background: #ef4444; }

        @media (max-width: 640px) {
            .form-sala-grid { grid-template-columns: 1fr; }
            .form-sala-grupo.span2 { grid-column: 1; }
            .modalidades-grid { grid-template-columns: 1fr; }
            .modal-sala { max-height: 95vh; }
        }
    </style>
</head>

<body>

<?php if ($flashMsg): ?>
<div class="flash-toast <?= htmlspecialchars($flashTipo) ?>" id="flashToast">
    <i class="fa-solid <?= $flashTipo === 'sucesso' ? 'fa-circle-check' : 'fa-circle-xmark' ?>"></i>
    <?= htmlspecialchars($flashMsg) ?>
</div>
<script>setTimeout(function(){var t=document.getElementById('flashToast');if(t)t.remove();},4000);</script>
<?php endif; ?>

<aside class="sidebar" id="sidebar">
    <div class="sidebar-logo">
        <a href="/soee/src/frontend/views/site/home.php">S<span>O</span>EE</a>
        <small>ADM de Sala</small>
    </div>

    <?php if ($turmaId): ?>
    <div class="sidebar-turma">
        <div class="turma-label">Sua Turma</div>
        <div class="turma-nome"><?= htmlspecialchars($userData['nome_turma'] ?? '—') ?></div>
        <div class="turma-curso"><?= htmlspecialchars($userData['sigla_curso'] ?? '') ?> &middot; <?= ucfirst($userData['periodo_turma'] ?? '') ?></div>
    </div>
    <?php endif; ?>

    <a href="/soee/src/frontend/views/site/profile.php" class="sidebar-perfil"
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
        <a href="javascript:void(0)" class="nav-item ativo" data-painel="overview" onclick="trocarPainel(this)">
            <i class="fa-solid fa-gauge"></i> Dashboard
        </a>
        <a href="/soee/src/frontend/views/site/home.php" class="nav-item">
            <i class="fa-solid fa-house"></i> Início
        </a>

        <div class="nav-secao">Minha Sala</div>
        <a href="javascript:void(0)" class="nav-item" data-painel="alunos" onclick="trocarPainel(this)">
            <i class="fa-solid fa-users"></i> Alunos
            <span class="nav-badge"><?= count($alunos) ?></span>
        </a>
        <a href="javascript:void(0)" class="nav-item" data-painel="inscricoes" onclick="trocarPainel(this)">
            <i class="fa-solid fa-clipboard-list"></i> Inscrições
            <span class="nav-badge"><?= $stats['total_inscricoes'] ?></span>
        </a>
        <a href="javascript:void(0)" class="nav-item" data-painel="partidas" onclick="trocarPainel(this)">
            <i class="fa-solid fa-calendar-days"></i> Partidas
        </a>
        <a href="javascript:void(0)" class="nav-item" data-painel="classificacao" onclick="trocarPainel(this)">
            <i class="fa-solid fa-ranking-star"></i> Classificação
        </a>

        <div class="nav-secao">Outros</div>
        <a href="javascript:void(0)" class="nav-item" data-painel="modalidades" onclick="trocarPainel(this)">
            <i class="fa-solid fa-futbol"></i> Modalidades
            <span class="nav-badge"><?= count($todasModalidades) ?></span>
        </a>
        <a href="/soee/src/frontend/views/forms/feedback.php" class="nav-item">
            <i class="fa-solid fa-comment-dots"></i> Feedback
        </a>
    </nav>

    <div class="sidebar-rodape">
        <a href="/soee/src/backend/includes/logout.php" class="btn-sair">
            <i class="fa-solid fa-right-from-bracket"></i> Sair da conta
        </a>
    </div>
</aside>

<div class="conteudo">
    <header class="topbar">
        <button class="btn-icone" id="toggleSidebar" aria-label="Menu">
            <i class="fa-solid fa-bars"></i>
        </button>
        <div class="topbar-titulo" id="topbar-titulo">Dashboard</div>
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

        <!-- ══════════════════════════════════════
             OVERVIEW
        ══════════════════════════════════════ -->
        <div class="painel active" id="painel-overview">
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
                    <a href="javascript:void(0)" onclick="trocarPainelById('inscricoes')" class="btn-acesso-rapido">
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
                <!-- Partidas recentes -->
                <div class="card">
                    <div class="card-header">
                        <div class="card-titulo"><i class="fa-solid fa-calendar"></i> Partidas da Turma</div>
                        <a href="javascript:void(0)" onclick="trocarPainelById('partidas')" class="card-link">Ver todas</a>
                    </div>
                    <?php if (empty($partidas)): ?>
                        <div class="empty-state"><i class="fa-solid fa-calendar-xmark"></i>Nenhuma partida encontrada</div>
                    <?php else: ?>
                        <?php foreach (array_slice($partidas, 0, 4) as $p):
                            $temPlacar = isset($p['placar_time_a']) && $p['status_partida'] === 'realizada'; ?>
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
                    <!-- Classificação resumida -->
                    <div class="card">
                        <div class="card-header">
                            <div class="card-titulo"><i class="fa-solid fa-ranking-star"></i> Classificação</div>
                            <a href="javascript:void(0)" onclick="trocarPainelById('classificacao')" class="card-link">Ver todas</a>
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
                                    <div class="cstat"><div class="cstat-val" style="color:var(--laranja)"><?= $cl['pontos'] ?></div><div class="cstat-label">Pts</div></div>
                                    <div class="cstat"><div class="cstat-val" style="color:var(--verde)"><?= $cl['vitorias'] ?></div><div class="cstat-label">V</div></div>
                                    <div class="cstat"><div class="cstat-val" style="color:var(--vermelho)"><?= $cl['derrotas'] ?></div><div class="cstat-label">D</div></div>
                                    <div class="cstat"><div class="cstat-val"><?= $cl['saldo'] >= 0 ? '+'.$cl['saldo'] : $cl['saldo'] ?></div><div class="cstat-label">Saldo</div></div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Modalidades abertas (widget) -->
                    <div class="card">
                        <div class="card-header">
                            <div class="card-titulo"><i class="fa-solid fa-futbol"></i> Modalidades Abertas</div>
                            <a href="javascript:void(0)" onclick="trocarPainelById('modalidades')" class="card-link">Ver todas</a>
                        </div>
                        <?php if (empty($modalidades)): ?>
                            <div class="empty-state"><i class="fa-solid fa-futbol"></i>Nenhuma aberta</div>
                        <?php else: ?>
                            <?php foreach ($modalidades as $md):
                                $fim     = new DateTime($md['data_fim_inscricao']);
                                $hoje    = new DateTime();
                                $diff    = (int) $hoje->diff($fim)->days;
                                $urgente = $diff <= 3; ?>
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
                <!-- Alunos resumido -->
                <div class="card">
                    <div class="card-header">
                        <div class="card-titulo"><i class="fa-solid fa-users"></i> Alunos da Turma</div>
                        <a href="javascript:void(0)" onclick="trocarPainelById('alunos')" class="card-link">Ver todos</a>
                    </div>
                    <div class="busca-aluno">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" id="buscaAlunoOverview" placeholder="Buscar aluno..." autocomplete="off">
                    </div>
                    <div class="tabela-alunos-lista" id="listaAlunosOverview">
                        <?php if (empty($alunos)): ?>
                            <div class="empty-state"><i class="fa-solid fa-user-slash"></i>Nenhum aluno na turma</div>
                        <?php else: ?>
                            <?php foreach (array_slice($alunos, 0, 6) as $a): ?>
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

                <!-- Inscrições recentes -->
                <div class="card">
                    <div class="card-header">
                        <div class="card-titulo"><i class="fa-solid fa-clipboard-list"></i> Inscrições Recentes</div>
                        <a href="javascript:void(0)" onclick="trocarPainelById('inscricoes')" class="card-link">Ver todas</a>
                    </div>
                    <?php if (empty($inscricoes)): ?>
                        <div class="empty-state"><i class="fa-solid fa-clipboard-list"></i>Nenhuma inscrição</div>
                    <?php else: ?>
                        <?php foreach (array_slice($inscricoes, 0, 5) as $ins): ?>
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
        </div>

        <!-- ══════════════════════════════════════
             ALUNOS
        ══════════════════════════════════════ -->

        <div class="painel" id="painel-alunos">
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
        </div>

        <!-- ══════════════════════════════════════
             INSCRIÇÕES
        ══════════════════════════════════════ -->

        <div class="painel" id="painel-inscricoes">
            <div class="card">
                <div class="card-header">
                    <div class="card-titulo"><i class="fa-solid fa-clipboard-list"></i> Inscrições da Turma</div>
                    <span style="font-size:.8rem;color:var(--texto-2)"><?= count($inscricoes) ?> registro(s)</span>
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

        <!-- ══════════════════════════════════════
             PARTIDAS
        ══════════════════════════════════════ -->
        
        <div class="painel" id="painel-partidas">
            <div class="card">
                <div class="card-header">
                    <div class="card-titulo"><i class="fa-solid fa-calendar"></i> Partidas da Turma</div>
                    <span style="font-size:.8rem;color:var(--texto-2)"><?= count($partidas) ?> registro(s)</span>
                </div>
                <?php if (empty($partidas)): ?>
                    <div class="empty-state"><i class="fa-solid fa-calendar-xmark"></i>Nenhuma partida encontrada</div>
                <?php else: ?>
                    <?php foreach ($partidas as $p):
                        $temPlacar = isset($p['placar_time_a']) && $p['status_partida'] === 'realizada'; ?>
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
        </div>

        <!-- ══════════════════════════════════════
             CLASSIFICAÇÃO
        ══════════════════════════════════════ -->
        <div class="painel" id="painel-classificacao">
            <div class="card">
                <div class="card-header">
                    <div class="card-titulo"><i class="fa-solid fa-ranking-star"></i> Classificação</div>
                </div>
                <?php if (empty($classificacoes)): ?>
                    <div class="empty-state"><i class="fa-solid fa-ranking-star"></i>Sem classificação disponível</div>
                <?php else: ?>
                    <?php foreach ($classificacoes as $cl): ?>
                    <div class="classif-item">
                        <div class="classif-info">
                            <div class="classif-modalidade"><?= htmlspecialchars($cl['nome_modalidade']) ?></div>
                            <div class="classif-edicao"><?= htmlspecialchars($cl['nome_edicao']) ?></div>
                        </div>
                        <div class="classif-stats">
                            <div class="cstat"><div class="cstat-val" style="color:var(--laranja)"><?= $cl['pontos'] ?></div><div class="cstat-label">Pts</div></div>
                            <div class="cstat"><div class="cstat-val" style="color:var(--verde)"><?= $cl['vitorias'] ?></div><div class="cstat-label">V</div></div>
                            <div class="cstat"><div class="cstat-val" style="color:var(--vermelho)"><?= $cl['derrotas'] ?></div><div class="cstat-label">D</div></div>
                            <div class="cstat"><div class="cstat-val"><?= $cl['empates'] ?></div><div class="cstat-label">E</div></div>
                            <div class="cstat"><div class="cstat-val"><?= $cl['jogos'] ?></div><div class="cstat-label">J</div></div>
                            <div class="cstat"><div class="cstat-val"><?= $cl['saldo'] >= 0 ? '+'.$cl['saldo'] : $cl['saldo'] ?></div><div class="cstat-label">Saldo</div></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- ══════════════════════════════════════
             MODALIDADES
        ══════════════════════════════════════ -->
        <div class="painel" id="painel-modalidades">

            <!-- KPIs -->
            <div class="stats-grid" style="margin-bottom:24px;">
                <div class="stat-card azul">
                    <div class="stat-icone"><i class="fa-solid fa-layer-group"></i></div>
                    <div class="stat-valor"><?= count($todasModalidades) ?></div>
                    <div class="stat-label">Total de Modalidades</div>
                </div>
                <div class="stat-card verde">
                    <div class="stat-icone"><i class="fa-solid fa-circle-check"></i></div>
                    <div class="stat-valor"><?= count(array_filter($todasModalidades, fn($m) => $m['ativo_modalidade'])) ?></div>
                    <div class="stat-label">Modalidades Ativas</div>
                </div>
                <div class="stat-card laranja">
                    <div class="stat-icone"><i class="fa-solid fa-clock"></i></div>
                    <div class="stat-valor"><?= count($modalidades) ?></div>
                    <div class="stat-label">Inscrições Abertas</div>
                </div>
                <div class="stat-card amarelo">
                    <div class="stat-icone"><i class="fa-solid fa-trophy"></i></div>
                    <div class="stat-valor"><?= count($edicoesAtivas) ?></div>
                    <div class="stat-label">Edições em Aberto</div>
                </div>
            </div>

            <!-- Lista completa -->
            <div class="card" style="margin-bottom:24px;">
                <div class="card-header">
                    <div class="card-titulo"><i class="fa-solid fa-futbol"></i> Todas as Modalidades</div>
                    <button class="btn-nova-modalidade" onclick="abrirModalNovaModalidade()">
                        <i class="fa-solid fa-plus"></i> Nova Modalidade
                    </button>
                </div>

                <!-- Filtro -->
                <div class="busca-aluno" style="margin:0 0 16px;">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" id="filtroModalidade"
                           placeholder="Filtrar por nome ou tipo…"
                           autocomplete="off"
                           oninput="filtrarModalidades(this.value)">
                </div>

                <!-- Grid de cards -->
                <div class="modalidades-grid" id="gridModalidades">
                    <?php if (empty($todasModalidades)): ?>
                        <div class="empty-state" style="grid-column:1/-1">
                            <i class="fa-solid fa-futbol"></i>Nenhuma modalidade cadastrada
                        </div>
                    <?php else: ?>
                        <?php foreach ($todasModalidades as $md):
                            $icon   = $tipoIcons[$md['tipo_modalidade']] ?? 'fa-star';
                            $ativo  = (bool) $md['ativo_modalidade'];
                            $mdJson = htmlspecialchars(json_encode($md), ENT_QUOTES, 'UTF-8');
                        ?>
                        <div class="modalidade-card <?= $ativo ? '' : 'inativa' ?>"
                             data-nome="<?= strtolower(htmlspecialchars($md['nome_modalidade'])) ?>"
                             data-tipo="<?= htmlspecialchars($md['tipo_modalidade']) ?>">

                            <div class="mc-topo">
                                <div class="mc-icone"><i class="fa-solid <?= $icon ?>"></i></div>
                                <?php if ($ativo): ?>
                                    <span class="badge-ativa">Ativa</span>
                                <?php else: ?>
                                    <span class="badge-inativa">Inativa</span>
                                <?php endif; ?>
                            </div>

                            <div class="mc-nome"><?= htmlspecialchars($md['nome_modalidade']) ?></div>

                            <?php if (!empty($md['descricao_modalidade'])): ?>
                                <div class="mc-desc">
                                    <?= htmlspecialchars(mb_substr($md['descricao_modalidade'], 0, 90)) ?>…
                                </div>
                            <?php endif; ?>

                            <div class="mc-meta">
                                <span><i class="fa-solid fa-sitemap"></i> <?= $formatoLabel[$md['formato_modalidade']] ?? $md['formato_modalidade'] ?></span>
                                <span><i class="fa-solid fa-users"></i> <?= $participacaoLabel[$md['tipo_participacao']] ?? $md['tipo_participacao'] ?></span>
                                <span><i class="fa-solid fa-shirt"></i> <?= $md['qtd_min_jogadores'] ?>–<?= $md['qtd_max_jogadores'] ?> jog.</span>
                            </div>

                            <div class="mc-rodape">
                                <button class="mc-btn-editar"
                                        onclick="abrirModalEditarModalidade(<?= $mdJson ?>)">
                                    <i class="fa-solid fa-pen"></i> Editar
                                </button>
                                <button class="mc-btn-inscricao"
                                        onclick="abrirModalVincularEdicao(<?= (int)$md['id_modalidade'] ?>, '<?= addslashes(htmlspecialchars($md['nome_modalidade'])) ?>')">
                                    <i class="fa-solid fa-link"></i> Vincular Edição
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Inscrições abertas agora -->
            <?php if (!empty($modalidades)): ?>
            <div class="card">
                <div class="card-header">
                    <div class="card-titulo"><i class="fa-solid fa-clock"></i> Com Inscrições Abertas Agora</div>
                </div>
                <?php foreach ($modalidades as $md):
                    $fim     = new DateTime($md['data_fim_inscricao']);
                    $hoje    = new DateTime();
                    $diff    = (int) $hoje->diff($fim)->days;
                    $urgente = $diff <= 3; ?>
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
            </div>
            <?php endif; ?>

        </div><!-- /painel-modalidades -->

    </main>
</div>

<!-- ══════════════════════════════════════════════════
     MODAL — Criar / Editar Modalidade
══════════════════════════════════════════════════ -->
<div class="modal-overlay-sala" id="modal-modalidade" onclick="fecharSeOverlay(event,'modal-modalidade')">
    <div class="modal-sala">
        <div class="modal-sala-header">
            <h3 id="modal-modalidade-titulo">
                <i class="fa-solid fa-futbol"></i> Nova Modalidade
            </h3>
            <button class="modal-sala-fechar" onclick="fecharModal('modal-modalidade')" aria-label="Fechar">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="modal-sala-body">
            <form id="form-modalidade"
                  action="/soee/src/backend/actions/salvar-modalidade.php"
                  method="POST"
                  enctype="multipart/form-data">
                <input type="hidden" name="id_modalidade" id="inp-id-modalidade">
                <input type="hidden" name="origem_foto" value="upload">

                <div class="form-sala-grid">

                    <div class="form-sala-grupo span2">
                        <label class="form-sala-label">Nome <span class="obrig">*</span></label>
                        <input class="form-sala-input" type="text" name="nome_modalidade"
                               id="inp-nome" placeholder="Ex.: Futsal, Vôlei…" required maxlength="60">
                    </div>

                    <div class="form-sala-grupo">
                        <label class="form-sala-label">Tipo <span class="obrig">*</span></label>
                        <select class="form-sala-select" name="tipo_modalidade" id="inp-tipo" required>
                            <option value="">Selecionar…</option>
                            <option value="quadra">Quadra</option>
                            <option value="campo">Campo</option>
                            <option value="mesa">Mesa</option>
                            <option value="outro">Outro</option>
                        </select>
                    </div>

                    <div class="form-sala-grupo">
                        <label class="form-sala-label">Formato <span class="obrig">*</span></label>
                        <select class="form-sala-select" name="formato_modalidade" id="inp-formato" required>
                            <option value="">Selecionar…</option>
                            <option value="mata_mata">Mata-mata</option>
                            <option value="grupos">Grupos</option>
                            <option value="grupos_mata_mata">Grupos + Mata-mata</option>
                            <option value="todos_contra_todos">Todos contra todos</option>
                        </select>
                    </div>

                    <div class="form-sala-grupo">
                        <label class="form-sala-label">Participação <span class="obrig">*</span></label>
                        <select class="form-sala-select" name="tipo_participacao" id="inp-participacao" required>
                            <option value="">Selecionar…</option>
                            <option value="solo">Individual (Solo)</option>
                            <option value="dupla">Dupla</option>
                            <option value="trio">Trio</option>
                            <option value="time">Time</option>
                        </select>
                    </div>

                    <div class="form-sala-grupo">
                        <label class="form-sala-label">Mín. jogadores <span class="obrig">*</span></label>
                        <input class="form-sala-input" type="number" name="qtd_min_jogadores"
                               id="inp-min" min="1" max="99" placeholder="5" required>
                    </div>

                    <div class="form-sala-grupo">
                        <label class="form-sala-label">Máx. jogadores <span class="obrig">*</span></label>
                        <input class="form-sala-input" type="number" name="qtd_max_jogadores"
                               id="inp-max" min="1" max="99" placeholder="7" required>
                    </div>

                    <div class="form-sala-grupo span2">
                        <label class="form-sala-label">Tipo de Duração</label>
                        <select class="form-sala-select" name="tipo_duracao" id="inp-tipo-duracao"
                                onchange="toggleDuracao(this.value)">
                            <option value="">Sem limite definido</option>
                            <option value="minutos">Por tempo (minutos)</option>
                            <option value="pontos">Por pontuação</option>
                        </select>
                    </div>

                    <div class="form-sala-grupo span2" id="grupo-dur-minutos" style="display:none">
                        <label class="form-sala-label">Duração (ex: 2x15 ou 30)</label>
                        <input class="form-sala-input" type="text" name="duracao_minutos"
                               id="inp-dur-minutos" placeholder="Ex.: 2x15">
                    </div>

                    <div class="form-sala-grupo span2" id="grupo-dur-pontos" style="display:none">
                        <label class="form-sala-label">Pontos para vencer</label>
                        <input class="form-sala-input" type="number" name="duracao_pontos"
                               id="inp-dur-pontos" min="1" max="255" placeholder="Ex.: 21">
                    </div>

                    <div class="form-sala-grupo span2">
                        <label class="form-sala-label">Foto da Modalidade (opcional — JPG, PNG, WEBP, até 5 MB)</label>
                        <input class="form-sala-input" type="file" name="foto_arquivo"
                               accept=".jpg,.jpeg,.png,.webp,.gif">
                    </div>

                    <div class="form-sala-grupo span2">
                        <label class="form-sala-label">Descrição</label>
                        <textarea class="form-sala-textarea" name="descricao_modalidade"
                                  id="inp-desc" placeholder="Regras básicas, observações…" rows="3"></textarea>
                    </div>

                    <div class="form-sala-grupo span2">
                        <label class="form-sala-label">Regulamento</label>
                        <textarea class="form-sala-textarea" name="regulamento_modalidade"
                                  id="inp-regul" placeholder="Regulamento completo…" rows="4"></textarea>
                    </div>

                    <div class="form-sala-grupo span2">
                        <label class="toggle-label">
                            <input type="checkbox" name="ativo_modalidade" id="inp-ativo" value="1" checked>
                            <span class="toggle-track"></span>
                            Modalidade ativa
                        </label>
                    </div>

                </div>
            </form>
        </div>
        <div class="modal-sala-footer">
            <button class="btn-modal-cancelar" onclick="fecharModal('modal-modalidade')">Cancelar</button>
            <button class="btn-modal-salvar" onclick="document.getElementById('form-modalidade').submit()">
                <i class="fa-solid fa-floppy-disk"></i> Salvar Modalidade
            </button>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════
     MODAL — Vincular Modalidade a uma Edição
══════════════════════════════════════════════════ -->
<div class="modal-overlay-sala" id="modal-vincular" onclick="fecharSeOverlay(event,'modal-vincular')">
    <div class="modal-sala modal-sala-sm">
        <div class="modal-sala-header">
            <h3><i class="fa-solid fa-link"></i> Vincular à Edição</h3>
            <button class="modal-sala-fechar" onclick="fecharModal('modal-vincular')" aria-label="Fechar">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="modal-sala-body">
            <form id="form-vincular"
                  action="/soee/src/backend/actions/salvar-edicao-modalidade.php"
                  method="POST">
                <input type="hidden" name="modalidade_id_modalidade" id="vinc-modal-id">

                <div class="form-sala-grid">

                    <div class="form-sala-grupo span2">
                        <label class="form-sala-label">Modalidade</label>
                        <input class="form-sala-input" type="text" id="vinc-modal-nome" disabled>
                    </div>

                    <div class="form-sala-grupo span2">
                        <label class="form-sala-label">Edição <span class="obrig">*</span></label>
                        <select class="form-sala-select" name="edicao_id_edicao" required>
                            <option value="">Selecionar edição…</option>
                            <?php foreach ($edicoesAtivas as $ed): ?>
                            <option value="<?= $ed['id_edicao'] ?>">
                                <?= htmlspecialchars($ed['nome_edicao']) ?> (<?= $ed['ano_edicao'] ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-sala-grupo">
                        <label class="form-sala-label">Início das inscrições <span class="obrig">*</span></label>
                        <input class="form-sala-input" type="date" name="data_inicio_inscricao" required>
                    </div>

                    <div class="form-sala-grupo">
                        <label class="form-sala-label">Fim das inscrições <span class="obrig">*</span></label>
                        <input class="form-sala-input" type="date" name="data_fim_inscricao" required>
                    </div>

                    <div class="form-sala-grupo span2">
                        <label class="form-sala-label">Status <span class="obrig">*</span></label>
                        <select class="form-sala-select" name="status_edicao_modalidade" required>
                            <option value="inscricoes">Inscrições abertas</option>
                            <option value="em_andamento">Em andamento</option>
                            <option value="encerrado">Encerrado</option>
                        </select>
                    </div>

                </div>
            </form>
        </div>
        <div class="modal-sala-footer">
            <button class="btn-modal-cancelar" onclick="fecharModal('modal-vincular')">Cancelar</button>
            <button class="btn-modal-salvar" onclick="document.getElementById('form-vincular').submit()">
                <i class="fa-solid fa-link"></i> Vincular
            </button>
        </div>
    </div>
</div>

<script src="/soee/src/frontend/scripts/dash-adm-sala.js"></script>
<script>
/* ── Tema ──────────────────────────────────────────── */
(function () {
    var t = localStorage.getItem('theme');
    if (t) document.documentElement.setAttribute('data-theme', t);
})();

/* ── Modais — abrir ────────────────────────────────── */
function abrirModalNovaModalidade() {
    document.getElementById('modal-modalidade-titulo').innerHTML =
        '<i class="fa-solid fa-plus"></i> Nova Modalidade';
    document.getElementById('form-modalidade').reset();
    document.getElementById('inp-id-modalidade').value = '';
    document.getElementById('inp-ativo').checked = true;
    toggleDuracao('');
    document.getElementById('modal-modalidade').classList.add('open');
}

function abrirModalEditarModalidade(md) {
    document.getElementById('modal-modalidade-titulo').innerHTML =
        '<i class="fa-solid fa-pen"></i> Editar Modalidade';
    document.getElementById('inp-id-modalidade').value   = md.id_modalidade    || '';
    document.getElementById('inp-nome').value            = md.nome_modalidade  || '';
    document.getElementById('inp-tipo').value            = md.tipo_modalidade  || '';
    document.getElementById('inp-formato').value         = md.formato_modalidade || '';
    document.getElementById('inp-participacao').value    = md.tipo_participacao || '';
    document.getElementById('inp-min').value             = md.qtd_min_jogadores || '';
    document.getElementById('inp-max').value             = md.qtd_max_jogadores || '';
    document.getElementById('inp-desc').value            = md.descricao_modalidade  || '';
    document.getElementById('inp-regul').value           = md.regulamento_modalidade || '';
    document.getElementById('inp-ativo').checked         = md.ativo_modalidade == 1;

    var td = md.tipo_duracao || '';
    document.getElementById('inp-tipo-duracao').value = td;
    toggleDuracao(td);
    if (td === 'minutos') document.getElementById('inp-dur-minutos').value = md.duracao_minutos || '';
    if (td === 'pontos')  document.getElementById('inp-dur-pontos').value  = md.duracao_pontos  || '';

    document.getElementById('modal-modalidade').classList.add('open');
}

function abrirModalVincularEdicao(id, nome) {
    document.getElementById('vinc-modal-id').value   = id;
    document.getElementById('vinc-modal-nome').value = nome;
    document.getElementById('modal-vincular').classList.add('open');
}

/* ── Modais — fechar ───────────────────────────────── */
function fecharModal(id) {
    document.getElementById(id).classList.remove('open');
}
function fecharSeOverlay(event, id) {
    if (event.target === document.getElementById(id)) fecharModal(id);
}

/* ── Toggle duração ────────────────────────────────── */
function toggleDuracao(val) {
    document.getElementById('grupo-dur-minutos').style.display = val === 'minutos' ? '' : 'none';
    document.getElementById('grupo-dur-pontos').style.display  = val === 'pontos'  ? '' : 'none';
}

/* ── Filtro de modalidades ─────────────────────────── */
function filtrarModalidades(q) {
    q = q.toLowerCase();
    document.querySelectorAll('#gridModalidades .modalidade-card').forEach(function (el) {
        var nome = el.dataset.nome || '';
        var tipo = el.dataset.tipo || '';
        el.style.display = (nome.includes(q) || tipo.includes(q)) ? '' : 'none';
    });
}
</script>

<?php include __DIR__ . '/../includes/end.php'; ?>