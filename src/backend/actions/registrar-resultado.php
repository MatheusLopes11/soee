<?php
// ═══════════════════════════════════════════════════════════
//  registrar-resultado.php — SOEE
//  FIX 1: _atualizarClassificacao agora usa UPDATE correto
//          com parâmetros nomeados sem colisão.
//  FIX 2: _avancarMataMata corrigido para progressão
//          automática confiável entre fases.
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

$turmaA   = (int) $partida['turma_id_time_a'];
$turmaB   = (int) $partida['turma_id_time_b'];
$usuarioA = $ehIndividual ? (int) $partida['usuario_id_time_a'] : null;
$usuarioB = $ehIndividual ? (int) $partida['usuario_id_time_b'] : null;

// ── Determinar vencedor ────────────────────────────────────
if ($wo) {
    $vencedorTurma   = $turmaA;
    $vencedorUsuario = $usuarioA;
    $perdedorTurma   = $turmaB;
    $perdedorUsuario = $usuarioB;
    $empate          = false;
    $statusPartida   = 'wo';
} elseif ($placarA > $placarB) {
    $vencedorTurma   = $turmaA;
    $vencedorUsuario = $usuarioA;
    $perdedorTurma   = $turmaB;
    $perdedorUsuario = $usuarioB;
    $empate          = false;
    $statusPartida   = 'realizada';
} elseif ($placarB > $placarA) {
    $vencedorTurma   = $turmaB;
    $vencedorUsuario = $usuarioB;
    $perdedorTurma   = $turmaA;
    $perdedorUsuario = $usuarioA;
    $empate          = false;
    $statusPartida   = 'realizada';
} else {
    $vencedorTurma   = null;
    $vencedorUsuario = null;
    $perdedorTurma   = null;
    $perdedorUsuario = null;
    $empate          = true;
    $statusPartida   = 'realizada';
}

// Mata-mata não pode ter empate
if ($empate && $fase !== 'grupos') {
    echo json_encode(['ok' => false, 'erro' => 'Partidas eliminatórias não podem terminar empatadas.']);
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

    // 3. FIX: Atualizar classificação (somente fase de grupos)
    if ($fase === 'grupos') {
        _atualizarClassificacao(
            $conn, $emId, $ehIndividual,
            $turmaA, $usuarioA, $turmaB, $usuarioB,
            $placarA, $placarB, $empate,
            $vencedorTurma, $vencedorUsuario,
            $perdedorTurma, $perdedorUsuario
        );
    }

    // 4. FIX: Avançar vencedor no mata-mata
    $fasesEliminatorias = ['oitavas', 'quartas', 'semi', 'final', 'terceiro_lugar'];
    if (!$empate && in_array($fase, $fasesEliminatorias)) {
        _avancarMataMata(
            $conn, $emId, $ehIndividual, $partida,
            $vencedorTurma, $vencedorUsuario,
            $perdedorTurma, $perdedorUsuario
        );
    }

    // 5. Checar se grupos acabaram → gerar mata-mata
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
 * FIX: Assinatura corrigida — turmaA/B e usuarioA/B passados
 *      explicitamente para evitar confusão de parâmetros.
 *      Cada UPDATE usa parâmetros PDO completamente distintos.
 */
function _atualizarClassificacao(
    PDO $conn, int $emId, bool $ehIndividual,
    int $turmaA, ?int $usuarioA, int $turmaB, ?int $usuarioB,
    int $placarA, int $placarB, bool $empate,
    ?int $vencedorTurma, ?int $vencedorUsuario,
    ?int $perdedorTurma, ?int $perdedorUsuario
): void {

    if ($empate) {
        // Ambos ganham 1 ponto, 0 vitórias, 1 empate
        _updateLinha($conn, $emId, $ehIndividual, $turmaA, $usuarioA,
                     1, 0, 0, 1, $placarA, $placarB);
        _updateLinha($conn, $emId, $ehIndividual, $turmaB, $usuarioB,
                     1, 0, 0, 1, $placarB, $placarA);
    } else {
        // Determina placares do vencedor e do perdedor
        if ($vencedorTurma === $turmaA && (!$ehIndividual || $vencedorUsuario === $usuarioA)) {
            $placarVenc = $placarA;
            $placarPerd = $placarB;
        } else {
            $placarVenc = $placarB;
            $placarPerd = $placarA;
        }

        // Vencedor: 3 pts, 1 vitória
        _updateLinha($conn, $emId, $ehIndividual, $vencedorTurma, $vencedorUsuario,
                     3, 1, 0, 0, $placarVenc, $placarPerd);

        // Perdedor: 0 pts, 1 derrota
        _updateLinha($conn, $emId, $ehIndividual, $perdedorTurma, $perdedorUsuario,
                     0, 0, 1, 0, $placarPerd, $placarVenc);
    }
}

/**
 * FIX: Parâmetros PDO nomeados com prefixo único para evitar
 *      colisão quando a função é chamada duas vezes na mesma
 *      transação (PDO reutiliza statements preparados).
 */
function _updateLinha(
    PDO $conn, int $emId, bool $ehIndividual,
    ?int $turmaId, ?int $usuarioId,
    int $pontos, int $vitorias, int $derrotas, int $empates,
    int $pro, int $contra
): void {
    if ($turmaId === null) return; // segurança

    $saldo = $pro - $contra;

    if ($ehIndividual) {
        if ($usuarioId === null) return;
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
            WHERE edicao_modalidade_id      = :emid
              AND usuario_id_participante   = :uid
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
            WHERE edicao_modalidade_id    = :emid
              AND turma_id_turma          = :tid
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
 * FIX PRINCIPAL: Progressão automática no mata-mata.
 *
 * Lógica corrigida:
 *  - Busca TODAS as partidas da fase atual ordenadas por id.
 *  - Calcula em qual partida da próxima fase o vencedor deve entrar
 *    (par de partidas → 1 vaga) e qual lado (time_a ou time_b).
 *  - Se a partida da próxima fase já existe, apenas atualiza a vaga.
 *  - Se não existe, cria a partida nova.
 *  - Perdedores de semifinal → disputa 3º lugar.
 */
function _avancarMataMata(
    PDO $conn, int $emId, bool $ehIndividual,
    array $partida,
    ?int $vencedorTurma, ?int $vencedorUsuario,
    ?int $perdedorTurma, ?int $perdedorUsuario
): void {

    $fasesMap = [
        'oitavas' => 'quartas',
        'quartas' => 'semi',
        'semi'    => 'final',
    ];

    $faseAtual = $partida['fase_partida'];

    // Perdedor de semi → 3º lugar (tratado separadamente)
    if ($faseAtual === 'semi') {
        _gerarOuAtualizarTerceiro(
            $conn, $emId, $ehIndividual,
            $perdedorTurma, $perdedorUsuario
        );
    }

    if (!isset($fasesMap[$faseAtual])) {
        return; // final / terceiro_lugar não avançam
    }

    $proximaFase = $fasesMap[$faseAtual];
    $dataStr     = (new DateTime('+14 days'))->format('Y-m-d');
    $horaStr     = '08:00:00';

    // Posição da partida atual dentro de todas as da fase
    $stmtFase = $conn->prepare("
        SELECT id_partida FROM partida
        WHERE edicao_modalidade_id = :emid AND fase_partida = :fase
        ORDER BY id_partida ASC
    ");
    $stmtFase->execute([':emid' => $emId, ':fase' => $faseAtual]);
    $idsFase = $stmtFase->fetchAll(PDO::FETCH_COLUMN);

    $posicao = array_search((int)$partida['id_partida'], array_map('intval', $idsFase));
    if ($posicao === false) return;

    // Cada par de partidas (0-1, 2-3, …) gera 1 vaga na próxima fase
    $vagaIdx  = (int) floor($posicao / 2); // qual partida da próxima fase
    $ladoVaga = $posicao % 2;              // 0 = time_a, 1 = time_b

    // Busca partidas já existentes na próxima fase
    $stmtProx = $conn->prepare("
        SELECT id_partida FROM partida
        WHERE edicao_modalidade_id = :emid AND fase_partida = :fase
        ORDER BY id_partida ASC
    ");
    $stmtProx->execute([':emid' => $emId, ':fase' => $proximaFase]);
    $idsProxFase = $stmtProx->fetchAll(PDO::FETCH_COLUMN);

    if (isset($idsProxFase[$vagaIdx])) {
        // Partida da próxima fase já existe: atualiza a vaga correta
        $pid = (int) $idsProxFase[$vagaIdx];
        if ($ladoVaga === 0) {
            $sql = "UPDATE partida SET turma_id_time_a = :turma, usuario_id_time_a = :usuario WHERE id_partida = :pid";
        } else {
            $sql = "UPDATE partida SET turma_id_time_b = :turma, usuario_id_time_b = :usuario WHERE id_partida = :pid";
        }
        $conn->prepare($sql)->execute([
            ':turma'   => $vencedorTurma,
            ':usuario' => $vencedorUsuario,
            ':pid'     => $pid,
        ]);
    } else {
        // Partida da próxima fase ainda não existe: cria
        // Dependendo do lado, time_a ou time_b fica como placeholder (0)
        if ($ladoVaga === 0) {
            // Este vencedor é o time_a; time_b virá do próximo resultado
            _inserirPartida($conn, $emId, $ehIndividual,
                $vencedorTurma, $vencedorUsuario,
                0, null,
                $dataStr, $horaStr, $proximaFase);
        } else {
            // Este vencedor é o time_b; busca a partida recém-criada (time_b = 0)
            $stmtUlt = $conn->prepare("
                SELECT id_partida FROM partida
                WHERE edicao_modalidade_id = :emid
                  AND fase_partida = :fase
                  AND turma_id_time_b = 0
                ORDER BY id_partida DESC LIMIT 1
            ");
            $stmtUlt->execute([':emid' => $emId, ':fase' => $proximaFase]);
            $ultId = $stmtUlt->fetchColumn();
            if ($ultId) {
                $conn->prepare("
                    UPDATE partida SET
                        turma_id_time_b   = :turma,
                        usuario_id_time_b = :usuario
                    WHERE id_partida = :pid
                ")->execute([
                    ':turma'   => $vencedorTurma,
                    ':usuario' => $vencedorUsuario,
                    ':pid'     => (int)$ultId,
                ]);
            }
        }
    }
}

/**
 * Cria ou atualiza a partida de 3º lugar com o perdedor da semi.
 */
function _gerarOuAtualizarTerceiro(
    PDO $conn, int $emId, bool $ehIndividual,
    ?int $perdedorTurma, ?int $perdedorUsuario
): void {
    $dataStr = (new DateTime('+14 days'))->format('Y-m-d');
    $horaStr = '08:00:00';

    $stmtTer = $conn->prepare("
        SELECT id_partida FROM partida
        WHERE edicao_modalidade_id = :emid AND fase_partida = 'terceiro_lugar'
        ORDER BY id_partida ASC LIMIT 1
    ");
    $stmtTer->execute([':emid' => $emId]);
    $terceiroId = $stmtTer->fetchColumn();

    if ($terceiroId) {
        // Partida já existe: preenche o lado que ainda está vazio (turma_id = 0)
        $stmtCheck = $conn->prepare("
            SELECT turma_id_time_a, turma_id_time_b FROM partida WHERE id_partida = :pid
        ");
        $stmtCheck->execute([':pid' => (int)$terceiroId]);
        $row = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if ((int)$row['turma_id_time_a'] === 0 || $row['turma_id_time_a'] === null) {
            $conn->prepare("
                UPDATE partida SET turma_id_time_a = :turma, usuario_id_time_a = :usuario
                WHERE id_partida = :pid
            ")->execute([':turma' => $perdedorTurma, ':usuario' => $perdedorUsuario, ':pid' => (int)$terceiroId]);
        } else {
            $conn->prepare("
                UPDATE partida SET turma_id_time_b = :turma, usuario_id_time_b = :usuario
                WHERE id_partida = :pid
            ")->execute([':turma' => $perdedorTurma, ':usuario' => $perdedorUsuario, ':pid' => (int)$terceiroId]);
        }
    } else {
        // Cria a partida de 3º lugar com time_a = perdedor, time_b = 0 (virá do outro semi)
        _inserirPartida($conn, $emId, $ehIndividual,
            $perdedorTurma, $perdedorUsuario,
            0, null,
            $dataStr, $horaStr, 'terceiro_lugar');
    }
}

/**
 * Helper de INSERT de partida.
 */
function _inserirPartida(
    PDO $conn, int $emId, bool $ehIndividual,
    ?int $turmaA, ?int $usuarioA,
    int $turmaB, ?int $usuarioB,
    string $data, string $hora, string $fase
): void {
    if ($ehIndividual) {
        $conn->prepare("
            INSERT INTO partida
                (edicao_modalidade_id, turma_id_time_a, turma_id_time_b,
                 usuario_id_time_a, usuario_id_time_b,
                 data_partida, hora_partida, fase_partida, status_partida)
            VALUES (:emid, :ta, :tb, :ua, :ub, :data, :hora, :fase, 'agendada')
        ")->execute([
            ':emid' => $emId, ':ta' => $turmaA, ':tb' => $turmaB,
            ':ua'   => $usuarioA, ':ub' => $usuarioB,
            ':data' => $data, ':hora' => $hora, ':fase' => $fase,
        ]);
    } else {
        $conn->prepare("
            INSERT INTO partida
                (edicao_modalidade_id, turma_id_time_a, turma_id_time_b,
                 data_partida, hora_partida, fase_partida, status_partida)
            VALUES (:emid, :ta, :tb, :data, :hora, :fase, 'agendada')
        ")->execute([
            ':emid' => $emId, ':ta' => $turmaA, ':tb' => $turmaB,
            ':data' => $data, ':hora' => $hora, ':fase' => $fase,
        ]);
    }
}

/**
 * Verifica se todos os grupos terminaram e gera o mata-mata.
 * (lógica inalterada, apenas refatorada para usar _inserirPartida)
 */
function _tentarGerarMataMata(PDO $conn, int $emId, bool $ehIndividual): void
{
    $stmtPend = $conn->prepare("
        SELECT COUNT(*) FROM partida
        WHERE edicao_modalidade_id = :emid
          AND fase_partida = 'grupos'
          AND status_partida NOT IN ('realizada', 'wo', 'cancelada')
    ");
    $stmtPend->execute([':emid' => $emId]);
    if ((int)$stmtPend->fetchColumn() > 0) return;

    $stmtMM = $conn->prepare("
        SELECT COUNT(*) FROM partida
        WHERE edicao_modalidade_id = :emid AND fase_partida != 'grupos'
    ");
    $stmtMM->execute([':emid' => $emId]);
    if ((int)$stmtMM->fetchColumn() > 0) return;

    // Busca top 2 de cada grupo
    if ($ehIndividual) {
        $stmtCl = $conn->prepare("
            SELECT cl.grupo_classificacao,
                   cl.usuario_id_participante AS id,
                   u.nome_usuario AS nome,
                   u.turma_id_turma AS turma_id,
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
            SELECT cl.grupo_classificacao,
                   t.id_turma AS id, t.nome_turma AS nome,
                   t.id_turma AS turma_id, t.nome_turma
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

    $porGrupo = [];
    foreach ($linhas as $row) {
        $g = $row['grupo_classificacao'];
        if (!isset($porGrupo[$g])) $porGrupo[$g] = [];
        if (count($porGrupo[$g]) < 2) $porGrupo[$g][] = $row;
    }

    $classificados = [];
    $grupos = array_keys($porGrupo);
    foreach ($grupos as $g) {
        if (isset($porGrupo[$g][0])) $classificados[] = $porGrupo[$g][0];
    }
    foreach ($grupos as $g) {
        if (isset($porGrupo[$g][1])) $classificados[] = $porGrupo[$g][1];
    }
    if (count($classificados) < 2) return;

    shuffle($classificados);

    $total    = count($classificados);
    $potencia = 1; while ($potencia < $total) $potencia *= 2;
    $fase     = _faseInicialMM($potencia);
    $dataStr  = (new DateTime('+7 days'))->format('Y-m-d');
    $horaStr  = '08:00:00';

    $numByes = $potencia - $total;
    $comBye  = array_slice($classificados, 0, $numByes);
    $semBye  = array_slice($classificados, $numByes);

    for ($i = 0; $i + 1 < count($semBye); $i += 2) {
        _inserirPartida($conn, $emId, $ehIndividual,
            (int)$semBye[$i]['turma_id'],     $ehIndividual ? (int)$semBye[$i]['id']     : null,
            (int)$semBye[$i+1]['turma_id'],   $ehIndividual ? (int)$semBye[$i+1]['id']   : null,
            $dataStr, $horaStr, $fase);
    }

    // Times com BYE entram na próxima fase diretamente
    $proximaFaseBye = _proximaFase($fase);
    if (!empty($comBye) && $proximaFaseBye) {
        foreach ($comBye as $bye) {
            _inserirPartida($conn, $emId, $ehIndividual,
                (int)$bye['turma_id'], $ehIndividual ? (int)$bye['id'] : null,
                0, null,
                $dataStr, $horaStr, $proximaFaseBye);
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