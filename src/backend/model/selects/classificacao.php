<?php
// ═══════════════════════════════════════════════════════════
//  classificacao.php — SOEE · Página de Campeonato
//  Selects com suporte a modalidades individuais (solo/dupla/trio)
//  e por time (turma).
// ═══════════════════════════════════════════════════════════
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/soee/src/backend/includes/conexao.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/soee/src/backend/controllers/home.php';

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

if (!$modalidadeId && !empty($esportes)) {
    $modalidadeId = (int) $esportes[0]['id_modalidade'];
}

// ── DADOS DA MODALIDADE SELECIONADA ──────────────────────
$esporte      = null;
$emId         = null;
$formato      = null;
$participacao = 'time';

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
            m.foto_modalidade,
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

$ehIndividual = in_array($participacao, ['solo', 'dupla', 'trio']);

// ── CLASSIFICAÇÃO POR GRUPOS ──────────────────────────────
// Para individuais: exibe nome do aluno e turma de origem.
// Para times:       exibe nome da turma normalmente.
$grupos    = [];
$temGrupos = $formato && in_array($formato, ['grupos', 'grupos_mata_mata', 'todos_contra_todos']);

if ($emId && $temGrupos) {
    if ($ehIndividual) {
        // Classificação individual: join com usuario para pegar o nome do aluno
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
                cl.usuario_id_participante,
                u.nome_usuario  AS nome_participante,
                t.nome_turma,
                t.id_turma
            FROM classificacao cl
            INNER JOIN usuario u ON u.id_usuario = cl.usuario_id_participante
            INNER JOIN turma   t ON t.id_turma   = u.turma_id_turma
            WHERE cl.edicao_modalidade_id = :emid
              AND cl.usuario_id_participante IS NOT NULL
            ORDER BY
                cl.grupo_classificacao ASC,
                cl.pontos DESC,
                cl.saldo DESC,
                cl.vitorias DESC,
                cl.pontos_pro DESC
        ");
    } else {
        // Classificação por time: join com turma
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
                NULL AS usuario_id_participante,
                t.nome_turma  AS nome_participante,
                t.nome_turma,
                t.id_turma
            FROM classificacao cl
            INNER JOIN turma t ON t.id_turma = cl.turma_id_turma
            WHERE cl.edicao_modalidade_id = :emid
              AND cl.usuario_id_participante IS NULL
            ORDER BY
                cl.grupo_classificacao ASC,
                cl.pontos DESC,
                cl.saldo DESC,
                cl.vitorias DESC,
                cl.pontos_pro DESC
        ");
    }

    $stmtCl->execute([':emid' => $emId]);
    foreach ($stmtCl->fetchAll(PDO::FETCH_ASSOC) as $row) {
        // Normaliza o campo exibido: para individual usa nome do aluno,
        // para time usa nome da turma — ambos ficam em 'nome_turma' para
        // manter compatibilidade com a view existente.
        if ($ehIndividual) {
            $row['nome_exibicao'] = $row['nome_participante'];
            $row['subtitulo']     = $row['nome_turma']; // turma de origem do aluno
        } else {
            $row['nome_exibicao'] = $row['nome_turma'];
            $row['subtitulo']     = null;
        }
        $g = $row['grupo_classificacao'] ?: 'A';
        $grupos[$g][] = $row;
    }
}

// ── PARTIDAS POR FASE ─────────────────────────────────────
// Para individuais: exibe nome do aluno (e turma de origem entre parênteses).
// Para times:       exibe nome da turma normalmente.
$partidas_fase = [];
$todasPartidas = [];

if ($emId) {
    if ($ehIndividual) {
        // Partidas individuais: join com usuario para time_a e time_b
        $stmtP = $conn->prepare("
            SELECT
                p.id_partida,
                p.data_partida,
                p.hora_partida,
                p.local_partida,
                p.fase_partida,
                p.status_partida,
                p.grupo_partida,
                p.usuario_id_time_a,
                p.usuario_id_time_b,
                p.turma_id_time_a,
                p.turma_id_time_b,
                ua.nome_usuario                         AS time_a,
                ta.nome_turma                           AS turma_time_a,
                ub.nome_usuario                         AS time_b,
                tb.nome_turma                           AS turma_time_b,
                r.placar_time_a,
                r.placar_time_b,
                uv.nome_usuario                         AS vencedor
            FROM partida p
            INNER JOIN usuario ua ON ua.id_usuario = p.usuario_id_time_a
            INNER JOIN turma   ta ON ta.id_turma   = p.turma_id_time_a
            INNER JOIN usuario ub ON ub.id_usuario = p.usuario_id_time_b
            INNER JOIN turma   tb ON tb.id_turma   = p.turma_id_time_b
            LEFT  JOIN resultado r  ON r.partida_id_partida = p.id_partida
            LEFT  JOIN usuario  uv  ON uv.id_usuario = r.usuario_id_vencedor
            WHERE p.edicao_modalidade_id = :emid
              AND p.usuario_id_time_a IS NOT NULL
            ORDER BY
            CASE p.fase_partida
                WHEN 'grupos'        THEN 1
                WHEN 'oitavas'       THEN 2
                WHEN 'quartas'       THEN 3
                WHEN 'semi'          THEN 4
                WHEN 'terceiro_lugar' THEN 5
                WHEN 'final'         THEN 6
                ELSE 99
            END,
            p.data_partida ASC,
            p.hora_partida ASC
        ");
    } else {
        // Partidas por time: join com turma (comportamento original)
        $stmtP = $conn->prepare("
            SELECT
                p.id_partida,
                p.data_partida,
                p.hora_partida,
                p.local_partida,
                p.fase_partida,
                p.status_partida,
                p.grupo_partida,
                NULL AS usuario_id_time_a,
                NULL AS usuario_id_time_b,
                p.turma_id_time_a,
                p.turma_id_time_b,
                ta.nome_turma AS time_a,
                ta.nome_turma AS turma_time_a,
                tb.nome_turma AS time_b,
                tb.nome_turma AS turma_time_b,
                r.placar_time_a,
                r.placar_time_b,
                tv.nome_turma AS vencedor
            FROM partida p
            INNER JOIN turma ta ON ta.id_turma = p.turma_id_time_a
            INNER JOIN turma tb ON tb.id_turma = p.turma_id_time_b
            LEFT  JOIN resultado r  ON r.partida_id_partida = p.id_partida
            LEFT  JOIN turma     tv ON tv.id_turma = r.turma_id_vencedor
            WHERE p.edicao_modalidade_id = :emid
              AND p.usuario_id_time_a IS NULL
            ORDER BY
            CASE p.fase_partida
                WHEN 'grupos'        THEN 1
                WHEN 'oitavas'       THEN 2
                WHEN 'quartas'       THEN 3
                WHEN 'semi'          THEN 4
                WHEN 'terceiro_lugar' THEN 5
                WHEN 'final'         THEN 6
                ELSE 99
            END,
            p.data_partida ASC,
            p.hora_partida ASC
        ");
    }

    $stmtP->execute([':emid' => $emId]);
    $todasPartidas = $stmtP->fetchAll(PDO::FETCH_ASSOC);
    foreach ($todasPartidas as $p) {
        $partidas_fase[$p['fase_partida']][] = $p;
    }
}

// ── SORTEIO JÁ GERADO? ────────────────────────────────────
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