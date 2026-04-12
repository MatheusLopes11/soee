<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/soee/src/backend/includes/conexao.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $sql = "INSERT INTO turma 
    (nome_turma, curso_id_curso, ano_serie_turma, ano_letivo_turma, periodo_turma)
    VALUES 
    (:nome, :curso, :serie, :ano, :periodo)";

    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':nome' => $_POST['nome_turma'],
        ':curso' => $_POST['curso_id_curso'],
        ':serie' => $_POST['ano_serie_turma'],
        ':ano' => $_POST['ano_letivo_turma'],
        ':periodo' => $_POST['periodo_turma']
    ]);
}

header("Location: /soee/src/backend/php/dashboard/dash-adm.php");
exit;