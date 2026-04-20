<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . "/soee/src/backend/includes/conexao.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/soee/src/backend/controllers/home.php";

AuthHome::exigirTipo(['adm_geral', 'professor', 'adm_sala']);

$campos = ['edicao_id_edicao', 'modalidade_id_modalidade', 'data_inicio_inscricao', 'data_fim_inscricao'];
foreach ($campos as $c) {
    if (empty($_POST[$c])) {
        $_SESSION['flash_msg']  = 'Preencha todos os campos obrigatórios para vincular a edição.';
        $_SESSION['flash_tipo'] = 'erro';
        $tipo = AuthHome::getTipo();
        $dest = $tipo === 'adm_sala'
            ? '/soee/src/frontend/views/dashboards/adm-sala.php'
            : '/soee/src/frontend/views/dashboards/professor.php';
        header("Location: $dest");
        exit;
    }
}

$edicao      = (int) $_POST['edicao_id_edicao'];
$modalidade  = (int) $_POST['modalidade_id_modalidade'];
$inicio      = $_POST['data_inicio_inscricao'];
$fim         = $_POST['data_fim_inscricao'];

$stmtStatus = $conn->prepare("SELECT status_edicao FROM edicao WHERE id_edicao = :e LIMIT 1");
$stmtStatus->execute([':e' => $edicao]);
$statusEdicao = $stmtStatus->fetchColumn();
$mapaStatus = [
    'inscricoes'   => 'inscricoes',
    'em_andamento' => 'em_andamento',
    'planejamento' => 'inscricoes',
    'encerrado'    => 'encerrado',
];
$status = $mapaStatus[$statusEdicao] ?? 'inscricoes';

// Verificar se já existe o vínculo
$check = $conn->prepare("
    SELECT id_edicao_modalidade FROM edicao_modalidade
    WHERE edicao_id_edicao = :e AND modalidade_id_modalidade = :m
    LIMIT 1
");
$check->execute([':e' => $edicao, ':m' => $modalidade]);
if ($check->fetchColumn()) {
    $_SESSION['flash_msg']  = 'Esta modalidade já está vinculada a essa edição.';
    $_SESSION['flash_tipo'] = 'erro';
} else {
    $sql = "INSERT INTO edicao_modalidade 
        (edicao_id_edicao, modalidade_id_modalidade, data_inicio_inscricao, data_fim_inscricao, status_edicao_modalidade)
        VALUES (:edicao, :modalidade, :inicio, :fim, :status)";

    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':edicao'     => $edicao,
        ':modalidade' => $modalidade,
        ':inicio'     => $inicio,
        ':fim'        => $fim,
        ':status'     => $status,
    ]);

    $_SESSION['flash_msg']  = 'Modalidade vinculada à edição com sucesso!';
    $_SESSION['flash_tipo'] = 'sucesso';
}

$tipo = AuthHome::getTipo();
if ($tipo === 'adm_sala') {
    header("Location: /soee/src/frontend/views/dashboards/adm-sala.php");
} elseif ($tipo === 'professor') {
    header("Location: /soee/src/frontend/views/dashboards/professor.php?ok=1");
} else {
    header("Location: /soee/src/frontend/views/dashboards/adm.php");
}
exit;