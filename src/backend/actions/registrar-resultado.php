<?php
// ═══════════════════════════════════════════════════════════
//  actions/registrar-resultado.php — SOEE
//  Delega toda a lógica para resultado-motor.php
// ═══════════════════════════════════════════════════════════
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/soee/src/backend/includes/conexao.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/soee/src/backend/controllers/home.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/soee/src/backend/helpers/resultado-motor.php';

header('Content-Type: application/json; charset=utf-8');
AuthHome::exigirTipo(['professor', 'adm_geral']);

$resultado = processarResultado($conn, [
    'partida_id' => $_POST['partida_id']  ?? 0,
    'placar_a'   => $_POST['placar_a']    ?? 0,
    'placar_b'   => $_POST['placar_b']    ?? 0,
    'wo'         => $_POST['wo']          ?? false,
]);

echo json_encode($resultado, JSON_UNESCAPED_UNICODE);