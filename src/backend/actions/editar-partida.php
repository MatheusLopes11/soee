<?php
/**
 * editar-partida.php
 * Atualiza data, hora e local de uma partida existente.
 * Método: POST
 * Campos: id_partida, data_partida, hora_partida, local_partida
 */

session_start();
require_once __DIR__ . '/../../../includes/conexao.php';
require_once __DIR__ . '/../../../controllers/home.php';

header('Content-Type: application/json');

AuthHome::exigirTipo(['professor', 'adm_geral']);

$id    = filter_input(INPUT_POST, 'id_partida',    FILTER_VALIDATE_INT);
$data  = trim($_POST['data_partida']  ?? '');
$hora  = trim($_POST['hora_partida']  ?? '');
$local = trim($_POST['local_partida'] ?? '');

/* ── Validações básicas ── */
if (!$id || $id <= 0) {
    echo json_encode(['ok' => false, 'erro' => 'ID de partida inválido.']);
    exit;
}

if (!$data || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data)) {
    echo json_encode(['ok' => false, 'erro' => 'Data inválida.']);
    exit;
}

if (!$hora || !preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $hora)) {
    echo json_encode(['ok' => false, 'erro' => 'Hora inválida.']);
    exit;
}

/* Normaliza hora para HH:MM:SS */
if (strlen($hora) === 5) $hora .= ':00';

try {
    $stmt = $conn->prepare("
        UPDATE partida
        SET data_partida  = :data,
            hora_partida  = :hora,
            local_partida = :local
        WHERE id_partida  = :id
    ");
    $stmt->execute([
        ':data'  => $data,
        ':hora'  => $hora,
        ':local' => $local ?: null,
        ':id'    => $id,
    ]);

    if ($stmt->rowCount() === 0) {
        echo json_encode(['ok' => false, 'erro' => 'Partida não encontrada ou nada foi alterado.']);
        exit;
    }

    echo json_encode(['ok' => true, 'msg' => 'Partida atualizada com sucesso.']);

} catch (PDOException $e) {
    error_log('editar-partida: ' . $e->getMessage());
    echo json_encode(['ok' => false, 'erro' => 'Erro interno ao salvar.']);
}