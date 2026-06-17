<?php
// ┌─────────────────────────────────────────────────────────────────┐
// │  salvar-sumula.php — SOEE                                       │
// │  Handles both INSERT (novo upload) and UPDATE (editar partida)  │
// └─────────────────────────────────────────────────────────────────┘
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/soee/src/backend/includes/conexao.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/soee/src/backend/controllers/home.php';

AuthHome::exigirTipo(['adm_geral', 'adm_sala', 'professor']);

$usuario = AuthHome::getId();
$tipo    = AuthHome::getTipo();

$redir = '/soee/src/frontend/views/dashboards/'
       . ($tipo === 'adm_geral' ? 'adm' : ($tipo === 'professor' ? 'professor' : 'adm-sala'))
       . '.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: $redir");
    exit;
}

$acao     = $_POST['acao']              ?? 'criar';  // 'criar' | 'editar'
$partida  = (int)($_POST['partida_id_partida'] ?? 0);
$idSumula = (int)($_POST['id_sumula']   ?? 0);

// ── EDITAR: apenas troca a partida vinculada ──────────────────────
if ($acao === 'editar') {
    if (!$idSumula || !$partida) {
        $_SESSION['flash_msg']  = 'Dados inválidos para editar a súmula.';
        $_SESSION['flash_tipo'] = 'erro';
        header("Location: $redir");
        exit;
    }

    $check = $conn->prepare("SELECT id_sumula FROM sumula WHERE id_sumula = :id LIMIT 1");
    $check->execute([':id' => $idSumula]);
    if (!$check->fetch()) {
        $_SESSION['flash_msg']  = 'Súmula não encontrada.';
        $_SESSION['flash_tipo'] = 'erro';
        header("Location: $redir");
        exit;
    }

    $stmt = $conn->prepare("
        UPDATE sumula
        SET partida_id_partida = :partida
        WHERE id_sumula = :id
    ");
    $stmt->execute([':partida' => $partida, ':id' => $idSumula]);

    $_SESSION['flash_msg']  = 'Súmula vinculada à partida com sucesso!';
    $_SESSION['flash_tipo'] = 'sucesso';
    header("Location: $redir");
    exit;
}

// ── CRIAR: novo upload ────────────────────────────────────────────
if (!$partida) {
    $_SESSION['flash_msg']  = 'Partida não selecionada.';
    $_SESSION['flash_tipo'] = 'erro';
    header("Location: $redir");
    exit;
}

if (empty($_FILES['arquivo_sumula']['tmp_name'])) {
    $_SESSION['flash_msg']  = 'Nenhum arquivo enviado.';
    $_SESSION['flash_tipo'] = 'erro';
    header("Location: $redir");
    exit;
}

$arquivo = $_FILES['arquivo_sumula'];

if ($arquivo['error'] !== UPLOAD_ERR_OK) {
    $_SESSION['flash_msg']  = 'Erro ao receber o arquivo (código ' . $arquivo['error'] . ').';
    $_SESSION['flash_tipo'] = 'erro';
    header("Location: $redir");
    exit;
}

// Tipos permitidos: PDF e imagens
$permitidos = ['application/pdf', 'image/jpeg', 'image/png', 'image/webp'];
$finfo      = finfo_open(FILEINFO_MIME_TYPE);
$mime       = finfo_file($finfo, $arquivo['tmp_name']);
finfo_close($finfo);

if (!in_array($mime, $permitidos)) {
    $_SESSION['flash_msg']  = 'Formato inválido. Envie PDF, JPG, PNG ou WEBP.';
    $_SESSION['flash_tipo'] = 'erro';
    header("Location: $redir");
    exit;
}

if ($arquivo['size'] > 10 * 1024 * 1024) {
    $_SESSION['flash_msg']  = 'Arquivo muito grande. Máximo: 10 MB.';
    $_SESSION['flash_tipo'] = 'erro';
    header("Location: $redir");
    exit;
}

// Diretório de upload — mesmo usado pelo editar-sumula.php
$uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/soee/src/frontend/assets/sumulas/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0775, true);
}

$ext          = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
$nome         = uniqid('sumula_') . '.' . $ext;
$caminho      = $uploadDir . $nome;
$caminhoBanco = '/soee/src/frontend/assets/sumulas/' . $nome;  // path completo salvo no banco

if (!move_uploaded_file($arquivo['tmp_name'], $caminho)) {
    $_SESSION['flash_msg']  = 'Falha ao salvar o arquivo no servidor.';
    $_SESSION['flash_tipo'] = 'erro';
    header("Location: $redir");
    exit;
}

try {
    $stmt = $conn->prepare("
        INSERT INTO sumula
            (partida_id_partida, usuario_id_enviou, nome_arquivo_sumula,
             caminho_arquivo_sumula, tipo_arquivo_sumula, status_sumula)
        VALUES
            (:partida, :usuario, :nome, :caminho, :tipo, 'pendente')
    ");
    $stmt->execute([
        ':partida'  => $partida,
        ':usuario'  => $usuario,
        ':nome'     => $nome,
        ':caminho'  => $caminhoBanco,
        ':tipo'     => $ext,
    ]);

    $_SESSION['flash_msg']  = 'Súmula enviada com sucesso!';
    $_SESSION['flash_tipo'] = 'sucesso';

} catch (PDOException $e) {
    @unlink($caminho);
    error_log('Erro ao salvar súmula: ' . $e->getMessage());
    $_SESSION['flash_msg']  = 'Erro ao registrar a súmula no banco de dados.';
    $_SESSION['flash_tipo'] = 'erro';
}

header("Location: $redir");
exit;