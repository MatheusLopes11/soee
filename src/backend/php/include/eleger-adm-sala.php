<?php
require_once __DIR__ . '/../include/conexao.php';
require_once __DIR__ . '/../auth/auth-home.php';

AuthHome::exigirTipo(['professor']);

header('Content-Type: application/json');

$id = intval($_POST['id_usuario'] ?? 0);
if (!$id) {
    echo json_encode(['ok' => false, 'erro' => 'ID inválido.']);
    exit;
}

try {
    // Busca tipo atual
    $stmt = $conn->prepare("SELECT tipo_usuario FROM usuario WHERE id_usuario = :id");
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        echo json_encode(['ok' => false, 'erro' => 'Usuário não encontrado.']);
        exit;
    }

    // Alterna entre aluno e adm_sala
    $novoTipo = ($row['tipo_usuario'] === 'adm_sala') ? 'aluno' : 'adm_sala';

    $upd = $conn->prepare("UPDATE usuario SET tipo_usuario = :tipo WHERE id_usuario = :id");
    $upd->execute([':tipo' => $novoTipo, ':id' => $id]);

    echo json_encode(['ok' => true, 'novo_tipo' => $novoTipo]);
} catch (Exception $e) {
    echo json_encode(['ok' => false, 'erro' => $e->getMessage()]);
}