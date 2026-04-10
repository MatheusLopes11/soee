<?php
require_once __DIR__ . '/../php/include/conexao.php';

if (isset($_GET['tipo']) && isset($_GET['id'])) {

    $tipo = $_GET['tipo'];
    $id   = $_GET['id'];

    $tabelas = [
        'modalidade' => 'modalidade',
        'edicao'     => 'edicao',
        'partida'    => 'partida',
        'resultado'  => 'resultado',
        'sumula'     => 'sumula'
    ];

    if (array_key_exists($tipo, $tabelas)) {
        $sql = "DELETE FROM {$tabelas[$tipo]} WHERE id_$tipo = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$id]);
    }
}

header("Location: /soee/src/backend/php/dashboard/dash-adm.php");
exit;