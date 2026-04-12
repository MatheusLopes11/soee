<?php
// actions/excluir-registro.php
require_once $_SERVER['DOCUMENT_ROOT'] . "/soee/src/backend/php/include/conexao.php";
header('Content-Type: application/json');

$entidade = $_POST['entidade'] ?? '';
$id       = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

// Mapa entidade → tabela e coluna PK
$mapa = [
    'usuario'    => ['tabela' => 'usuario',    'pk' => 'id_usuario'],
    'turma'      => ['tabela' => 'turma',       'pk' => 'id_turma'],
    'modalidade' => ['tabela' => 'modalidade',  'pk' => 'id_modalidade'],
    'edicao'     => ['tabela' => 'edicao',      'pk' => 'id_edicao'],
    'partida'    => ['tabela' => 'partida',     'pk' => 'id_partida'],
    'resultado'  => ['tabela' => 'resultado',   'pk' => 'id_resultado'],
    'sumula'     => ['tabela' => 'sumula',      'pk' => 'id_sumula'],
];

if (!$id || !isset($mapa[$entidade])) {
    echo json_encode(['ok' => false, 'erro' => 'Entidade ou ID inválido.']);
    exit;
}

$tabela = $mapa[$entidade]['tabela'];
$pk     = $mapa[$entidade]['pk'];

$stmt = $conn->prepare("DELETE FROM `$tabela` WHERE `$pk` = ?");
$ok   = $stmt->execute([$id]);

echo json_encode(['ok' => $ok, 'erro' => $ok ? null : 'Falha ao excluir.']);