<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/soee/src/backend/includes/conexao.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/soee/src/backend/controllers/home.php';

header('Content-Type: application/json');

// ── Aceita aluno E adm_sala ───────────────────────────────────────
AuthHome::exigirTipo(['aluno', 'adm_sala']);

$userId      = AuthHome::getId();
$idInscricao = (int) ($_POST['id_inscricao'] ?? 0);

if (!$idInscricao) {
    echo json_encode(['ok' => false, 'erro' => 'ID de inscrição inválido.']);
    exit;
}

// ── Verifica se a inscrição pertence ao usuário logado ────────────
$stmtCheck = $conn->prepare("
    SELECT id_inscricao FROM inscricao
    WHERE id_inscricao        = :id
      AND usuario_id_usuario  = :uid
      AND status_inscricao    = 'ativa'
    LIMIT 1
");
$stmtCheck->execute([':id' => $idInscricao, ':uid' => $userId]);

if (!$stmtCheck->fetch()) {
    echo json_encode(['ok' => false, 'erro' => 'Inscrição não encontrada ou já cancelada.']);
    exit;
}

// ── Cancela ───────────────────────────────────────────────────────
try {
    $stmtUp = $conn->prepare("
        UPDATE inscricao
        SET status_inscricao = 'cancelada'
        WHERE id_inscricao = :id
    ");
    $stmtUp->execute([':id' => $idInscricao]);

    echo json_encode(['ok' => true]);
} catch (PDOException $e) {
    echo json_encode(['ok' => false, 'erro' => 'Erro ao cancelar inscrição. Tente novamente.']);
}