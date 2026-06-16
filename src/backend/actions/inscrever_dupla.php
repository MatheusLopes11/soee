<?php
// ═══════════════════════════════════════════════════════
//  actions/inscrever_dupla.php  —  SOEE
//  Inscreve o aluno logado + parceiro(s) como uma dupla/trio.
//  Chamado via fetch() POST pelo aluno.js
// ═══════════════════════════════════════════════════════

session_start();
require_once __DIR__ . '/../../../../backend/includes/conexao.php';
require_once __DIR__ . '/../../../../backend/controllers/home.php';

header('Content-Type: application/json');

AuthHome::exigirTipo(['aluno']);

$userId = AuthHome::getId();

// ── Entrada ──────────────────────────────────────────
$emId          = (int)   ($_POST['edicao_modalidade_id'] ?? 0);
$parceiro1Id   = (int)   ($_POST['parceiro1_id']         ?? 0);
$parceiro2Id   = (int)   ($_POST['parceiro2_id']         ?? 0); // só para trio
$nomeCamisa    = trim(   $_POST['nome_camisa']            ?? '');
$numCamisa     = !empty($_POST['camisa']) ? (int)$_POST['camisa'] : null;
$nomeCamisa1   = trim(   $_POST['nome_camisa_p1']         ?? '');
$numCamisa1    = !empty($_POST['camisa_p1']) ? (int)$_POST['camisa_p1'] : null;
$nomeCamisa2   = trim(   $_POST['nome_camisa_p2']         ?? '');
$numCamisa2    = !empty($_POST['camisa_p2']) ? (int)$_POST['camisa_p2'] : null;

function jsonErro(string $msg): void {
    echo json_encode(['ok' => false, 'msg' => $msg]);
    exit;
}
function jsonOk(string $msg): void {
    echo json_encode(['ok' => true, 'msg' => $msg]);
    exit;
}

if (!$emId) jsonErro('Modalidade inválida.');

// ── Busca dados da modalidade ────────────────────────
$stmtMod = $conn->prepare("
    SELECT m.tipo_participacao, em.status_edicao_modalidade,
           em.data_fim_inscricao
    FROM edicao_modalidade em
    INNER JOIN modalidade m ON m.id_modalidade = em.modalidade_id_modalidade
    WHERE em.id_edicao_modalidade = :emId
");
$stmtMod->execute([':emId' => $emId]);
$mod = $stmtMod->fetch(PDO::FETCH_ASSOC);

if (!$mod) jsonErro('Modalidade não encontrada.');
if ($mod['status_edicao_modalidade'] !== 'inscricoes') jsonErro('Inscrições encerradas para esta modalidade.');
if (strtotime($mod['data_fim_inscricao']) < strtotime('today')) jsonErro('Prazo de inscrições encerrado.');

$tipo = $mod['tipo_participacao']; // 'dupla' | 'trio'

// ── Valida parceiros ─────────────────────────────────
if ($tipo === 'dupla') {
    if (!$parceiro1Id) jsonErro('Informe o parceiro da dupla.');
    if ($parceiro1Id === $userId) jsonErro('Você não pode ser seu próprio parceiro.');
    $membros = [$userId, $parceiro1Id];
} elseif ($tipo === 'trio') {
    if (!$parceiro1Id || !$parceiro2Id) jsonErro('Informe os dois parceiros do trio.');
    if ($parceiro1Id === $userId || $parceiro2Id === $userId) jsonErro('Você não pode ser seu próprio parceiro.');
    if ($parceiro1Id === $parceiro2Id) jsonErro('Os parceiros devem ser pessoas diferentes.');
    $membros = [$userId, $parceiro1Id, $parceiro2Id];
} else {
    // solo ou time — usa o endpoint original
    jsonErro('Use o endpoint padrão para modalidades individuais ou por time.');
}

// ── Verifica se algum membro já está inscrito ────────
$placeholders = implode(',', array_fill(0, count($membros), '?'));
$stmtJa = $conn->prepare("
    SELECT usuario_id_usuario FROM inscricao
    WHERE edicao_modalidade_id = ?
      AND usuario_id_usuario IN ($placeholders)
      AND status_inscricao = 'ativa'
");
$stmtJa->execute(array_merge([$emId], $membros));
$jaInscritos = $stmtJa->fetchAll(PDO::FETCH_COLUMN);

if (!empty($jaInscritos)) {
    // Busca nomes para mensagem de erro
    $stmtNomes = $conn->prepare("SELECT nome_usuario FROM usuario WHERE id_usuario IN ($placeholders)");
    $stmtNomes->execute($jaInscritos);
    $nomes = $stmtNomes->fetchAll(PDO::FETCH_COLUMN);
    jsonErro(implode(', ', $nomes) . ' já está inscrito nesta modalidade.');
}

// ── Verifica se os parceiros existem e são alunos ────
$stmtCheck = $conn->prepare("
    SELECT id_usuario, nome_usuario FROM usuario
    WHERE id_usuario IN ($placeholders)
      AND tipo_usuario IN ('aluno','adm_sala')
      AND ativo_usuario = TRUE
");
$stmtCheck->execute($membros);
$encontrados = $stmtCheck->fetchAll(PDO::FETCH_COLUMN);
if (count($encontrados) !== count($membros)) {
    jsonErro('Um ou mais membros não foram encontrados ou não são alunos ativos.');
}

// ── Gera UUID para o grupo da dupla/trio ─────────────
$grupoDuplaId = sprintf(
    '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
    mt_rand(0, 0xffff), mt_rand(0, 0xffff),
    mt_rand(0, 0xffff),
    mt_rand(0, 0x0fff) | 0x4000,
    mt_rand(0, 0x3fff) | 0x8000,
    mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
);

// ── Dados de cada membro [userId, nomeCamisa, numCamisa] ──
$dadosMembros = [
    [$userId,     $nomeCamisa,  $numCamisa,  true],  // capitão = quem se inscreveu
    [$parceiro1Id,$nomeCamisa1, $numCamisa1, false],
];
if ($tipo === 'trio') {
    $dadosMembros[] = [$parceiro2Id, $nomeCamisa2, $numCamisa2, false];
}

// ── Insere no banco ───────────────────────────────────
try {
    $conn->beginTransaction();

    $stmtIns = $conn->prepare("
        INSERT INTO inscricao
            (usuario_id_usuario, edicao_modalidade_id,
             nome_camisa_inscricao, numero_camisa_inscricao,
             capitao_inscricao, status_inscricao, grupo_dupla_id)
        VALUES
            (:uid, :emId, :nomeCamisa, :numCamisa, :capitao, 'ativa', :grupoDuplaId)
    ");

    foreach ($dadosMembros as [$uid, $nc, $num, $cap]) {
        $stmtIns->execute([
            ':uid'          => $uid,
            ':emId'         => $emId,
            ':nomeCamisa'   => $nc ?: null,
            ':numCamisa'    => $num ?: null,
            ':capitao'      => $cap ? 'TRUE' : 'FALSE',
            ':grupoDuplaId' => $grupoDuplaId,
        ]);
    }

    // ── Salva nome de camisa na sessão (só do usuário logado) ──
    if ($nomeCamisa) {
        $_SESSION['nome_camisa_salvo'] = $nomeCamisa;
    }

    $conn->commit();
    jsonOk('Dupla inscrita com sucesso!');

} catch (Exception $e) {
    $conn->rollBack();
    jsonErro('Erro ao salvar inscrição: ' . $e->getMessage());
}
