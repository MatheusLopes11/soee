<?php
/**
 * api-dashboard.php
 * Retorna JSON com classificação, times e jogadores de uma edicao_modalidade.
 * Chamado pelo dash-user.js via fetch.
 *
 * GET params:
 *   acao = classificacao | times | jogadores | partidas
 *   em_id = id_edicao_modalidade
 *   turma_id = (opcional) para filtrar jogadores da turma
 */
session_start();
require_once __DIR__ . '/../include/conexao.php';
require_once __DIR__ . '/../auth/auth-home.php';

header('Content-Type: application/json; charset=utf-8');

// Exige login
if (!AuthHome::estaLogado()) {
    http_response_code(401);
    echo json_encode(['erro' => 'Não autorizado']);
    exit();
}

$acao  = $_GET['acao']  ?? '';
$emId  = (int) ($_GET['em_id'] ?? 0);
$turmaId = (int) ($_GET['turma_id'] ?? 0);

if (!$emId) {
    echo json_encode(['erro' => 'em_id obrigatório']);
    exit();
}

try {

    /* ════════════════════════════════
       CLASSIFICAÇÃO
    ════════════════════════════════ */
    if ($acao === 'classificacao') {
        $stmt = $conn->prepare("
            SELECT
                cl.turma_id_turma,
                t.nome_turma,
                cl.pontos, cl.vitorias, cl.derrotas, cl.empates,
                cl.pontos_pro, cl.pontos_contra, cl.saldo, cl.jogos
            FROM classificacao cl
            INNER JOIN turma t ON t.id_turma = cl.turma_id_turma
            WHERE cl.edicao_modalidade_id = :emid
            ORDER BY cl.pontos DESC, cl.saldo DESC, cl.vitorias DESC, t.nome_turma ASC
        ");
        $stmt->execute([':emid' => $emId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['dados' => $rows]);
        exit();
    }

    /* ════════════════════════════════
       TIMES (turmas com inscrição ativa)
    ════════════════════════════════ */
    if ($acao === 'times') {
        $stmt = $conn->prepare("
            SELECT DISTINCT t.id_turma, t.nome_turma,
                   COUNT(i.id_inscricao) AS total_inscritos
            FROM inscricao i
            INNER JOIN usuario u ON u.id_usuario = i.usuario_id_usuario
            INNER JOIN turma t   ON t.id_turma = u.turma_id_turma
            WHERE i.edicao_modalidade_id = :emid
              AND i.status_inscricao = 'ativa'
            GROUP BY t.id_turma, t.nome_turma
            ORDER BY t.nome_turma ASC
        ");
        $stmt->execute([':emid' => $emId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['dados' => $rows]);
        exit();
    }

    /* ════════════════════════════════
       JOGADORES de uma turma
    ════════════════════════════════ */
    if ($acao === 'jogadores') {
        if (!$turmaId) { echo json_encode(['erro' => 'turma_id obrigatório']); exit(); }
        $stmt = $conn->prepare("
            SELECT u.id_usuario, u.nome_usuario, u.genero_usuario,
                   i.posicao_inscricao, i.numero_camisa_inscricao, i.capitao_inscricao
            FROM inscricao i
            INNER JOIN usuario u ON u.id_usuario = i.usuario_id_usuario
            WHERE i.edicao_modalidade_id = :emid
              AND u.turma_id_turma = :turma
              AND i.status_inscricao = 'ativa'
            ORDER BY i.capitao_inscricao DESC, u.nome_usuario ASC
        ");
        $stmt->execute([':emid' => $emId, ':turma' => $turmaId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['dados' => $rows]);
        exit();
    }

    /* ════════════════════════════════
       PARTIDAS
    ════════════════════════════════ */
    if ($acao === 'partidas') {
        $stmt = $conn->prepare("
            SELECT p.id_partida, p.data_partida, p.hora_partida,
                   p.local_partida, p.fase_partida, p.status_partida,
                   p.grupo_partida,
                   ta.nome_turma AS nome_time_a,
                   tb.nome_turma AS nome_time_b,
                   p.turma_id_time_a, p.turma_id_time_b,
                   r.placar_time_a, r.placar_time_b
            FROM partida p
            INNER JOIN turma ta ON ta.id_turma = p.turma_id_time_a
            INNER JOIN turma tb ON tb.id_turma = p.turma_id_time_b
            LEFT  JOIN resultado r ON r.partida_id_partida = p.id_partida
            WHERE p.edicao_modalidade_id = :emid
            ORDER BY p.data_partida ASC, p.hora_partida ASC
        ");
        $stmt->execute([':emid' => $emId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['dados' => $rows]);
        exit();
    }

    echo json_encode(['erro' => 'Ação inválida']);

} catch (PDOException $e) {
    http_response_code(500);
    error_log('API dashboard: ' . $e->getMessage());
    echo json_encode(['erro' => 'Erro interno']);
}