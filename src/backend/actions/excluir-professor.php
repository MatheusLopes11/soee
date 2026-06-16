<?php

session_start();

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../includes/conexao.php';


$idProfessor = filter_input(
    INPUT_POST,
    'id_usuario',
    FILTER_VALIDATE_INT
);


if (!$idProfessor) {

    echo json_encode([
        'success' => false,
        'message' => 'ID do professor inválido.'
    ]);

    exit;
}


try {

    $conn->beginTransaction();


    /*
    |--------------------------------------------------------------------------
    | Busca usuário
    |--------------------------------------------------------------------------
    */

    $stmt = $conn->prepare("
        SELECT 
            id_usuario,
            nome_usuario,
            tipo_usuario,
            foto_perfil_usuario
        FROM usuario
        WHERE id_usuario = ?
    ");

    $stmt->execute([$idProfessor]);

    $professor = $stmt->fetch(PDO::FETCH_ASSOC);


    if (!$professor) {

        throw new Exception(
            'Professor não encontrado.'
        );
    }



    /*
    |--------------------------------------------------------------------------
    | Protege administradores
    |--------------------------------------------------------------------------
    */

    if ($professor['tipo_usuario'] === 'adm_geral') {

        throw new Exception(
            'Administradores gerais não podem ser excluídos.'
        );
    }



    /*
    |--------------------------------------------------------------------------
    | Apaga arquivos relacionados
    |--------------------------------------------------------------------------
    */

    if (!empty($professor['foto_perfil_usuario'])) {

        $arquivo = __DIR__ .
            '/../../../' .
            $professor['foto_perfil_usuario'];


        if (file_exists($arquivo)) {

            unlink($arquivo);
        }
    }



    /*
    |--------------------------------------------------------------------------
    | Limpeza manual dos relacionamentos
    |--------------------------------------------------------------------------
    */


    // Fotos
    $stmt = $conn->prepare("
        DELETE FROM foto_perfil
        WHERE usuario_id_usuario = ?
    ");

    $stmt->execute([$idProfessor]);



    // Feedback
    $stmt = $conn->prepare("
        DELETE FROM feedback
        WHERE usuario_id_usuario = ?
    ");

    $stmt->execute([$idProfessor]);



    // Inscrições
    $stmt = $conn->prepare("
        DELETE FROM inscricao
        WHERE usuario_id_usuario = ?
    ");

    $stmt->execute([$idProfessor]);



    // Classificação individual
    $stmt = $conn->prepare("
        DELETE FROM classificacao
        WHERE usuario_id_participante = ?
    ");

    $stmt->execute([$idProfessor]);



    // Súmulas enviadas
    $stmt = $conn->prepare("
        DELETE FROM sumula
        WHERE usuario_id_enviou = ?
    ");

    $stmt->execute([$idProfessor]);



    // Resultados vencidos
    $stmt = $conn->prepare("
        DELETE FROM resultado
        WHERE usuario_id_vencedor = ?
    ");

    $stmt->execute([$idProfessor]);



    // Partidas individuais
    $stmt = $conn->prepare("
        DELETE FROM partida
        WHERE usuario_id_time_a = ?
        OR usuario_id_time_b = ?
    ");

    $stmt->execute([
        $idProfessor,
        $idProfessor
    ]);



    // Sorteios
    $stmt = $conn->prepare("
        DELETE FROM sorteio_gerado
        WHERE gerado_por = ?
    ");

    $stmt->execute([$idProfessor]);



    /*
    |--------------------------------------------------------------------------
    | Remove usuário definitivamente
    |--------------------------------------------------------------------------
    */

    $stmt = $conn->prepare("
        DELETE FROM usuario
        WHERE id_usuario = ?
    ");
    $stmt->execute([$idProfessor]);
    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' =>
            'Professor excluído completamente do sistema.'
    ]);
} catch(Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}