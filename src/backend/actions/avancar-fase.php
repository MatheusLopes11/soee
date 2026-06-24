<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/soee/src/backend/controllers/home.php';
AuthHome::exigirTipo(['professor', 'adm_geral', 'adm_sala']);
/**
 * Verifica se a fase de grupos acabou e gera o chaveamento mata-mata automaticamente.
 * * @param PDO $conn Conexão com o banco de dados
 * @param int $emId ID da edição modalidade
 */
function verificarEGerarMataMata(PDO $conn, int $emId) {
    // 1. Verificar se o formato é misto e se a fase de grupos realmente terminou
    $stmtCheck = $conn->prepare("
        SELECT em.formato_modalidade, m.tipo_participacao,
               (SELECT COUNT(*) FROM partida WHERE edicao_modalidade_id = :emid AND fase_partida = 'grupos' AND status_partida = 'agendada') as pendentes
        FROM edicao_modalidade em
        INNER JOIN modalidade m ON m.id_modalidade = em.modalidade_id_modalidade
        WHERE em.id_edicao_modalidade = :emid
    ");
    $stmtCheck->execute([':emid' => $emId]);
    $meta = $stmtCheck->fetch(PDO::FETCH_ASSOC);

    // Só avança se for formato misto E não houver mais nenhum jogo pendente na fase de grupos
    if (!$meta || $meta['formato_modalidade'] !== 'grupos_mata_mata' || (int)$meta['pendentes'] > 0) {
        return; 
    }

    // Verificar se o mata-mata já foi gerado anteriormente para evitar duplicidade
    $stmtJaGerado = $conn->prepare("SELECT id_partida FROM partida WHERE edicao_modalidade_id = :emid AND fase_partida != 'grupos' LIMIT 1");
    $stmtJaGerado->execute([':emid' => $emId]);
    if ($stmtJaGerado->fetchColumn()) {
        return; // Chaveamento já existe
    }

    $ehIndividual = in_array($meta['tipo_participacao'], ['solo', 'dupla', 'trio']);

    // 2. Buscar os classificados ordenados pelas regras do campeonato (Pontos, Vitórias, Saldo, etc.)
    // Filtra para pegar os 2 melhores de cada grupo (padrão em torneios com mata-mata sequencial)
    if ($ehIndividual) {
        $stmtClassificados = $conn->prepare("
            SELECT * FROM (
                SELECT c.usuario_id_participante AS id, c.turma_id_turma AS turma_id, c.grupo_classificacao AS grupo,
                       ROW_NUMBER() OVER (PARTITION BY c.grupo_classificacao ORDER BY c.pontos DESC, c.vitorias DESC, c.saldo DESC) as posicao
                FROM classificacao c
                WHERE c.edicao_modalidade_id = :emid
            ) ranked WHERE ranked.posicao <= 2
        ");
    } else {
        $stmtClassificados = $conn->prepare("
            SELECT * FROM (
                SELECT c.turma_id_turma AS id, c.turma_id_turma AS turma_id, c.grupo_classificacao AS grupo,
                       ROW_NUMBER() OVER (PARTITION BY c.grupo_classificacao ORDER BY c.pontos DESC, c.vitorias DESC, c.saldo DESC) as posicao
                FROM classificacao c
                WHERE c.edicao_modalidade_id = :emid
            ) ranked WHERE ranked.posicao <= 2
        ");
    }
    $stmtClassificados->execute([':emid' => $emId]);
    $classificados = $stmtClassificados->fetchAll(PDO::FETCH_ASSOC);

    // Organiza por Grupo e Posição para facilitar o cruzamento olímpico
    $chaves = [];
    foreach ($classificados as $c) {
        $chaves[$c['grupo']][$c['posicao']] = $c;
    }

    // 3. Montar o Cruzamento Olímpico Dinâmico (1ºA x 2ºB, 1ºB x 2ºA, 1ºC x 2ºD, etc.)
    $gruposDisponiveis = array_keys($chaves);
    sort($gruposDisponiveis); // Garante a ordem A, B, C, D...
    
    $confrontos = [];
    $totalGrupos = count($gruposDisponiveis);

    for ($i = 0; $i < $totalGrupos; $i += 2) {
        if (!isset($gruposDisponiveis[$i+1])) {
            // Caso o número de grupos seja ímpar (Ex: 3 grupos), o 1º do grupo restante pode pegar um BYE
            break; 
        }
        $grupoX = $gruposDisponiveis[$i];
        $grupoY = $gruposDisponiveis[$i+1];

        // Jogo 1: 1º de X contra 2º de Y
        if (isset($chaves[$grupoX][1]) && isset($chaves[$grupoY][2])) {
            $confrontos[] = ['a' => $chaves[$grupoX][1], 'b' => $chaves[$grupoY][2]];
        }
        // Jogo 2: 1º de Y contra 2º de X
        if (isset($chaves[$grupoY][1]) && isset($chaves[$grupoX][2])) {
            $confrontos[] = ['a' => $chaves[$grupoY][1], 'b' => $chaves[$grupoX][2]];
        }
    }

    if (empty($confrontos)) return;

    // 4. Definir qual será a fase inicial do mata-mata baseado na quantidade de confrontos
    $totalConfrontos = count($confrontos);
    $faseMetas = 'semi'; 
    if ($totalConfrontos > 4)  $faseMetas = 'oitavas';
    elseif ($totalConfrontos > 2) $faseMetas = 'quartas';

    // 5. Inserir no banco de dados as novas partidas geradas automaticamente
    $dataPartida = (new DateTime('+2 days'))->format('Y-m-d'); // Agenda temporariamente para 2 dias depois
    
    if ($ehIndividual) {
        $stmtInsert = $conn->prepare("
            INSERT INTO partida (edicao_modalidade_id, usuario_id_time_a, turma_id_time_a, usuario_id_time_b, turma_id_time_b, data_partida, hora_partida, fase_partida, status_partida)
            VALUES (:emid, :ua, :ta, :ub, :tb, :data, '08:00:00', :fase, 'agendada')
        ");
    } else {
        $stmtInsert = $conn->prepare("
            INSERT INTO partida (edicao_modalidade_id, turma_id_time_a, turma_id_time_b, data_partida, hora_partida, fase_partida, status_partida)
            VALUES (:emid, :ta, :tb, :data, '08:00:00', :fase, 'agendada')
        ");
    }

    foreach ($confrontos as $conf) {
        $params = [
            ':emid' => $emId,
            ':ta'   => $conf['a']['turma_id'],
            ':tb'   => $conf['b']['turma_id'],
            ':data' => $dataPartida,
            ':fase' => $faseMetas
        ];
        if ($ehIndividual) {
            $params[':ua'] = $conf['a']['id'];
            $params[':ub'] = $conf['b']['id'];
        }
        $stmtInsert->execute($params);
    }
}