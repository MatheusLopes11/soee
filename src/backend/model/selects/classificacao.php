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
    INNER JOIN edicao e             ON e.id_edicao = em.edicao_id_edicao
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
        INNER JOIN edicao e             ON e.id_edicao = em.edicao_id_edicao
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

// ── CLASSIFICAÇÃO POR GRUPOS (CÁLCULO DINÂMICO VIA INNER JOIN) ──
$grupos    = [];
$temGrupos = $formato && in_array($formato, ['grupos', 'grupos_mata_mata', 'todos_contra_todos']);

if ($emId && $temGrupos) {
    if ($ehIndividual) {
        // Classificação Individual (Usa a presença do resultado em vez do ENUM status)
        $stmtCl = $conn->prepare("
            SELECT
                u.id_usuario AS usuario_id_participante,
                u.nome_usuario AS nome_participante,
                t.nome_turma,
                t.id_turma,
                p.grupo_partida AS grupo_classificacao,
                COUNT(r.id_resultado) AS jogos,
                SUM(CASE WHEN r.usuario_id_vencedor = u.id_usuario THEN 1 ELSE 0 END) AS vitorias,
                SUM(CASE WHEN r.usuario_id_vencedor IS NULL AND r.id_resultado IS NOT NULL THEN 1 ELSE 0 END) AS empates,
                SUM(CASE WHEN r.usuario_id_vencedor != u.id_usuario AND r.usuario_id_vencedor IS NOT NULL THEN 1 ELSE 0 END) AS derrotas,
                SUM(CASE WHEN p.usuario_id_time_a = u.id_usuario THEN r.placar_time_a ELSE r.placar_time_b END) AS pontos_pro,
                SUM(CASE WHEN p.usuario_id_time_a = u.id_usuario THEN r.placar_time_b ELSE r.placar_time_a END) AS pontos_contra,
                (SUM(CASE WHEN p.usuario_id_time_a = u.id_usuario THEN r.placar_time_a ELSE r.placar_time_b END) - 
                 SUM(CASE WHEN p.usuario_id_time_a = u.id_usuario THEN r.placar_time_b ELSE r.placar_time_a END)) AS saldo,
                SUM(CASE 
                    WHEN r.usuario_id_vencedor = u.id_usuario THEN 3 
                    WHEN r.usuario_id_vencedor IS NULL AND r.id_resultado IS NOT NULL THEN 1 
                    ELSE 0 
                END) AS pontos
            FROM partida p
            INNER JOIN resultado r ON r.partida_id_partida = p.id_partida
            INNER JOIN usuario u  ON (u.id_usuario = p.usuario_id_time_a OR u.id_usuario = p.usuario_id_time_b)
            INNER JOIN turma t    ON t.id_turma = u.turma_id_turma
            WHERE p.edicao_modalidade_id = :emid
            GROUP BY u.id_usuario, u.nome_usuario, t.nome_turma, t.id_turma, p.grupo_partida
            ORDER BY p.grupo_partida ASC, pontos DESC, saldo DESC, vitorias DESC, pontos_pro DESC
        ");
    } else {
        // Classificação por Time/Turma (Usa a presença do resultado em vez do ENUM status)
        $stmtCl = $conn->prepare("
            SELECT
                t.id_turma,
                t.nome_turma,
                t.nome_turma AS nome_participante,
                NULL AS usuario_id_participante,
                p.grupo_partida AS grupo_classificacao,
                COUNT(r.id_resultado) AS jogos,
                SUM(CASE WHEN r.turma_id_vencedor = t.id_turma THEN 1 ELSE 0 END) AS vitorias,
                SUM(CASE WHEN r.turma_id_vencedor IS NULL AND r.id_resultado IS NOT NULL THEN 1 ELSE 0 END) AS empates,
                SUM(CASE WHEN r.turma_id_vencedor != t.id_turma AND r.turma_id_vencedor IS NOT NULL THEN 1 ELSE 0 END) AS derrotas,
                SUM(CASE WHEN p.turma_id_time_a = t.id_turma THEN r.placar_time_a ELSE r.placar_time_b END) AS pontos_pro,
                SUM(CASE WHEN p.turma_id_time_a = t.id_turma THEN r.placar_time_b ELSE r.placar_time_a END) AS pontos_contra,
                (SUM(CASE WHEN p.turma_id_time_a = t.id_turma THEN r.placar_time_a ELSE r.placar_time_b END) - 
                 SUM(CASE WHEN p.turma_id_time_a = t.id_turma THEN r.placar_time_b ELSE r.placar_time_a END)) AS saldo,
                SUM(CASE 
                    WHEN r.turma_id_vencedor = t.id_turma THEN 3 
                    WHEN r.turma_id_vencedor IS NULL AND r.id_resultado IS NOT NULL THEN 1 
                    ELSE 0 
                END) AS pontos
            FROM partida p
            INNER JOIN resultado r ON r.partida_id_partida = p.id_partida
            INNER JOIN turma t    ON (t.id_turma = p.turma_id_time_a OR t.id_turma = p.turma_id_time_b)
            WHERE p.edicao_modalidade_id = :emid
            GROUP BY t.id_turma, t.nome_turma, p.grupo_partida
            ORDER BY p.grupo_partida ASC, pontos DESC, saldo DESC, vitorias DESC, pontos_pro DESC
        ");
    }

    $stmtCl->execute([':emid' => $emId]);
    foreach ($stmtCl->fetchAll(PDO::FETCH_ASSOC) as $row) {
        if ($ehIndividual) {
            $row['nome_exibicao'] = $row['nome_participante'];
            $row['subtitulo']     = $row['nome_turma']; 
        } else {
            $row['nome_exibicao'] = $row['nome_turma'];
            $row['subtitulo']     = null;
        }
        $g = $row['grupo_classificacao'] ?: 'A';
        $grupos[$g][] = $row;
    }
}

// ── PARTIDAS POR FASE ─────────────────────────────────────
$partidas_fase = [];
$todasPartidas = [];

if ($emId) {
    if ($ehIndividual) {
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
                ua.nome_usuario AS time_a,
                ta.nome_turma   AS turma_time_a,
                ub.nome_usuario AS time_b,
                tb.nome_turma   AS turma_time_b,
                r.placar_time_a,
                r.placar_time_b,
                uv.nome_usuario AS vencedor
            FROM partida p
            INNER JOIN usuario ua ON ua.id_usuario = p.usuario_id_time_a
            INNER JOIN turma ta   ON ta.id_turma   = p.turma_id_time_a
            INNER JOIN usuario ub ON ub.id_usuario = p.usuario_id_time_b
            INNER JOIN turma tb   ON tb.id_turma   = p.turma_id_time_b
            LEFT JOIN resultado r ON r.partida_id_partida = p.id_partida
            LEFT JOIN usuario uv  ON uv.id_usuario = r.usuario_id_vencedor
            WHERE p.edicao_modalidade_id = :emid
              AND p.usuario_id_time_a IS NOT NULL
            ORDER BY
            CASE p.fase_partida
                WHEN 'grupos'         THEN 1
                WHEN 'oitavas'        THEN 2
                WHEN 'quartas'        THEN 3
                WHEN 'semi'           THEN 4
                WHEN 'terceiro_lugar' THEN 5
                WHEN 'final'          THEN 6
                ELSE 99
            END,
            p.data_partida ASC,
            p.hora_partida ASC
        ");
    } else {
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
            LEFT JOIN resultado r ON r.partida_id_partida = p.id_partida
            LEFT JOIN turma tv   ON tv.id_turma = r.turma_id_vencedor
            WHERE p.edicao_modalidade_id = :emid
              AND p.usuario_id_time_a IS NULL
            ORDER BY
            CASE p.fase_partida
                WHEN 'grupos'         THEN 1
                WHEN 'oitavas'        THEN 2
                WHEN 'quartas'        THEN 3
                WHEN 'semi'           THEN 4
                WHEN 'terceiro_lugar' THEN 5
                WHEN 'final'          THEN 6
                ELSE 99
            END,
            p.data_partida ASC,
            p.hora_partida ASC
        ");
    }

    $stmtP->execute([':emid' => $emId]);
    $todasPartidas = $stmtP->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($todasPartidas as $p) {
        // Se não houver placar registrado, exibe o traço padrão
        if ($p['placar_time_a'] === null) {
            $p['placar_time_a'] = '-';
            $p['placar_time_b'] = '-';
        }
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