<?php
/**
 * Verifica se a fase de grupos terminou e monta o mata-mata automaticamente.
 */
function verificarEGerarMataMata(PDO $conn, int $idPartida) {
    // 1. Descobre a modalidade e se a partida atual finalizada era de grupos
    $stmt = $conn->prepare("SELECT edicao_modalidade_id, fase_partida FROM partida WHERE id_partida = :id");
    $stmt->execute([':id' => $idPartida]);
    $partidaAtual = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$partidaAtual || $partidaAtual['fase_partida'] !== 'grupos') {
        return; // Se não for jogo de grupo, ignora
    }

    $emId = $partidaAtual['edicao_modalidade_id'];

    // 2. Verifica se ainda existem jogos pendentes na fase de grupos desta modalidade
    // AJUSTE: Conta partidas que NÃO possuem um ID correspondente cadastrado na tabela de resultados
    $stmtCheck = $conn->prepare("
        SELECT em.formato_modalidade,
               (SELECT COUNT(*) 
                FROM partida p 
                LEFT JOIN resultado r ON r.partida_id_partida = p.id_partida 
                WHERE p.edicao_modalidade_id = :emid 
                  AND p.fase_partida = 'grupos' 
                  AND r.id_resultado IS NULL) as pendentes
        FROM edicao_modalidade em WHERE em.id_edicao_modalidade = :emid
    ");
    $stmtCheck->execute([':emid' => $emId]);
    $meta = $stmtCheck->fetch(PDO::FETCH_ASSOC);

    // Só prossegue se for formato misto E não houver mais jogos sem resultado no grupo
    if (!$meta || $meta['formato_modalidade'] !== 'grupos_mata_mata' || (int)$meta['pendentes'] > 0) {
        return; 
    }

    // 3. Evita duplicidade (vê se o mata-mata já foi criado antes)
    $stmtJaGerado = $conn->prepare("SELECT id_partida FROM partida WHERE edicao_modalidade_id = :emid AND fase_partida != 'grupos' LIMIT 1");
    $stmtJaGerado->execute([':emid' => $emId]);
    if ($stmtJaGerado->fetchColumn()) {
        return; 
    }

    // 4. Busca os 2 melhores de cada grupo ordenados por Pontos, Vitórias e Saldo (PostgreSQL/Supabase)
    $stmtClassificados = $conn->prepare("
        SELECT * FROM (
            SELECT c.turma_id_turma AS id, c.grupo_classificacao AS grupo,
                   ROW_NUMBER() OVER (
                       PARTITION BY c.grupo_classificacao 
                       ORDER BY c.pontos DESC, c.vitorias DESC, c.saldo DESC
                   ) as posicao
            FROM classificacao c WHERE c.edicao_modalidade_id = :emid
        ) ranked WHERE ranked.posicao <= 2
    ");
    $classificados = $stmtClassificados->fetchAll(PDO::FETCH_ASSOC);
    $stmtClassificados->execute([':emid' => $emId]);
    $classificados = $stmtClassificados->fetchAll(PDO::FETCH_ASSOC);

    // Organiza em chaves de array: $chaves['A'][1] = id_da_turma
    $chaves = [];
    foreach ($classificados as $c) {
        $chaves[$c['grupo']][$c['posicao']] = $c['id'];
    }

    $grupos = array_keys($chaves);
    sort($grupos);
    $confrontos = [];

    // 5. Cruzamento Olímpico Dinâmico (1ºA x 2ºB, 1ºB x 2ºA...)
    for ($i = 0; $i < count($grupos); $i += 2) {
        if (!isset($grupos[$i+1])) break;
        $gA = $grupos[$i]; $gB = $grupos[$i+1];

        if (isset($chaves[$gA][1], $chaves[$gB][2])) $confrontos[] = ['a' => $chaves[$gA][1], 'b' => $chaves[$gB][2]];
        if (isset($chaves[$gB][1], $chaves[$gA][2])) $confrontos[] = ['a' => $chaves[$gB][1], 'b' => $chaves[$gA][2]];
    }

    if (empty($confrontos)) return;

    // 6. Define a fase inicial pelo volume de turmas qualificadas
    $totalConfrontos = count($confrontos);
    $faseInicial = $totalConfrontos > 4 ? 'oitavas' : ($totalConfrontos > 2 ? 'quartas' : 'semi');

    // 7. Insere os novos confrontos agendados no banco para daqui a 2 dias (PostgreSQL Syntax)
    $stmtInsert = $conn->prepare("
        INSERT INTO partida (edicao_modalidade_id, turma_id_time_a, turma_id_time_b, fase_partida, status_partida, data_partida)
        VALUES (:emid, :ta, :tb, :fase, 'agendada', CURRENT_DATE + INTERVAL '2 days')
    ");

    foreach ($confrontos as $conf) {
        $stmtInsert->execute([
            ':emid' => $emId, 
            ':ta' => $conf['a'], 
            ':tb' => $conf['b'], 
            ':fase' => $faseInicial
        ]);
    }
}
?>