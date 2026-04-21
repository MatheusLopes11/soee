<?php
// ═══════════════════════════════════════════════════════════
<<<<<<< HEAD
//  gerar-sorteio.php — SOEE
//  Gera partidas aleatórias baseadas nas turmas inscritas
//  POST: edicao_modalidade_id
//  Retorna: JSON {ok, msg, partidas[]}
=======
//  gerar-sorteio.php — Gera partidas automaticamente
//  ao fechar inscrições de uma edicao_modalidade
//
//  Chamado por: professor.php (via fetch POST)
//  ou automaticamente ao mudar status para 'em_andamento'
>>>>>>> 2bcbb5a3bf459c76ddd4add567cd304c16ea8994
// ═══════════════════════════════════════════════════════════
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/soee/src/backend/includes/conexao.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/soee/src/backend/controllers/home.php';

<<<<<<< HEAD
header('Content-Type: application/json; charset=utf-8');
=======
header('Content-Type: application/json');
>>>>>>> 2bcbb5a3bf459c76ddd4add567cd304c16ea8994
AuthHome::exigirTipo(['professor', 'adm_geral']);

$emId   = (int) ($_POST['edicao_modalidade_id'] ?? 0);
$userId = AuthHome::getId();

if (!$emId) {
    echo json_encode(['ok' => false, 'erro' => 'ID inválido.']);
    exit;
}

<<<<<<< HEAD
// ── Verifica se já foi gerado ────────────────────────────
$jaGerado = $conn->prepare("
    SELECT id FROM sorteio_gerado
    WHERE edicao_modalidade_id = :emid LIMIT 1
");
$jaGerado->execute([':emid' => $emId]);
if ($jaGerado->fetchColumn()) {
    echo json_encode(['ok' => false, 'erro' => 'O sorteio já foi gerado para esta modalidade. Para refazer, exclua as partidas manualmente no banco.']);
    exit;
}

// ── Dados da edicao_modalidade ───────────────────────────
$stmtEm = $conn->prepare("
    SELECT em.id_edicao_modalidade, em.edicao_id_edicao,
           m.formato_modalidade, m.tipo_participacao, m.nome_modalidade
=======
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
>>>>>>> 2bcbb5a3bf459c76ddd4add567cd304c16ea8994
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

<<<<<<< HEAD
$formato      = $em['formato_modalidade'];
$participacao = $em['tipo_participacao'];

// ── Turmas com pelo menos 1 aluno inscrito ───────────────
=======
$formato     = $em['formato_modalidade'];
$participacao = $em['tipo_participacao'];

// ── Busca turmas inscritas (distinct, com pelo menos 1 inscricao ativa) ──
>>>>>>> 2bcbb5a3bf459c76ddd4add567cd304c16ea8994
$stmtTurmas = $conn->prepare("
    SELECT DISTINCT t.id_turma, t.nome_turma
    FROM inscricao i
    INNER JOIN usuario u ON u.id_usuario = i.usuario_id_usuario
    INNER JOIN turma t ON t.id_turma = u.turma_id_turma
    WHERE i.edicao_modalidade_id = :emid
      AND i.status_inscricao = 'ativa'
<<<<<<< HEAD
    ORDER BY t.nome_turma ASC
=======
    ORDER BY RAND()
>>>>>>> 2bcbb5a3bf459c76ddd4add567cd304c16ea8994
");
$stmtTurmas->execute([':emid' => $emId]);
$turmas = $stmtTurmas->fetchAll(PDO::FETCH_ASSOC);

<<<<<<< HEAD
$total = count($turmas);

if ($total < 2) {
    echo json_encode(['ok' => false, 'erro' => "Inscritos insuficientes ($total turma(s)). Mínimo: 2 turmas com alunos inscritos."]);
    exit;
}

// ── Embaralha as turmas (sorteio aleatório) ──────────────
shuffle($turmas);

// ── Data padrão: hoje + 7 dias (sem horário definido) ───
$dataBase = new DateTime();
$dataBase->modify('+7 days');
$dataStr  = $dataBase->format('Y-m-d');
$horaStr  = '08:00:00'; // placeholder — professor altera depois

// ══════════════════════════════════════════════════════════
//  HELPER: determina fase inicial do mata-mata
// ══════════════════════════════════════════════════════════
function faseInicial(int $n): string {
    if ($n <= 2)  return 'final';
    if ($n <= 4)  return 'semi';
    if ($n <= 8)  return 'quartas';
    return 'oitavas';
}

// ══════════════════════════════════════════════════════════
//  HELPER: divide array em grupos de tamanho equilibrado
// ══════════════════════════════════════════════════════════
function dividirGrupos(array $times, int $numGrupos): array {
    $grupos = array_fill(0, $numGrupos, []);
    foreach ($times as $idx => $t) {
        $grupos[$idx % $numGrupos][] = $t;
    }
    return $grupos;
}

// ── Número de grupos baseado no total de turmas ──────────
function calcNumGrupos(int $n): int {
    if ($n <= 6)  return 2;
    if ($n <= 9)  return 3;
    return 4;
}

// ══════════════════════════════════════════════════════════
//  GERAR PARTIDAS
// ══════════════════════════════════════════════════════════
$conn->beginTransaction();
try {
    $stmtInsert = $conn->prepare("
        INSERT INTO partida
            (edicao_modalidade_id, turma_id_time_a, turma_id_time_b,
             data_partida, hora_partida, fase_partida, grupo_partida, status_partida)
        VALUES
            (:emid, :ta, :tb, :data, :hora, :fase, :grupo, 'agendada')
    ");

    $stmtClassif = $conn->prepare("
        INSERT IGNORE INTO classificacao
            (edicao_modalidade_id, turma_id_turma, grupo_classificacao,
             pontos, vitorias, derrotas, empates, jogos,
             pontos_pro, pontos_contra, saldo)
        VALUES
            (:emid, :turma, :grupo, 0,0,0,0,0,0,0,0)
    ");

    $letras        = ['A','B','C','D'];
    $partidasGeradas = [];

    // ────────────────────────────────────────────────────
    //  TODOS CONTRA TODOS
    // ────────────────────────────────────────────────────
    if ($formato === 'todos_contra_todos') {
        foreach ($turmas as $t) {
            $stmtClassif->execute([':emid'=>$emId, ':turma'=>$t['id_turma'], ':grupo'=>'A']);
        }
        for ($i = 0; $i < $total; $i++) {
            for ($j = $i + 1; $j < $total; $j++) {
                $stmtInsert->execute([
                    ':emid'  => $emId,
                    ':ta'    => $turmas[$i]['id_turma'],
                    ':tb'    => $turmas[$j]['id_turma'],
                    ':data'  => $dataStr,
                    ':hora'  => $horaStr,
                    ':fase'  => 'grupos',
                    ':grupo' => 'A',
                ]);
                $partidasGeradas[] = [
                    'time_a' => $turmas[$i]['nome_turma'],
                    'time_b' => $turmas[$j]['nome_turma'],
                    'fase'   => 'Grupos',
                    'grupo'  => 'A',
                ];
=======
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
>>>>>>> 2bcbb5a3bf459c76ddd4add567cd304c16ea8994
            }
        }
    }

<<<<<<< HEAD
    // ────────────────────────────────────────────────────
    //  SÓ GRUPOS (round-robin por grupo)
    // ────────────────────────────────────────────────────
    elseif ($formato === 'grupos') {
        $numGrupos = calcNumGrupos($total);
        $grupos    = dividirGrupos($turmas, $numGrupos);

        foreach ($grupos as $gIdx => $grupo) {
            $letra = $letras[$gIdx];
            foreach ($grupo as $t) {
                $stmtClassif->execute([':emid'=>$emId, ':turma'=>$t['id_turma'], ':grupo'=>$letra]);
            }
            $n = count($grupo);
            for ($i = 0; $i < $n; $i++) {
                for ($j = $i + 1; $j < $n; $j++) {
                    $stmtInsert->execute([
                        ':emid'  => $emId,
                        ':ta'    => $grupo[$i]['id_turma'],
                        ':tb'    => $grupo[$j]['id_turma'],
                        ':data'  => $dataStr,
                        ':hora'  => $horaStr,
                        ':fase'  => 'grupos',
                        ':grupo' => $letra,
                    ]);
                    $partidasGeradas[] = [
                        'time_a' => $grupo[$i]['nome_turma'],
                        'time_b' => $grupo[$j]['nome_turma'],
                        'fase'   => 'Grupos',
                        'grupo'  => $letra,
                    ];
=======
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
>>>>>>> 2bcbb5a3bf459c76ddd4add567cd304c16ea8994
                }
            }
        }
    }

<<<<<<< HEAD
    // ────────────────────────────────────────────────────
    //  SÓ MATA-MATA
    // ────────────────────────────────────────────────────
    elseif ($formato === 'mata_mata') {
        $fase = faseInicial($total);
        // Pares: 1x2, 3x4, 5x6...
        for ($i = 0; $i + 1 < $total; $i += 2) {
            $stmtInsert->execute([
                ':emid'  => $emId,
                ':ta'    => $turmas[$i]['id_turma'],
                ':tb'    => $turmas[$i + 1]['id_turma'],
                ':data'  => $dataStr,
                ':hora'  => $horaStr,
                ':fase'  => $fase,
                ':grupo' => null,
            ]);
            $partidasGeradas[] = [
                'time_a' => $turmas[$i]['nome_turma'],
                'time_b' => $turmas[$i + 1]['nome_turma'],
                'fase'   => ucfirst($fase),
                'grupo'  => null,
            ];
        }
        // Se ímpar: última turma avança automaticamente (bye)
        if ($total % 2 !== 0) {
            $bye = end($turmas);
            $partidasGeradas[] = [
                'time_a' => $bye['nome_turma'],
                'time_b' => '— BYE (avança direto)',
                'fase'   => ucfirst($fase),
                'grupo'  => null,
            ];
        }
    }

    // ────────────────────────────────────────────────────
    //  GRUPOS + MATA-MATA
    // ────────────────────────────────────────────────────
    elseif ($formato === 'grupos_mata_mata') {
        $numGrupos = calcNumGrupos($total);
        $grupos    = dividirGrupos($turmas, $numGrupos);

        foreach ($grupos as $gIdx => $grupo) {
            $letra = $letras[$gIdx];
            foreach ($grupo as $t) {
                $stmtClassif->execute([':emid'=>$emId, ':turma'=>$t['id_turma'], ':grupo'=>$letra]);
=======
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
>>>>>>> 2bcbb5a3bf459c76ddd4add567cd304c16ea8994
            }
            $n = count($grupo);
            for ($i = 0; $i < $n; $i++) {
                for ($j = $i + 1; $j < $n; $j++) {
<<<<<<< HEAD
                    $stmtInsert->execute([
                        ':emid'  => $emId,
                        ':ta'    => $grupo[$i]['id_turma'],
                        ':tb'    => $grupo[$j]['id_turma'],
                        ':data'  => $dataStr,
                        ':hora'  => $horaStr,
                        ':fase'  => 'grupos',
                        ':grupo' => $letra,
                    ]);
                    $partidasGeradas[] = [
                        'time_a' => $grupo[$i]['nome_turma'],
                        'time_b' => $grupo[$j]['nome_turma'],
                        'fase'   => 'Grupos',
                        'grupo'  => $letra,
                    ];
                }
            }
        }
        // Nota: partidas de mata-mata (semi/final) serão geradas
        // pelo professor após a fase de grupos ser concluída.
    }

    // ── Registra o sorteio como gerado ──────────────────
    $conn->prepare("
        INSERT INTO sorteio_gerado (edicao_modalidade_id, gerado_por)
        VALUES (:emid, :uid)
    ")->execute([':emid' => $emId, ':uid' => $userId]);

    // ── Atualiza status da edicao_modalidade ─────────────
    $conn->prepare("
        UPDATE edicao_modalidade
        SET status_edicao_modalidade = 'em_andamento'
        WHERE id_edicao_modalidade = :emid
    ")->execute([':emid' => $emId]);

    $conn->commit();

    echo json_encode([
        'ok'       => true,
        'msg'      => count($partidasGeradas) . ' partida(s) gerada(s) com sucesso! Os horários ficaram como placeholder — ajuste no painel de Partidas.',
        'total'    => count($partidasGeradas),
        'partidas' => $partidasGeradas,
=======
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
>>>>>>> 2bcbb5a3bf459c76ddd4add567cd304c16ea8994
    ]);

} catch (Exception $e) {
    $conn->rollBack();
<<<<<<< HEAD
    echo json_encode(['ok' => false, 'erro' => 'Erro ao gerar: ' . $e->getMessage()]);
=======
    echo json_encode(['ok' => false, 'erro' => 'Erro ao gerar sorteio: ' . $e->getMessage()]);
>>>>>>> 2bcbb5a3bf459c76ddd4add567cd304c16ea8994
}