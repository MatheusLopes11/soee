<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/soee/src/backend/includes/conexao.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (
        empty($_POST['edicao_modalidade_id']) ||
        empty($_POST['turma_id_time_a']) ||
        empty($_POST['turma_id_time_b'])
    ) {
        header("Location: /soee/src/frontend/views/dashboards/adm.php?erro=dados_invalidos");
        exit;
    }

    $sql = "INSERT INTO partida 
    (edicao_modalidade_id, turma_id_time_a, turma_id_time_b, data_partida, hora_partida, local_partida, fase_partida, status_partida)
    VALUES 
    (:em, :a, :b, :data, :hora, :local, :fase, 'agendada')";

    $stmt = $conn->prepare($sql);

    $stmt->execute([
        ':em' => (int) $_POST['edicao_modalidade_id'],
        ':a' => (int) $_POST['turma_id_time_a'],
        ':b' => (int) $_POST['turma_id_time_b'],
        ':data' => $_POST['data_partida'],
        ':hora' => $_POST['hora_partida'],
        ':local' => $_POST['local_partida'] ?? null,
        ':fase' => $_POST['fase_partida']
    ]);
}

header("Location: /soee/src/frontend/views/dashboards/adm.php?ok=partida");
exit;