<?php
session_start();
require_once __DIR__ . '/../includes/conexao.php';
header('Content-Type: application/json; charset=utf-8');

$tipo = $_SESSION['user_tipo'] ?? null;
$userId = $_SESSION['user_id'] ?? 0;

if (!in_array($tipo, ['aluno', 'adm_sala'])) {
    echo json_encode(['ok' => false, 'erro' => 'Acesso negado']); exit;
}

$emId   = intval($_POST['edicao_modalidade_id'] ?? 0);
$posicao = trim($_POST['posicao'] ?? '');
$camisa  = intval($_POST['camisa'] ?? 0) ?: null;

if (!$emId) { echo json_encode(['ok' => false, 'erro' => 'Modalidade inválida']); exit; }

try {
    // Verifica se já está inscrito
    $chk = $conn->prepare("SELECT id_inscricao FROM inscricao WHERE usuario_id_usuario = :u AND edicao_modalidade_id = :em AND status_inscricao = 'ativa'");
    $chk->execute([':u' => $userId, ':em' => $emId]);
    if ($chk->fetch()) { echo json_encode(['ok' => false, 'erro' => 'Já inscrito nesta modalidade']); exit; }

    $stmt = $conn->prepare("
        INSERT INTO inscricao (usuario_id_usuario, edicao_modalidade_id, posicao_inscricao, numero_camisa_inscricao, status_inscricao)
        VALUES (:u, :em, :pos, :camisa, 'ativa')
    ");
    $stmt->execute([':u' => $userId, ':em' => $emId, ':pos' => $posicao ?: null, ':camisa' => $camisa]);
    echo json_encode(['ok' => true]);
} catch (PDOException $e) {
    echo json_encode(['ok' => false, 'erro' => $e->getMessage()]);
}