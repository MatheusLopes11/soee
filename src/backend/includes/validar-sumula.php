<?php
// actions/validar-sumula.php
// CAMINHO CORRIGIDO: era /soee/src/backend/php/include/ → /soee/src/backend/includes/
require_once $_SERVER['DOCUMENT_ROOT'] . "/soee/src/backend/includes/conexao.php";
header('Content-Type: application/json');

$id     = filter_input(INPUT_POST, 'id_sumula', FILTER_VALIDATE_INT);
$status = $_POST['status_sumula'] ?? '';

$permitidos = ['validada', 'rejeitada'];
if (!$id || !in_array($status, $permitidos)) {
    echo json_encode(['ok' => false, 'erro' => 'Parâmetros inválidos.']);
    exit;
}

$stmt = $conn->prepare("UPDATE sumula SET status_sumula = ? WHERE id_sumula = ?");
$ok   = $stmt->execute([$status, $id]);

echo json_encode(['ok' => $ok, 'erro' => $ok ? null : 'Falha ao atualizar.']);

///////////ARRUMAR SUMULA: erro - Usuário não autenticado.