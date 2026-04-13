<?php
session_start();
require_once __DIR__ . '/../includes/conexao.php';
header('Content-Type: application/json; charset=utf-8');

$userId = $_SESSION['user_id'] ?? 0;
if (!$userId) { echo json_encode(['ok' => false, 'erro' => 'Não autenticado']); exit; }

$id = intval($_POST['id_inscricao'] ?? 0);
if (!$id) { echo json_encode(['ok' => false, 'erro' => 'ID inválido']); exit; }

try {
    // Garante que só cancela inscrição do próprio usuário
    $stmt = $conn->prepare("UPDATE inscricao SET status_inscricao = 'cancelada' WHERE id_inscricao = :id AND usuario_id_usuario = :u");
    $stmt->execute([':id' => $id, ':u' => $userId]);
    echo json_encode(['ok' => true]);
} catch (PDOException $e) {
    echo json_encode(['ok' => false, 'erro' => $e->getMessage()]);
}