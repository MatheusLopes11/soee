<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/soee/src/backend/includes/conexao.php";

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_POST['partida_id_partida']) || $_POST['partida_id_partida'] === '') {
        die("Partida não selecionada.");
    }

    $partida = (int) $_POST['partida_id_partida'];
    $usuario = $_SESSION['id_usuario'] ?? null;

    if (!$usuario) {
        die("Usuário não autenticado.");
    }

    if (!isset($_FILES['arquivo_sumula'])) {
        die("Nenhum arquivo enviado.");
    }

    $arquivo = $_FILES['arquivo_sumula'];

    if ($arquivo['error'] === 0) {

        // Caminho físico no servidor
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . "/soee/src/frontend/assets/sumulas/";

        // Cria a pasta se não existir
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Nome único para evitar sobrescritas
        $nome = uniqid() . "_" . basename($arquivo['name']);

        // Caminho completo do arquivo
        $caminho = $uploadDir . $nome;

        // Move arquivo
        if (!move_uploaded_file($arquivo['tmp_name'], $caminho)) {
            die("Erro ao mover arquivo.");
        }

        // Caminho relativo salvo no banco
        $caminhoBanco = "/soee/src/frontend/assets/sumulas/" . $nome;

        $sql = "INSERT INTO sumula
        (
            partida_id_partida,
            usuario_id_enviou,
            nome_arquivo_sumula,
            caminho_arquivo_sumula,
            tipo_arquivo_sumula,
            data_envio_sumula,
            status_sumula
        )
        VALUES
        (
            :partida,
            :usuario,
            :nome,
            :caminho,
            :tipo,
            NOW(),
            'pendente'
        )";

        $stmt = $conn->prepare($sql);

        if (!$stmt->execute([
            ':partida' => $partida,
            ':usuario' => $usuario,
            ':nome' => $nome,
            ':caminho' => $caminhoBanco,
            ':tipo' => pathinfo($nome, PATHINFO_EXTENSION)
        ])) {
            print_r($stmt->errorInfo());
            exit;
        }
    }
}

header("Location: /soee/src/frontend/views/dashboards/adm.php");
exit;