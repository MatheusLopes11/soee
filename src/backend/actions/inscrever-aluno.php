<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/soee/src/backend/includes/conexao.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/soee/src/backend/controllers/home.php';

header('Content-Type: application/json');

AuthHome::exigirTipo(['aluno']);

$userId             = AuthHome::getId();
$edicaoModalidadeId = (int) ($_POST['edicao_modalidade_id'] ?? 0);
$nomeCamisa         = trim($_POST['nome_camisa'] ?? '');
$camisa             = isset($_POST['camisa']) && $_POST['camisa'] !== '' ? (int) $_POST['camisa'] : null;

if (!$edicaoModalidadeId) {
    echo json_encode(['ok' => false, 'erro' => 'Modalidade inválida.']);
    exit;
}

$stmtU = $conn->prepare("SELECT genero_usuario FROM usuario WHERE id_usuario = :id LIMIT 1");
$stmtU->execute([':id' => $userId]);
$usuario = $stmtU->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    echo json_encode(['ok' => false, 'erro' => 'Usuário não encontrado.']);
    exit;
}

$generoUsuario = $usuario['genero_usuario'];

$stmtMod = $conn->prepare("
    SELECT m.genero_modalidade, em.status_edicao_modalidade, e.status_edicao
    FROM edicao_modalidade em
    INNER JOIN modalidade m ON m.id_modalidade = em.modalidade_id_modalidade
    INNER JOIN edicao e ON e.id_edicao = em.edicao_id_edicao
    WHERE em.id_edicao_modalidade = :emid
    LIMIT 1
");
$stmtMod->execute([':emid' => $edicaoModalidadeId]);
$modalidade = $stmtMod->fetch(PDO::FETCH_ASSOC);

if (!$modalidade) {
    echo json_encode(['ok' => false, 'erro' => 'Modalidade não encontrada.']);
    exit;
}

if ($modalidade['status_edicao_modalidade'] !== 'inscricoes') {
    echo json_encode(['ok' => false, 'erro' => 'As inscrições para esta modalidade estão encerradas.']);
    exit;
}
if ($modalidade['status_edicao'] === 'encerrado') {
    echo json_encode(['ok' => false, 'erro' => 'Esta edição está encerrada.']);
    exit;
}

$generoModalidade = $modalidade['genero_modalidade'];

if ($generoModalidade !== 'misto') {
    $permitido = false;
    if ($generoModalidade === 'masculino' && $generoUsuario === 'm') $permitido = true;
    if ($generoModalidade === 'feminino'  && $generoUsuario === 'f') $permitido = true;

    if (!$permitido) {
        $nomeGenero = $generoModalidade === 'masculino' ? 'masculina' : 'feminina';
        echo json_encode(['ok' => false, 'erro' => "Esta modalidade é $nomeGenero. Seu perfil não permite a inscrição."]);
        exit;
    }
}

$stmtDup = $conn->prepare("
    SELECT id_inscricao FROM inscricao
    WHERE usuario_id_usuario = :uid
      AND edicao_modalidade_id = :emid
      AND status_inscricao = 'ativa'
    LIMIT 1
");
$stmtDup->execute([':uid' => $userId, ':emid' => $edicaoModalidadeId]);
if ($stmtDup->fetch()) {
    echo json_encode(['ok' => false, 'erro' => 'Você já está inscrito nesta modalidade.']);
    exit;
}

try {
    $stmtIns = $conn->prepare("
        INSERT INTO inscricao
            (usuario_id_usuario, edicao_modalidade_id, nome_camisa_inscricao, numero_camisa_inscricao)
        VALUES
            (:uid, :emid, :nome_camisa, :camisa)
    ");
    $stmtIns->execute([
        ':uid'         => $userId,
        ':emid'        => $edicaoModalidadeId,
        ':nome_camisa' => $nomeCamisa ?: null,
        ':camisa'      => $camisa,
    ]);

    echo json_encode(['ok' => true]);
} catch (PDOException $e) {
    echo json_encode(['ok' => false, 'erro' => 'Erro ao salvar inscrição. Tente novamente.']);
}