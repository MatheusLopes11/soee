<?php
// ═══════════════════════════════════════════════════════
//  actions/buscar_parceiros.php  —  SOEE
//  Retorna alunos disponíveis para formar dupla/trio.
//  GET ?em=<edicao_modalidade_id>&q=<termo>
// ═══════════════════════════════════════════════════════

session_start();
require_once __DIR__ . '/../../../../backend/includes/conexao.php';
require_once __DIR__ . '/../../../../backend/controllers/home.php';

header('Content-Type: application/json');

AuthHome::exigirTipo(['aluno']);

$userId = AuthHome::getId();
$emId   = (int)   ($_GET['em'] ?? 0);
$q      = trim(   $_GET['q']   ?? '');

if (!$emId || strlen($q) < 2) {
    echo json_encode([]);
    exit;
}

// Busca alunos ativos que:
// 1. Não sejam o próprio usuário
// 2. Não estejam já inscritos nesta modalidade
// 3. O nome contenha o termo buscado
$stmt = $conn->prepare("
    SELECT u.id_usuario, u.nome_usuario, t.nome_turma
    FROM usuario u
    LEFT JOIN turma t ON t.id_turma = u.turma_id_turma
    WHERE u.tipo_usuario IN ('aluno', 'adm_sala')
      AND u.ativo_usuario = TRUE
      AND u.id_usuario != :uid
      AND u.nome_usuario ILIKE :q
      AND u.id_usuario NOT IN (
          SELECT usuario_id_usuario
          FROM inscricao
          WHERE edicao_modalidade_id = :emId
            AND status_inscricao = 'ativa'
      )
    ORDER BY t.nome_turma ASC, u.nome_usuario ASC
    LIMIT 10
");

$stmt->execute([
    ':uid'  => $userId,
    ':emId' => $emId,
    ':q'    => '%' . $q . '%',
]);

$alunos = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($alunos);
