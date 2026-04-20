<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . "/soee/src/backend/includes/conexao.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/soee/src/backend/controllers/home.php";

AuthHome::exigirTipo(['professor', 'adm_geral']);

header('Content-Type: application/json');

$id     = (int) ($_POST['id_edicao'] ?? 0);
$status = $_POST['status'] ?? '';
$validos = ['planejamento', 'inscricoes', 'em_andamento', 'encerrado'];

if (!$id || !in_array($status, $validos)) {
    echo json_encode(['ok' => false, 'erro' => 'Dados inválidos.']);
    exit;
}

try {
    $stmt = $conn->prepare("UPDATE edicao SET status_edicao = :s WHERE id_edicao = :id");
    $stmt->execute([':s' => $status, ':id' => $id]);
    echo json_encode(['ok' => true]);
} catch (Exception $e) {
    echo json_encode(['ok' => false, 'erro' => 'Erro ao atualizar no banco.']);
}