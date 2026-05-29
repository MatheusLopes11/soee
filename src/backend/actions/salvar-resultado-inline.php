<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/soee/src/backend/includes/conexao.php';

header('Content-Type: application/json');

$idPartida = filter_input(INPUT_POST, 'partida_id',    FILTER_VALIDATE_INT);
$pA        = filter_input(INPUT_POST, 'placar_time_a', FILTER_VALIDATE_INT);
$pB        = filter_input(INPUT_POST, 'placar_time_b', FILTER_VALIDATE_INT);

if (!$idPartida || $idPartida <= 0)              { echo json_encode(['ok'=>false,'erro'=>'ID inválido.']); exit; }
if ($pA === false || $pA === null || $pA < 0 ||
    $pB === false || $pB === null || $pB < 0)    { echo json_encode(['ok'=>false,'erro'=>'Placares inválidos.']); exit; }

try {
    $stmtP = $conn->prepare("SELECT turma_id_time_a, turma_id_time_b FROM partida WHERE id_partida = :id");
    $stmtP->execute([':id' => $idPartida]);
    $partida = $stmtP->fetch(PDO::FETCH_ASSOC);

    if (!$partida) { echo json_encode(['ok'=>false,'erro'=>'Partida não encontrada.']); exit; }

    if      ($pA > $pB) $idVencedor = $partida['turma_id_time_a'];
    elseif  ($pB > $pA) $idVencedor = $partida['turma_id_time_b'];
    else                $idVencedor = null;

    $conn->beginTransaction();

    // PostgreSQL não tem ON DUPLICATE KEY UPDATE.
    // Usamos ON CONFLICT (coluna_unique) DO UPDATE SET ...
    // A coluna partida_id_partida tem UNIQUE no schema Supabase.
    $conn->prepare("
        INSERT INTO resultado (partida_id_partida, placar_time_a, placar_time_b, turma_id_vencedor)
        VALUES (:pid, :pA, :pB, :venc)
        ON CONFLICT (partida_id_partida) DO UPDATE SET
            placar_time_a     = EXCLUDED.placar_time_a,
            placar_time_b     = EXCLUDED.placar_time_b,
            turma_id_vencedor = EXCLUDED.turma_id_vencedor
    ")->execute([':pid'=>$idPartida, ':pA'=>$pA, ':pB'=>$pB, ':venc'=>$idVencedor]);

    $conn->prepare("UPDATE partida SET status_partida = 'realizada' WHERE id_partida = :id")
         ->execute([':id' => $idPartida]);

    $conn->commit();
    echo json_encode(['ok'=>true, 'msg'=>'Resultado salvo.']);

} catch (PDOException $e) {
    if ($conn->inTransaction()) $conn->rollBack();
    echo json_encode(['ok'=>false, 'erro'=>$e->getMessage()]);
}