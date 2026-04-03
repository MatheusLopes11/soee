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
<!DOCTYPE html>
<html lang="pt-BR" data-theme="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard — Professor | SOEE</title>
<link rel="icon" type="image/png" href="/soee/src/images/logo-soee.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;800&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous">
<script>
    const _t = localStorage.getItem('theme');
    if (_t) document.documentElement.setAttribute('data-theme', _t);
</script>
<style>
:root {
    --azul: #1e5671;
    --azul-2: #2c7da3;
    --laranja: #ff4d12;
    --laranja-s: rgba(255,77,18,.22);
    --fundo: #f0f4f8;
    --bloco: #ffffff;
    --texto: #1e293b;
    --texto-2: #64748b;
    --branco: #ffffff;
    --borda: rgba(30,86,113,.1);
    --sombra: 0 4px 24px -6px rgba(0,0,0,.09);
    --sombra-h: 0 12px 40px -8px rgba(0,0,0,.15);
    --r: 16px;
    --rm: 10px;
    --tr: all .32s cubic-bezier(.4,0,.2,1);
    --sidebar: 260px;
    --verde: #22c55e;
    --amarelo: #f59e0b;
    --vermelho: #ef4444;
    --roxo: #8b5cf6;
}
[data-theme="dark"] {
    --fundo: #070e17;
    --bloco: #0d1b2a;
    --texto: #e2e8f0;
    --texto-2: #8da8bc;
    --borda: rgba(44,125,163,.13);
}
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box}
html{scroll-behavior:smooth}
body{font-family:'DM Sans',sans-serif;background:var(--fundo);color:var(--texto);line-height:1.6;overflow-x:hidden;display:flex;min-height:100vh}

.sidebar {
    width: var(--sidebar);
    min-height: 100vh;
    background: var(--azul);
    display: flex;
    flex-direction: column;
    position: fixed;
    top: 0; left: 0; bottom: 0;
    z-index: 200;
    transition: transform .3s ease;
    overflow-y: auto;
}
.sidebar-logo {
    padding: 28px 24px 20px;
    border-bottom: 1px solid rgba(255,255,255,.1);
}
.sidebar-logo a {
    font-family: 'Playfair Display', serif;
    font-size: 1.6rem; font-weight: 800;
    color: white; text-decoration: none;
    letter-spacing: .05em;
}
.sidebar-logo a span { color: var(--laranja); }
.sidebar-logo small {
    display: block; font-size: .65rem;
    letter-spacing: .15em; text-transform: uppercase;
    color: rgba(255,255,255,.45); margin-top: 2px;
}
.sidebar-perfil {
    padding: 20px 24px;
    border-bottom: 1px solid rgba(255,255,255,.08);
    display: flex; align-items: center; gap: 12px;
}
.perfil-avatar {
    width: 44px; height: 44px; border-radius: 50%;
    background: rgba(255,255,255,.15);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.1rem; color: white; flex-shrink: 0;
    overflow: hidden;
}
.perfil-avatar img { width: 100%; height: 100%; object-fit: cover; }
.perfil-info { overflow: hidden; }
.perfil-nome {
    font-size: .85rem; font-weight: 700;
    color: white; white-space: nowrap;
    overflow: hidden; text-overflow: ellipsis;
}
.perfil-cargo {
    font-size: .7rem; color: rgba(255,255,255,.5);
    text-transform: uppercase; letter-spacing: .1em;
}
.sidebar-nav { flex: 1; padding: 16px 0; }
.nav-secao {
    padding: 8px 24px 4px;
    font-size: .62rem; font-weight: 700;
    letter-spacing: .15em; text-transform: uppercase;
    color: rgba(255,255,255,.3);
}
.nav-item {
    display: flex; align-items: center; gap: 12px;
    padding: 11px 24px; text-decoration: none;
    color: rgba(255,255,255,.65); font-size: .88rem;
    font-weight: 500; transition: var(--tr);
    position: relative; border-left: 3px solid transparent;
}
.nav-item:hover, .nav-item.ativo {
    color: white;
    background: rgba(255,255,255,.08);
    border-left-color: var(--laranja);
}
.nav-item.ativo { background: rgba(255,77,18,.15); }
.nav-item i { width: 18px; text-align: center; font-size: .9rem; }
.nav-badge {
    margin-left: auto;
    background: var(--laranja); color: white;
    font-size: .65rem; font-weight: 700;
    padding: 2px 7px; border-radius: 999px;
}
.sidebar-rodape {
    padding: 16px 24px;
    border-top: 1px solid rgba(255,255,255,.08);
}
.btn-sair {
    display: flex; align-items: center; gap: 10px;
    width: 100%; padding: 10px 14px;
    background: rgba(239,68,68,.15);
    border: 1px solid rgba(239,68,68,.25);
    border-radius: var(--rm); color: #fca5a5;
    font-size: .85rem; font-weight: 600;
    cursor: pointer; text-decoration: none;
    transition: var(--tr);
}
.btn-sair:hover { background: rgba(239,68,68,.3); color: white; }

.conteudo {
    margin-left: var(--sidebar);
    flex: 1;
    display: flex;
    flex-direction: column;
    min-width: 0;
}
.topbar {
    height: 64px;
    background: var(--bloco);
    border-bottom: 1px solid var(--borda);
    display: flex; align-items: center;
    padding: 0 32px;
    gap: 16px;
    position: sticky; top: 0; z-index: 100;
}
.topbar-titulo {
    flex: 1;
    font-family: 'Playfair Display', serif;
    font-size: 1.2rem; font-weight: 700;
    color: var(--azul);
}
.topbar-acoes { display: flex; align-items: center; gap: 10px; }
.btn-icone {
    width: 36px; height: 36px;
    background: none; border: 1px solid var(--borda);
    border-radius: var(--rm);
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; color: var(--texto-2);
    font-size: .9rem; transition: var(--tr);
}
.btn-icone:hover { border-color: var(--laranja); color: var(--laranja); }
.data-atual {
    font-size: .8rem; color: var(--texto-2);
    background: var(--fundo);
    padding: 6px 14px; border-radius: var(--rm);
    border: 1px solid var(--borda);
}

.pagina { padding: 32px; }

.boas-vindas {
    background: linear-gradient(135deg, var(--azul) 0%, var(--azul-2) 100%);
    border-radius: var(--r); padding: 32px 36px;
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 28px; position: relative; overflow: hidden;
    color: white;
}
.boas-vindas::before {
    content: '';
    position: absolute; right: -40px; top: -40px;
    width: 220px; height: 220px;
    background: rgba(255,255,255,.05);
    border-radius: 50%;
}
.boas-vindas::after {
    content: '';
    position: absolute; right: 40px; bottom: -60px;
    width: 160px; height: 160px;
    background: rgba(255,77,18,.1);
    border-radius: 50%;
}
.bv-texto h2 {
    font-family: 'Playfair Display', serif;
    font-size: 1.6rem; font-weight: 800; margin-bottom: 6px;
}
.bv-texto p { color: rgba(255,255,255,.7); font-size: .9rem; }
.bv-badge {
    background: rgba(255,255,255,.12);
    border: 1px solid rgba(255,255,255,.2);
    padding: 10px 20px; border-radius: var(--rm);
    font-size: .8rem; font-weight: 600; letter-spacing: .08em;
    text-transform: uppercase; position: relative; z-index: 1;
}
.bv-badge i { color: var(--laranja); margin-right: 6px; }

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
    gap: 16px;
    margin-bottom: 28px;
}
.stat-card {
    background: var(--bloco);
    border: 1px solid var(--borda);
    border-radius: var(--r);
    padding: 22px 20px;
    box-shadow: var(--sombra);
    transition: var(--tr);
    position: relative; overflow: hidden;
}
.stat-card:hover { transform: translateY(-4px); box-shadow: var(--sombra-h); }
.stat-card::before {
    content: '';
    position: absolute; top: 0; left: 0; right: 0; height: 3px;
}
.stat-card.azul::before  { background: var(--azul-2); }
.stat-card.laranja::before { background: var(--laranja); }
.stat-card.verde::before   { background: var(--verde); }
.stat-card.roxo::before    { background: var(--roxo); }
.stat-card.amarelo::before { background: var(--amarelo); }
.stat-card.vermelho::before { background: var(--vermelho); }
.stat-icone {
    width: 42px; height: 42px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1rem; margin-bottom: 14px;
}
.azul .stat-icone   { background: rgba(44,125,163,.12); color: var(--azul-2); }
.laranja .stat-icone { background: rgba(255,77,18,.1); color: var(--laranja); }
.verde .stat-icone   { background: rgba(34,197,94,.1); color: var(--verde); }
.roxo .stat-icone    { background: rgba(139,92,246,.1); color: var(--roxo); }
.amarelo .stat-icone { background: rgba(245,158,11,.1); color: var(--amarelo); }
.vermelho .stat-icone { background: rgba(239,68,68,.1); color: var(--vermelho); }
.stat-valor {
    font-family: 'Playfair Display', serif;
    font-size: 2rem; font-weight: 800;
    color: var(--texto); line-height: 1;
}
.stat-label {
    font-size: .78rem; color: var(--texto-2);
    text-transform: uppercase; letter-spacing: .07em;
    margin-top: 4px;
}

.grid-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px;
    margin-bottom: 24px;
}
.grid-3 {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 24px;
    margin-bottom: 24px;
}
.card {
    background: var(--bloco);
    border: 1px solid var(--borda);
    border-radius: var(--r);
    box-shadow: var(--sombra);
    overflow: hidden;
}
.card-header {
    padding: 20px 24px 0;
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 16px;
}
.card-titulo {
    font-family: 'Playfair Display', serif;
    font-size: 1.05rem; font-weight: 700; color: var(--azul);
    display: flex; align-items: center; gap: 8px;
}
.card-titulo i { color: var(--laranja); font-size: .95rem; }
.card-link {
    font-size: .78rem; color: var(--azul-2); text-decoration: none;
    font-weight: 600; transition: var(--tr);
}
.card-link:hover { color: var(--laranja); }

.partida-item {
    display: flex; align-items: center;
    padding: 14px 24px; gap: 14px;
    border-bottom: 1px solid var(--borda);
    transition: var(--tr);
}
.partida-item:last-child { border-bottom: none; }
.partida-item:hover { background: rgba(30,86,113,.03); }
.partida-data {
    min-width: 54px; text-align: center;
    background: var(--fundo); border-radius: 10px;
    padding: 7px 6px; border: 1px solid var(--borda);
}
.partida-dia { font-size: 1.2rem; font-weight: 800; color: var(--azul); line-height: 1; }
.partida-mes { font-size: .65rem; text-transform: uppercase; color: var(--texto-2); letter-spacing: .07em; }
.partida-info { flex: 1; min-width: 0; }
.partida-times {
    font-weight: 700; font-size: .88rem;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.partida-detalhe {
    font-size: .75rem; color: var(--texto-2); margin-top: 2px;
}
.partida-hora {
    font-size: .8rem; font-weight: 700;
    color: var(--azul-2); white-space: nowrap;
}

.resultado-item {
    display: flex; align-items: center;
    padding: 12px 24px; gap: 12px;
    border-bottom: 1px solid var(--borda);
    transition: var(--tr);
}
.resultado-item:last-child { border-bottom: none; }
.resultado-item:hover { background: rgba(30,86,113,.03); }
.res-placar {
    background: var(--azul); color: white;
    padding: 6px 12px; border-radius: var(--rm);
    font-weight: 800; font-size: .9rem;
    white-space: nowrap; min-width: 60px; text-align: center;
}
.res-info { flex: 1; min-width: 0; }
.res-times { font-weight: 600; font-size: .85rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.res-detalhe { font-size: .72rem; color: var(--texto-2); margin-top: 2px; }
.res-vencedor {
    font-size: .72rem; font-weight: 700;
    color: var(--verde); white-space: nowrap;
}

.edicao-item {
    display: flex; align-items: center;
    padding: 14px 24px; gap: 12px;
    border-bottom: 1px solid var(--borda);
    transition: var(--tr);
}
.edicao-item:last-child { border-bottom: none; }
.edicao-item:hover { background: rgba(30,86,113,.03); }
.edicao-icone {
    width: 38px; height: 38px; border-radius: 10px;
    background: rgba(30,86,113,.08);
    display: flex; align-items: center; justify-content: center;
    color: var(--azul); font-size: .9rem; flex-shrink: 0;
}
.edicao-info { flex: 1; min-width: 0; }
.edicao-nome { font-weight: 700; font-size: .88rem; }
.edicao-detalhe { font-size: .72rem; color: var(--texto-2); margin-top: 2px; }

.sumula-item {
    display: flex; align-items: center;
    padding: 12px 24px; gap: 12px;
    border-bottom: 1px solid var(--borda);
    transition: var(--tr);
}
.sumula-item:last-child { border-bottom: none; }
.sumula-item:hover { background: rgba(30,86,113,.03); }
.sumula-icone {
    width: 36px; height: 36px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: .85rem; flex-shrink: 0;
}
.sumula-info { flex: 1; min-width: 0; }
.sumula-nome { font-weight: 600; font-size: .83rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.sumula-detalhe { font-size: .72rem; color: var(--texto-2); margin-top: 2px; }

.badge-status {
    font-size: .68rem; font-weight: 700;
    padding: 3px 10px; border-radius: 999px;
    white-space: nowrap;
}
.badge-status.pendente   { background: rgba(245,158,11,.12); color: #b45309; }
.badge-status.validada   { background: rgba(34,197,94,.12);  color: #15803d; }
.badge-status.rejeitada  { background: rgba(239,68,68,.12);  color: #b91c1c; }
.badge-status.inscricoes  { background: rgba(139,92,246,.12); color: #7c3aed; }
.badge-status.em_andamento { background: rgba(34,197,94,.12); color: #15803d; }
.badge-status.planejamento { background: rgba(100,116,139,.1); color: #475569; }
.badge-status.encerrado    { background: rgba(30,86,113,.1); color: var(--azul); }

.empty-state {
    padding: 36px 24px; text-align: center;
    color: var(--texto-2); font-size: .88rem;
}
.empty-state i { font-size: 2rem; opacity: .3; margin-bottom: 10px; display: block; }

.mb-24 { margin-bottom: 24px; }

@media (max-width: 1100px) {
    .grid-2, .grid-3 { grid-template-columns: 1fr; }
}
@media (max-width: 768px) {
    :root { --sidebar: 0px; }
    .sidebar { transform: translateX(-260px); width: 260px; }
    .sidebar.aberta { transform: translateX(0); }
    .conteudo { margin-left: 0; }
    .pagina { padding: 20px 16px; }
    .topbar { padding: 0 16px; }
    .boas-vindas { flex-direction: column; gap: 16px; align-items: flex-start; }
    .stats-grid { grid-template-columns: repeat(2, 1fr); }
}
</style>
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
        <button class="btn-icone" id="toggleSidebar" aria-label="Menu">
            <i class="fa-solid fa-bars"></i>
        </button>
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

<script>
    document.getElementById('dataAtual').textContent = new Date().toLocaleDateString('pt-BR', {
        weekday: 'short', day: '2-digit', month: 'short', year: 'numeric'
    });

    const sidebar = document.getElementById('sidebar');
    document.getElementById('toggleSidebar').addEventListener('click', () => {
        sidebar.classList.toggle('aberta');
    });

    const html      = document.documentElement;
    const btnTema   = document.getElementById('toggleTema');
    const iconeTema = document.getElementById('iconeTema');
    const temaSalvo = localStorage.getItem('theme') || 'light';
    html.setAttribute('data-theme', temaSalvo);
    iconeTema.className = temaSalvo === 'dark' ? 'fa-solid fa-sun' : 'fa-solid fa-moon';

    btnTema.addEventListener('click', () => {
        const atual = html.getAttribute('data-theme');
        const novo  = atual === 'dark' ? 'light' : 'dark';
        html.setAttribute('data-theme', novo);
        localStorage.setItem('theme', novo);
        iconeTema.className = novo === 'dark' ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
    });
</script>
</body>
</html>