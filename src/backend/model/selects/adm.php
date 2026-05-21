<?php
// ── FOTO DE PERFIL ─────────────────────────────────────────
$stmtFoto = $conn->prepare("
    SELECT caminho_foto FROM foto_perfil
    WHERE usuario_id_usuario = :id AND atual_foto = 1
    LIMIT 1
");
$stmtFoto->execute([':id' => $userId]);
$fotoPerfil = $stmtFoto->fetchColumn();

// ── KPIs ──────────────────────────────────────────────────
$kpi_alunos      = $conn->query("SELECT COUNT(*) FROM usuario WHERE tipo_usuario = 'aluno' AND ativo_usuario = 1")->fetchColumn();
$kpi_partidas    = $conn->query("SELECT COUNT(*) FROM partida WHERE status_partida = 'agendada'")->fetchColumn();
$kpi_realizadas  = $conn->query("SELECT COUNT(*) FROM partida WHERE status_partida = 'realizada'")->fetchColumn();
$kpi_modalidades = $conn->query("SELECT COUNT(*) FROM modalidade WHERE ativo_modalidade = 1")->fetchColumn();

// ── USUÁRIOS ───────────────────────────────────────────────
$usuarios = $conn->query("
    SELECT u.id_usuario, u.nome_usuario, u.email_usuario,
           t.nome_turma, u.tipo_usuario, u.genero_usuario, u.ativo_usuario
    FROM usuario u
    LEFT JOIN turma t ON t.id_turma = u.turma_id_turma
    ORDER BY u.id_usuario
")->fetchAll(PDO::FETCH_ASSOC);

// ── TURMAS ─────────────────────────────────────────────────
$turmas = $conn->query("
    SELECT t.id_turma, t.nome_turma, c.nome_curso, t.ano_serie_turma,
           t.ano_letivo_turma, t.periodo_turma
    FROM turma t
    JOIN curso c ON c.id_curso = t.curso_id_curso
    ORDER BY t.id_turma
")->fetchAll(PDO::FETCH_ASSOC);

// ── MODALIDADES ────────────────────────────────────────────
$modalidades = $conn->query("
    SELECT id_modalidade, nome_modalidade, tipo_modalidade,
           formato_modalidade, tipo_participacao,
           qtd_min_jogadores, qtd_max_jogadores, ativo_modalidade,
           descricao_modalidade
    FROM modalidade
    ORDER BY id_modalidade
")->fetchAll(PDO::FETCH_ASSOC);

// ── EDIÇÕES ────────────────────────────────────────────────
$edicoes = $conn->query("
    SELECT id_edicao, nome_edicao, ano_edicao,
           data_inicio_edicao, data_fim_edicao, status_edicao,
           descricao_edicao
    FROM edicao
    ORDER BY id_edicao DESC
")->fetchAll(PDO::FETCH_ASSOC);

// ── PARTIDAS ───────────────────────────────────────────────
$partidas = $conn->query("
    SELECT p.id_partida, m.nome_modalidade,
           ta.nome_turma AS time_a, tb.nome_turma AS time_b,
           p.turma_id_time_a, p.turma_id_time_b,
           p.edicao_modalidade_id,
           p.data_partida, p.hora_partida, p.local_partida,
           p.fase_partida, p.status_partida, p.observacoes_partida
    FROM partida p
    JOIN edicao_modalidade em ON em.id_edicao_modalidade = p.edicao_modalidade_id
    JOIN modalidade m ON m.id_modalidade = em.modalidade_id_modalidade
    JOIN turma ta ON ta.id_turma = p.turma_id_time_a
    JOIN turma tb ON tb.id_turma = p.turma_id_time_b
    ORDER BY p.data_partida, p.hora_partida
")->fetchAll(PDO::FETCH_ASSOC);

// ── RESULTADOS ─────────────────────────────────────────────
$resultados = $conn->query("
    SELECT r.id_resultado,
           ta.nome_turma AS time_a, tb.nome_turma AS time_b,
           m.nome_modalidade,
           r.partida_id_partida,
           r.placar_time_a, r.placar_time_b,
           r.turma_id_vencedor,
           tv.nome_turma AS vencedor,
           r.observacoes_resultado
    FROM resultado r
    JOIN partida p ON p.id_partida = r.partida_id_partida
    JOIN edicao_modalidade em ON em.id_edicao_modalidade = p.edicao_modalidade_id
    JOIN modalidade m ON m.id_modalidade = em.modalidade_id_modalidade
    JOIN turma ta ON ta.id_turma = p.turma_id_time_a
    JOIN turma tb ON tb.id_turma = p.turma_id_time_b
    LEFT JOIN turma tv ON tv.id_turma = r.turma_id_vencedor
    ORDER BY r.id_resultado DESC
")->fetchAll(PDO::FETCH_ASSOC);

// ── SÚMULAS — CORRIGIDO: caminho_arquivo_sumula incluído ───
$sumulas = $conn->query("
    SELECT s.id_sumula,
           ta.nome_turma AS time_a, tb.nome_turma AS time_b,
           m.nome_modalidade,
           u.nome_usuario AS enviado_por,
           s.nome_arquivo_sumula, s.caminho_arquivo_sumula,
           s.tipo_arquivo_sumula,
           s.data_envio_sumula, s.status_sumula
    FROM sumula s
    JOIN partida p ON p.id_partida = s.partida_id_partida
    JOIN edicao_modalidade em ON em.id_edicao_modalidade = p.edicao_modalidade_id
    JOIN modalidade m ON m.id_modalidade = em.modalidade_id_modalidade
    JOIN turma ta ON ta.id_turma = p.turma_id_time_a
    JOIN turma tb ON tb.id_turma = p.turma_id_time_b
    JOIN usuario u ON u.id_usuario = s.usuario_id_enviou
    ORDER BY s.data_envio_sumula DESC
")->fetchAll(PDO::FETCH_ASSOC);
$sumulas_pendentes = array_filter($sumulas, fn($s) => $s['status_sumula'] === 'pendente');

// ── AGENDA ─────────────────────────────────────────────────
$agenda = $conn->query("
    SELECT p.id_partida, m.nome_modalidade,
           ta.nome_turma AS time_a, tb.nome_turma AS time_b,
           p.data_partida, p.hora_partida, p.local_partida, p.fase_partida
    FROM partida p
    JOIN edicao_modalidade em ON em.id_edicao_modalidade = p.edicao_modalidade_id
    JOIN modalidade m ON m.id_modalidade = em.modalidade_id_modalidade
    JOIN turma ta ON ta.id_turma = p.turma_id_time_a
    JOIN turma tb ON tb.id_turma = p.turma_id_time_b
    WHERE p.status_partida = 'agendada'
    ORDER BY p.data_partida, p.hora_partida
    LIMIT 20
")->fetchAll(PDO::FETCH_ASSOC);

// ── SELECT options para modais ─────────────────────────────
$turmas_select   = $conn->query("SELECT id_turma, nome_turma FROM turma ORDER BY nome_turma")->fetchAll(PDO::FETCH_ASSOC);
$partidas_select = $conn->query("
    SELECT p.id_partida,
           CONCAT(m.nome_modalidade,' — ',ta.nome_turma,' vs ',tb.nome_turma,
                  ' (',DATE_FORMAT(p.data_partida,'%d/%m'),')') AS label
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
$cursos = $conn->query("SELECT id_curso, nome_curso FROM curso ORDER BY nome_curso")->fetchAll(PDO::FETCH_ASSOC);
?>