<?php
// ═══════════════════════════════════════════════════════════
//  eleger-professor.php — SOEE
//  Promove um aluno/adm_sala para professor, ou rebaixa um
//  professor de volta a aluno. Apenas adm_geral pode fazer isso.
// ═══════════════════════════════════════════════════════════
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/soee/src/backend/includes/conexao.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/soee/src/backend/controllers/home.php';

header('Content-Type: application/json; charset=utf-8');
AuthHome::exigirTipo(['professor', 'adm_geral']);

$targetId = (int)($_POST['usuario_id'] ?? 0);
$acao     = trim($_POST['acao'] ?? '');  // 'promover' | 'rebaixar'
$meId     = AuthHome::getId();

if (!$targetId || !in_array($acao, ['promover', 'rebaixar'])) {
    echo json_encode(['ok' => false, 'erro' => 'Dados inválidos.']);
    exit;
}

if ($targetId === $meId) {
    echo json_encode(['ok' => false, 'erro' => 'Você não pode alterar seu próprio cargo aqui.']);
    exit;
}

// Busca usuário alvo
$stmtU = $conn->prepare("SELECT id_usuario, nome_usuario, tipo_usuario FROM usuario WHERE id_usuario = :id");
$stmtU->execute([':id' => $targetId]);
$target = $stmtU->fetch(PDO::FETCH_ASSOC);

if (!$target) {
    echo json_encode(['ok' => false, 'erro' => 'Usuário não encontrado.']);
    exit;
}

if ($acao === 'promover') {
    // Só alunos e adm_sala podem ser promovidos a professor
    if (!in_array($target['tipo_usuario'], ['aluno', 'adm_sala'])) {
        echo json_encode(['ok' => false, 'erro' => 'Este usuário já é professor ou administrador.']);
        exit;
    }
    $novoTipo = 'professor';
    $msg      = htmlspecialchars($target['nome_usuario']) . ' agora é professor.';
} else {
    // Só professores podem ser rebaixados (adm_geral é protegido)
    if ($target['tipo_usuario'] !== 'professor') {
        echo json_encode(['ok' => false, 'erro' => 'Este usuário não é professor.']);
        exit;
    }
    $novoTipo = 'aluno';
    $msg      = htmlspecialchars($target['nome_usuario']) . ' foi removido do cargo de professor.';
}

$conn->prepare("UPDATE usuario SET tipo_usuario = :tipo WHERE id_usuario = :id")
     ->execute([':tipo' => $novoTipo, ':id' => $targetId]);

echo json_encode(['ok' => true, 'msg' => $msg, 'novo_tipo' => $novoTipo]);