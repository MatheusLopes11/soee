<?php
// ═══════════════════════════════════════════════════════════
//  gerar-sorteio.php — SOEE
//  Gera partidas aleatórias baseadas nas turmas inscritas
//  POST: edicao_modalidade_id
//  Retorna: JSON {ok, msg, partidas[]}
// ═══════════════════════════════════════════════════════════
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/soee/src/backend/includes/conexao.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/soee/src/backend/controllers/home.php';

header('Content-Type: application/json; charset=utf-8');
AuthHome::exigirTipo(['professor', 'adm_geral']);

$emId   = (int) ($_POST['edicao_modalidade_id'] ?? 0);
$userId = AuthHome::getId();

if (!$emId) {
    echo json_encode(['ok' => false, 'erro' => 'ID inválido.']);
    exit;
}

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

$formato      = $em['formato_modalidade'];
$participacao = $em['tipo_participacao'];

// ── Turmas com pelo menos 1 aluno inscrito ───────────────
$stmtTurmas = $conn->prepare("
    SELECT DISTINCT t.id_turma, t.nome_turma
    FROM inscricao i
    INNER JOIN usuario u ON u.id_usuario = i.usuario_id_usuario
    INNER JOIN turma t ON t.id_turma = u.turma_id_turma
    WHERE i.edicao_modalidade_id = :emid
      AND i.status_inscricao = 'ativa'
    ORDER BY t.nome_turma ASC
");
$stmtTurmas->execute([':emid' => $emId]);
$turmas = $stmtTurmas->fetchAll(PDO::FETCH_ASSOC);

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
            }
        }
    }

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
                }
            }
        }
    }

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
    ]);

} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode(['ok' => false, 'erro' => 'Erro ao gerar: ' . $e->getMessage()]);
}