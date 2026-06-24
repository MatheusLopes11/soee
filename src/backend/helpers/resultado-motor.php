<?php
// ═══════════════════════════════════════════════════════════
//  helpers/resultado-motor.php — SOEE
//  Motor de resultado + classificação + progressão automática.
//  Compatível com o banco PostgreSQL/Supabase real:
//    - Suporta totalmente modalidades individuais/duplas/trios via usuario_id_time_a/b
// ═══════════════════════════════════════════════════════════

if (!function_exists('processarResultado')) {

function processarResultado(PDO $conn, array $dados): array
{
    $partidaId = (int) ($dados['partida_id'] ?? 0);
    $placarA   = (int) ($dados['placar_a']   ?? 0);
    $placarB   = (int) ($dados['placar_b']   ?? 0);
    $wo        = !empty($dados['wo']);

    if (!$partidaId) {
        return ['ok' => false, 'erro' => 'ID de partida inválido.'];
    }

    $stmtP = $conn->prepare("
        SELECT p.*, m.formato_modalidade
        FROM partida p
        INNER JOIN edicao_modalidade em ON em.id_edicao_modalidade = p.edicao_modalidade_id
        INNER JOIN modalidade m         ON m.id_modalidade = em.modalidade_id_modalidade
        WHERE p.id_partida = :pid
        LIMIT 1
    ");
    $stmtP->execute([':pid' => $partidaId]);
    $partida = $stmtP->fetch(PDO::FETCH_ASSOC);

    if (!$partida) {
        return ['ok' => false, 'erro' => 'Partida não encontrada.'];
    }

    $emId   = (int) $partida['edicao_modalidade_id'];
    $fase   = $partida['fase_partida'];
    
    $turmaA = (int) $partida['turma_id_time_a'];
    $turmaB = (int) $partida['turma_id_time_b'];
    
    $usuarioA = !empty($partida['usuario_id_time_a']) ? (int) $partida['usuario_id_time_a'] : null;
    $usuarioB = !empty($partida['usuario_id_time_b']) ? (int) $partida['usuario_id_time_b'] : null;

    // ── Determinar vencedor ────────────────────────────────
    if ($wo) {
        $vencedorTurma = $turmaA;
        $perdedorTurma = $turmaB;
        $vencedorUsuario = $usuarioA;
        $perdedorUsuario = $usuarioB;
        $empate        = false;
        $statusPartida = 'wo';
    } elseif ($placarA > $placarB) {
        $vencedorTurma = $turmaA;
        $perdedorTurma = $turmaB;
        $vencedorUsuario = $usuarioA;
        $perdedorUsuario = $usuarioB;
        $empate        = false;
        $statusPartida = 'realizada';
    } elseif ($placarB > $placarA) {
        $vencedorTurma = $turmaB;
        $perdedorTurma = $turmaA;
        $vencedorUsuario = $usuarioB;
        $perdedorUsuario = $usuarioA;
        $empate        = false;
        $statusPartida = 'realizada';
    } else {
        $vencedorTurma = null;
        $perdedorTurma = null;
        $vencedorUsuario = null;
        $perdedorUsuario = null;
        $empate        = true;
        $statusPartida = 'realizada';
    }

    $fasesElim = ['oitavas', 'quartas', 'semi', 'final', 'terceiro_lugar'];
    if ($empate && in_array($fase, $fasesElim)) {
        return ['ok' => false, 'erro' => 'Partidas eliminatórias não podem terminar empatadas.'];
    }

    $conn->beginTransaction();
    try {

        // 1. Upsert resultado
        $stmtEx = $conn->prepare("SELECT id_resultado FROM resultado WHERE partida_id_partida = :pid");
        $stmtEx->execute([':pid' => $partidaId]);

        if ($stmtEx->fetchColumn()) {
            $conn->prepare("
                UPDATE resultado SET
                    placar_time_a       = :pa,
                    placar_time_b       = :pb,
                    turma_id_vencedor   = :tv,
                    usuario_id_vencedor = :uv
                WHERE partida_id_partida = :pid
            ")->execute([
                ':pa'  => $placarA,
                ':pb'  => $placarB,
                ':tv'  => $vencedorTurma,
                ':uv'  => $vencedorUsuario,
                ':pid' => $partidaId
            ]);
        } else {
            $conn->prepare("
                INSERT INTO resultado (partida_id_partida, placar_time_a, placar_time_b, turma_id_vencedor, usuario_id_vencedor)
                VALUES (:pid, :pa, :pb, :tv, :uv)
            ")->execute([
                ':pid' => $partidaId,
                ':pa'  => $placarA,
                ':pb'  => $placarB,
                ':tv'  => $vencedorTurma,
                ':uv'  => $vencedorUsuario
            ]);
        }

        // 2. Status da partida
        $conn->prepare("UPDATE partida SET status_partida = :s WHERE id_partida = :pid")
             ->execute([':s' => $statusPartida, ':pid' => $partidaId]);

        // 3. Classificação (só grupos — 3 pts vitória, 1 empate, 0 derrota)
        if ($fase === 'grupos') {
            rm_atualizarClassificacao(
                $conn, $emId,
                $turmaA, $turmaB,
                $usuarioA, $usuarioB,
                $placarA, $placarB,
                $empate,
                $vencedorTurma, $perdedorTurma,
                $vencedorUsuario, $perdedorUsuario
            );
        }

        // 4. Gerar mata-mata quando todos os grupos terminam
        if ($fase === 'grupos' && $partida['formato_modalidade'] === 'grupos_mata_mata') {
            rm_tentarGerarMataMata($conn, $emId);
        }

        // 5. Progressão no mata-mata
        if (!$empate && in_array($fase, $fasesElim) && $vencedorTurma) {
            rm_avancarMataMata($conn, $emId, $partida, $vencedorTurma, $perdedorTurma, $vencedorUsuario, $perdedorUsuario);
        }

        $conn->commit();
        return ['ok' => true, 'msg' => 'Resultado registrado com sucesso!'];

    } catch (Exception $e) {
        $conn->rollBack();
        return ['ok' => false, 'erro' => 'Erro ao registrar: ' . $e->getMessage()];
    }
}

// ──────────────────────────────────────────────────────────

function rm_atualizarClassificacao(
    PDO $conn, int $emId,
    int $turmaA, int $turmaB,
    ?int $usuarioA, ?int $usuarioB,
    int $placarA, int $placarB,
    bool $empate,
    ?int $vencedorTurma, ?int $perdedorTurma,
    ?int $vencedorUsuario, ?int $perdedorUsuario
): void {
    if ($empate) {
        // Empate: 1 ponto para cada
        rm_updateLinha($conn, $emId, $turmaA, $usuarioA, 1, 0, 0, 1, $placarA, $placarB);
        rm_updateLinha($conn, $emId, $turmaB, $usuarioB, 1, 0, 0, 1, $placarB, $placarA);
    } else {
        // Vitória: 3 pts pro vencedor, 0 pro perdedor
        $pVenc = ($vencedorTurma === $turmaA) ? $placarA : $placarB;
        $pPerd = ($vencedorTurma === $turmaA) ? $placarB : $placarA;
        rm_updateLinha($conn, $emId, $vencedorTurma, $vencedorUsuario, 3, 1, 0, 0, $pVenc, $pPerd);
        rm_updateLinha($conn, $emId, $perdedorTurma, $perdedorUsuario, 0, 0, 1, 0, $pPerd, $pVenc);
    }
}

function rm_updateLinha(
    PDO $conn, int $emId, ?int $turmaId, ?int $usuarioId,
    int $pontos, int $vitorias, int $derrotas, int $empates,
    int $pro, int $contra
): void {
    if (!$turmaId && !$usuarioId) return;
    $saldo = $pro - $contra;

    $sql = "
        UPDATE classificacao SET
            pontos        = pontos        + :pts,
            vitorias      = vitorias      + :v,
            derrotas      = derrotas      + :d,
            empates       = empates       + :e,
            jogos         = jogos         + 1,
            pontos_pro    = pontos_pro    + :pro,
            pontos_contra = pontos_contra + :contra,
            saldo         = saldo         + :saldo
        WHERE edicao_modalidade_id = :emid
    ";

    $params = [
        ':pts'    => $pontos,
        ':v'      => $vitorias,
        ':d'      => $derrotas,
        ':e'      => $empates,
        ':pro'    => $pro,
        ':contra' => $contra,
        ':saldo'  => $saldo,
        ':emid'   => $emId,
    ];

    if ($usuarioId) {
        $sql .= " AND usuario_id_participante = :uid ";
        $params[':uid'] = $usuarioId;
    } else {
        $sql .= " AND turma_id_turma = :tid AND usuario_id_participante IS NULL ";
        $params[':tid'] = $turmaId;
    }

    $conn->prepare($sql)->execute($params);
}

// ──────────────────────────────────────────────────────────

function rm_faseInicialPorConfrontos(int $n): string
{
    if ($n > 4) return 'oitavas';
    if ($n > 2) return 'quartas';
    return 'semi'; 
}

function rm_tentarGerarMataMata(PDO $conn, int $emId): void
{
    // Ainda tem partidas de grupos pendentes?
    $stmtPend = $conn->prepare("
        SELECT COUNT(*) FROM partida
        WHERE edicao_modalidade_id = :emid
          AND fase_partida         = 'grupos'
          AND status_partida NOT IN ('realizada', 'wo', 'cancelada')
    ");
    $stmtPend->execute([':emid' => $emId]);
    if ((int) $stmtPend->fetchColumn() > 0) return;

    // Mata-mata já foi gerado?
    $stmtMM = $conn->prepare("
        SELECT COUNT(*) FROM partida
        WHERE edicao_modalidade_id = :emid AND fase_partida != 'grupos'
    ");
    $stmtMM->execute([':emid' => $emId]);
    if ((int) $stmtMM->fetchColumn() > 0) return;

    // Top-2 de cada grupo via classificacao
    $stmtCl = $conn->prepare("
        SELECT ranked.*
        FROM (
            SELECT
                cl.grupo_classificacao,
                t.id_turma   AS turma_id,
                t.nome_turma AS nome,
                cl.usuario_id_participante AS usuario_id,
                ROW_NUMBER() OVER (
                    PARTITION BY cl.grupo_classificacao
                    ORDER BY cl.pontos DESC, cl.saldo DESC,
                             cl.vitorias DESC, cl.pontos_pro DESC
                ) AS posicao
            FROM classificacao cl
            INNER JOIN turma t ON t.id_turma = cl.turma_id_turma
            WHERE cl.edicao_modalidade_id = :emid
        ) ranked
        WHERE ranked.posicao <= 2
        ORDER BY ranked.grupo_classificacao, ranked.posicao
    ");
    $stmtCl->execute([':emid' => $emId]);
    $linhas = $stmtCl->fetchAll(PDO::FETCH_ASSOC);

    $porGrupo = [];
    foreach ($linhas as $row) {
        $g = $row['grupo_classificacao'];
        if (!isset($porGrupo[$g])) $porGrupo[$g] = [];
        if (count($porGrupo[$g]) < 2) $porGrupo[$g][] = $row;
    }

    $grupos = array_keys($porGrupo);
    sort($grupos);
    if (count($grupos) < 2) return;

    $confrontos = [];
    for ($i = 0; $i + 1 < count($grupos); $i += 2) {
        $gX  = $grupos[$i];
        $gY  = $grupos[$i + 1];
        $px1 = $porGrupo[$gX][0] ?? null;
        $px2 = $porGrupo[$gX][1] ?? null;
        $py1 = $porGrupo[$gY][0] ?? null;
        $py2 = $porGrupo[$gY][1] ?? null;
        if ($px1 && $py2) $confrontos[] = [
            'ta' => (int)$px1['turma_id'], 'ua' => $px1['usuario_id'] ? (int)$px1['usuario_id'] : null,
            'tb' => (int)$py2['turma_id'], 'ub' => $py2['usuario_id'] ? (int)$py2['usuario_id'] : null
        ];
        if ($py1 && $px2) $confrontos[] = [
            'ta' => (int)$py1['turma_id'], 'ua' => $py1['usuario_id'] ? (int)$py1['usuario_id'] : null,
            'tb' => (int)$px2['turma_id'], 'ub' => $px2['usuario_id'] ? (int)$px2['usuario_id'] : null
        ];
    }

    if (empty($confrontos)) return;

    $faseInicial = rm_faseInicialPorConfrontos(count($confrontos));
    $dataStr     = (new DateTime('+7 days'))->format('Y-m-d');

    $stmtIns = $conn->prepare("
        INSERT INTO partida
            (edicao_modalidade_id, turma_id_time_a, turma_id_time_b, usuario_id_time_a, usuario_id_time_b,
             data_partida, hora_partida, fase_partida, status_partida)
        VALUES (:emid, :ta, :tb, :ua, :ub, :data, '08:00:00', :fase, 'agendada')
    ");
    foreach ($confrontos as $c) {
        $stmtIns->execute([
            ':emid' => $emId,
            ':ta'   => $c['ta'],
            ':tb'   => $c['tb'],
            ':ua'   => $c['ua'],
            ':ub'   => $c['ub'],
            ':data' => $dataStr,
            ':fase' => $faseInicial,
        ]);
    }
}

function rm_avancarMataMata(
    PDO $conn, int $emId, array $partida,
    int $vencedorTurma, ?int $perdedorTurma,
    ?int $vencedorUsuario, ?int $perdedorUsuario
): void {
    $fasesMap  = ['oitavas' => 'quartas', 'quartas' => 'semi', 'semi' => 'final'];
    $faseAtual = $partida['fase_partida'];

    // GUARDA ANTI-DUPLICAÇÃO
    if (isset($fasesMap[$faseAtual])) {
        $proximaFase = $fasesMap[$faseAtual];

        $stmtJa = $conn->prepare("
            SELECT COUNT(*) FROM partida
            WHERE edicao_modalidade_id = :emid
              AND fase_partida         = :fase
              AND (turma_id_time_a = :t OR turma_id_time_b = :t)
              AND (usuario_id_time_a IS NOT DISTINCT FROM :u OR usuario_id_time_b IS NOT DISTINCT FROM :u)
        ");
        $stmtJa->execute([
            ':emid' => $emId,
            ':fase' => $proximaFase,
            ':t'    => $vencedorTurma,
            ':u'    => $vencedorUsuario,
        ]);

        if ((int) $stmtJa->fetchColumn() > 0) {
            if ($faseAtual === 'semi' && $perdedorTurma) {
                rm_gerarOuAtualizarTerceiro($conn, $emId, $perdedorTurma, $perdedorUsuario);
            }
            return;
        }
    }

    if ($faseAtual === 'semi' && $perdedorTurma) {
        rm_gerarOuAtualizarTerceiro($conn, $emId, $perdedorTurma, $perdedorUsuario);
    }

    if (!isset($fasesMap[$faseAtual])) return;

    $proximaFase = $fasesMap[$faseAtual];
    $dataStr     = (new DateTime('+14 days'))->format('Y-m-d');

    $stmtFase = $conn->prepare("
        SELECT id_partida FROM partida
        WHERE edicao_modalidade_id = :emid AND fase_partida = :fase
        ORDER BY id_partida ASC
    ");
    $stmtFase->execute([':emid' => $emId, ':fase' => $faseAtual]);
    $idsFase = array_values(array_map('intval', $stmtFase->fetchAll(PDO::FETCH_COLUMN)));

    $posicao = array_search((int) $partida['id_partida'], $idsFase);
    if ($posicao === false) return;

    $vagaIdx  = (int) floor($posicao / 2);
    $ladoVaga = $posicao % 2; 

    $stmtProx = $conn->prepare("
        SELECT id_partida, turma_id_time_a, turma_id_time_b FROM partida
        WHERE edicao_modalidade_id = :emid AND fase_partida = :fase
        ORDER BY id_partida ASC
    ");
    $stmtProx->execute([':emid' => $emId, ':fase' => $proximaFase]);
    $partidasProx = array_values($stmtProx->fetchAll(PDO::FETCH_ASSOC));

    if (isset($partidasProx[$vagaIdx])) {
        $pid = (int) $partidasProx[$vagaIdx]['id_partida'];
        $colT = ($ladoVaga === 0) ? 'turma_id_time_a' : 'turma_id_time_b';
        $colU = ($ladoVaga === 0) ? 'usuario_id_time_a' : 'usuario_id_time_b';
        $conn->prepare("UPDATE partida SET {$colT} = :turma, {$colU} = :usu WHERE id_partida = :pid")
             ->execute([':turma' => $vencedorTurma, ':usu' => $vencedorUsuario, ':pid' => $pid]);

    } elseif ($ladoVaga === 0) {
        $conn->prepare("
            INSERT INTO partida
                (edicao_modalidade_id, turma_id_time_a, turma_id_time_b, usuario_id_time_a, usuario_id_time_b,
                 data_partida, hora_partida, fase_partida, status_partida)
            VALUES (:emid, :ta, :ta, :ua, :ua, :data, '08:00:00', :fase, 'agendada')
        ")->execute([
            ':emid' => $emId, ':ta' => $vencedorTurma, ':ua' => $vencedorUsuario,
            ':data' => $dataStr, ':fase' => $proximaFase,
        ]);

    } else {
        $stmtSen = $conn->prepare("
            SELECT id_partida FROM partida
            WHERE edicao_modalidade_id = :emid
              AND fase_partida         = :fase
              AND turma_id_time_a      = turma_id_time_b
              AND status_partida       = 'agendada'
            ORDER BY id_partida ASC LIMIT 1
        ");
        $stmtSen->execute([':emid' => $emId, ':fase' => $proximaFase]);
        $senId = $stmtSen->fetchColumn();

        if ($senId) {
            $conn->prepare("UPDATE partida SET turma_id_time_b = :turma, usuario_id_time_b = :usu WHERE id_partida = :pid")
                 ->execute([':turma' => $vencedorTurma, ':usu' => $vencedorUsuario, ':pid' => (int)$senId]);
        } else {
            $conn->prepare("
                INSERT INTO partida
                    (edicao_modalidade_id, turma_id_time_a, turma_id_time_b, usuario_id_time_a, usuario_id_time_b,
                     data_partida, hora_partida, fase_partida, status_partida)
            VALUES (:emid, :ta, :ta, :ua, :ua, :data, '08:00:00', :fase, 'agendada')
            ")->execute([
                ':emid' => $emId, ':ta' => $vencedorTurma, ':ua' => $vencedorUsuario,
                ':data' => $dataStr, ':fase' => $proximaFase,
            ]);
        }
    }
}

function rm_gerarOuAtualizarTerceiro(PDO $conn, int $emId, int $perdedorTurma, ?int $perdedorUsuario): void
{
    $dataStr = (new DateTime('+14 days'))->format('Y-m-d');

    $stmtEx = $conn->prepare("
        SELECT id_partida, turma_id_time_a, turma_id_time_b, usuario_id_time_a, usuario_id_time_b FROM partida
        WHERE edicao_modalidade_id = :emid AND fase_partida = 'terceiro_lugar'
        ORDER BY id_partida ASC LIMIT 1
    ");
    $stmtEx->execute([':emid' => $emId]);
    $row = $stmtEx->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $taA = (int) $row['turma_id_time_a'];
        $taB = (int) $row['turma_id_time_b'];
        $uaA = $row['usuario_id_time_a'] ? (int) $row['usuario_id_time_a'] : null;
        $uaB = $row['usuario_id_time_b'] ? (int) $row['usuario_id_time_b'] : null;

        if (($taA === $perdedorTurma && $uaA === $perdedorUsuario) || ($taB === $perdedorTurma && $uaB === $perdedorUsuario)) {
            return;
        }

        if ($taA === $taB && $uaA === $uaB) {
            $conn->prepare("UPDATE partida SET turma_id_time_b = :t, usuario_id_time_b = :u WHERE id_partida = :pid")
                 ->execute([':t' => $perdedorTurma, ':u' => $perdedorUsuario, ':pid' => (int)$row['id_partida']]);
        }
    } else {
        $conn->prepare("
            INSERT INTO partida
                (edicao_modalidade_id, turma_id_time_a, turma_id_time_b, usuario_id_time_a, usuario_id_time_b,
                 data_partida, hora_partida, fase_partida, status_partida)
            VALUES (:emid, :ta, :ta, :ua, :ua, :data, '08:00:00', 'terceiro_lugar', 'agendada')
        ")->execute([':emid' => $emId, ':ta' => $perdedorTurma, ':ua' => $perdedorUsuario, ':data' => $dataStr]);
    }
}

function rm_reconstruirProgressaoMataMata(PDO $conn, int $emId): void
{
    foreach (['oitavas', 'quartas', 'semi'] as $faseLabel) {
        $stmtCount = $conn->prepare("
            SELECT COUNT(*) FROM partida
            WHERE edicao_modalidade_id = :emid
              AND fase_partida = :fase
        ");
        $stmtCount->execute([':emid' => $emId, ':fase' => $faseLabel]);
        $qtd = (int) $stmtCount->fetchColumn();

        if ($qtd === 0) continue;

        $correta = rm_faseInicialPorConfrontos($qtd);

        if ($correta !== $faseLabel) {
            $stmtExisteCorreta = $conn->prepare("
                SELECT COUNT(*) FROM partida
                WHERE edicao_modalidade_id = :emid AND fase_partida = :correta
            ");
            $stmtExisteCorreta->execute([':emid' => $emId, ':correta' => $correta]);
            if ((int) $stmtExisteCorreta->fetchColumn() === 0) {
                $conn->prepare("
                    UPDATE partida SET fase_partida = :correta
                    WHERE edicao_modalidade_id = :emid AND fase_partida = :atual
                ")->execute([
                    ':correta' => $correta,
                    ':emid'    => $emId,
                    ':atual'   => $faseLabel,
                ]);
            }
        }
        break; 
    }

    foreach (['oitavas', 'quartas', 'semi'] as $fase) {
        $stmt = $conn->prepare("
            SELECT p.*, r.turma_id_vencedor, r.usuario_id_vencedor
            FROM partida p
            INNER JOIN resultado r ON r.partida_id_partida = p.id_partida
            WHERE p.edicao_modalidade_id = :emid
              AND p.fase_partida = :fase
              AND p.status_partida IN ('realizada','wo')
              AND p.turma_id_time_a != p.turma_id_time_b
              AND r.turma_id_vencedor IS NOT NULL
            ORDER BY p.id_partida ASC
        ");
        $stmt->execute([':emid' => $emId, ':fase' => $fase]);
        $partidas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($partidas as $partida) {
            $vencedor = (int) $partida['turma_id_vencedor'];
            $vencedorU = !empty($partida['usuario_id_vencedor']) ? (int) $partida['usuario_id_vencedor'] : null;
            $taA      = (int) $partida['turma_id_time_a'];
            $taB      = (int) $partida['turma_id_time_b'];
            $uaA      = !empty($partida['usuario_id_time_a']) ? (int) $partida['usuario_id_time_a'] : null;
            $uaB      = !empty($partida['usuario_id_time_b']) ? (int) $partida['usuario_id_time_b'] : null;
            
            $perdedor = ($vencedor === $taA) ? $taB : $taA;
            $perdedorU = ($vencedorU === $uaA) ? $uaB : $uaA;

            rm_avancarMataMata($conn, $emId, $partida, $vencedor, $perdedor, $vencedorU, $perdedorU);
        }
    }
}

} // end if !function_exists