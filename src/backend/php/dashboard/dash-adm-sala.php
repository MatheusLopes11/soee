<?php
session_start();
include __DIR__ . '/../include/conexao.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /soee/index.php');
    exit();
}

if ($_SESSION['user_tipo'] !== 'adm_sala') {
    header('Location: /soee/index.php');
    exit();
}

$userId   = (int) $_SESSION['user_id'];
$userNome = $_SESSION['user_nome'];

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
<!DOCTYPE html>
<html lang="pt-BR" data-theme="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard — ADM Sala | SOEE</title>
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
    --teal: #0d9488;
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
    width: var(--sidebar); min-height: 100vh;
    background: linear-gradient(180deg, #0f2d3d 0%, #1e5671 60%, #2c7da3 100%);
    display: flex; flex-direction: column;
    position: fixed; top: 0; left: 0; bottom: 0;
    z-index: 200; overflow-y: auto; transition: transform .3s ease;
}
.sidebar-logo { padding: 28px 24px 20px; border-bottom: 1px solid rgba(255,255,255,.1); }
.sidebar-logo a {
    font-family: 'Playfair Display', serif;
    font-size: 1.6rem; font-weight: 800;
    color: white; text-decoration: none; letter-spacing: .05em;
}
.sidebar-logo a span { color: var(--laranja); }
.sidebar-logo small {
    display: block; font-size: .65rem; letter-spacing: .15em;
    text-transform: uppercase; color: rgba(255,255,255,.45); margin-top: 2px;
}
.sidebar-turma {
    margin: 16px 16px 0;
    background: rgba(255,77,18,.15);
    border: 1px solid rgba(255,77,18,.3);
    border-radius: var(--rm); padding: 14px 16px;
}
.turma-label { font-size: .65rem; letter-spacing: .12em; text-transform: uppercase; color: rgba(255,255,255,.5); }
.turma-nome { font-size: 1.1rem; font-weight: 800; color: white; margin-top: 2px; }
.turma-curso { font-size: .72rem; color: rgba(255,255,255,.55); margin-top: 2px; }
.sidebar-perfil {
    padding: 16px; display: flex; align-items: center; gap: 12px;
    border-bottom: 1px solid rgba(255,255,255,.08); margin-top: 12px;
}
.perfil-avatar {
    width: 42px; height: 42px; border-radius: 50%;
    background: rgba(255,255,255,.15);
    display: flex; align-items: center; justify-content: center;
    font-size: 1rem; color: white; flex-shrink: 0; overflow: hidden;
}
.perfil-avatar img { width: 100%; height: 100%; object-fit: cover; }
.perfil-nome { font-size: .83rem; font-weight: 700; color: white; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.perfil-cargo { font-size: .68rem; color: rgba(255,255,255,.5); text-transform: uppercase; letter-spacing: .1em; }
.sidebar-nav { flex: 1; padding: 12px 0; }
.nav-secao { padding: 8px 24px 4px; font-size: .62rem; font-weight: 700; letter-spacing: .15em; text-transform: uppercase; color: rgba(255,255,255,.3); }
.nav-item {
    display: flex; align-items: center; gap: 12px;
    padding: 11px 24px; text-decoration: none;
    color: rgba(255,255,255,.65); font-size: .88rem; font-weight: 500;
    transition: var(--tr); border-left: 3px solid transparent;
}
.nav-item:hover, .nav-item.ativo { color: white; background: rgba(255,255,255,.08); border-left-color: var(--laranja); }
.nav-item.ativo { background: rgba(255,77,18,.15); }
.nav-item i { width: 18px; text-align: center; font-size: .9rem; }
.nav-badge { margin-left: auto; background: var(--laranja); color: white; font-size: .65rem; font-weight: 700; padding: 2px 7px; border-radius: 999px; }
.sidebar-rodape { padding: 16px; border-top: 1px solid rgba(255,255,255,.08); }
.btn-sair {
    display: flex; align-items: center; gap: 10px; width: 100%; padding: 10px 14px;
    background: rgba(239,68,68,.15); border: 1px solid rgba(239,68,68,.25);
    border-radius: var(--rm); color: #fca5a5; font-size: .85rem; font-weight: 600;
    cursor: pointer; text-decoration: none; transition: var(--tr);
}
.btn-sair:hover { background: rgba(239,68,68,.3); color: white; }

.conteudo { margin-left: var(--sidebar); flex: 1; display: flex; flex-direction: column; min-width: 0; }
.topbar {
    height: 64px; background: var(--bloco);
    border-bottom: 1px solid var(--borda);
    display: flex; align-items: center; padding: 0 32px; gap: 16px;
    position: sticky; top: 0; z-index: 100;
}
.topbar-titulo { flex: 1; font-family: 'Playfair Display', serif; font-size: 1.2rem; font-weight: 700; color: var(--azul); }
.topbar-turma {
    background: rgba(255,77,18,.1); border: 1px solid rgba(255,77,18,.2);
    border-radius: var(--rm); padding: 5px 14px;
    font-size: .78rem; font-weight: 700; color: var(--laranja);
}
.topbar-acoes { display: flex; align-items: center; gap: 10px; }
.btn-icone {
    width: 36px; height: 36px; background: none; border: 1px solid var(--borda);
    border-radius: var(--rm); display: flex; align-items: center; justify-content: center;
    cursor: pointer; color: var(--texto-2); font-size: .9rem; transition: var(--tr);
}
.btn-icone:hover { border-color: var(--laranja); color: var(--laranja); }

.pagina { padding: 32px; }

.boas-vindas {
    background: linear-gradient(135deg, #0f2d3d 0%, #1e5671 50%, #2c7da3 100%);
    border-radius: var(--r); padding: 30px 36px;
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 28px; position: relative; overflow: hidden; color: white;
}
.boas-vindas::after {
    content: '';
    position: absolute; right: -30px; bottom: -50px;
    width: 180px; height: 180px;
    background: rgba(255,77,18,.12); border-radius: 50%;
}
.bv-esq { display: flex; align-items: center; gap: 20px; position: relative; z-index: 1; }
.bv-turma-badge {
    background: rgba(255,77,18,.2); border: 1px solid rgba(255,77,18,.4);
    border-radius: 12px; padding: 12px 16px; text-align: center; min-width: 80px;
}
.bv-turma-sigla { font-family: 'Playfair Display', serif; font-size: 1.4rem; font-weight: 800; }
.bv-turma-periodo { font-size: .65rem; text-transform: uppercase; letter-spacing: .1em; opacity: .7; }
.bv-texto h2 { font-family: 'Playfair Display', serif; font-size: 1.5rem; font-weight: 800; margin-bottom: 4px; }
.bv-texto p { color: rgba(255,255,255,.65); font-size: .88rem; }
.bv-acoes { position: relative; z-index: 1; }
.btn-acesso-rapido {
    display: flex; align-items: center; gap: 8px;
    background: var(--laranja); color: white; border: none;
    padding: 10px 20px; border-radius: var(--rm);
    font-size: .85rem; font-weight: 700; cursor: pointer;
    text-decoration: none; transition: var(--tr);
    box-shadow: 0 4px 16px rgba(255,77,18,.35);
}
.btn-acesso-rapido:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(255,77,18,.45); }

.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px; margin-bottom: 28px;
}
.stat-card {
    background: var(--bloco); border: 1px solid var(--borda);
    border-radius: var(--r); padding: 22px 20px;
    box-shadow: var(--sombra); transition: var(--tr); position: relative; overflow: hidden;
}
.stat-card:hover { transform: translateY(-4px); box-shadow: var(--sombra-h); }
.stat-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px; }
.stat-card.azul::before  { background: var(--azul-2); }
.stat-card.laranja::before { background: var(--laranja); }
.stat-card.verde::before   { background: var(--verde); }
.stat-card.amarelo::before { background: var(--amarelo); }
.stat-icone { width: 42px; height: 42px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1rem; margin-bottom: 14px; }
.azul .stat-icone   { background: rgba(44,125,163,.12); color: var(--azul-2); }
.laranja .stat-icone { background: rgba(255,77,18,.1); color: var(--laranja); }
.verde .stat-icone   { background: rgba(34,197,94,.1); color: var(--verde); }
.amarelo .stat-icone { background: rgba(245,158,11,.1); color: var(--amarelo); }
.stat-valor { font-family: 'Playfair Display', serif; font-size: 2rem; font-weight: 800; color: var(--texto); line-height: 1; }
.stat-label { font-size: .78rem; color: var(--texto-2); text-transform: uppercase; letter-spacing: .07em; margin-top: 4px; }

.grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 24px; }
.grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 24px; margin-bottom: 24px; }

.card { background: var(--bloco); border: 1px solid var(--borda); border-radius: var(--r); box-shadow: var(--sombra); overflow: hidden; }
.card-header { padding: 20px 24px 0; display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; }
.card-titulo { font-family: 'Playfair Display', serif; font-size: 1.05rem; font-weight: 700; color: var(--azul); display: flex; align-items: center; gap: 8px; }
.card-titulo i { color: var(--laranja); font-size: .95rem; }
.card-link { font-size: .78rem; color: var(--azul-2); text-decoration: none; font-weight: 600; transition: var(--tr); }
.card-link:hover { color: var(--laranja); }

.aluno-item {
    display: flex; align-items: center; gap: 12px;
    padding: 12px 24px; border-bottom: 1px solid var(--borda); transition: var(--tr);
}
.aluno-item:last-child { border-bottom: none; }
.aluno-item:hover { background: rgba(30,86,113,.03); }
.aluno-avatar {
    width: 36px; height: 36px; border-radius: 50%;
    background: var(--fundo); border: 1px solid var(--borda);
    display: flex; align-items: center; justify-content: center;
    font-size: .85rem; color: var(--azul); flex-shrink: 0; overflow: hidden;
}
.aluno-avatar img { width: 100%; height: 100%; object-fit: cover; }
.aluno-nome { font-weight: 600; font-size: .85rem; }
.aluno-email { font-size: .72rem; color: var(--texto-2); }

.partida-item {
    display: flex; align-items: center; padding: 12px 24px; gap: 12px;
    border-bottom: 1px solid var(--borda); transition: var(--tr);
}
.partida-item:last-child { border-bottom: none; }
.partida-item:hover { background: rgba(30,86,113,.03); }
.partida-status-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
.partida-status-dot.agendada  { background: var(--amarelo); }
.partida-status-dot.realizada { background: var(--verde); }
.partida-status-dot.cancelada { background: var(--vermelho); }
.partida-status-dot.wo        { background: var(--texto-2); }
.partida-info { flex: 1; min-width: 0; }
.partida-times { font-weight: 700; font-size: .85rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.partida-detalhe { font-size: .72rem; color: var(--texto-2); margin-top: 2px; }
.partida-placar {
    background: var(--azul); color: white;
    padding: 4px 10px; border-radius: var(--rm);
    font-weight: 800; font-size: .82rem; white-space: nowrap;
}
.partida-placar.agendada { background: var(--fundo); color: var(--texto-2); border: 1px solid var(--borda); }

.inscricao-item {
    display: flex; align-items: center; padding: 11px 24px; gap: 12px;
    border-bottom: 1px solid var(--borda); transition: var(--tr);
}
.inscricao-item:last-child { border-bottom: none; }
.inscricao-item:hover { background: rgba(30,86,113,.03); }
.ins-info { flex: 1; min-width: 0; }
.ins-nome { font-weight: 600; font-size: .85rem; }
.ins-detalhe { font-size: .72rem; color: var(--texto-2); margin-top: 2px; }
.ins-camisa {
    background: var(--fundo); border: 1px solid var(--borda);
    border-radius: 8px; padding: 4px 10px;
    font-size: .75rem; font-weight: 700; color: var(--azul);
}

.classif-item {
    display: flex; align-items: center; padding: 14px 24px; gap: 12px;
    border-bottom: 1px solid var(--borda); transition: var(--tr);
}
.classif-item:last-child { border-bottom: none; }
.classif-info { flex: 1; }
.classif-modalidade { font-weight: 700; font-size: .88rem; }
.classif-edicao { font-size: .72rem; color: var(--texto-2); }
.classif-stats { display: flex; gap: 14px; }
.cstat { text-align: center; }
.cstat-val { font-family: 'Playfair Display', serif; font-size: 1.1rem; font-weight: 800; color: var(--azul); }
.cstat-label { font-size: .62rem; text-transform: uppercase; color: var(--texto-2); letter-spacing: .06em; }

.modal-inscricao-item {
    padding: 14px 24px; border-bottom: 1px solid var(--borda); transition: var(--tr);
}
.modal-inscricao-item:last-child { border-bottom: none; }
.modal-inscricao-item:hover { background: rgba(30,86,113,.03); }
.mi-topo { display: flex; align-items: center; justify-content: space-between; margin-bottom: 4px; }
.mi-nome { font-weight: 700; font-size: .88rem; }
.mi-prazo { font-size: .72rem; color: var(--texto-2); margin-top: 2px; }
.mi-prazo.urgente { color: var(--vermelho); font-weight: 600; }

.badge-status { font-size: .68rem; font-weight: 700; padding: 3px 10px; border-radius: 999px; white-space: nowrap; }
.badge-status.ativa      { background: rgba(34,197,94,.12); color: #15803d; }
.badge-status.cancelada  { background: rgba(239,68,68,.12); color: #b91c1c; }
.badge-status.agendada   { background: rgba(245,158,11,.12); color: #b45309; }
.badge-status.realizada  { background: rgba(34,197,94,.12); color: #15803d; }
.badge-status.wo         { background: rgba(100,116,139,.1); color: #475569; }
.badge-status.inscricoes  { background: rgba(139,92,246,.12); color: #7c3aed; }
.badge-status.em_andamento { background: rgba(34,197,94,.12); color: #15803d; }

.empty-state { padding: 36px 24px; text-align: center; color: var(--texto-2); font-size: .88rem; }
.empty-state i { font-size: 2rem; opacity: .3; margin-bottom: 10px; display: block; }

.tabela-alunos-lista { max-height: 320px; overflow-y: auto; }
.tabela-alunos-lista::-webkit-scrollbar { width: 4px; }
.tabela-alunos-lista::-webkit-scrollbar-track { background: var(--fundo); }
.tabela-alunos-lista::-webkit-scrollbar-thumb { background: var(--borda); border-radius: 4px; }

.busca-aluno {
    margin: 0 24px 12px;
    display: flex; align-items: center; gap: 8px;
    background: var(--fundo); border: 1px solid var(--borda);
    border-radius: var(--rm); padding: 9px 14px;
}
.busca-aluno i { color: var(--texto-2); font-size: .85rem; }
.busca-aluno input {
    flex: 1; background: none; border: none; outline: none;
    font-family: 'DM Sans', sans-serif; font-size: .85rem;
    color: var(--texto);
}

@media (max-width: 1200px) {
    .stats-grid { grid-template-columns: repeat(2, 1fr); }
    .grid-3 { grid-template-columns: 1fr 1fr; }
}
@media (max-width: 900px) {
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
    .bv-esq { flex-direction: column; align-items: flex-start; }
    .topbar-turma { display: none; }
}
</style>
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

    <div class="sidebar-perfil">
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
    </div>

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
                    <p>Gerencie sua sala, inscricões e acompanhe o desempenho nos interclasses.</p>
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
                            <?= $temPlacar ? $p['placar_time_a'].' x '.$p['placar_time_b'] : ($statusPartidaLabel[$p['status_partida']] ?? $p['status_partida']) ?>
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
                            $fim  = new DateTime($md['data_fim_inscricao']);
                            $hoje = new DateTime();
                            $diff = (int) $hoje->diff($fim)->days;
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
                                    &middot; <span style="color:var(--laranja);font-weight:700"><i class="fa-solid fa-star" style="font-size:.65rem"></i> Capitão</span>
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

<script>
    const sidebar     = document.getElementById('sidebar');
    const html        = document.documentElement;
    const btnTema     = document.getElementById('toggleTema');
    const iconeTema   = document.getElementById('iconeTema');
    const temaSalvo   = localStorage.getItem('theme') || 'light';

    html.setAttribute('data-theme', temaSalvo);
    iconeTema.className = temaSalvo === 'dark' ? 'fa-solid fa-sun' : 'fa-solid fa-moon';

    document.getElementById('toggleSidebar').addEventListener('click', () => {
        sidebar.classList.toggle('aberta');
    });

    btnTema.addEventListener('click', () => {
        const atual = html.getAttribute('data-theme');
        const novo  = atual === 'dark' ? 'light' : 'dark';
        html.setAttribute('data-theme', novo);
        localStorage.setItem('theme', novo);
        iconeTema.className = novo === 'dark' ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
    });

    const buscaInput = document.getElementById('buscaAluno');
    const listaItens = document.querySelectorAll('#listaAlunos .aluno-item');
    if (buscaInput) {
        buscaInput.addEventListener('input', function () {
            const q = this.value.toLowerCase().trim();
            listaItens.forEach(item => {
                const nome = item.getAttribute('data-nome') || '';
                item.style.display = nome.includes(q) ? '' : 'none';
            });
        });
    }
</script>
</body>
</html>