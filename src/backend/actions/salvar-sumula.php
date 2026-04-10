<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/soee/src/backend/php/include/conexao.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $arquivo = $_FILES['arquivo_sumula'];

    if ($arquivo['error'] === 0) {

        $nome = uniqid() . "_" . $arquivo['name'];
        $caminho = $_SERVER['DOCUMENT_ROOT'] . "/soee/uploads/" . $nome;

        move_uploaded_file($arquivo['tmp_name'], $caminho);

        $sql = "INSERT INTO sumula 
        (partida_id_partida, usuario_id_enviou, nome_arquivo_sumula, tipo_arquivo_sumula, data_envio_sumula, status_sumula)
        VALUES 
        (:partida, :usuario, :nome, :tipo, NOW(), 'pendente')";

        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':partida' => $_POST['partida_id_partida'],
            ':usuario' => 1, // depois você pode pegar da sessão
            ':nome' => $nome,
            ':tipo' => pathinfo($nome, PATHINFO_EXTENSION)
        ]);
    }
}

header("Location: /soee/src/backend/php/dashboard/dash-adm.php");
exit;