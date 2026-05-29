<?php
/**
 * api-dashboard.php — SOEE
 * Endpoint JSON consumido pelo dash-user.js
 * Ações: classificacao | times | partidas | jogadores | esportes
 */
session_start();
// CAMINHO CORRIGIDO: era '/../include/conexao.php' e '/../auth/auth-home.php'
// A estrutura real é: includes/conexao.php e controllers/home.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/soee/src/backend/includes/conexao.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/soee/src/backend/controllers/home.php';

AuthHome::exigirTipo(['aluno','adm_sala','adm_geral','professor']);

header('Content-Type: application/json; charset=utf-8');

$acao    = trim($_GET['acao']    ?? '');
$emId    = (int)($_GET['em_id']    ?? 0);
$turmaId = (int)($_GET['turma_id'] ?? 0);

/* ── helpers ── */
function responder(array $dados): void {
    echo json_encode(['dados' => $dados], JSON_UNESCAPED_UNICODE);
    exit;
}
function erro(string $msg): void {
    echo json_encode(['erro' => $msg], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!$emId && $acao !== 'esportes') {
    erro('em_id ausente');
}

/* ═══════════════════════════════════════════════════════
   AÇÃO: esportes
════════════════════════════════════════════════════════ */
if ($acao === 'esportes') {
    // PostgreSQL: ativo_modalidade é BOOLEAN → TRUE (não 1)
    $rows = $conn->query("
        SELECT m.id_modalidade, m.nome_modalidade, m.tipo_modalidade,
               m.tipo_participacao, em.id_edicao_modalidade,
               em.status_edicao_modalidade
        FROM modalidade m
        INNER JOIN edicao_modalidade em ON em.modalidade_id_modalidade = m.id_modalidade
        INNER JOIN edicao e ON e.id_edicao = em.edicao_id_edicao
        WHERE m.ativo_modalidade = TRUE
          AND e.status_edicao IN ('inscricoes','em_andamento')
        ORDER BY m.nome_modalidade
    ")->fetchAll(PDO::FETCH_ASSOC);
    responder($rows);
}

/* ═══════════════════════════════════════════════════════
   AÇÃO: classificacao
════════════════════════════════════════════════════════ */
if ($acao === 'classificacao') {
    $stmt = $conn->prepare("
        SELECT cl.turma_id_turma, t.nome_turma,
               cl.pontos, cl.vitorias, cl.derrotas, cl.empates,
               cl.pontos_pro, cl.pontos_contra, cl.saldo, cl.jogos
        FROM classificacao cl
        INNER JOIN turma t ON t.id_turma = cl.turma_id_turma
        WHERE cl.edicao_modalidade_id = :emid
        ORDER BY cl.pontos DESC, cl.saldo DESC, cl.vitorias DESC
    ");
    $stmt->execute([':emid' => $emId]);
    responder($stmt->fetchAll(PDO::FETCH_ASSOC));
}

/* ═══════════════════════════════════════════════════════
   AÇÃO: times
════════════════════════════════════════════════════════ */
if ($acao === 'times') {
    $stmt = $conn->prepare("
        SELECT DISTINCT t.id_turma, t.nome_turma,
               COUNT(DISTINCT i.usuario_id_usuario) AS total_inscritos
        FROM inscricao i
        INNER JOIN usuario u ON u.id_usuario = i.usuario_id_usuario
        INNER JOIN turma t   ON t.id_turma   = u.turma_id_turma
        WHERE i.edicao_modalidade_id = :emid
          AND i.status_inscricao     = 'ativa'
          AND t.ano_serie_turma      < 4
        GROUP BY t.id_turma, t.nome_turma
        ORDER BY t.nome_turma
    ");
    $stmt->execute([':emid' => $emId]);
    responder($stmt->fetchAll(PDO::FETCH_ASSOC));
}

/* ═══════════════════════════════════════════════════════
   AÇÃO: partidas
════════════════════════════════════════════════════════ */
if ($acao === 'partidas') {
    $stmt = $conn->prepare("
        SELECT p.id_partida,
               p.turma_id_time_a, p.turma_id_time_b,
               ta.nome_turma AS nome_time_a,
               tb.nome_turma AS nome_time_b,
               p.data_partida, p.hora_partida, p.local_partida,
               p.fase_partida, p.grupo_partida, p.status_partida,
               r.placar_time_a, r.placar_time_b
        FROM partida p
        INNER JOIN turma ta ON ta.id_turma = p.turma_id_time_a
        INNER JOIN turma tb ON tb.id_turma = p.turma_id_time_b
        LEFT  JOIN resultado r ON r.partida_id_partida = p.id_partida
        WHERE p.edicao_modalidade_id = :emid
        ORDER BY p.data_partida ASC, p.hora_partida ASC
    ");
    $stmt->execute([':emid' => $emId]);
    responder($stmt->fetchAll(PDO::FETCH_ASSOC));
}

/* ═══════════════════════════════════════════════════════
   AÇÃO: jogadores
════════════════════════════════════════════════════════ */
if ($acao === 'jogadores') {
    if (!$turmaId) erro('turma_id ausente');
    $stmt = $conn->prepare("
        SELECT u.id_usuario, u.nome_usuario, u.genero_usuario,
               i.numero_camisa_inscricao, i.posicao_inscricao, i.capitao_inscricao
        FROM inscricao i
        INNER JOIN usuario u ON u.id_usuario = i.usuario_id_usuario
        WHERE i.edicao_modalidade_id = :emid
          AND u.turma_id_turma       = :turma
          AND i.status_inscricao     = 'ativa'
        ORDER BY i.capitao_inscricao DESC, u.nome_usuario ASC
    ");
    $stmt->execute([':emid' => $emId, ':turma' => $turmaId]);
    responder($stmt->fetchAll(PDO::FETCH_ASSOC));
}

/* ── Ação desconhecida ── */
erro('acao inválida');