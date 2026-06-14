<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../includes/conexao.php';
require_once __DIR__ . '/../controllers/home.php';

AuthHome::exigirTipo(['professor']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['ok' => false, 'erro' => 'Método de requisição inválido.']);
    exit;
}

$usuario_id = filter_input(INPUT_POST, 'usuario_id', FILTER_VALIDATE_INT);
$acao       = filter_input(INPUT_POST, 'acao', FILTER_DEFAULT);

if (!$usuario_id || $acao !== 'remover') {
    echo json_encode(['ok' => false, 'erro' => 'Parâmetros inválidos ou insuficientes.']);
    exit;
}

try {

    $conn->beginTransaction();

    $stmt = $conn->prepare("DELETE FROM feedback WHERE usuario_id_usuario = :uid");
    $stmt->execute([':uid' => $usuario_id]);

    $stmt = $conn->prepare("DELETE FROM foto_perfil WHERE usuario_id_usuario = :uid");
    $stmt->execute([':uid' => $usuario_id]);

    $stmt = $conn->prepare("DELETE FROM inscricao WHERE usuario_id_usuario = :uid");
    $stmt->execute([':uid' => $usuario_id]);

    $stmt = $conn->prepare("DELETE FROM sumula WHERE usuario_id_enviou = :uid");
    $stmt->execute([':uid' => $usuario_id]);

    $stmt = $conn->prepare("
        DELETE FROM usuario 
        WHERE id_usuario = :uid AND tipo_usuario IN ('aluno', 'adm_sala')
    ");
    $stmt->execute([':uid' => $usuario_id]);

    if ($stmt->rowCount() > 0) {

        $conn->commit();

        echo json_encode([
            'ok' => true,
            'msg' => 'Aluno desativado do sistema com sucesso!'
        ]);
    } else {
        $conn->rollBack();
        echo json_encode(['ok' => false, 'erro' => 'O aluno selecionado não foi localizado ou já foi removido.']);
    }

} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo json_encode(['ok' => false, 'erro' => 'Falha na varredura completa: ' . $e->getMessage()]);
}
exit;