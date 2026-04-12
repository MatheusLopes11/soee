<?php
require_once __DIR__ . '/../include/conexao.php';
require_once __DIR__ . '/../auth/auth-home.php';

session_start();

$tipo = $_SESSION['user_tipo'] ?? null;

if (!in_array($tipo, ['professor', 'adm_geral'])) {
    http_response_code(403);
    header('Content-Type: application/json; charset=utf-8');

    echo json_encode([
        'ok' => false,
        'erro' => 'Acesso negado'
    ]);
    exit;
}

$id = intval($_POST['id_usuario'] ?? 0);

if (!$id) {
    echo json_encode(['ok' => false, 'erro' => 'ID inválido.']);
    exit;
}

try {
    $stmt = $conn->prepare("
        SELECT tipo_usuario FROM usuario
        WHERE id_usuario = :id
        AND tipo_usuario IN ('aluno', 'adm_sala')
    ");
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        echo json_encode(['ok' => false, 'erro' => 'Usuário não encontrado ou tipo inválido.']);
        exit;
    }

    $novoTipo = ($row['tipo_usuario'] === 'adm_sala') ? 'aluno' : 'adm_sala';

    $upd = $conn->prepare("UPDATE usuario SET tipo_usuario = :tipo WHERE id_usuario = :id");
    $upd->execute([':tipo' => $novoTipo, ':id' => $id]);

    echo json_encode(['ok' => true, 'novo_tipo' => $novoTipo]);

} catch (PDOException $e) {
    echo json_encode(['ok' => false, 'erro' => $e->getMessage()]);
}