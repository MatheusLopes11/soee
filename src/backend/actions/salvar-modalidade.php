<?php
require_once __DIR__ . '/../includes/conexao.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /soee/src/frontend/views/dashboards/adm.php");
    exit;
}

try {

    $nome = $_POST['nome_modalidade'] ?? null;
    $tipo = $_POST['tipo_modalidade'] ?? null;
    $formato = $_POST['formato_modalidade'] ?? null;
    $participacao = $_POST['tipo_participacao'] ?? null;
    $min = $_POST['qtd_min_jogadores'] ?? null;
    $max = $_POST['qtd_max_jogadores'] ?? null;
    $descricao = $_POST['descricao_modalidade'] ?? null;

    if (!$nome || !$tipo || !$formato) {
        header("Location: /soee/src/frontend/views/dashboards/adm.php?erro=dados_incompletos");
        exit;
    }

    $sql = "INSERT INTO modalidade 
    (nome_modalidade, tipo_modalidade, formato_modalidade, tipo_participacao, qtd_min_jogadores, qtd_max_jogadores, descricao_modalidade, ativo_modalidade)
    VALUES (?, ?, ?, ?, ?, ?, ?, 1)";

    $stmt = $conn->prepare($sql);
    $stmt->execute([$nome, $tipo, $formato, $participacao, $min, $max, $descricao]);

    header("Location: /soee/src/frontend/views/dashboards/adm.php?msg=ok");
    exit;

} catch (PDOException $e) {

    header("Location: /soee/src/frontend/views/dashboards/adm.php?erro=modalidade_duplicada");
    exit;
}
?>