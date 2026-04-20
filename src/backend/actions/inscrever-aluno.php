<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/soee/src/backend/includes/conexao.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/soee/src/backend/controllers/home.php';

header('Content-Type: application/json');

// Só alunos podem se inscrever
AuthHome::exigirTipo(['aluno']);

$userId            = AuthHome::getId();
$edicaoModalidadeId = (int) ($_POST['edicao_modalidade_id'] ?? 0);
$posicao           = trim($_POST['posicao'] ?? '');
$camisa            = isset($_POST['camisa']) && $_POST['camisa'] !== '' ? (int) $_POST['camisa'] : null;

if (!$edicaoModalidadeId) {
    echo json_encode(['ok' => false, 'erro' => 'Modalidade inválida.']);
    exit;
}

// ── 1. Busca dados do usuário (gênero) ──────────────────────────
$stmtU = $conn->prepare("SELECT genero_usuario FROM usuario WHERE id_usuario = :id LIMIT 1");
$stmtU->execute([':id' => $userId]);
$usuario = $stmtU->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    echo json_encode(['ok' => false, 'erro' => 'Usuário não encontrado.']);
    exit;
}

$generoUsuario = $usuario['genero_usuario']; // 'm', 'f' ou 'n'

// ── 2. Busca gênero da modalidade e status da edição ────────────
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

// ── 3. Verifica se inscrições estão abertas ──────────────────────
if ($modalidade['status_edicao_modalidade'] !== 'inscricoes') {
    echo json_encode(['ok' => false, 'erro' => 'As inscrições para esta modalidade estão encerradas.']);
    exit;
}
if ($modalidade['status_edicao'] === 'encerrado') {
    echo json_encode(['ok' => false, 'erro' => 'Esta edição está encerrada.']);
    exit;
}

// ── 4. Validação de gênero ───────────────────────────────────────
$generoModalidade = $modalidade['genero_modalidade']; // 'masculino', 'feminino', 'misto'

if ($generoModalidade !== 'misto') {
    // Modalidade não é mista — verificar compatibilidade
    // Usuário com genero 'n' (não informado) não pode participar de modalidades restritas
    $permitido = false;

    if ($generoModalidade === 'masculino' && $generoUsuario === 'm') {
        $permitido = true;
    } elseif ($generoModalidade === 'feminino' && $generoUsuario === 'f') {
        $permitido = true;
    }

    if (!$permitido) {
        $nomeGenero = $generoModalidade === 'masculino' ? 'masculina' : 'feminina';
        echo json_encode([
            'ok'   => false,
            'erro' => "Esta modalidade é $nomeGenero. Seu perfil não permite a inscrição.",
        ]);
        exit;
    }
}

// ── 5. Verifica se já está inscrito ─────────────────────────────
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

// ── 6. Insere inscrição ──────────────────────────────────────────
try {
    $stmtIns = $conn->prepare("
        INSERT INTO inscricao
            (usuario_id_usuario, edicao_modalidade_id, posicao_inscricao, numero_camisa_inscricao)
        VALUES
            (:uid, :emid, :posicao, :camisa)
    ");
    $stmtIns->execute([
        ':uid'    => $userId,
        ':emid'   => $edicaoModalidadeId,
        ':posicao' => $posicao ?: null,
        ':camisa'  => $camisa,
    ]);

    echo json_encode(['ok' => true]);
} catch (PDOException $e) {
    echo json_encode(['ok' => false, 'erro' => 'Erro ao salvar inscrição. Tente novamente.']);
}