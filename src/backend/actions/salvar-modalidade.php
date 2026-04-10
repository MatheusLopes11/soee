<?php
require_once __DIR__ . '/../php/include/conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome_modalidade'];
    $tipo = $_POST['tipo_modalidade'];
    $formato = $_POST['formato_modalidade'];
    $participacao = $_POST['tipo_participacao'];
    $min = $_POST['qtd_min_jogadores'];
    $max = $_POST['qtd_max_jogadores'];
    $descricao = $_POST['descricao_modalidade'];

    $sql = "INSERT INTO modalidade 
    (nome_modalidade, tipo_modalidade, formato_modalidade, tipo_participacao, qtd_min_jogadores, qtd_max_jogadores, descricao_modalidade, ativo_modalidade)
    VALUES (?, ?, ?, ?, ?, ?, ?, 1)";

    $stmt = $conn->prepare($sql);
    $stmt->execute([$nome, $tipo, $formato, $participacao, $min, $max, $descricao]);
}

header("Location: /soee/src/backend/php/dashboard/dash-adm.php");
exit;