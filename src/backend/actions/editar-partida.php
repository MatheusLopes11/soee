<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/soee/src/backend/includes/conexao.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/soee/src/backend/controllers/home.php';
AuthHome::exigirTipo(['professor', 'adm_geral', 'adm_sala']);

header('Content-Type: application/json');

$id    = filter_input(INPUT_POST, 'id_partida',    FILTER_VALIDATE_INT);
$data  = trim($_POST['data_partida']  ?? '');
$hora  = trim($_POST['hora_partida']  ?? '');
$local = trim($_POST['local_partida'] ?? '');

if (!$id || $id <= 0) { echo json_encode(['ok'=>false,'erro'=>'ID inválido.']); exit; }
if (!$data)           { echo json_encode(['ok'=>false,'erro'=>'Data inválida.']); exit; }
if (!$hora)           { echo json_encode(['ok'=>false,'erro'=>'Hora inválida.']); exit; }
if (strlen($hora) === 5) $hora .= ':00';

try {
    $stmt = $conn->prepare("
        UPDATE partida
        SET data_partida  = :data,
            hora_partida  = :hora,
            local_partida = :local
        WHERE id_partida  = :id
    ");
    $stmt->execute([':data'=>$data, ':hora'=>$hora, ':local'=>($local ?: null), ':id'=>$id]);
    echo json_encode(['ok'=>true, 'msg'=>'Partida atualizada.']);
} catch (PDOException $e) {
    echo json_encode(['ok'=>false, 'erro'=>$e->getMessage()]);
}