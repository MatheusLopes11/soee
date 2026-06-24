<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/soee/src/backend/includes/conexao.php";
require_once $_SERVER['DOCUMENT_ROOT'] . '/soee/src/backend/controllers/home.php';
AuthHome::exigirTipo(['professor', 'adm_geral', 'adm_sala']);

$edicao_modalidade_id = filter_input(INPUT_POST, 'edicao_modalidade_id', FILTER_VALIDATE_INT);
$turma_a = filter_input(INPUT_POST, 'turma_id_time_a', FILTER_VALIDATE_INT);
$turma_b = filter_input(INPUT_POST, 'turma_id_time_b', FILTER_VALIDATE_INT);

if (!$edicao_modalidade_id || !$turma_a || !$turma_b) {
    header("Location: /soee/src/frontend/views/dashboards/adm.php?erro=dados_invalidos");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $sql = "INSERT INTO partida 
    (edicao_modalidade_id, turma_id_time_a, turma_id_time_b, data_partida, hora_partida, local_partida, fase_partida, status_partida)
    VALUES 
    (:em, :a, :b, :data, :hora, :local, :fase, 'agendada')";

    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':em' => $_POST['edicao_modalidade_id'],
        ':a' => $_POST['turma_id_time_a'],
        ':b' => $_POST['turma_id_time_b'],
        ':data' => $_POST['data_partida'],
        ':hora' => $_POST['hora_partida'],
        ':local' => $_POST['local_partida'],
        ':fase' => $_POST['fase_partida']
    ]);
}

header("Location: /soee/src/frontend/views/dashboards/adm.php");
exit;