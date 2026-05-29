<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/soee/src/backend/includes/conexao.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // PostgreSQL: ativo_usuario é BOOLEAN → TRUE/FALSE, não 1/0
    $ativoRaw = $_POST['ativo_usuario'] ?? '0';
    $ativo    = filter_var($ativoRaw, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? (bool) $ativoRaw;

    $sql = "INSERT INTO usuario 
    (nome_usuario, email_usuario, senha_usuario, turma_id_turma, tipo_usuario, genero_usuario, ativo_usuario)
    VALUES 
    (:nome, :email, :senha, :turma, :tipo, :genero, :ativo)";

    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':nome'   => $_POST['nome_usuario'],
        ':email'  => $_POST['email_usuario'],
        ':senha'  => password_hash($_POST['senha_usuario'], PASSWORD_DEFAULT),
        ':turma'  => $_POST['turma_id_turma'] ?: null,
        ':tipo'   => $_POST['tipo_usuario'],
        ':genero' => $_POST['genero_usuario'],
        ':ativo'  => $ativo,
    ]);
}

header("Location: /soee/src/backend/php/dashboard/dash-adm.php");
exit;