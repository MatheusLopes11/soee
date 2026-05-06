<?php
session_start();
require_once __DIR__ . '/../includes/conexao.php';
require_once __DIR__ . '/../controllers/home.php';

AuthHome::exigirTipo(['professor', 'adm_geral']);

header('Content-Type: application/json');

$id = (int)($_POST['id_usuario'] ?? 0);
if (!$id) {
    echo json_encode(['ok' => false, 'msg' => 'ID inválido.']);
    exit;
}

// Busca o tipo atual do usuário
$stmt = $conn->prepare("SELECT tipo_usuario FROM usuario WHERE id_usuario = :id");
$stmt->execute([':id' => $id]);
$tipo = $stmt->fetchColumn();

if (!$tipo) {
    echo json_encode(['ok' => false, 'msg' => 'Usuário não encontrado.']);
    exit;
}

// Alterna entre aluno <-> adm_sala
$novoTipo = ($tipo === 'adm_sala') ? 'aluno' : 'adm_sala';

$upd = $conn->prepare("UPDATE usuario SET tipo_usuario = :tipo WHERE id_usuario = :id");
$upd->execute([':tipo' => $novoTipo, ':id' => $id]);

echo json_encode(['ok' => true, 'novo_tipo' => $novoTipo]);