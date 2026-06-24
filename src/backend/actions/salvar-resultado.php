<?php
// ═══════════════════════════════════════════════════════════
//  actions/salvar-resultado.php — SOEE
//  Delega toda a lógica para resultado-motor.php
// ═══════════════════════════════════════════════════════════
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/soee/src/backend/includes/conexao.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/soee/src/backend/helpers/resultado-motor.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/soee/src/backend/controllers/home.php';
AuthHome::exigirTipo(['professor', 'adm_geral', 'adm_sala']);

// Suporte a W.O. manual via turma_id_vencedor
$wo = !empty($_POST['turma_id_vencedor']);

$resultado = processarResultado($conn, [
    'partida_id' => $_POST['partida_id_partida'] ?? 0,
    'placar_a'   => $_POST['placar_time_a']      ?? 0,
    'placar_b'   => $_POST['placar_time_b']      ?? 0,
    'wo'         => $wo,
]);

if ($resultado['ok']) {
    header("Location: /soee/src/frontend/views/site/classificacao.php?sucesso=registrado");
} else {
    header("Location: /soee/src/frontend/views/site/classificacao.php?erro=automacao_falhou");
}
exit;