<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/soee/src/backend/php/include/conexao.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $sql = "INSERT INTO edicao 
    (nome_edicao, ano_edicao, data_inicio_edicao, data_fim_edicao, status_edicao, descricao_edicao)
    VALUES 
    (:nome, :ano, :inicio, :fim, :status, :descricao)";

    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':nome' => $_POST['nome_edicao'],
        ':ano' => $_POST['ano_edicao'],
        ':inicio' => $_POST['data_inicio_edicao'],
        ':fim' => $_POST['data_fim_edicao'] ?: null,
        ':status' => $_POST['status_edicao'],
        ':descricao' => $_POST['descricao_edicao']
    ]);
}

header("Location: /soee/src/backend/php/dashboard/dash-adm.php");
exit;