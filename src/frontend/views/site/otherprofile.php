<?php
ob_start();
session_start();
require_once __DIR__ . '/../../../backend/includes/conexao.php';
require_once __DIR__ . '/../../../backend/controllers/home.php';

AuthHome::exigirLogin();

$userTipo    = AuthHome::getTipo();
$dashboardUrl = AuthHome::getRota($userTipo);

$perfilId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($perfilId <= 0) {
    header('Location: ' . $dashboardUrl);
    exit;
}

// ─── Busca dados públicos do usuário ─────────────────────────────────────────
// PostgreSQL: ativo_usuario é BOOLEAN → comparar com TRUE (não com 1)
$stmt = $conn->prepare("
    SELECT u.id_usuario, u.nome_usuario, u.genero_usuario,
           u.tipo_usuario, u.ativo_usuario, u.foto_perfil_usuario,
           t.nome_turma, t.periodo_turma,
           c.nome_curso, c.sigla_curso
    FROM usuario u
    LEFT JOIN turma t ON t.id_turma = u.turma_id_turma
    LEFT JOIN curso c ON c.id_curso = t.curso_id_curso
    WHERE u.id_usuario = :id AND u.ativo_usuario = TRUE
");
$stmt->execute([':id' => $perfilId]);
$perfil = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$perfil) {
    header('Location: ' . $dashboardUrl);
    exit;
}
// ─── Inscrições ativas (esportes + camisa) ────────────────────────────────────
$stmtIns = $conn->prepare("
    SELECT i.numero_camisa_inscricao,
           i.nome_camisa_inscricao,
           i.posicao_inscricao,
           i.capitao_inscricao,
           m.nome_modalidade,
           m.tipo_modalidade,
           m.foto_modalidade,
           e.nome_edicao,
           i.status_inscricao
    FROM inscricao i
    INNER JOIN edicao_modalidade em ON em.id_edicao_modalidade = i.edicao_modalidade_id
    INNER JOIN modalidade m         ON m.id_modalidade = em.modalidade_id_modalidade
    INNER JOIN edicao e             ON e.id_edicao = em.edicao_id_edicao
    WHERE i.usuario_id_usuario = :id
      AND i.status_inscricao   = 'ativa'
    ORDER BY i.data_inscricao DESC
");
$stmtIns->execute([':id' => $perfilId]);
$inscricoes = $stmtIns->fetchAll(PDO::FETCH_ASSOC);

// Camisa da inscrição mais recente ativa
$camisaAtual = $inscricoes[0] ?? null;

$tipoLabel   = ['adm_geral' => 'Administrador Geral', 'adm_sala' => 'ADM de Sala', 'professor' => 'Professor', 'aluno' => 'Aluno'];
$generoLabel = ['m' => 'Masculino', 'f' => 'Feminino', 'n' => 'Não informado'];
$tipoIcone   = ['adm_geral' => 'crown', 'adm_sala' => 'user-shield', 'professor' => 'chalkboard-teacher', 'aluno' => 'graduation-cap'];

$tipoModalidade = [
    'quadra' => 'fa-basketball',
    'mesa'   => 'fa-table-tennis-paddle-ball',
    'outro'  => 'fa-trophy',
];
?>
<?php include __DIR__ . '/../includes/doctype.php'; ?>
<head>
    <title>SOEE | Perfil de <?= htmlspecialchars($perfil['nome_usuario']) ?></title>
    <!-- Reutiliza exatamente o mesmo CSS do user-conta -->
    <link rel="stylesheet" href="/soee/src/frontend/styles/user-conta.css">
    <!-- Estilos extras exclusivos desta página (mínimos) -->
    <style>
        /* ── Card de esporte ─────────────────────────────────── */
        .esportes-grid {
            padding: 14px 18px 20px;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 12px;
        }

        .esporte-card {
            background: var(--fundo);
            border: 1px solid var(--borda);
            border-radius: var(--rm);
            padding: 16px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            transition: var(--tr);
            position: relative;
            overflow: hidden;
        }
        .esporte-card::before {
            content: '';
            position: absolute; top: 0; left: 0; right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--laranja), var(--azul-2));
            transform: scaleX(0);
            transform-origin: left;
            transition: transform .35s ease;
        }
        .esporte-card:hover {
            border-color: var(--azul-2);
            transform: translateY(-4px);
            box-shadow: var(--sombra-h);
        }
        .esporte-card:hover::before { transform: scaleX(1); }

        .esporte-topo {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .esporte-icone {
            width: 40px; height: 40px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--azul), var(--azul-2));
            display: flex; align-items: center; justify-content: center;
            color: #fff;
            font-size: .9rem;
            flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(30,86,113,.3);
        }
        .esporte-nome {
            font-size: .9rem;
            font-weight: 700;
            color: var(--texto);
            line-height: 1.2;
        }
        .esporte-edicao {
            font-size: .72rem;
            color: var(--texto-2);
        }

        .esporte-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
        }
        .esporte-badge {
            font-size: .62rem;
            font-weight: 700;
            padding: 3px 9px;
            border-radius: 999px;
            text-transform: uppercase;
            letter-spacing: .07em;
        }
        .esporte-badge.capitao {
            background: rgba(255,77,18,.12);
            color: var(--laranja);
        }
        .esporte-badge.posicao {
            background: rgba(44,125,163,.1);
            color: var(--azul-2);
        }

        /* ── Camisa destaque ─────────────────────────────────── */
        .camisa-destaque {
            display: flex;
            align-items: center;
            gap: 20px;
            padding: 24px 26px;
            flex-wrap: wrap;
        }
        .camisa-visual {
            width: 80px; height: 80px;
            border-radius: 18px;
            background: linear-gradient(135deg, var(--azul), var(--azul-2));
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            color: #fff;
            box-shadow: 0 8px 24px rgba(30,86,113,.35);
            flex-shrink: 0;
        }
        .camisa-visual .num {
            font-family: 'Playfair Display', serif;
            font-size: 1.6rem;
            font-weight: 800;
            line-height: 1;
        }
        .camisa-visual .label {
            font-size: .52rem;
            text-transform: uppercase;
            letter-spacing: .1em;
            opacity: .7;
            margin-top: 2px;
        }
        .camisa-dados {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .camisa-nome-display {
            font-family: monospace;
            font-size: 1.4rem;
            font-weight: 800;
            letter-spacing: .15em;
            color: var(--texto);
        }
        .camisa-sub {
            font-size: .76rem;
            color: var(--texto-2);
        }

        /* ── Sem inscrições ─────────────────────────────────── */
        .vazio-estado {
            padding: 40px 26px;
            text-align: center;
            color: var(--texto-2);
            font-size: .9rem;
        }
        .vazio-estado i {
            display: block;
            font-size: 2rem;
            margin-bottom: 12px;
            opacity: .3;
            color: var(--azul-2);
        }

        /* ── Chip de gênero ─────────────────────────────────── */
        .chip-genero {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: .75rem;
            font-weight: 600;
            padding: 4px 12px;
            border-radius: 999px;
            background: rgba(44,125,163,.1);
            color: var(--azul-2);
            border: 1px solid rgba(44,125,163,.2);
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

<!-- HERO -->
<div class="conta-hero">
    <div class="hero-grid"></div>
    <div class="hero-particles"><span></span><span></span><span></span></div>
    <div class="hero-conteudo">
        <div class="hero-avatar-wrap">
            <div class="hero-avatar">
                <?php if (!empty($perfil['foto_perfil_usuario'])): ?>
                    <img src="<?= htmlspecialchars($perfil['foto_perfil_usuario']) ?>" alt="Foto de perfil">
                <?php else: ?>
                    <?= strtoupper(substr($perfil['nome_usuario'], 0, 2)) ?>
                <?php endif; ?>
            </div>
        </div>
        <div class="hero-info">
            <div class="hero-tipo-badge">
                <i class="fa-solid fa-<?= $tipoIcone[$perfil['tipo_usuario']] ?? 'user' ?>"></i>
                <?= htmlspecialchars($tipoLabel[$perfil['tipo_usuario']] ?? $perfil['tipo_usuario']) ?>
            </div>
            <h1 class="hero-nome"><?= htmlspecialchars($perfil['nome_usuario']) ?></h1>
            <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:10px;">
                <span class="chip-genero">
                    <i class="fa-solid fa-venus-mars"></i>
                    <?= htmlspecialchars($generoLabel[$perfil['genero_usuario']] ?? '—') ?>
                </span>
            </div>
            <?php if (!empty($perfil['nome_turma'])): ?>
            <div class="hero-turma">
                <i class="fa-solid fa-door-open"></i>
                <?= htmlspecialchars($perfil['nome_turma']) ?>
                <?php if (!empty($perfil['sigla_curso'])): ?> · <?= htmlspecialchars($perfil['sigla_curso']) ?><?php endif; ?>
                <?php if (!empty($perfil['periodo_turma'])): ?> · <?= ucfirst($perfil['periodo_turma']) ?><?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<main class="conta-main">

    <!-- ══════════════════════════════════════════════════════════
         Camisa
    ══════════════════════════════════════════════════════════ -->
    <?php if ($camisaAtual && (!empty($camisaAtual['numero_camisa_inscricao']) || !empty($camisaAtual['nome_camisa_inscricao']))): ?>
    <section class="conta-secao reveal reveal-delay-1">
        <div class="secao-header">
            <i class="fa-solid fa-shirt"></i>
            <h2>Camisa</h2>
        </div>
        <div class="camisa-destaque">
            <div class="camisa-visual">
                <span class="num">
                    <?= $camisaAtual['numero_camisa_inscricao'] ? '#' . $camisaAtual['numero_camisa_inscricao'] : '—' ?>
                </span>
                <span class="label">nº</span>
            </div>
            <div class="camisa-dados">
                <?php if (!empty($camisaAtual['nome_camisa_inscricao'])): ?>
                    <div class="camisa-nome-display"><?= htmlspecialchars(strtoupper($camisaAtual['nome_camisa_inscricao'])) ?></div>
                <?php endif; ?>
                <div class="camisa-sub">
                    <?php if (!empty($camisaAtual['numero_camisa_inscricao'])): ?>
                        Número <strong>#<?= (int)$camisaAtual['numero_camisa_inscricao'] ?></strong>
                    <?php endif; ?>
                </div>
                <?php if ($camisaAtual['capitao_inscricao']): ?>
                    <span style="font-size:.75rem;color:var(--laranja);font-weight:700;margin-top:4px;">
                        <i class="fa-solid fa-star"></i> Capitão
                    </span>
                <?php endif; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- ══════════════════════════════════════════════════════════
         Esportes / Inscrições ativas
    ══════════════════════════════════════════════════════════ -->
    <section class="conta-secao reveal reveal-delay-2">
        <div class="secao-header">
            <i class="fa-solid fa-medal"></i>
            <h2>Participando</h2>
        </div>

        <?php if (!empty($inscricoes)): ?>
        <div class="esportes-grid">
            <?php foreach ($inscricoes as $ins): ?>
            <div class="esporte-card">
                <div class="esporte-topo">
                    <div class="esporte-icone">
                        <i class="fa-solid <?= $tipoModalidade[$ins['tipo_modalidade']] ?? 'fa-trophy' ?>"></i>
                    </div>
                    <div>
                        <div class="esporte-nome"><?= htmlspecialchars($ins['nome_modalidade']) ?></div>
                        <div class="esporte-edicao"><?= htmlspecialchars($ins['nome_edicao']) ?></div>
                    </div>
                </div>
                <div class="esporte-badges">
                    <?php if (!empty($ins['posicao_inscricao'])): ?>
                        <span class="esporte-badge posicao">
                            <i class="fa-solid fa-running"></i> <?= htmlspecialchars($ins['posicao_inscricao']) ?>
                        </span>
                    <?php endif; ?>
                    <?php if ($ins['capitao_inscricao']): ?>
                        <span class="esporte-badge capitao">
                            <i class="fa-solid fa-star"></i> Capitão
                        </span>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="vazio-estado">
            <i class="fa-solid fa-flag"></i>
            <?= htmlspecialchars(explode(' ', $perfil['nome_usuario'])[0]) ?> ainda não está inscrito em nenhuma modalidade.
        </div>
        <?php endif; ?>
    </section>

</main>

<!-- Reutiliza exatamente o mesmo JS do user-conta -->
<script src="/soee/src/frontend/scripts/user-conta.js"></script>
<script>
/* ── Tema ─────────────────────────────────────────────────────────── */
const _t = localStorage.getItem('theme');
if (_t) document.documentElement.setAttribute('data-theme', _t);
</script>

<?php include __DIR__ . '/../includes/end.php'; ?>