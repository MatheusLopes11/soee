<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/soee/src/backend/includes/conexao.php";

$edicao = $_POST['edicao_id_edicao'];
$modalidade = $_POST['modalidade_id_modalidade'];
$inicio = $_POST['data_inicio_inscricao'];
$fim = $_POST['data_fim_inscricao'];

$sql = "INSERT INTO edicao_modalidade 
(edicao_id_edicao, modalidade_id_modalidade, data_inicio_inscricao, data_fim_inscricao)
VALUES 
(:edicao, :modalidade, :inicio, :fim)";

$stmt = $conn->prepare($sql);
$stmt->execute([
    ':edicao' => $edicao,
    ':modalidade' => $modalidade,
    ':inicio' => $inicio,
    ':fim' => $fim
]);

header("Location: /soee/src/frontend/views/dashboards/adm.php");
exit;