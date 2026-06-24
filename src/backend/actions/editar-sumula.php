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

$id_sumula     = $_POST['id_sumula']          ?? null;
$id_partida    = $_POST['partida_id_partida'] ?? null;
$status_sumula = $_POST['status_sumula']      ?? 'pendente';

if (!$id_sumula || !$id_partida) {
    echo json_encode(['success' => false, 'message' => 'Parâmetros insuficientes.']);
    exit;
}

$statusPermitidos = ['pendente', 'validada', 'rejeitada'];
if (!in_array($status_sumula, $statusPermitidos)) {
    echo json_encode(['success' => false, 'message' => 'Status inválido.']);
    exit;
}

try {
    // Se foi enviado um novo arquivo, substitui o anterior
    if (
        isset($_FILES['arquivo_sumula']) &&
        $_FILES['arquivo_sumula']['error'] === UPLOAD_ERR_OK
    ) {
        $file = $_FILES['arquivo_sumula'];
        $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        $extsPermitidas = ['pdf', 'jpg', 'jpeg', 'png'];
        if (!in_array($ext, $extsPermitidas)) {
            echo json_encode(['success' => false, 'message' => 'Extensão de arquivo não permitida.']);
            exit;
        }

        // Mesmo diretório que o salvar-sumula.php usa
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/soee/src/frontend/assets/sumulas/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }

        // Apaga o arquivo físico anterior, se existir
        $stmtAntigo = $conn->prepare(
            "SELECT caminho_arquivo_sumula FROM sumula WHERE id_sumula = ?"
        );
        $stmtAntigo->execute([$id_sumula]);
        $caminhoAntigo = $stmtAntigo->fetchColumn();

        if ($caminhoAntigo) {
            $arquivoFisico = $_SERVER['DOCUMENT_ROOT'] . $caminhoAntigo;
            if (file_exists($arquivoFisico)) {
                @unlink($arquivoFisico);
            }
        }

        // Salva o novo arquivo
        $novo_nome    = uniqid('sumula_') . '.' . $ext;
        $nome_original = basename($file['name']);
        $caminhoBanco = '/soee/src/frontend/assets/sumulas/' . $novo_nome;

        if (!move_uploaded_file($file['tmp_name'], $uploadDir . $novo_nome)) {
            echo json_encode(['success' => false, 'message' => 'Falha ao mover o arquivo enviado.']);
            exit;
        }

        $stmt = $conn->prepare("
            UPDATE sumula
            SET partida_id_partida     = ?,
                nome_arquivo_sumula    = ?,
                caminho_arquivo_sumula = ?,
                tipo_arquivo_sumula    = ?,
                status_sumula          = ?
            WHERE id_sumula = ?
        ");
        $stmt->execute([
            $id_partida,
            $nome_original,
            $caminhoBanco,  // path completo, igual ao salvar-sumula.php
            $ext,
            $status_sumula,
            $id_sumula,
        ]);

    } else {
        // Sem novo arquivo: atualiza apenas partida e status
        $stmt = $conn->prepare("
            UPDATE sumula
            SET partida_id_partida = ?,
                status_sumula      = ?
            WHERE id_sumula = ?
        ");
        $stmt->execute([$id_partida, $status_sumula, $id_sumula]);
    }

    echo json_encode(['success' => true, 'message' => 'Súmula atualizada com sucesso.']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}