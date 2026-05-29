<?php
// ═══════════════════════════════════════════════════════════
//  classificacao.php — SOEE · Página de Campeonato
//  URL: classificacao.php?id={id_modalidade}
// ═══════════════════════════════════════════════════════════
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/soee/src/backend/includes/conexao.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/soee/src/backend/controllers/home.php';

// ── ID da modalidade via GET ──────────────────────────────
$modalidadeId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// ── MODALIDADES EM ANDAMENTO (sidebar) ───────────────────
$stmtEsportes = $conn->query("
    SELECT DISTINCT
        m.id_modalidade,
        m.nome_modalidade,
        m.tipo_modalidade,
        m.formato_modalidade,
        em.status_edicao_modalidade
    FROM modalidade m
    INNER JOIN edicao_modalidade em ON em.modalidade_id_modalidade = m.id_modalidade
    INNER JOIN edicao e            ON e.id_edicao = em.edicao_id_edicao
    WHERE m.ativo_modalidade = true
      AND e.status_edicao = 'em_andamento'
    ORDER BY m.nome_modalidade ASC
");
$esportes = $stmtEsportes->fetchAll(PDO::FETCH_ASSOC);

// Se não veio ID, usa o primeiro disponível
if (!$modalidadeId && !empty($esportes)) {
    $modalidadeId = (int) $esportes[0]['id_modalidade'];
}

// ── DADOS DA MODALIDADE SELECIONADA ──────────────────────
$esporte = null;
$emId    = null;
$formato = null;

if ($modalidadeId) {
    $stmtEsporte = $conn->prepare("
        SELECT
            m.id_modalidade,
            m.nome_modalidade,
            m.descricao_modalidade,
            m.tipo_modalidade,
            m.formato_modalidade,
            m.tipo_participacao,
            m.qtd_min_jogadores,
            m.qtd_max_jogadores,
            em.id_edicao_modalidade,
            em.status_edicao_modalidade,
            em.data_inicio_inscricao,
            em.data_fim_inscricao,
            e.nome_edicao,
            e.ano_edicao
        FROM modalidade m
        INNER JOIN edicao_modalidade em ON em.modalidade_id_modalidade = m.id_modalidade
        INNER JOIN edicao e            ON e.id_edicao = em.edicao_id_edicao
        WHERE m.id_modalidade = :id
          AND e.status_edicao = 'em_andamento'
        ORDER BY em.id_edicao_modalidade DESC
        LIMIT 1
    ");
    $stmtEsporte->execute([':id' => $modalidadeId]);
    $esporte = $stmtEsporte->fetch(PDO::FETCH_ASSOC);

    if ($esporte) {
        $emId         = (int) $esporte['id_edicao_modalidade'];
        $formato      = $esporte['formato_modalidade'];
        $participacao = $esporte['tipo_participacao'];
    }
}

// ── CLASSIFICAÇÃO POR GRUPOS ─────────────────────────────
$grupos   = [];
$temGrupos = $formato && in_array($formato, ['grupos', 'grupos_mata_mata', 'todos_contra_todos']);

if ($emId && $temGrupos) {
    $stmtCl = $conn->prepare("
        SELECT
            cl.pontos,
            cl.vitorias,
            cl.derrotas,
            cl.empates,
            cl.jogos,
            cl.saldo,
            cl.pontos_pro,
            cl.pontos_contra,
            cl.grupo_classificacao,
            t.nome_turma,
            t.id_turma
        FROM classificacao cl
        INNER JOIN turma t ON t.id_turma = cl.turma_id_turma
        WHERE cl.edicao_modalidade_id = :emid
        ORDER BY
            cl.grupo_classificacao ASC,
            cl.pontos DESC,
            cl.saldo DESC,
            cl.vitorias DESC,
            cl.pontos_pro DESC
    ");
    $stmtCl->execute([':emid' => $emId]);
    foreach ($stmtCl->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $g = $row['grupo_classificacao'] ?: 'A';
        $grupos[$g][] = $row;
    }
}

// ── PARTIDAS POR FASE ────────────────────────────────────
$partidas_fase = [];
$todasPartidas = [];

if ($emId) {
    $stmtP = $conn->prepare("
        SELECT
            p.id_partida,
            p.data_partida,
            p.hora_partida,
            p.local_partida,
            p.fase_partida,
            p.status_partida,
            p.grupo_partida,
            p.turma_id_time_a,
            p.turma_id_time_b,
            ta.nome_turma  AS time_a,
            tb.nome_turma  AS time_b,
            r.placar_time_a,
            r.placar_time_b,
            tv.nome_turma  AS vencedor
        FROM partida p
        INNER JOIN turma ta ON ta.id_turma = p.turma_id_time_a
        INNER JOIN turma tb ON tb.id_turma = p.turma_id_time_b
        LEFT  JOIN resultado r  ON r.partida_id_partida = p.id_partida
        LEFT  JOIN turma tv     ON tv.id_turma = r.turma_id_vencedor
        WHERE p.edicao_modalidade_id = :emid
        ORDER BY
        CASE p.fase_partida
            WHEN 'grupos' THEN 1
            WHEN 'oitavas' THEN 2
            WHEN 'quartas' THEN 3
            WHEN 'semi' THEN 4
            WHEN 'terceiro_lugar' THEN 5
            WHEN 'final' THEN 6
            ELSE 99
        END,
        p.data_partida ASC,
        p.hora_partida ASC
    ");
    $stmtP->execute([':emid' => $emId]);
    $todasPartidas = $stmtP->fetchAll(PDO::FETCH_ASSOC);
    foreach ($todasPartidas as $p) {
        $partidas_fase[$p['fase_partida']][] = $p;
    }
}

// ── SORTEIO JÁ GERADO? ───────────────────────────────────
$sorteioGerado = false;
if ($emId) {
    $stmtSg = $conn->prepare("
        SELECT id FROM sorteio_gerado
        WHERE edicao_modalidade_id = :emid
        LIMIT 1
    ");
    $stmtSg->execute([':emid' => $emId]);
    $sorteioGerado = (bool) $stmtSg->fetchColumn();
}
?>