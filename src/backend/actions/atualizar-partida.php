<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/soee/src/backend/controllers/home.php';
AuthHome::exigirTipo(['professor', 'adm_geral', 'adm_sala']);
function encerrarFaseDeGruposEGerarMataMata($id_modalidade, $pdo) {
    // 1. Evita duplicidade: verifica se o mata-mata já foi gerado
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM partidas WHERE id_modalidade = ? AND fase != 'grupos'");
    $stmt->execute([$id_modalidade]);
    if ($stmt->fetchColumn() > 0) {
        return "O mata-mata para esta modalidade já foi gerado anteriormente.";
    }

    // 2. Busca a classificação dos grupos
    // Usamos a mesma lógica/função que alimenta a sua view para pegar os times
    $grupos = obterClassificacaoGrupos($id_modalidade); // <-- Substitua pelo nome real da sua função que retorna os grupos

    if (empty($grupos) || count($grupos) < 2) {
        return "Dados de grupos insuficientes para gerar o chaveamento.";
    }

    $classificados = [];

    // 3. Mapeia o Top 2 de cada grupo
    foreach ($grupos as $nomeGrupo => $times) {
        if (count($times) < 2) {
            return "O Grupo $nomeGrupo não possui times suficientes classificados.";
        }
        // Salva o ID ou Nome do time (use a coluna que identifica o time na tabela de partidas, ex: 'id_time' ou 'nome_exibicao')
        $classificados[$nomeGrupo][] = $times[0]['id_time']; // 1º Lugar
        $classificados[$nomeGrupo][] = $times[1]['id_time']; // 2º Lugar
    }

    // 4. Configura o Cruzamento Olímpico (1ºA vs 2ºB e 1ºB vs 2ºA)
    // Adapte os índices de acordo com o nome dos seus grupos ('A', 'B', etc.)
    $confrontos = [
        [
            'time_a' => $classificados['A'][0], 
            'time_b' => $classificados['B'][1], 
            'fase'   => 'semi'
        ],
        [
            'time_a' => $classificados['B'][0], 
            'time_b' => $classificados['A'][1], 
            'fase'   => 'semi'
        ]
    ];

    // 5. Insere os novos jogos de Mata-Mata no banco
    // Ajuste o nome das colunas ('time_a', 'time_b', 'fase', 'status_partida') conforme seu banco
    $sqlInsert = "INSERT INTO partidas (id_modalidade, time_a, time_b, fase, status_partida, data_partida) 
                  VALUES (?, ?, ?, ?, 'agendada', CURDATE())";
    
    $stmtInsert = $pdo->prepare($sqlInsert);

    foreach ($confrontos as $jogo) {
        $stmtInsert->execute([
            $id_modalidade, 
            $jogo['time_a'], 
            $jogo['time_b'], 
            $jogo['fase']
        ]);
    }

    return true; // Sucesso
}
?>