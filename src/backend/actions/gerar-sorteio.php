<?php
// ═══════════════════════════════════════════════════════════
//  gerar-sorteio.php — SOEE
//  Compatível com banco PostgreSQL/Supabase real:
//    - classificacao: sem usuario_id_participante
//    - partida: sem usuario_id_time_a/b
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

// Já gerado?
$jaGerado = $conn->prepare("SELECT id FROM sorteio_gerado WHERE edicao_modalidade_id = :emid LIMIT 1");
$jaGerado->execute([':emid' => $emId]);
if ($jaGerado->fetchColumn()) {
    echo json_encode(['ok' => false, 'erro' => 'Sorteio já gerado. Para refazer, exclua as partidas e o registro em sorteio_gerado no banco.']);
    exit;
}

// Dados da modalidade
$stmtEm = $conn->prepare("
    SELECT em.id_edicao_modalidade, m.formato_modalidade, m.tipo_participacao, m.nome_modalidade
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

$formato = $em['formato_modalidade'];
$dataStr = (new DateTime('+7 days'))->format('Y-m-d');
$horaStr = '08:00:00';

// Busca turmas inscritas (banco real: só modalidades por time/turma)
$stmtP = $conn->prepare("
    SELECT DISTINCT
        t.id_turma   AS id,
        t.nome_turma AS nome,
        t.id_turma   AS turma_id,
        t.nome_turma
    FROM inscricao i
    INNER JOIN usuario u ON u.id_usuario = i.usuario_id_usuario
    INNER JOIN turma   t ON t.id_turma   = u.turma_id_turma
    WHERE i.edicao_modalidade_id = :emid
      AND i.status_inscricao     = 'ativa'
    ORDER BY t.nome_turma
");
$stmtP->execute([':emid' => $emId]);
$participantes = $stmtP->fetchAll(PDO::FETCH_ASSOC);

$total = count($participantes);
if ($total < 2) {
    echo json_encode(['ok' => false, 'erro' => "Inscritos insuficientes ($total turma(s)). Mínimo: 2."]);
    exit;
}

shuffle($participantes);

// Helpers
function gs_proximaPotencia2(int $n): int { $p = 1; while ($p < $n) $p *= 2; return $p; }
function gs_faseInicial(int $n): string {
    if ($n <= 2) return 'final';
    if ($n <= 4) return 'semi';
    if ($n <= 8) return 'quartas';
    return 'oitavas';
}
function gs_calcNumGrupos(int $n): int {
    if ($n <= 6)  return 2;
    if ($n <= 9)  return 3;
    return 4;
}
function gs_dividirEmGrupos(array $lista, int $num): array {
    $g = array_fill(0, $num, []);
    foreach ($lista as $i => $item) $g[$i % $num][] = $item;
    return $g;
}

$conn->beginTransaction();
try {

    // INSERT partida (banco real: sem usuario_id_time_a/b)
    $stmtInsert = $conn->prepare("
        INSERT INTO partida
            (edicao_modalidade_id, turma_id_time_a, turma_id_time_b,
             data_partida, hora_partida, fase_partida, grupo_partida, status_partida)
        VALUES (:emid, :ta, :tb, :data, :hora, :fase, :grupo, 'agendada')
    ");

    // INSERT classificacao (banco real: sem usuario_id_participante)
$stmtClassif = $conn->prepare("
    INSERT INTO classificacao
        (edicao_modalidade_id, turma_id_turma, grupo_classificacao,
         pontos, vitorias, derrotas, empates, jogos, pontos_pro, pontos_contra, saldo)
    SELECT :emid, :turma, :grupo, 0,0,0,0,0,0,0,0
    WHERE NOT EXISTS (
        SELECT 1 FROM classificacao
        WHERE edicao_modalidade_id = :emid2
          AND turma_id_turma       = :turma2
    )
");

    $letras          = ['A','B','C','D','E','F','G','H'];
    $partidasGeradas = [];
    $byesGerados     = [];

    $inserir = function(array $a, array $b, string $fase, ?string $grupo)
        use ($stmtInsert, $emId, $dataStr, $horaStr, &$partidasGeradas) {
        $stmtInsert->execute([
            ':emid'  => $emId,
            ':ta'    => (int) $a['turma_id'],
            ':tb'    => (int) $b['turma_id'],
            ':data'  => $dataStr,
            ':hora'  => $horaStr,
            ':fase'  => $fase,
            ':grupo' => $grupo,
        ]);
        $partidasGeradas[] = [
            'time_a' => $a['nome'],
            'time_b' => $b['nome'],
            'fase'   => $fase,
            'grupo'  => $grupo,
        ];
    };

$registrarClassif = function(array $grupo, string $letra) use ($stmtClassif, $emId) {
    $vistos = [];
    foreach ($grupo as $p) {
        $k = 't_' . $p['turma_id'];
        if (isset($vistos[$k])) continue;
        $vistos[$k] = true;
        $stmtClassif->execute([
            ':emid'   => $emId,
            ':turma'  => (int)$p['turma_id'],
            ':grupo'  => $letra,
            ':emid2'  => $emId,
            ':turma2' => (int)$p['turma_id'],
        ]);
    }        
};

    if ($formato === 'todos_contra_todos') {
        $registrarClassif($participantes, 'A');
        for ($i = 0; $i < $total; $i++)
            for ($j = $i + 1; $j < $total; $j++)
                $inserir($participantes[$i], $participantes[$j], 'grupos', 'A');

    } elseif ($formato === 'grupos') {
        $numG   = gs_calcNumGrupos($total);
        $grupos = gs_dividirEmGrupos($participantes, $numG);
        foreach ($grupos as $gi => $grupo) {
            $letra = $letras[$gi];
            $registrarClassif($grupo, $letra);
            $n = count($grupo);
            for ($i = 0; $i < $n; $i++)
                for ($j = $i + 1; $j < $n; $j++)
                    $inserir($grupo[$i], $grupo[$j], 'grupos', $letra);
        }

    } elseif ($formato === 'mata_mata') {
        $potencia = gs_proximaPotencia2($total);
        $numByes  = $potencia - $total;
        $fase     = gs_faseInicial($potencia);
        $comBye   = array_slice($participantes, 0, $numByes);
        $semBye   = array_slice($participantes, $numByes);

        for ($i = 0; $i + 1 < count($semBye); $i += 2)
            $inserir($semBye[$i], $semBye[$i + 1], $fase, null);

        foreach ($comBye as $b) {
            $byesGerados[] = ['participante' => $b['nome'], 'observacao' => 'BYE — avança direto'];
            $partidasGeradas[] = ['time_a' => $b['nome'], 'time_b' => 'BYE', 'fase' => $fase, 'grupo' => null];
        }

    } elseif ($formato === 'grupos_mata_mata') {
        $numG   = gs_calcNumGrupos($total);
        $grupos = gs_dividirEmGrupos($participantes, $numG);
        foreach ($grupos as $gi => $grupo) {
            $letra = $letras[$gi];
            $registrarClassif($grupo, $letra);
            $n = count($grupo);
            for ($i = 0; $i < $n; $i++)
                for ($j = $i + 1; $j < $n; $j++)
                    $inserir($grupo[$i], $grupo[$j], 'grupos', $letra);
        }
    }

    $conn->prepare("INSERT INTO sorteio_gerado (edicao_modalidade_id, gerado_por) VALUES (:emid, :uid)")
         ->execute([':emid' => $emId, ':uid' => $userId]);

    $conn->prepare("UPDATE edicao_modalidade SET status_edicao_modalidade = 'em_andamento' WHERE id_edicao_modalidade = :emid")
         ->execute([':emid' => $emId]);

    $conn->commit();

    $totalPartidas = count(array_filter($partidasGeradas, fn($p) => $p['time_b'] !== 'BYE'));
    $totalByes     = count($byesGerados);
    $msg = "$totalPartidas partida(s) gerada(s) com sucesso!";
    if ($totalByes > 0) $msg .= " $totalByes turma(s) com BYE avançam direto.";
    $msg .= ' Ajuste datas e horários no painel de Partidas.';

    echo json_encode(['ok' => true, 'msg' => $msg, 'total' => $totalPartidas, 'byes' => $totalByes, 'partidas' => $partidasGeradas], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode(['ok' => false, 'erro' => 'Erro ao gerar: ' . $e->getMessage()]);
}