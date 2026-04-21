<?php
/**
 * salvar-resultado-inline.php
 * Registra (ou atualiza) o resultado de uma partida e calcula
 * o vencedor automaticamente com base nos placares.
 *
 * Método: POST
 * Campos: partida_id, placar_time_a, placar_time_b
 */

session_start();
require_once __DIR__ . '/../../../includes/conexao.php';
require_once __DIR__ . '/../../../controllers/home.php';

header('Content-Type: application/json');

AuthHome::exigirTipo(['professor', 'adm_geral']);

$idPartida = filter_input(INPUT_POST, 'partida_id',    FILTER_VALIDATE_INT);
$pA        = filter_input(INPUT_POST, 'placar_time_a', FILTER_VALIDATE_INT);
$pB        = filter_input(INPUT_POST, 'placar_time_b', FILTER_VALIDATE_INT);

/* ── Validações ── */
if (!$idPartida || $idPartida <= 0) {
    echo json_encode(['ok' => false, 'erro' => 'ID de partida inválido.']);
    exit;
}
if ($pA === false || $pA === null || $pA < 0 ||
    $pB === false || $pB === null || $pB < 0) {
    echo json_encode(['ok' => false, 'erro' => 'Placares inválidos.']);
    exit;
}

try {
    /* Busca a partida para saber os times */
    $stmtP = $conn->prepare("
        SELECT turma_id_time_a, turma_id_time_b, status_partida
        FROM partida
        WHERE id_partida = :id
    ");
    $stmtP->execute([':id' => $idPartida]);
    $partida = $stmtP->fetch(PDO::FETCH_ASSOC);

    if (!$partida) {
        echo json_encode(['ok' => false, 'erro' => 'Partida não encontrada.']);
        exit;
    }

    /* ── Calcula vencedor ── */
    $idVencedor = null;
    if ($pA > $pB) {
        $idVencedor = $partida['turma_id_time_a'];
    } elseif ($pB > $pA) {
        $idVencedor = $partida['turma_id_time_b'];
    }
    /* Empate → vencedor NULL */

    $conn->beginTransaction();

    /* Upsert no resultado */
    $stmtR = $conn->prepare("
        INSERT INTO resultado
            (partida_id_partida, placar_time_a, placar_time_b, turma_id_vencedor)
        VALUES
            (:pid, :pA, :pB, :venc)
        ON DUPLICATE KEY UPDATE
            placar_time_a    = VALUES(placar_time_a),
            placar_time_b    = VALUES(placar_time_b),
            turma_id_vencedor = VALUES(turma_id_vencedor)
    ");
    $stmtR->execute([
        ':pid'  => $idPartida,
        ':pA'   => $pA,
        ':pB'   => $pB,
        ':venc' => $idVencedor,
    ]);

    /* Marca a partida como realizada */
    $conn->prepare("
        UPDATE partida SET status_partida = 'realizada' WHERE id_partida = :id
    ")->execute([':id' => $idPartida]);

    $conn->commit();

    echo json_encode([
        'ok'        => true,
        'msg'       => 'Resultado salvo com sucesso.',
        'vencedor'  => $idVencedor,
        'placar_a'  => $pA,
        'placar_b'  => $pB,
    ]);

} catch (PDOException $e) {
    if ($conn->inTransaction()) $conn->rollBack();
    error_log('salvar-resultado-inline: ' . $e->getMessage());
    echo json_encode(['ok' => false, 'erro' => 'Erro interno ao salvar resultado.']);
}