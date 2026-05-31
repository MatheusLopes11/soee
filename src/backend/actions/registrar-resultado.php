<?php
// ═══════════════════════════════════════════════════════════
//  registrar-resultado.php — SOEE
//  Salva resultado de uma partida, atualiza classificação
//  e avança o vencedor no mata-mata automaticamente.
//
//  Destino: soee/src/backend/actions/registrar-resultado.php
// ═══════════════════════════════════════════════════════════
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/soee/src/backend/includes/conexao.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/soee/src/backend/controllers/home.php';

header('Content-Type: application/json; charset=utf-8');
AuthHome::exigirTipo(['professor', 'adm_geral']);

// ── Entrada ────────────────────────────────────────────────
$partidaId = (int) ($_POST['partida_id']  ?? 0);
$placarA   = (int) ($_POST['placar_a']    ?? 0);
$placarB   = (int) ($_POST['placar_b']    ?? 0);
$wo        = isset($_POST['wo']) && $_POST['wo'] === '1';

if (!$partidaId) {
    echo json_encode(['ok' => false, 'erro' => 'ID de partida inválido.']);
    exit;
}

// ── Busca partida + modalidade ─────────────────────────────
$stmtP = $conn->prepare("
    SELECT
        p.*,
        m.tipo_participacao,
        m.formato_modalidade
    FROM partida p
    INNER JOIN edicao_modalidade em ON em.id_edicao_modalidade = p.edicao_modalidade_id
    INNER JOIN modalidade m         ON m.id_modalidade = em.modalidade_id_modalidade
    WHERE p.id_partida = :pid
    LIMIT 1
");
$stmtP->execute([':pid' => $partidaId]);
$partida = $stmtP->fetch(PDO::FETCH_ASSOC);

if (!$partida) {
    echo json_encode(['ok' => false, 'erro' => 'Partida não encontrada.']);
    exit;
}

$ehIndividual = in_array($partida['tipo_participacao'], ['solo', 'dupla', 'trio']);
$emId         = (int) $partida['edicao_modalidade_id'];
$fase         = $partida['fase_partida'];

// ── Determinar vencedor ────────────────────────────────────
if ($wo) {
    // W.O.: time_a vence por default (admin pode ajustar quem levou WO via placar)
    $vencedorTurma   = (int) $partida['turma_id_time_a'];
    $vencedorUsuario = $ehIndividual ? (int) $partida['usuario_id_time_a'] : null;
    $perdedorTurma   = (int) $partida['turma_id_time_b'];
    $perdedorUsuario = $ehIndividual ? (int) $partida['usuario_id_time_b'] : null;
    $empate          = false;
    $statusPartida   = 'wo';
} elseif ($placarA > $placarB) {
    $vencedorTurma   = (int) $partida['turma_id_time_a'];
    $vencedorUsuario = $ehIndividual ? (int) $partida['usuario_id_time_a'] : null;
    $perdedorTurma   = (int) $partida['turma_id_time_b'];
    $perdedorUsuario = $ehIndividual ? (int) $partida['usuario_id_time_b'] : null;
    $empate          = false;
    $statusPartida   = 'realizada';
} elseif ($placarB > $placarA) {
    $vencedorTurma   = (int) $partida['turma_id_time_b'];
    $vencedorUsuario = $ehIndividual ? (int) $partida['usuario_id_time_b'] : null;
    $perdedorTurma   = (int) $partida['turma_id_time_a'];
    $perdedorUsuario = $ehIndividual ? (int) $partida['usuario_id_time_a'] : null;
    $empate          = false;
    $statusPartida   = 'realizada';
} else {
    // Empate (só permitido em fase de grupos)
    $vencedorTurma   = null;
    $vencedorUsuario = null;
    $perdedorTurma   = null;
    $perdedorUsuario = null;
    $empate          = true;
    $statusPartida   = 'realizada';
}

// Mata-mata não pode ter empate
if ($empate && $fase !== 'grupos') {
    echo json_encode(['ok' => false, 'erro' => 'Partidas eliminatórias não podem terminar empatadas. Informe um placar desempatador.']);
    exit;
}

// ── Transação ──────────────────────────────────────────────
$conn->beginTransaction();
try {

    // 1. Salvar / atualizar resultado
    $stmtExiste = $conn->prepare("SELECT id_resultado FROM resultado WHERE partida_id_partida = :pid");
    $stmtExiste->execute([':pid' => $partidaId]);
    $resultadoExistente = $stmtExiste->fetchColumn();

    if ($resultadoExistente) {
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
            ':pid' => $partidaId,
        ]);
    } else {
        $conn->prepare("
            INSERT INTO resultado
                (partida_id_partida, placar_time_a, placar_time_b,
                 turma_id_vencedor, usuario_id_vencedor)
            VALUES (:pid, :pa, :pb, :tv, :uv)
        ")->execute([
            ':pid' => $partidaId,
            ':pa'  => $placarA,
            ':pb'  => $placarB,
            ':tv'  => $vencedorTurma,
            ':uv'  => $vencedorUsuario,
        ]);
    }

    // 2. Atualizar status da partida
    $conn->prepare("
        UPDATE partida SET status_partida = :status WHERE id_partida = :pid
    ")->execute([':status' => $statusPartida, ':pid' => $partidaId]);

    // 3. Atualizar classificação (somente fase de grupos)
    if ($fase === 'grupos') {
        _atualizarClassificacao(
            $conn, $emId, $ehIndividual,
            $partida, $placarA, $placarB, $empate,
            $vencedorTurma, $vencedorUsuario,
            $perdedorTurma, $perdedorUsuario
        );
    }

    // 4. Avançar vencedor no mata-mata (fases eliminatórias)
    $fasesEliminatorias = ['oitavas', 'quartas', 'semi', 'final', 'terceiro_lugar'];
    if (!$empate && in_array($fase, $fasesEliminatorias)) {
        _avancarMataMatа(
            $conn, $emId, $ehIndividual, $partida,
            $vencedorTurma, $vencedorUsuario
        );
    }

    // 5. Checar se todos os jogos dos grupos acabaram → gerar mata-mata
    if ($fase === 'grupos' && $partida['formato_modalidade'] === 'grupos_mata_mata') {
        _tentarGerarMataMata($conn, $emId, $ehIndividual);
    }

    $conn->commit();
    echo json_encode(['ok' => true, 'msg' => 'Resultado registrado com sucesso!']);

} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode(['ok' => false, 'erro' => 'Erro ao registrar: ' . $e->getMessage()]);
}

// ══════════════════════════════════════════════════════════
//  FUNÇÕES AUXILIARES
// ══════════════════════════════════════════════════════════

/**
 * Atualiza pontos, vitórias, empates, etc. na tabela classificacao.
 */
function _atualizarClassificacao(
    PDO $conn, int $emId, bool $ehIndividual,
    array $partida, int $placarA, int $placarB, bool $empate,
    ?int $vencedorTurma, ?int $vencedorUsuario,
    ?int $perdedorTurma, ?int $perdedorUsuario
): void {

    $turmaA   = (int) $partida['turma_id_time_a'];
    $turmaB   = (int) $partida['turma_id_time_b'];
    $usuarioA = $ehIndividual ? (int) $partida['usuario_id_time_a'] : null;
    $usuarioB = $ehIndividual ? (int) $partida['usuario_id_time_b'] : null;

    if ($empate) {
        // Ambos ganham 1 ponto
        _updateLinha($conn, $emId, $ehIndividual, $turmaA, $usuarioA, 1, 0, 0, 1, $placarA, $placarB);
        _updateLinha($conn, $emId, $ehIndividual, $turmaB, $usuarioB, 1, 0, 0, 1, $placarB, $placarA);
    } else {
        // Vencedor: 3 pts, 1 vitória
        $turmaV   = $vencedorTurma;
        $usuarioV = $vencedorUsuario;
        $placarV  = ($vencedorTurma === $turmaA && (!$ehIndividual || $vencedorUsuario === $usuarioA))
                    ? $placarA : $placarB;
        $placarD  = ($placarV === $placarA) ? $placarB : $placarA;

        $turmaD   = $perdedorTurma;
        $usuarioD = $perdedorUsuario;

        _updateLinha($conn, $emId, $ehIndividual, $turmaV, $usuarioV, 3, 1, 0, 0, $placarV, $placarD);
        _updateLinha($conn, $emId, $ehIndividual, $turmaD, $usuarioD, 0, 0, 1, 0, $placarD, $placarV);
    }
}

/**
 * Aplica UPDATE em uma linha da classificação.
 */
function _updateLinha(
    PDO $conn, int $emId, bool $ehIndividual,
    int $turmaId, ?int $usuarioId,
    int $pontos, int $vitorias, int $derrotas, int $empates,
    int $pro, int $contra
): void {
    $saldo = $pro - $contra;

    if ($ehIndividual) {
        $conn->prepare("
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
              AND usuario_id_participante = :uid
        ")->execute([
            ':pts'    => $pontos,
            ':v'      => $vitorias,
            ':d'      => $derrotas,
            ':e'      => $empates,
            ':pro'    => $pro,
            ':contra' => $contra,
            ':saldo'  => $saldo,
            ':emid'   => $emId,
            ':uid'    => $usuarioId,
        ]);
    } else {
        $conn->prepare("
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
              AND turma_id_turma = :tid
              AND usuario_id_participante IS NULL
        ")->execute([
            ':pts'    => $pontos,
            ':v'      => $vitorias,
            ':d'      => $derrotas,
            ':e'      => $empates,
            ':pro'    => $pro,
            ':contra' => $contra,
            ':saldo'  => $saldo,
            ':emid'   => $emId,
            ':tid'    => $turmaId,
        ]);
    }
}

/**
 * Avança o vencedor de uma partida eliminatória para a próxima vaga.
 *
 * Lógica de progressão:
 *   oitavas  → quartas
 *   quartas  → semi
 *   semi (1ª partida) → final + terceiro_lugar (perdedor)
 *   semi (2ª partida) → final + terceiro_lugar (perdedor)
 *   final    → encerra
 */
function _avancarMataMatа(
    PDO $conn, int $emId, bool $ehIndividual,
    array $partida, ?int $vencedorTurma, ?int $vencedorUsuario
): void {

    $fasesMap = [
        'oitavas' => 'quartas',
        'quartas' => 'semi',
        'semi'    => 'final',
    ];

    $faseAtual = $partida['fase_partida'];
    if (!isset($fasesMap[$faseAtual])) {
        return; // final ou terceiro_lugar não avançam
    }

    $proximaFase = $fasesMap[$faseAtual];

    // Pega todas as partidas da fase atual (ordenadas por id para manter posição)
    $stmtFase = $conn->prepare("
        SELECT id_partida FROM partida
        WHERE edicao_modalidade_id = :emid
          AND fase_partida = :fase
        ORDER BY id_partida ASC
    ");
    $stmtFase->execute([':emid' => $emId, ':fase' => $faseAtual]);
    $partidasFase = $stmtFase->fetchAll(PDO::FETCH_COLUMN);

    // Posição da partida atual dentro da fase (0-based)
    $posicao = array_search($partida['id_partida'], $partidasFase);
    if ($posicao === false) return;

    // Cada 2 partidas geram 1 vaga na fase seguinte
    $vagaIndex    = (int) floor($posicao / 2); // qual partida da próxima fase
    $ladoDaVaga   = $posicao % 2;              // 0 = time_a, 1 = time_b

    // Busca as partidas da próxima fase
    $stmtProxima = $conn->prepare("
        SELECT id_partida FROM partida
        WHERE edicao_modalidade_id = :emid
          AND fase_partida = :fase
        ORDER BY id_partida ASC
    ");
    $stmtProxima->execute([':emid' => $emId, ':fase' => $proximaFase]);
    $partidasProxFase = $stmtProxima->fetchAll(PDO::FETCH_COLUMN);

    if (isset($partidasProxFase[$vagaIndex])) {
        // Atualiza a vaga existente
        $proximaPartidaId = $partidasProxFase[$vagaIndex];
        if ($ladoDaVaga === 0) {
            $sql = "UPDATE partida SET
                        turma_id_time_a   = :turma,
                        usuario_id_time_a = :usuario
                    WHERE id_partida = :pid";
        } else {
            $sql = "UPDATE partida SET
                        turma_id_time_b   = :turma,
                        usuario_id_time_b = :usuario
                    WHERE id_partida = :pid";
        }
        $conn->prepare($sql)->execute([
            ':turma'   => $vencedorTurma,
            ':usuario' => $vencedorUsuario,
            ':pid'     => $proximaPartidaId,
        ]);
    } else {
        // Precisa criar a partida da próxima fase
        $dataStr = (new DateTime('+14 days'))->format('Y-m-d');
        $horaStr = '08:00:00';

        if ($ladoDaVaga === 0) {
            // Cria nova partida com time_a preenchido (time_b vem depois)
            if ($ehIndividual) {
                $conn->prepare("
                    INSERT INTO partida
                        (edicao_modalidade_id, turma_id_time_a, turma_id_time_b,
                         usuario_id_time_a, usuario_id_time_b,
                         data_partida, hora_partida, fase_partida, status_partida)
                    VALUES (:emid, :ta, 0, :ua, NULL, :data, :hora, :fase, 'agendada')
                ")->execute([
                    ':emid' => $emId,
                    ':ta'   => $vencedorTurma,
                    ':ua'   => $vencedorUsuario,
                    ':data' => $dataStr,
                    ':hora' => $horaStr,
                    ':fase' => $proximaFase,
                ]);
            } else {
                $conn->prepare("
                    INSERT INTO partida
                        (edicao_modalidade_id, turma_id_time_a, turma_id_time_b,
                         data_partida, hora_partida, fase_partida, status_partida)
                    VALUES (:emid, :ta, 0, :data, :hora, :fase, 'agendada')
                ")->execute([
                    ':emid' => $emId,
                    ':ta'   => $vencedorTurma,
                    ':data' => $dataStr,
                    ':hora' => $horaStr,
                    ':fase' => $proximaFase,
                ]);
            }
        } else {
            // time_b chega depois: atualiza a partida recém-criada pelo time_a
            $stmtUlt = $conn->prepare("
                SELECT id_partida FROM partida
                WHERE edicao_modalidade_id = :emid
                  AND fase_partida = :fase
                  AND turma_id_time_b = 0
                ORDER BY id_partida DESC LIMIT 1
            ");
            $stmtUlt->execute([':emid' => $emId, ':fase' => $proximaFase]);
            $ultimaId = $stmtUlt->fetchColumn();
            if ($ultimaId) {
                $conn->prepare("
                    UPDATE partida SET
                        turma_id_time_b   = :turma,
                        usuario_id_time_b = :usuario
                    WHERE id_partida = :pid
                ")->execute([
                    ':turma'   => $vencedorTurma,
                    ':usuario' => $vencedorUsuario,
                    ':pid'     => $ultimaId,
                ]);
            }
        }
    }

    // ── Perdedor da semi → disputa 3º lugar ───────────────
    if ($faseAtual === 'semi') {
        // Determina perdedor
        $perdedorTurma   = ($vencedorTurma === (int)$partida['turma_id_time_a'])
                           ? (int)$partida['turma_id_time_b']
                           : (int)$partida['turma_id_time_a'];
        $perdedorUsuario = null;
        if ($ehIndividual) {
            $perdedorUsuario = ($vencedorUsuario === (int)$partida['usuario_id_time_a'])
                               ? (int)$partida['usuario_id_time_b']
                               : (int)$partida['usuario_id_time_a'];
        }

        // Verifica se já existe partida de terceiro_lugar
        $stmtTer = $conn->prepare("
            SELECT id_partida FROM partida
            WHERE edicao_modalidade_id = :emid AND fase_partida = 'terceiro_lugar'
            ORDER BY id_partida ASC LIMIT 1
        ");
        $stmtTer->execute([':emid' => $emId]);
        $terceiroId = $stmtTer->fetchColumn();

        $dataStr = (new DateTime('+14 days'))->format('Y-m-d');
        $horaStr = '08:00:00';

        if ($terceiroId) {
            $conn->prepare("
                UPDATE partida SET
                    turma_id_time_b   = :turma,
                    usuario_id_time_b = :usuario
                WHERE id_partida = :pid
            ")->execute([
                ':turma'   => $perdedorTurma,
                ':usuario' => $perdedorUsuario,
                ':pid'     => $terceiroId,
            ]);
        } else {
            if ($ehIndividual) {
                $conn->prepare("
                    INSERT INTO partida
                        (edicao_modalidade_id, turma_id_time_a, turma_id_time_b,
                         usuario_id_time_a, usuario_id_time_b,
                         data_partida, hora_partida, fase_partida, status_partida)
                    VALUES (:emid, :ta, 0, :ua, NULL, :data, :hora, 'terceiro_lugar', 'agendada')
                ")->execute([
                    ':emid' => $emId, ':ta' => $perdedorTurma,
                    ':ua' => $perdedorUsuario, ':data' => $dataStr, ':hora' => $horaStr,
                ]);
            } else {
                $conn->prepare("
                    INSERT INTO partida
                        (edicao_modalidade_id, turma_id_time_a, turma_id_time_b,
                         data_partida, hora_partida, fase_partida, status_partida)
                    VALUES (:emid, :ta, 0, :data, :hora, 'terceiro_lugar', 'agendada')
                ")->execute([
                    ':emid' => $emId, ':ta' => $perdedorTurma,
                    ':data' => $dataStr, ':hora' => $horaStr,
                ]);
            }
        }
    }
}

/**
 * Verifica se todas as partidas de grupos foram realizadas.
 * Se sim, e o formato é grupos_mata_mata, gera automaticamente
 * as partidas do mata-mata com os classificados de cada grupo.
 */
function _tentarGerarMataMata(PDO $conn, int $emId, bool $ehIndividual): void
{
    // Conta partidas de grupos pendentes
    $stmtPend = $conn->prepare("
        SELECT COUNT(*) FROM partida
        WHERE edicao_modalidade_id = :emid
          AND fase_partida = 'grupos'
          AND status_partida NOT IN ('realizada', 'wo', 'cancelada')
    ");
    $stmtPend->execute([':emid' => $emId]);
    $pendentes = (int) $stmtPend->fetchColumn();

    if ($pendentes > 0) return; // ainda há jogos de grupo

    // Verifica se mata-mata já foi gerado
    $stmtMM = $conn->prepare("
        SELECT COUNT(*) FROM partida
        WHERE edicao_modalidade_id = :emid
          AND fase_partida != 'grupos'
    ");
    $stmtMM->execute([':emid' => $emId]);
    if ((int) $stmtMM->fetchColumn() > 0) return;

    // ── Busca classificados por grupo (top 2 de cada) ────
    if ($ehIndividual) {
        $stmtCl = $conn->prepare("
            SELECT
                cl.grupo_classificacao,
                cl.usuario_id_participante AS id,
                u.nome_usuario             AS nome,
                u.turma_id_turma           AS turma_id,
                t.nome_turma
            FROM classificacao cl
            INNER JOIN usuario u ON u.id_usuario = cl.usuario_id_participante
            INNER JOIN turma   t ON t.id_turma   = u.turma_id_turma
            WHERE cl.edicao_modalidade_id = :emid
              AND cl.usuario_id_participante IS NOT NULL
            ORDER BY cl.grupo_classificacao, cl.pontos DESC, cl.saldo DESC,
                     cl.vitorias DESC, cl.pontos_pro DESC
        ");
    } else {
        $stmtCl = $conn->prepare("
            SELECT
                cl.grupo_classificacao,
                t.id_turma  AS id,
                t.nome_turma AS nome,
                t.id_turma  AS turma_id,
                t.nome_turma
            FROM classificacao cl
            INNER JOIN turma t ON t.id_turma = cl.turma_id_turma
            WHERE cl.edicao_modalidade_id = :emid
              AND cl.usuario_id_participante IS NULL
            ORDER BY cl.grupo_classificacao, cl.pontos DESC, cl.saldo DESC,
                     cl.vitorias DESC, cl.pontos_pro DESC
        ");
    }
    $stmtCl->execute([':emid' => $emId]);
    $linhas = $stmtCl->fetchAll(PDO::FETCH_ASSOC);

    // Agrupa por grupo e pega top 2
    $porGrupo = [];
    foreach ($linhas as $row) {
        $g = $row['grupo_classificacao'];
        if (!isset($porGrupo[$g])) $porGrupo[$g] = [];
        if (count($porGrupo[$g]) < 2) $porGrupo[$g][] = $row;
    }

    // Monta lista de classificados (alternando 1º e 2º de grupos diferentes)
    $classificados = [];
    $grupos = array_keys($porGrupo);
    foreach ($grupos as $g) {
        if (isset($porGrupo[$g][0])) $classificados[] = $porGrupo[$g][0];
    }
    foreach ($grupos as $g) {
        if (isset($porGrupo[$g][1])) $classificados[] = $porGrupo[$g][1];
    }

    if (count($classificados) < 2) return;

    shuffle($classificados); // embaralha para não ser sempre o mesmo cruzamento

    // Determina fase inicial do mata-mata
    $total    = count($classificados);
    $potencia = 1; while ($potencia < $total) $potencia *= 2;
    $fase = _faseInicialMM($potencia);

    $dataStr = (new DateTime('+7 days'))->format('Y-m-d');
    $horaStr = '08:00:00';

    if ($ehIndividual) {
        $stmtIns = $conn->prepare("
            INSERT INTO partida
                (edicao_modalidade_id, turma_id_time_a, turma_id_time_b,
                 usuario_id_time_a, usuario_id_time_b,
                 data_partida, hora_partida, fase_partida, status_partida)
            VALUES (:emid, :ta, :tb, :ua, :ub, :data, :hora, :fase, 'agendada')
        ");
    } else {
        $stmtIns = $conn->prepare("
            INSERT INTO partida
                (edicao_modalidade_id, turma_id_time_a, turma_id_time_b,
                 data_partida, hora_partida, fase_partida, status_partida)
            VALUES (:emid, :ta, :tb, :data, :hora, :fase, 'agendada')
        ");
    }

    // Gera byes se necessário
    $numByes = $potencia - $total;
    $comBye  = array_slice($classificados, 0, $numByes);
    $semBye  = array_slice($classificados, $numByes);

    for ($i = 0; $i + 1 < count($semBye); $i += 2) {
        $params = [
            ':emid' => $emId,
            ':ta'   => (int) $semBye[$i]['turma_id'],
            ':tb'   => (int) $semBye[$i + 1]['turma_id'],
            ':data' => $dataStr,
            ':hora' => $horaStr,
            ':fase' => $fase,
        ];
        if ($ehIndividual) {
            $params[':ua'] = (int) $semBye[$i]['id'];
            $params[':ub'] = (int) $semBye[$i + 1]['id'];
        }
        $stmtIns->execute($params);
    }

    // Times com BYE avançam direto (serão inseridos na próxima fase)
    // Neste momento apenas registramos — eles serão colocados nas vagas
    // da próxima fase quando a primeira partida do mata-mata for registrada.
    // Para simplificar: criamos já as partidas da próxima fase com os times com BYE.
    if (!empty($comBye) && count($semBye) > 0) {
        $proximaFaseBye = _proximaFase($fase);
        if ($proximaFaseBye) {
            foreach ($comBye as $bye) {
                $params = [
                    ':emid' => $emId,
                    ':ta'   => (int) $bye['turma_id'],
                    ':tb'   => 0,
                    ':data' => $dataStr,
                    ':hora' => $horaStr,
                    ':fase' => $proximaFaseBye,
                ];
                if ($ehIndividual) {
                    $params[':ua'] = (int) $bye['id'];
                    $params[':ub'] = null;
                }
                $stmtIns->execute($params);
            }
        }
    }
}

function _faseInicialMM(int $potencia): string {
    if ($potencia <= 2) return 'final';
    if ($potencia <= 4) return 'semi';
    if ($potencia <= 8) return 'quartas';
    return 'oitavas';
}

function _proximaFase(string $fase): ?string {
    $map = ['oitavas' => 'quartas', 'quartas' => 'semi', 'semi' => 'final'];
    return $map[$fase] ?? null;
}