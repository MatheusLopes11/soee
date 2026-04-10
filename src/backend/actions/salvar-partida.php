<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/soee/src/backend/php/include/conexao.php";

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

header("Location: /soee/src/backend/php/dashboard/dash-adm.php");
exit;