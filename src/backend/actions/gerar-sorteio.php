<?php
// ═══════════════════════════════════════════════════════════
//  gerar-sorteio.php — Gera partidas automaticamente
//  ao fechar inscrições de uma edicao_modalidade
//
//  Chamado por: professor.php (via fetch POST)
//  ou automaticamente ao mudar status para 'em_andamento'
// ═══════════════════════════════════════════════════════════
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/soee/src/backend/includes/conexao.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/soee/src/backend/controllers/home.php';

header('Content-Type: application/json');
AuthHome::exigirTipo(['professor', 'adm_geral']);

$emId   = (int) ($_POST['edicao_modalidade_id'] ?? 0);
$userId = AuthHome::getId();

if (!$emId) {
    echo json_encode(['ok' => false, 'erro' => 'ID inválido.']);
    exit;
}

// ── Verifica se já foi gerado ────────────────────────────────
$jaGerado = $conn->prepare("SELECT id FROM sorteio_gerado WHERE edicao_modalidade_id = :emid LIMIT 1");
$jaGerado->execute([':emid' => $emId]);
if ($jaGerado->fetchColumn()) {
    echo json_encode(['ok' => false, 'erro' => 'O sorteio já foi gerado para esta modalidade. Não é possível refazer.']);
    exit;
}

// ── Busca dados da edicao_modalidade e modalidade ────────────
$stmtEm = $conn->prepare("
    SELECT em.*, m.formato_modalidade, m.tipo_participacao,
           m.nome_modalidade, m.qtd_min_jogadores, m.qtd_max_jogadores
    FROM edicao_modalidade em
    INNER JOIN modalidade m ON m.id_modalidade = em.modalidade_id_modalidade
    WHERE em.id_edicao_modalidade = :emid
    LIMIT 1
");
$stmtEm->execute([':emid' => $emId]);
$em = $stmtEm->fetch(PDO::FETCH_ASSOC);

if (!$em) {
    echo json_encode(['ok' => false, 'erro' => 'Edição/modalidade não encontrada.']);
    exit;
}

$formato     = $em['formato_modalidade'];
$participacao = $em['tipo_participacao'];

// ── Busca turmas inscritas (distinct, com pelo menos 1 inscricao ativa) ──
$stmtTurmas = $conn->prepare("
    SELECT DISTINCT t.id_turma, t.nome_turma
    FROM inscricao i
    INNER JOIN usuario u ON u.id_usuario = i.usuario_id_usuario
    INNER JOIN turma t ON t.id_turma = u.turma_id_turma
    WHERE i.edicao_modalidade_id = :emid
      AND i.status_inscricao = 'ativa'
    ORDER BY RAND()
");
$stmtTurmas->execute([':emid' => $emId]);
$turmas = $stmtTurmas->fetchAll(PDO::FETCH_ASSOC);

// Para modalidades solo/dupla/trio: os "times" são os próprios alunos
// Para time: as turmas são os times
if (in_array($participacao, ['solo', 'dupla', 'trio'])) {
    // Busca inscritos individualmente
    $stmtAlunos = $conn->prepare("
        SELECT i.id_inscricao, i.usuario_id_usuario, u.nome_usuario,
               u.turma_id_turma, t.nome_turma
        FROM inscricao i
        INNER JOIN usuario u ON u.id_usuario = i.usuario_id_usuario
        INNER JOIN turma t ON t.id_turma = u.turma_id_turma
        WHERE i.edicao_modalidade_id = :emid
          AND i.status_inscricao = 'ativa'
        ORDER BY RAND()
    ");
    $stmtAlunos->execute([':emid' => $emId]);
    $inscritos = $stmtAlunos->fetchAll(PDO::FETCH_ASSOC);
    $participantes = $inscritos;
    $tipoParticipante = 'individual';
} else {
    $participantes    = $turmas;
    $tipoParticipante = 'turma';
}

$totalParticipantes = count($participantes);

if ($totalParticipantes < 2) {
    echo json_encode(['ok' => false, 'erro' => "Inscritos insuficientes ($totalParticipantes). Mínimo: 2."]);
    exit;
}

// ── Data padrão das partidas = data_fim_inscricao + 7 dias ──
$dataBase = new DateTime($em['data_fim_inscricao']);
$dataBase->modify('+7 days');

// ── GERAÇÃO DAS PARTIDAS POR FORMATO ────────────────────────
$conn->beginTransaction();
try {

    // ══ HELPER: inserir partida ══════════════════════════════
    $stmtInsert = $conn->prepare("
        INSERT INTO partida
            (edicao_modalidade_id, turma_id_time_a, turma_id_time_b,
             data_partida, hora_partida, local_partida,
             fase_partida, grupo_partida, status_partida)
        VALUES
            (:emid, :ta, :tb, :data, :hora, :local, :fase, :grupo, 'agendada')
    ");

    // ══ HELPER: inserir classificação ═══════════════════════
    $stmtClassif = $conn->prepare("
        INSERT IGNORE INTO classificacao
            (edicao_modalidade_id, turma_id_turma, grupo_classificacao)
        VALUES (:emid, :turma, :grupo)
    ");

    // ── Hora base sequencial para não bater partidas ──────────
    $horaBase = new DateTime('08:00:00');
    $intervalo = 90; // minutos entre partidas

    $partNum = 0;
    function proximaHora(DateTime $base, int &$num, int $intervalo): string {
        $copia = clone $base;
        $copia->modify('+' . ($num * $intervalo) . ' minutes');
        $num++;
        return $copia->format('H:i:s');
    }

    // ════════════════════════════════════════════════════════
    //  FORMATO: TODOS CONTRA TODOS
    // ════════════════════════════════════════════════════════
    if ($formato === 'todos_contra_todos') {
        // Registra todos na classificação (grupo único 'A')
        foreach ($participantes as $p) {
            $turmaId = $tipoParticipante === 'turma' ? $p['id_turma'] : $p['turma_id_turma'];
            $stmtClassif->execute([':emid' => $emId, ':turma' => $turmaId, ':grupo' => 'A']);
        }

        // Gera todos contra todos (round-robin)
        for ($i = 0; $i < $totalParticipantes; $i++) {
            for ($j = $i + 1; $j < $totalParticipantes; $j++) {
                $taId = $tipoParticipante === 'turma' ? $participantes[$i]['id_turma'] : $participantes[$i]['turma_id_turma'];
                $tbId = $tipoParticipante === 'turma' ? $participantes[$j]['id_turma'] : $participantes[$j]['turma_id_turma'];
                $stmtInsert->execute([
                    ':emid'  => $emId,
                    ':ta'    => $taId,
                    ':tb'    => $tbId,
                    ':data'  => $dataBase->format('Y-m-d'),
                    ':hora'  => proximaHora($horaBase, $partNum, $intervalo),
                    ':local' => null,
                    ':fase'  => 'grupos',
                    ':grupo' => 'A',
                ]);
            }
        }
    }

    // ════════════════════════════════════════════════════════
    //  FORMATO: SÓ GRUPOS
    // ════════════════════════════════════════════════════════
    elseif ($formato === 'grupos') {
        // Divide em grupos de acordo com total
        // Se <= 8 times: 2 grupos; se <= 12: 3; se > 12: 4
        $numGrupos = $totalParticipantes <= 8 ? 2 : ($totalParticipantes <= 12 ? 3 : 4);
        $letras    = ['A', 'B', 'C', 'D'];

        // Embaralha e distribui nos grupos
        shuffle($participantes);
        $gruposMontados = array_fill(0, $numGrupos, []);
        foreach ($participantes as $idx => $p) {
            $gruposMontados[$idx % $numGrupos][] = $p;
        }

        foreach ($gruposMontados as $gIdx => $grupo) {
            $letra = $letras[$gIdx];
            foreach ($grupo as $p) {
                $turmaId = $tipoParticipante === 'turma' ? $p['id_turma'] : $p['turma_id_turma'];
                $stmtClassif->execute([':emid' => $emId, ':turma' => $turmaId, ':grupo' => $letra]);
            }
            // Round-robin dentro do grupo
            $n = count($grupo);
            for ($i = 0; $i < $n; $i++) {
                for ($j = $i + 1; $j < $n; $j++) {
                    $taId = $tipoParticipante === 'turma' ? $grupo[$i]['id_turma'] : $grupo[$i]['turma_id_turma'];
                    $tbId = $tipoParticipante === 'turma' ? $grupo[$j]['id_turma'] : $grupo[$j]['turma_id_turma'];
                    $stmtInsert->execute([
                        ':emid'  => $emId,
                        ':ta'    => $taId,
                        ':tb'    => $tbId,
                        ':data'  => $dataBase->format('Y-m-d'),
                        ':hora'  => proximaHora($horaBase, $partNum, $intervalo),
                        ':local' => null,
                        ':fase'  => 'grupos',
                        ':grupo' => $letra,
                    ]);
                }
            }
        }
    }

    // ════════════════════════════════════════════════════════
    //  FORMATO: SÓ MATA-MATA
    // ════════════════════════════════════════════════════════
    elseif ($formato === 'mata_mata') {
        shuffle($participantes);
        $n = $totalParticipantes;

        // Determina fase inicial pelo nº de participantes
        if ($n <= 2)       $fase = 'final';
        elseif ($n <= 4)   $fase = 'semi';
        elseif ($n <= 8)   $fase = 'quartas';
        elseif ($n <= 16)  $fase = 'oitavas';
        else               $fase = 'oitavas'; // limita nas oitavas

        // Pares da fase inicial
        for ($i = 0; $i + 1 < $n; $i += 2) {
            $taId = $tipoParticipante === 'turma' ? $participantes[$i]['id_turma']   : $participantes[$i]['turma_id_turma'];
            $tbId = $tipoParticipante === 'turma' ? $participantes[$i+1]['id_turma'] : $participantes[$i+1]['turma_id_turma'];
            $stmtInsert->execute([
                ':emid'  => $emId,
                ':ta'    => $taId,
                ':tb'    => $tbId,
                ':data'  => $dataBase->format('Y-m-d'),
                ':hora'  => proximaHora($horaBase, $partNum, $intervalo),
                ':local' => null,
                ':fase'  => $fase,
                ':grupo' => null,
            ]);
        }
        // Se ímpar: última turma avança direto (bye)
        // (não gera partida para ela — professor agenda manualmente)
    }

    // ════════════════════════════════════════════════════════
    //  FORMATO: GRUPOS + MATA-MATA
    // ════════════════════════════════════════════════════════
    elseif ($formato === 'grupos_mata_mata') {
        $numGrupos  = $totalParticipantes <= 8 ? 2 : ($totalParticipantes <= 12 ? 3 : 4);
        $letras     = ['A', 'B', 'C', 'D'];

        shuffle($participantes);
        $gruposMontados = array_fill(0, $numGrupos, []);
        foreach ($participantes as $idx => $p) {
            $gruposMontados[$idx % $numGrupos][] = $p;
        }

        foreach ($gruposMontados as $gIdx => $grupo) {
            $letra = $letras[$gIdx];
            foreach ($grupo as $p) {
                $turmaId = $tipoParticipante === 'turma' ? $p['id_turma'] : $p['turma_id_turma'];
                $stmtClassif->execute([':emid' => $emId, ':turma' => $turmaId, ':grupo' => $letra]);
            }
            $n = count($grupo);
            for ($i = 0; $i < $n; $i++) {
                for ($j = $i + 1; $j < $n; $j++) {
                    $taId = $tipoParticipante === 'turma' ? $grupo[$i]['id_turma'] : $grupo[$i]['turma_id_turma'];
                    $tbId = $tipoParticipante === 'turma' ? $grupo[$j]['id_turma'] : $grupo[$j]['turma_id_turma'];
                    $stmtInsert->execute([
                        ':emid'  => $emId,
                        ':ta'    => $taId,
                        ':tb'    => $tbId,
                        ':data'  => $dataBase->format('Y-m-d'),
                        ':hora'  => proximaHora($horaBase, $partNum, $intervalo),
                        ':local' => null,
                        ':fase'  => 'grupos',
                        ':grupo' => $letra,
                    ]);
                }
            }
        }
        // Partidas de mata-mata são geradas depois pelo professor
        // (placeholder: inserir uma partida "semifinal" para cada par de grupos)
        $dataFase2 = clone $dataBase;
        $dataFase2->modify('+14 days');
        $partNumFase2 = 0;

        // Gera semifinais entre vencedores dos grupos (A x B, C x D etc)
        for ($i = 0; $i + 1 < $numGrupos; $i += 2) {
            // Usa turma_id = 0 como placeholder "a definir"
            // Professor substitui ao cadastrar resultado dos grupos
            // Por ora, insere somente se houver pelo menos 2 grupos
        }
        // Nota: semifinais/finais são inseridas pelo professor após fase de grupos
    }

    // ── Registra que o sorteio foi gerado ────────────────────
    $stmtReg = $conn->prepare("
        INSERT INTO sorteio_gerado (edicao_modalidade_id, gerado_por)
        VALUES (:emid, :uid)
    ");
    $stmtReg->execute([':emid' => $emId, ':uid' => $userId]);

    // ── Atualiza status para em_andamento ────────────────────
    $conn->prepare("
        UPDATE edicao_modalidade SET status_edicao_modalidade = 'em_andamento'
        WHERE id_edicao_modalidade = :emid
    ")->execute([':emid' => $emId]);

    // Atualiza também a edição pai, se ainda estiver em inscrições
    $conn->prepare("
        UPDATE edicao e
        INNER JOIN edicao_modalidade em ON em.edicao_id_edicao = e.id_edicao
        SET e.status_edicao = 'em_andamento'
        WHERE em.id_edicao_modalidade = :emid
          AND e.status_edicao = 'inscricoes'
    ")->execute([':emid' => $emId]);

    $conn->commit();

    echo json_encode([
        'ok'  => true,
        'msg' => 'Sorteio gerado com sucesso! As partidas foram criadas no banco.',
    ]);

} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode(['ok' => false, 'erro' => 'Erro ao gerar sorteio: ' . $e->getMessage()]);
}