<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/conexao.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/soee/src/backend/controllers/home.php';
AuthHome::exigirTipo(['professor', 'adm_geral', 'adm_sala']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método inválido.']);
    exit;
}

$id_sumula = $_POST['id_sumula'] ?? null;

if (!$id_sumula) {
    echo json_encode(['success' => false, 'message' => 'ID da súmula não informado.']);
    exit;
}

try {
    // FIX: usa o nome de coluna correto do schema: caminho_arquivo_sumula
    $stmtFile = $conn->prepare(
        "SELECT caminho_arquivo_sumula FROM sumula WHERE id_sumula = ?"
    );
    $stmtFile->execute([$id_sumula]);
    $arq = $stmtFile->fetch(PDO::FETCH_ASSOC);

    // FIX: caminho físico = DOCUMENT_ROOT + valor do banco
    if ($arq && !empty($arq['caminho_arquivo_sumula'])) {
    $caminhoFisico = $_SERVER['DOCUMENT_ROOT'] . $arq['caminho_arquivo_sumula'];
    if (file_exists($caminhoFisico)) {
        @unlink($caminhoFisico);
    }
}

    // Remove o registro do banco
    $stmt = $conn->prepare("DELETE FROM sumula WHERE id_sumula = ?");
    $stmt->execute([$id_sumula]);

    echo json_encode(['success' => true, 'message' => 'Súmula removida com sucesso.']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}