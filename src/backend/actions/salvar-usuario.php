<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/soee/src/backend/php/include/conexao.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $sql = "INSERT INTO usuario 
    (nome_usuario, email_usuario, senha_usuario, turma_id_turma, tipo_usuario, genero_usuario, ativo_usuario)
    VALUES 
    (:nome, :email, :senha, :turma, :tipo, :genero, :ativo)";

    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':nome' => $_POST['nome_usuario'],
        ':email' => $_POST['email_usuario'],
        ':senha' => password_hash($_POST['senha_usuario'], PASSWORD_DEFAULT),
        ':turma' => $_POST['turma_id_turma'] ?: null,
        ':tipo' => $_POST['tipo_usuario'],
        ':genero' => $_POST['genero_usuario'],
        ':ativo' => $_POST['ativo_usuario']
    ]);
}

header("Location: /soee/src/backend/php/dashboard/dash-adm.php");
exit;