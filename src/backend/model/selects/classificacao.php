<?php
// ═══════════════════════════════════════════════════════════
//  model/selects/classificacao.php — SOEE
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

// ══════════════════════════════════════════════════════════
//  BUG 3 FIX — Recálculo automático da classificação
//  Lê todos os resultados de grupos já salvos e atualiza
//  a tabela classificacao caso ela esteja zerada/desatualizada.
// ══════════════════════════════════════════════════════════
if ($emId && in_array($formato, ['grupos', 'grupos_mata_mata', 'todos_contra_todos'])) {

    // Verifica se a classificacao está zerada mas existem resultados
    $stmtZero = $conn->prepare("
        SELECT SUM(jogos) AS total_jogos FROM classificacao
        WHERE edicao_modalidade_id = :emid
    ");
    $stmtZero->execute([':emid' => $emId]);
    $totalJogosNaClassificacao = (int) $stmtZero->fetchColumn();

    $stmtResultados = $conn->prepare("
        SELECT COUNT(*) FROM resultado r
        INNER JOIN partida p ON p.id_partida = r.partida_id_partida
        WHERE p.edicao_modalidade_id = :emid AND p.fase_partida = 'grupos'
    ");
    $stmtResultados->execute([':emid' => $emId]);
    $totalResultadosSalvos = (int) $stmtResultados->fetchColumn();

    // Só recalcula se há resultados salvos mas a classificacao está zerada
    if ($totalResultadosSalvos > 0 && $totalJogosNaClassificacao === 0) {

        // Busca todos os resultados de grupos
        $stmtR = $conn->prepare("
            SELECT
                p.turma_id_time_a,
                p.turma_id_time_b,
                r.placar_time_a,
                r.placar_time_b,
                r.turma_id_vencedor
            FROM partida p
            INNER JOIN resultado r ON r.partida_id_partida = p.id_partida
            WHERE p.edicao_modalidade_id = :emid
              AND p.fase_partida         = 'grupos'
        ");
        $stmtR->execute([':emid' => $emId]);
        $resultadosHistoricos = $stmtR->fetchAll(PDO::FETCH_ASSOC);

        // Acumula estatísticas por turma
        $statsRecalc = [];

        foreach ($resultadosHistoricos as $r) {
            $tA = (int) $r['turma_id_time_a'];
            $tB = (int) $r['turma_id_time_b'];
            $pA = (int) $r['placar_time_a'];
            $pB = (int) $r['placar_time_b'];
            $tv = $r['turma_id_vencedor'] !== null ? (int) $r['turma_id_vencedor'] : null;

            foreach ([$tA, $tB] as $tid) {
                if (!isset($statsRecalc[$tid])) {
                    $statsRecalc[$tid] = [
                        'pontos' => 0, 'vitorias' => 0, 'derrotas' => 0,
                        'empates' => 0, 'jogos' => 0,
                        'pontos_pro' => 0, 'pontos_contra' => 0,
                    ];
                }
            }

            $statsRecalc[$tA]['jogos']++;
            $statsRecalc[$tB]['jogos']++;

            if ($tv === null) {
                // Empate
                $statsRecalc[$tA]['empates']++;
                $statsRecalc[$tA]['pontos']++;
                $statsRecalc[$tA]['pontos_pro']    += $pA;
                $statsRecalc[$tA]['pontos_contra'] += $pB;

                $statsRecalc[$tB]['empates']++;
                $statsRecalc[$tB]['pontos']++;
                $statsRecalc[$tB]['pontos_pro']    += $pB;
                $statsRecalc[$tB]['pontos_contra'] += $pA;
            } else {
                // Vitória/derrota
                $perdedor = ($tv === $tA) ? $tB : $tA;
                $pVenc    = ($tv === $tA) ? $pA : $pB;
                $pPerd    = ($tv === $tA) ? $pB : $pA;

                $statsRecalc[$tv]['vitorias']++;
                $statsRecalc[$tv]['pontos']        += 3;
                $statsRecalc[$tv]['pontos_pro']    += $pVenc;
                $statsRecalc[$tv]['pontos_contra'] += $pPerd;

                $statsRecalc[$perdedor]['derrotas']++;
                $statsRecalc[$perdedor]['pontos_pro']    += $pPerd;
                $statsRecalc[$perdedor]['pontos_contra'] += $pVenc;
            }
        }

        // Atualiza a classificacao no banco
        $stmtUp = $conn->prepare("
            UPDATE classificacao
            SET pontos        = :pts,
                vitorias      = :v,
                derrotas      = :d,
                empates       = :e,
                jogos         = :j,
                pontos_pro    = :pro,
                pontos_contra = :contra,
                saldo         = :saldo
            WHERE edicao_modalidade_id = :emid
              AND turma_id_turma       = :tid
        ");

        foreach ($statsRecalc as $tid => $s) {
            $stmtUp->execute([
                ':pts'    => $s['pontos'],
                ':v'      => $s['vitorias'],
                ':d'      => $s['derrotas'],
                ':e'      => $s['empates'],
                ':j'      => $s['jogos'],
                ':pro'    => $s['pontos_pro'],
                ':contra' => $s['pontos_contra'],
                ':saldo'  => $s['pontos_pro'] - $s['pontos_contra'],
                ':emid'   => $emId,
                ':tid'    => $tid,
            ]);
        }
    }
}
// ══════════════════════════════════════════════════════════
//  FIM DO RECÁLCULO
// ══════════════════════════════════════════════════════════

// ══════════════════════════════════════════════════════════
//  BUG 1 + 2 FIX — Geração automática do mata-mata e
//  reconstrução de progressão para dados históricos
//  (preenche sentinelas, gera 3º lugar/final e corrige o
//  label da fase inicial: oitavas/quartas/semi)
// ══════════════════════════════════════════════════════════
if ($emId && ($formato === 'grupos_mata_mata' || $formato === 'mata_mata')) {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/soee/src/backend/helpers/resultado-motor.php';

    if ($formato === 'grupos_mata_mata') {
        // Tenta gerar o mata-mata (idempotente: só age se grupos
        // terminaram e o mata-mata ainda não existe)
        rm_tentarGerarMataMata($conn, $emId);
    }

    // Replay: corrige labels de fase e preenche sentinelas/
    // próximas fases (final, 3º lugar) baseado em resultados já salvos
    rm_reconstruirProgressaoMataMata($conn, $emId);
}
// ══════════════════════════════════════════════════════════
//  FIM BUG 1 + 2 FIX
// ══════════════════════════════════════════════════════════

// ── CLASSIFICAÇÃO POR GRUPOS ──────────────────────────────
$grupos    = [];
$temGrupos = $formato && in_array($formato, ['grupos', 'grupos_mata_mata', 'todos_contra_todos']);

if ($emId && $temGrupos) {
    if ($ehIndividual) {
        $stmtCl = $conn->prepare("
            SELECT
                t.id_turma,
                t.nome_turma,
                u.nome_usuario,
                cl.grupo_classificacao,
                cl.jogos,
                cl.vitorias,
                cl.empates,
                cl.derrotas,
                cl.pontos_pro,
                cl.pontos_contra,
                cl.saldo,
                cl.pontos
            FROM classificacao cl
            INNER JOIN turma t ON t.id_turma = cl.turma_id_turma
            LEFT JOIN inscricao i ON i.edicao_modalidade_id = cl.edicao_modalidade_id
                AND i.status_inscricao = 'ativa'
            LEFT JOIN usuario u ON u.id_usuario = i.usuario_id_usuario
                AND u.turma_id_turma = cl.turma_id_turma
            WHERE cl.edicao_modalidade_id = :emid
            ORDER BY
                cl.grupo_classificacao ASC,
                cl.pontos DESC,
                cl.saldo DESC,
                cl.vitorias DESC,
                cl.pontos_pro DESC
        ");
    } else {
        $stmtCl = $conn->prepare("
            SELECT
                t.id_turma,
                t.nome_turma,
                NULL AS nome_usuario,
                cl.grupo_classificacao,
                cl.jogos,
                cl.vitorias,
                cl.empates,
                cl.derrotas,
                cl.pontos_pro,
                cl.pontos_contra,
                cl.saldo,
                cl.pontos
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
    }
    $stmtCl->execute([':emid' => $emId]);

    foreach ($stmtCl->fetchAll(PDO::FETCH_ASSOC) as $row) {
        if ($ehIndividual && !empty($row['nome_usuario'])) {
            $row['nome_exibicao'] = $row['nome_usuario'];
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
                p.turma_id_time_a,
                p.turma_id_time_b,
                ta.nome_turma AS turma_time_a,
                tb.nome_turma AS turma_time_b,
                COALESCE(ua.nome_usuario, ta.nome_turma) AS time_a,
                COALESCE(ub.nome_usuario, tb.nome_turma) AS time_b,
                r.placar_time_a,
                r.placar_time_b,
                COALESCE(uv.nome_usuario, tv.nome_turma) AS vencedor
            FROM partida p
            INNER JOIN turma ta ON ta.id_turma = p.turma_id_time_a
            INNER JOIN turma tb ON tb.id_turma = p.turma_id_time_b
            LEFT JOIN (
                SELECT DISTINCT ON (u2.turma_id_turma)
                    u2.id_usuario, u2.nome_usuario, u2.turma_id_turma
                FROM inscricao i2
                INNER JOIN usuario u2 ON u2.id_usuario = i2.usuario_id_usuario
                WHERE i2.edicao_modalidade_id = :emid1
                  AND i2.status_inscricao = 'ativa'
                ORDER BY u2.turma_id_turma, u2.id_usuario ASC
            ) ua ON ua.turma_id_turma = p.turma_id_time_a
            LEFT JOIN (
                SELECT DISTINCT ON (u3.turma_id_turma)
                    u3.id_usuario, u3.nome_usuario, u3.turma_id_turma
                FROM inscricao i3
                INNER JOIN usuario u3 ON u3.id_usuario = i3.usuario_id_usuario
                WHERE i3.edicao_modalidade_id = :emid2
                  AND i3.status_inscricao = 'ativa'
                ORDER BY u3.turma_id_turma, u3.id_usuario ASC
            ) ub ON ub.turma_id_turma = p.turma_id_time_b
            LEFT JOIN resultado r  ON r.partida_id_partida = p.id_partida
            LEFT JOIN turma tv     ON tv.id_turma = r.turma_id_vencedor
            LEFT JOIN (
                SELECT DISTINCT ON (u4.turma_id_turma)
                    u4.id_usuario, u4.nome_usuario, u4.turma_id_turma
                FROM inscricao i4
                INNER JOIN usuario u4 ON u4.id_usuario = i4.usuario_id_usuario
                WHERE i4.edicao_modalidade_id = :emid3
                  AND i4.status_inscricao = 'ativa'
                ORDER BY u4.turma_id_turma, u4.id_usuario ASC
            ) uv ON uv.turma_id_turma = r.turma_id_vencedor
            WHERE p.edicao_modalidade_id = :emid4
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
        $stmtP->execute([
            ':emid1' => $emId,
            ':emid2' => $emId,
            ':emid3' => $emId,
            ':emid4' => $emId,
        ]);
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
            LEFT  JOIN resultado r ON r.partida_id_partida = p.id_partida
            LEFT  JOIN turma tv    ON tv.id_turma = r.turma_id_vencedor
            WHERE p.edicao_modalidade_id = :emid
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
        $stmtP->execute([':emid' => $emId]);
    }

    $todasPartidas = $stmtP->fetchAll(PDO::FETCH_ASSOC);

    foreach ($todasPartidas as $p) {
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
    $stmtSg = $conn->prepare("SELECT id FROM sorteio_gerado WHERE edicao_modalidade_id = :emid LIMIT 1");
    $stmtSg->execute([':emid' => $emId]);
    $sorteioGerado = (bool) $stmtSg->fetchColumn();
}