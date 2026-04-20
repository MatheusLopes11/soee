<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . "/soee/src/backend/includes/conexao.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/soee/src/backend/controllers/home.php";

AuthHome::exigirTipo(['adm_geral', 'professor']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $campos = ['nome_edicao', 'ano_edicao', 'data_inicio_edicao', 'status_edicao'];
    foreach ($campos as $c) {
        if (empty($_POST[$c])) {
            $_SESSION['flash_msg']  = 'Preencha todos os campos obrigatórios da edição.';
            $_SESSION['flash_tipo'] = 'erro';
            header("Location: " . $_SERVER['HTTP_REFERER']);
            exit;
        }
    }

    $sql = "INSERT INTO edicao 
        (nome_edicao, ano_edicao, data_inicio_edicao, data_fim_edicao, status_edicao, descricao_edicao)
        VALUES 
        (:nome, :ano, :inicio, :fim, :status, :descricao)";

    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':nome'      => trim($_POST['nome_edicao']),
        ':ano'       => (int) $_POST['ano_edicao'],
        ':inicio'    => $_POST['data_inicio_edicao'],
        ':fim'       => $_POST['data_fim_edicao'] ?: null,
        ':status'    => $_POST['status_edicao'],
        ':descricao' => trim($_POST['descricao_edicao'] ?? ''),
    ]);

    $_SESSION['flash_msg']  = 'Edição criada com sucesso!';
    $_SESSION['flash_tipo'] = 'sucesso';
}

$tipo_user = AuthHome::getTipo();
if ($tipo_user === 'professor') {
    header('Location: /soee/src/frontend/views/dashboards/professor.php?ok=1');
} else {
    header('Location: /soee/src/frontend/views/dashboards/adm.php');
}
exit;