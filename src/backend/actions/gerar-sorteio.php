<?php
// ═══════════════════════════════════════════════════════════
//  gerar-sorteio.php — SOEE
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

// ── Já gerado? ───────────────────────────────────────────
$jaGerado = $conn->prepare("SELECT id FROM sorteio_gerado WHERE edicao_modalidade_id = :emid LIMIT 1");
$jaGerado->execute([':emid' => $emId]);
if ($jaGerado->fetchColumn()) {
    echo json_encode(['ok' => false, 'erro' => 'Sorteio já gerado. Para refazer, exclua as partidas e o registro em sorteio_gerado no banco.']);
    exit;
}

// ── Dados da modalidade ──────────────────────────────────
$stmtEm = $conn->prepare("
    SELECT em.id_edicao_modalidade, em.edicao_id_edicao,
           m.formato_modalidade, m.tipo_participacao, m.nome_modalidade
    FROM edicao_modalidade em
    INNER JOIN modalidade m ON m.id_modalidade = em.modalidade_id_modalidade
    WHERE em.id_edicao_modalidade = :emid LIMIT 1
");
$stmtEm->execute([':emid' => $emId]);
$em = $stmtEm->fetch(PDO::FETCH_ASSOC);
if (!$em) {
    echo json_encode(['ok' => false, 'erro' => 'Edição/modalidade não encontrada.']);
    exit;
}

$formato      = $em['formato_modalidade'];
$participacao = $em['tipo_participacao'];
$ehIndividual = in_array($participacao, ['solo', 'dupla', 'trio']);

$dataStr = (new DateTime('+7 days'))->format('Y-m-d');
$horaStr = '08:00:00';

// ── Busca participantes ──────────────────────────────────
if ($ehIndividual) {
    $stmtP = $conn->prepare("
        SELECT
            i.usuario_id_usuario  AS id,
            u.nome_usuario        AS nome,
            u.turma_id_turma      AS turma_id,
            t.nome_turma
        FROM inscricao i
        INNER JOIN usuario u ON u.id_usuario  = i.usuario_id_usuario
        INNER JOIN turma  t ON t.id_turma     = u.turma_id_turma
        WHERE i.edicao_modalidade_id = :emid
          AND i.status_inscricao = 'ativa'
        ORDER BY u.nome_usuario
    ");
    $stmtP->execute([':emid' => $emId]);
    $participantes = $stmtP->fetchAll(PDO::FETCH_ASSOC);
} else {
    $stmtP = $conn->prepare("
        SELECT DISTINCT
            t.id_turma   AS id,
            t.nome_turma AS nome,
            t.id_turma   AS turma_id,
            t.nome_turma
        FROM inscricao i
        INNER JOIN usuario u ON u.id_usuario = i.usuario_id_usuario
        INNER JOIN turma  t ON t.id_turma    = u.turma_id_turma
        WHERE i.edicao_modalidade_id = :emid
          AND i.status_inscricao = 'ativa'
    ");
    $stmtP->execute([':emid' => $emId]);
    $participantes = $stmtP->fetchAll(PDO::FETCH_ASSOC);
}

$total = count($participantes);
if ($total < 2) {
    $label = $ehIndividual ? 'aluno(s) inscrito(s)' : 'turma(s) inscrita(s)';
    echo json_encode(['ok' => false, 'erro' => "Inscritos insuficientes ($total $label). Mínimo: 2."]);
    exit;
}

shuffle($participantes);

// ── Helpers ──────────────────────────────────────────────
function proximaPotencia2(int $n): int {
    $p = 1; while ($p < $n) $p *= 2; return $p;
}
function faseInicial(int $n): string {
    if ($n <= 2) return 'final';
    if ($n <= 4) return 'semi';
    if ($n <= 8) return 'quartas';
    return 'oitavas';
}
function dividirEmGrupos(array $lista, int $num): array {
    $g = array_fill(0, $num, []);
    foreach ($lista as $i => $item) $g[$i % $num][] = $item;
    return $g;
}
function calcNumGrupos(int $n): int {
    if ($n <= 6) return 2;
    if ($n <= 9) return 3;
    return 4;
}

// ── Transação ─────────────────────────────────────────────
$conn->beginTransaction();
try {

    // ── PREPARE partida ───────────────────────────────────
    if ($ehIndividual) {
        $stmtInsert = $conn->prepare("
            INSERT INTO partida
                (edicao_modalidade_id,
                 turma_id_time_a, turma_id_time_b,
                 usuario_id_time_a, usuario_id_time_b,
                 data_partida, hora_partida, fase_partida, grupo_partida, status_partida)
            VALUES
                (:emid,
                 :ta_turma, :tb_turma,
                 :ta_usuario, :tb_usuario,
                 :data, :hora, :fase, :grupo, 'agendada')
        ");
    } else {
        $stmtInsert = $conn->prepare("
            INSERT INTO partida
                (edicao_modalidade_id,
                 turma_id_time_a, turma_id_time_b,
                 data_partida, hora_partida, fase_partida, grupo_partida, status_partida)
            VALUES
                (:emid,
                 :ta_turma, :tb_turma,
                 :data, :hora, :fase, :grupo, 'agendada')
        ");
    }

    // ── PREPARE classificação ─────────────────────────────
    if ($ehIndividual) {
        $stmtClassif = $conn->prepare("
            INSERT INTO classificacao
                (edicao_modalidade_id, turma_id_turma, usuario_id_participante,
                 grupo_classificacao, pontos, vitorias, derrotas, empates,
                 jogos, pontos_pro, pontos_contra, saldo)
            VALUES
                (:emid, :turma, :usuario, :grupo, 0,0,0,0,0,0,0,0)
            ON CONFLICT (edicao_modalidade_id, usuario_id_participante)
            WHERE usuario_id_participante IS NOT NULL
            DO NOTHING
        ");
    } else {
        $stmtClassif = $conn->prepare("
            INSERT INTO classificacao
                (edicao_modalidade_id, turma_id_turma,
                 grupo_classificacao, pontos, vitorias, derrotas, empates,
                 jogos, pontos_pro, pontos_contra, saldo)
            VALUES
                (:emid, :turma, :grupo, 0,0,0,0,0,0,0,0)
            ON CONFLICT (edicao_modalidade_id, turma_id_turma)
            WHERE usuario_id_participante IS NULL
            DO NOTHING
        ");
    }

    $letras          = ['A','B','C','D','E','F','G','H'];
    $partidasGeradas = [];
    $byesGerados     = [];

    // ── Função inserir partida ────────────────────────────
    $inserir = function(array $a, array $b, string $fase, ?string $grupo)
        use ($stmtInsert, $emId, $dataStr, $horaStr, $ehIndividual, &$partidasGeradas) {

        $params = [
            ':emid'     => $emId,
            ':ta_turma' => (int) $a['turma_id'],
            ':tb_turma' => (int) $b['turma_id'],
            ':data'     => $dataStr,
            ':hora'     => $horaStr,
            ':fase'     => $fase,
            ':grupo'    => $grupo,
        ];
        if ($ehIndividual) {
            $params[':ta_usuario'] = (int) $a['id'];
            $params[':tb_usuario'] = (int) $b['id'];
        }
        $stmtInsert->execute($params);

        $partidasGeradas[] = [
            'time_a' => $a['nome'],
            'time_b' => $b['nome'],
            'sala_a' => $a['nome_turma'],
            'sala_b' => $b['nome_turma'],
            'fase'   => $fase,
            'grupo'  => $grupo,
        ];
    };

    // ── Função registrar classificação ────────────────────
    $registrarClassif = function(array $grupo, string $letra)
        use ($stmtClassif, $emId, $ehIndividual) {

        $vistos = [];
        foreach ($grupo as $p) {
            $chave = $ehIndividual ? 'u_' . $p['id'] : 't_' . $p['turma_id'];
            if (isset($vistos[$chave])) continue;
            $vistos[$chave] = true;

            $params = [
                ':emid'  => $emId,
                ':turma' => (int) $p['turma_id'],
                ':grupo' => $letra,
            ];
            if ($ehIndividual) {
                $params[':usuario'] = (int) $p['id'];
            }
            $stmtClassif->execute($params);
        }
    };

    // ── Todos contra todos ────────────────────────────────
    if ($formato === 'todos_contra_todos') {
        $registrarClassif($participantes, 'A');
        for ($i = 0; $i < $total; $i++) {
            for ($j = $i + 1; $j < $total; $j++) {
                $inserir($participantes[$i], $participantes[$j], 'grupos', 'A');
            }
        }
    }

    // ── Só grupos ─────────────────────────────────────────
    elseif ($formato === 'grupos') {
        $numG   = calcNumGrupos($total);
        $grupos = dividirEmGrupos($participantes, $numG);
        foreach ($grupos as $gi => $grupo) {
            $letra = $letras[$gi];
            $registrarClassif($grupo, $letra);
            $n = count($grupo);
            for ($i = 0; $i < $n; $i++) {
                for ($j = $i + 1; $j < $n; $j++) {
                    $inserir($grupo[$i], $grupo[$j], 'grupos', $letra);
                }
            }
        }
    }

    // ── Mata-mata ─────────────────────────────────────────
    elseif ($formato === 'mata_mata') {
        $potencia = proximaPotencia2($total);
        $numByes  = $potencia - $total;
        $fase     = faseInicial($potencia);

        $comBye = array_slice($participantes, 0, $numByes);
        $semBye = array_slice($participantes, $numByes);

        $nSem = count($semBye);
        for ($i = 0; $i + 1 < $nSem; $i += 2) {
            $inserir($semBye[$i], $semBye[$i + 1], $fase, null);
        }

        // Armazena quem passou de fase direto pelo BYE
        foreach ($comBye as $b) {
            $byesGerados[] = [
                'participante' => $b['nome'],
                'sala'         => $b['nome_turma'],
                'observacao'   => 'BYE — avança direto para próxima fase',
            ];
            $partidasGeradas[] = [
                'time_a' => $b['nome'],
                'time_b' => 'BYE — avança direto',
                'sala_a' => $b['nome_turma'],
                'sala_b' => '—',
                'fase'   => $fase,
                'grupo'  => null,
            ];
        }
    }

    // ── Grupos + Mata-mata (Misto) ────────────────────────
    elseif ($formato === 'grupos_mata_mata') {
        $numG   = calcNumGrupos($total);
        $grupos = dividirEmGrupos($participantes, $numG);
        foreach ($grupos as $gi => $grupo) {
            $letra = $letras[$gi];
            $registrarClassif($grupo, $letra);
            $n = count($grupo);
            for ($i = 0; $i < $n; $i++) {
                for ($j = $i + 1; $j < $n; $j++) {
                    $inserir($grupo[$i], $grupo[$j], 'grupos', $letra);
                }
            }
        }
    }

    // ── Finaliza Gravação dos Logs ────────────────────────
    $stmtSorteio = $conn->prepare("INSERT INTO sorteio_gerado (edicao_modalidade_id, gerado_por) VALUES (:emid, :uid)");
    $stmtSorteio->execute([':emid' => $emId, ':uid' => $userId]);

    $stmtUpdateEm = $conn->prepare("UPDATE edicao_modalidade SET status_edicao_modalidade = 'em_andamento' WHERE id_edicao_modalidade = :emid");
    $stmtUpdateEm->execute([':emid' => $emId]);

    $conn->commit();

    // Filtra BYEs da contagem real de jogos inseridos no banco
    $totalPartidas = count(array_filter($partidasGeradas, function($p) {
        return $p['time_b'] !== 'BYE — avança direto';
    }));
    $totalByes     = count($byesGerados);

    $msg = "$totalPartidas partida(s) gerada(s) com sucesso!";
    if ($totalByes > 0) {
        $msg .= " $totalByes participante(s) com BYE avançam direto para a próxima fase.";
    }
    $msg .= ' Ajuste datas e horários no painel de Partidas.';

    echo json_encode([
        'ok'       => true,
        'msg'      => $msg,
        'total'    => $totalPartidas,
        'byes'     => $totalByes,
        'partidas' => $partidasGeradas,
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode(['ok' => false, 'erro' => 'Erro ao gerar: ' . $e->getMessage()]);
}
?>