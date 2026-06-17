<?php
// ─────────────────────────────────────────────────────────
//  inscrever.php — inscrição solo / time
//  POST: edicao_modalidade_id, nome_camisa, camisa
// ─────────────────────────────────────────────────────────
session_start();
require_once __DIR__ . '/../includes/conexao.php';
require_once __DIR__ . '/../controllers/home.php';

header('Content-Type: application/json; charset=utf-8');
AuthHome::exigirTipo(['aluno']);

$userId = AuthHome::getId();
$emId   = (int) ($_POST['edicao_modalidade_id'] ?? 0);

if (!$emId) {
    echo json_encode(['ok' => false, 'msg' => 'Modalidade inválida.']);
    exit;
}

// Verifica se já está inscrito
$stmtCheck = $conn->prepare("
    SELECT id_inscricao FROM inscricao
    WHERE usuario_id_usuario = :uid
      AND edicao_modalidade_id = :emid
      AND status_inscricao = 'ativa'
    LIMIT 1
");
$stmtCheck->execute([':uid' => $userId, ':emid' => $emId]);
if ($stmtCheck->fetchColumn()) {
    echo json_encode(['ok' => false, 'msg' => 'Você já está inscrito nesta modalidade.']);
    exit;
}

// Verifica se inscrições estão abertas
$stmtEm = $conn->prepare("
    SELECT status_edicao_modalidade, data_fim_inscricao
    FROM edicao_modalidade
    WHERE id_edicao_modalidade = :emid
    LIMIT 1
");
$stmtEm->execute([':emid' => $emId]);
$em = $stmtEm->fetch(PDO::FETCH_ASSOC);

if (!$em || $em['status_edicao_modalidade'] !== 'inscricoes') {
    echo json_encode(['ok' => false, 'msg' => 'Inscrições encerradas para esta modalidade.']);
    exit;
}
if ($em['data_fim_inscricao'] < date('Y-m-d')) {
    echo json_encode(['ok' => false, 'msg' => 'Prazo de inscrição encerrado.']);
    exit;
}

$nomeCamisa = trim($_POST['nome_camisa'] ?? '');
$numCamisa  = !empty($_POST['camisa']) ? (int) $_POST['camisa'] : null;

$stmt = $conn->prepare("
    INSERT INTO inscricao
        (usuario_id_usuario, edicao_modalidade_id,
         nome_camisa_inscricao, numero_camisa_inscricao,
         status_inscricao, data_inscricao)
    VALUES
        (:uid, :emid, :nome, :num, 'ativa', NOW())
");
$stmt->execute([
    ':uid'  => $userId,
    ':emid' => $emId,
    ':nome' => $nomeCamisa ?: null,
    ':num'  => $numCamisa,
]);

echo json_encode(['ok' => true, 'msg' => 'Inscrição realizada com sucesso!']);