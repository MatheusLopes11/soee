<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/soee/src/backend/includes/conexao.php";

$partida = $_POST['partida_id_partida'];
$a = (int) $_POST['placar_time_a'];
$b = (int) $_POST['placar_time_b'];
$v_manual = $_POST['turma_id_vencedor'] ?: null;

// buscar times da partida
$sql = "SELECT turma_id_time_a, turma_id_time_b FROM partida WHERE id_partida = :partida";
$stmt = $conn->prepare($sql);
$stmt->execute([':partida' => $partida]);
$times = $stmt->fetch(PDO::FETCH_ASSOC);

// lógica do vencedor
if ($v_manual) {
    $vencedor = $v_manual; // W.O. manual
} else {
    if ($a > $b) $vencedor = $times['turma_id_time_a'];
    elseif ($b > $a) $vencedor = $times['turma_id_time_b'];
    else $vencedor = null; // empate
}

// verifica se já existe resultado
$sql = "SELECT id_resultado FROM resultado WHERE partida_id_partida = :partida";
$stmt = $conn->prepare($sql);
$stmt->execute([':partida' => $partida]);
$existe = $stmt->fetch();

if ($existe) {
    // UPDATE
    $sql = "UPDATE resultado SET 
        placar_time_a = :a,
        placar_time_b = :b,
        turma_id_vencedor = :v
    WHERE partida_id_partida = :partida";

    $msg = "atualizado";
} else {
    // INSERT
    $sql = "INSERT INTO resultado 
        (partida_id_partida, placar_time_a, placar_time_b, turma_id_vencedor)
        VALUES (:partida, :a, :b, :v)";

    $msg = "criado";
}

$stmt = $conn->prepare($sql);
$stmt->execute([
    ':partida' => $partida,
    ':a' => $a,
    ':b' => $b,
    ':v' => $vencedor
]);

header("Location: /soee/src/frontend/views/dashboards/adm.php?sucesso=$msg");
exit;