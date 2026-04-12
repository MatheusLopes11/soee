<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/soee/src/backend/includes/conexao.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $a = $_POST['placar_time_a'];
    $b = $_POST['placar_time_b'];
    $vencedor = $_POST['turma_id_vencedor'];

    // cálculo automático se não escolher vencedor
    if (!$vencedor) {
        if ($a > $b) $vencedor = $_POST['turma_id_time_a'] ?? null;
        elseif ($b > $a) $vencedor = $_POST['turma_id_time_b'] ?? null;
    }

    $sql = "INSERT INTO resultado 
    (partida_id_partida, placar_time_a, placar_time_b, turma_id_vencedor, observacoes_resultado)
    VALUES 
    (:partida, :a, :b, :vencedor, :obs)";

    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':partida' => $_POST['partida_id_partida'],
        ':a' => $a,
        ':b' => $b,
        ':vencedor' => $vencedor,
        ':obs' => $_POST['observacoes_resultado']
    ]);
}

header("Location: /soee/src/backend/php/dashboard/dash-adm.php");
exit;