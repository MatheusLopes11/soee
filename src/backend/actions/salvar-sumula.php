<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/soee/src/backend/includes/conexao.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $arquivo = $_FILES['arquivo_sumula'];

    if ($arquivo['error'] === 0) {

        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . "/soee/uploads/";

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // FILE NAME
        $nome = uniqid() . "_" . $arquivo['name'];
        $caminho = $uploadDir . $nome;

        if (!move_uploaded_file($arquivo['tmp_name'], $caminho)) {
            die("Erro ao mover arquivo.");
        }

        // PARTIDA SAFE
        $partida = (isset($_POST['partida_id_partida']) && $_POST['partida_id_partida'] !== '')
    ? (int) $_POST['partida_id_partida']
    : null;

        $sql = "INSERT INTO sumula 
                (partida_id_partida, usuario_id_enviou, nome_arquivo_sumula, caminho_arquivo_sumula, tipo_arquivo_sumula, data_envio_sumula, status_sumula)
                VALUES 
                (:partida, :usuario, :nome, :caminho, :tipo, NOW(), 'pendente')";

                $stmt = $conn->prepare($sql);

                    $stmt->execute([
                        ':partida' => $partida,
                        ':usuario' => 1,
                        ':nome' => $nome,
                        ':caminho' => '/soee/uploads/' . $nome,
                        ':tipo' => pathinfo($nome, PATHINFO_EXTENSION)
                        ]);
                    }
                }

header("Location: /soee/src/frontend/views/dashboards/adm.php");
exit;