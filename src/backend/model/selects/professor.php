<?php
/* ── FOTO DE PERFIL ─────────────────────────────── */
$stmtFoto = $conn->prepare("
    SELECT fp.caminho_foto
    FROM foto_perfil fp
    WHERE fp.usuario_id_usuario = :id
      AND fp.atual_foto = TRUE
    LIMIT 1
");
$stmtFoto->execute([':id' => $userId]);
$fotoPerfil = $stmtFoto->fetchColumn();

/* ── KPIs ────────────────────────────────────────── */
$kpi_alunos      = $conn->query("SELECT COUNT(*) FROM usuario WHERE tipo_usuario = 'aluno' AND ativo_usuario = TRUE")->fetchColumn();

$kpi_partidas    = $conn->query("SELECT COUNT(*) FROM partida WHERE status_partida = 'agendada'")->fetchColumn();

$kpi_realizadas  = $conn->query("SELECT COUNT(*) FROM partida WHERE status_partida = 'realizada'")->fetchColumn();

$kpi_modalidades = $conn->query("SELECT COUNT(*) FROM modalidade WHERE ativo_modalidade = TRUE")->fetchColumn();

/* ── AGENDA ──────────────────────────────────────── */
$agenda = $conn->query("
    SELECT p.id_partida, m.nome_modalidade,
           ta.nome_turma AS time_a, tb.nome_turma AS time_b,
           p.data_partida, p.hora_partida, p.local_partida, p.fase_partida
    FROM partida p
    JOIN edicao_modalidade em ON em.id_edicao_modalidade = p.edicao_modalidade_id
    JOIN modalidade m  ON m.id_modalidade  = em.modalidade_id_modalidade
    JOIN turma ta ON ta.id_turma = p.turma_id_time_a
    JOIN turma tb ON tb.id_turma = p.turma_id_time_b
    WHERE p.status_partida = 'agendada'
    ORDER BY p.data_partida, p.hora_partida
    LIMIT 30
")->fetchAll(PDO::FETCH_ASSOC);

/* ── SÚMULAS ─────────────────────────────────────── */
$sumulas = $conn->query("
    SELECT s.id_sumula,
           ta.nome_turma AS time_a, tb.nome_turma AS time_b,
           m.nome_modalidade,
           u.nome_usuario AS enviado_por,
           s.nome_arquivo_sumula, s.tipo_arquivo_sumula,
           s.data_envio_sumula, s.status_sumula
    FROM sumula s
    JOIN partida p  ON p.id_partida  = s.partida_id_partida
    JOIN edicao_modalidade em ON em.id_edicao_modalidade = p.edicao_modalidade_id
    JOIN modalidade m  ON m.id_modalidade  = em.modalidade_id_modalidade
    JOIN turma ta ON ta.id_turma = p.turma_id_time_a
    JOIN turma tb ON tb.id_turma = p.turma_id_time_b
    JOIN usuario u ON u.id_usuario = s.usuario_id_enviou
    ORDER BY s.data_envio_sumula DESC
")->fetchAll(PDO::FETCH_ASSOC);
$sumulas_pendentes = array_filter($sumulas, fn($s) => $s['status_sumula'] === 'pendente');

/* ── PARTIDAS ────────────────────────────────────── */
$partidas = $conn->query("
    SELECT p.id_partida, m.nome_modalidade,
           ta.nome_turma AS time_a, tb.nome_turma AS time_b,
           p.data_partida, p.hora_partida, p.local_partida,
           p.fase_partida, p.status_partida
    FROM partida p
    JOIN edicao_modalidade em ON em.id_edicao_modalidade = p.edicao_modalidade_id
    JOIN modalidade m  ON m.id_modalidade  = em.modalidade_id_modalidade
    JOIN turma ta ON ta.id_turma = p.turma_id_time_a
    JOIN turma tb ON tb.id_turma = p.turma_id_time_b
    ORDER BY p.data_partida DESC, p.hora_partida DESC
")->fetchAll(PDO::FETCH_ASSOC);

/* ── RESULTADOS ──────────────────────────────────── */
/*
   Busca TODAS as partidas já realizadas (com ou sem resultado),
   para exibir tanto placares registrados quanto o formulário inline.
*/
$resultados = $conn->query("
    SELECT p.id_partida,
           p.data_partida, p.status_partida,
           ta.nome_turma AS time_a, tb.nome_turma AS time_b,
           m.nome_modalidade,
           r.id_resultado,
           r.placar_time_a, r.placar_time_b,
           tv.nome_turma AS vencedor
    FROM partida p
    JOIN edicao_modalidade em ON em.id_edicao_modalidade = p.edicao_modalidade_id
    JOIN modalidade m  ON m.id_modalidade  = em.modalidade_id_modalidade
    JOIN turma ta ON ta.id_turma = p.turma_id_time_a
    JOIN turma tb ON tb.id_turma = p.turma_id_time_b
    LEFT JOIN resultado r ON r.partida_id_partida = p.id_partida
    LEFT JOIN turma tv   ON tv.id_turma = r.turma_id_vencedor
    WHERE p.status_partida IN ('realizada','agendada')
    ORDER BY p.data_partida DESC, p.hora_partida DESC
")->fetchAll(PDO::FETCH_ASSOC);

/* ── EDIÇÕES ─────────────────────────────────────── */
$edicoes = $conn->query("
    SELECT id_edicao, nome_edicao, ano_edicao,
           data_inicio_edicao, data_fim_edicao, status_edicao
    FROM edicao ORDER BY id_edicao DESC
")->fetchAll(PDO::FETCH_ASSOC);

/* ── ALUNOS ──────────────────────────────────────── */
$alunos = $conn->query("
    SELECT u.id_usuario, u.nome_usuario, u.email_usuario,
           t.nome_turma, u.tipo_usuario, u.genero_usuario, u.ativo_usuario
    FROM usuario u
    LEFT JOIN turma t ON t.id_turma = u.turma_id_turma
    WHERE u.tipo_usuario IN ('aluno','adm_sala')
    ORDER BY t.nome_turma, u.nome_usuario
")->fetchAll(PDO::FETCH_ASSOC);

/* ── SELECTs para modais ─────────────────────────── */
$turmas_select = $conn->query("SELECT id_turma, nome_turma FROM turma ORDER BY nome_turma")->fetchAll(PDO::FETCH_ASSOC);

$partidas_select = $conn->query("
    SELECT p.id_partida,
           CONCAT(
               m.nome_modalidade,' — ',
               ta.nome_turma,' vs ',
               tb.nome_turma,
               ' (',TO_CHAR(p.data_partida,'DD/MM'),')'
           ) AS label
    FROM partida p
    JOIN edicao_modalidade em ON em.id_edicao_modalidade = p.edicao_modalidade_id
    JOIN modalidade m ON m.id_modalidade = em.modalidade_id_modalidade
    JOIN turma ta ON ta.id_turma = p.turma_id_time_a
    JOIN turma tb ON tb.id_turma = p.turma_id_time_b
    ORDER BY p.data_partida DESC
")->fetchAll(PDO::FETCH_ASSOC);

$edicoes_modal_select = $conn->query("
    SELECT em.id_edicao_modalidade,
           CONCAT(e.nome_edicao,' — ',m.nome_modalidade) AS label
    FROM edicao_modalidade em
    JOIN edicao e ON e.id_edicao = em.edicao_id_edicao
    JOIN modalidade m ON m.id_modalidade = em.modalidade_id_modalidade
    ORDER BY e.ano_edicao DESC, m.nome_modalidade
")->fetchAll(PDO::FETCH_ASSOC);

/* ── SORTEIOS JÁ GERADOS ─────────────────────────── */
$sorteiosGerados = [];
try {
    foreach ($conn->query("SELECT edicao_modalidade_id FROM sorteio_gerado")->fetchAll(PDO::FETCH_COLUMN) as $emId) {
        $sorteiosGerados[$emId] = true;
    }
} catch (Exception $e) { /* tabela ainda não existe */ }

/* ── MODALIDADES POR EDIÇÃO ──────────────────────── */
$modalidadesPorEdicao = [];
$stmtMpe = $conn->query("
    SELECT em.id_edicao_modalidade,
       em.edicao_id_edicao,
       em.status_edicao_modalidade,
       m.nome_modalidade,
       COUNT(DISTINCT u.turma_id_turma) AS turmas_inscritas
FROM edicao_modalidade em
INNER JOIN modalidade m
    ON m.id_modalidade = em.modalidade_id_modalidade
LEFT JOIN inscricao i
    ON i.edicao_modalidade_id = em.id_edicao_modalidade
    AND i.status_inscricao = 'ativa'
LEFT JOIN usuario u
    ON u.id_usuario = i.usuario_id_usuario
GROUP BY
    em.id_edicao_modalidade,
    em.edicao_id_edicao,
    em.status_edicao_modalidade,
    m.nome_modalidade
ORDER BY m.nome_modalidade ASC
");
foreach ($stmtMpe->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $modalidadesPorEdicao[$row['edicao_id_edicao']][] = $row;
}
?>