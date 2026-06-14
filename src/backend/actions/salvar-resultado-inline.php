<?php
// ═══════════════════════════════════════════════════════════
//  actions/salvar-resultado-inline.php — SOEE
//  Versão AJAX (retorna JSON). Delega para resultado-motor.php
// ═══════════════════════════════════════════════════════════
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/soee/src/backend/includes/conexao.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/soee/src/backend/helpers/resultado-motor.php';

header('Content-Type: application/json; charset=utf-8');

$idPartida = filter_input(INPUT_POST, 'partida_id',    FILTER_VALIDATE_INT);
$pA        = filter_input(INPUT_POST, 'placar_time_a', FILTER_VALIDATE_INT);
$pB        = filter_input(INPUT_POST, 'placar_time_b', FILTER_VALIDATE_INT);

if (!$idPartida || $idPartida <= 0) {
    echo json_encode(['ok' => false, 'erro' => 'ID inválido.']);
    exit;
}
if ($pA === false || $pA === null || $pA < 0 ||
    $pB === false || $pB === null || $pB < 0) {
    echo json_encode(['ok' => false, 'erro' => 'Placares inválidos.']);
    exit;
}

$resultado = processarResultado($conn, [
    'partida_id' => $idPartida,
    'placar_a'   => $pA,
    'placar_b'   => $pB,
    'wo'         => false,
]);

echo json_encode($resultado, JSON_UNESCAPED_UNICODE);