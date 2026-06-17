<?php
// ═══════════════════════════════════════════════════════════
//  selects/professor.php — SOEE
//  FIX: $userId era usado antes de ser definido.
//       Agora lemos da sessão logo no topo, antes de qualquer query.
// ═══════════════════════════════════════════════════════════

// FIX: garante que $userId e $usuario_logado existem mesmo que
//      o arquivo seja incluído antes de serem definidos no controlador.
$userId         = $userId         ?? AuthHome::getId();
$usuario_logado = $usuario_logado ?? AuthHome::getNome();

// ── KPIs ─────────────────────────────────────────────────
$kpi_alunos = $conn->query("
    SELECT COUNT(*) FROM usuario
    WHERE tipo_usuario IN ('aluno','adm_sala') AND ativo_usuario = TRUE
")->fetchColumn();

$kpi_partidas = $conn->query("
    SELECT COUNT(*) FROM partida WHERE status_partida = 'agendada'
")->fetchColumn();

$kpi_realizadas = $conn->query("
    SELECT COUNT(*) FROM partida WHERE status_partida = 'realizada'
")->fetchColumn();

$kpi_modalidades = $conn->query("
    SELECT COUNT(DISTINCT m.id_modalidade)
    FROM modalidade m
    INNER JOIN edicao_modalidade em ON em.modalidade_id_modalidade = m.id_modalidade
    INNER JOIN edicao e            ON e.id_edicao = em.edicao_id_edicao
    WHERE e.status_edicao = 'em_andamento' AND m.ativo_modalidade = TRUE
")->fetchColumn();

// ── AGENDA (próximas partidas) ────────────────────────────
$agenda = $conn->query("
    SELECT
        p.id_partida,
        p.data_partida,
        p.hora_partida,
        p.local_partida,
        ta.nome_turma AS time_a,
        tb.nome_turma AS time_b,
        m.nome_modalidade
    FROM partida p
    INNER JOIN turma ta ON ta.id_turma = p.turma_id_time_a
    INNER JOIN turma tb ON tb.id_turma = p.turma_id_time_b
    INNER JOIN edicao_modalidade em ON em.id_edicao_modalidade = p.edicao_modalidade_id
    INNER JOIN modalidade m         ON m.id_modalidade = em.modalidade_id_modalidade
    WHERE p.status_partida = 'agendada'
      AND p.data_partida >= CURRENT_DATE
    ORDER BY p.data_partida ASC, p.hora_partida ASC
    LIMIT 50
")->fetchAll(PDO::FETCH_ASSOC);

// ── EDIÇÕES ────────────────────────────────────────────────
$edicoes = $conn->query("
    SELECT * FROM edicao ORDER BY ano_edicao DESC, id_edicao DESC
")->fetchAll(PDO::FETCH_ASSOC);

// ── MODALIDADES POR EDIÇÃO (para coluna de sorteio) ────────
$modalidadesPorEdicao = [];
$stmtMods = $conn->query("
    SELECT
        em.id_edicao_modalidade,
        em.edicao_id_edicao,
        m.nome_modalidade,
        COUNT(DISTINCT u.turma_id_turma) AS turmas_inscritas
    FROM edicao_modalidade em
    INNER JOIN modalidade m ON m.id_modalidade = em.modalidade_id_modalidade
    LEFT  JOIN inscricao  i ON i.edicao_modalidade_id = em.id_edicao_modalidade
                            AND i.status_inscricao = 'ativa'
    LEFT  JOIN usuario    u ON u.id_usuario = i.usuario_id_usuario
    GROUP BY em.id_edicao_modalidade, em.edicao_id_edicao, m.nome_modalidade
    ORDER BY m.nome_modalidade
");
foreach ($stmtMods->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $modalidadesPorEdicao[$row['edicao_id_edicao']][] = $row;
}

// ── SORTEIOS JÁ GERADOS ────────────────────────────────────
$sorteiosGerados = [];
$stmtSg = $conn->query("SELECT edicao_modalidade_id FROM sorteio_gerado");
foreach ($stmtSg->fetchAll(PDO::FETCH_COLUMN) as $emId) {
    $sorteiosGerados[$emId] = true;
}

// ── PARTIDAS (painel Partidas) ─────────────────────────────
$partidas = $conn->query("
    SELECT
        p.id_partida,
        p.data_partida,
        p.hora_partida,
        p.local_partida,
        p.fase_partida,
        p.status_partida,
        ta.nome_turma AS time_a,
        tb.nome_turma AS time_b,
        m.nome_modalidade
    FROM partida p
    INNER JOIN turma ta ON ta.id_turma = p.turma_id_time_a
    INNER JOIN turma tb ON tb.id_turma = p.turma_id_time_b
    INNER JOIN edicao_modalidade em ON em.id_edicao_modalidade = p.edicao_modalidade_id
    INNER JOIN modalidade m         ON m.id_modalidade = em.modalidade_id_modalidade
    ORDER BY p.data_partida DESC, p.hora_partida DESC
")->fetchAll(PDO::FETCH_ASSOC);

// ── RESULTADOS ────────────────────────────────────────────
$resultados = $conn->query("
    SELECT
        p.id_partida,
        p.data_partida,
        p.status_partida,
        ta.nome_turma AS time_a,
        tb.nome_turma AS time_b,
        m.nome_modalidade,
        r.id_resultado,
        r.placar_time_a,
        r.placar_time_b,
        tv.nome_turma AS vencedor
    FROM partida p
    INNER JOIN turma ta ON ta.id_turma = p.turma_id_time_a
    INNER JOIN turma tb ON tb.id_turma = p.turma_id_time_b
    INNER JOIN edicao_modalidade em ON em.id_edicao_modalidade = p.edicao_modalidade_id
    INNER JOIN modalidade m         ON m.id_modalidade = em.modalidade_id_modalidade
    LEFT  JOIN resultado r  ON r.partida_id_partida = p.id_partida
    LEFT  JOIN turma     tv ON tv.id_turma = r.turma_id_vencedor
    WHERE p.status_partida IN ('agendada','realizada','wo')
    ORDER BY p.data_partida DESC
")->fetchAll(PDO::FETCH_ASSOC);

// ── MODALIDADES ────────────────────────────────────────────
$modalidades = $conn->query("
    SELECT
        id_modalidade,
        nome_modalidade,
        tipo_modalidade,
        formato_modalidade,
        tipo_participacao,
        genero_modalidade,
        qtd_min_jogadores,
        qtd_max_jogadores,
        ativo_modalidade,
        descricao_modalidade,
        regulamento_modalidade,
        tipo_duracao,
        duracao_minutos,
        duracao_pontos
    FROM modalidade
    ORDER BY id_modalidade
")->fetchAll(PDO::FETCH_ASSOC);

// ── SÚMULAS PENDENTES (widget do overview) ────────────────
// FIX: traz apenas os campos necessários para o widget resumido
$sumulas_pendentes = $conn->query("
    SELECT
        s.id_sumula,
        s.status_sumula,
        s.partida_id_partida,
        u.nome_usuario AS enviado_por,
        m.nome_modalidade,
        ta.nome_turma AS time_a,
        tb.nome_turma AS time_b
    FROM sumula s
    INNER JOIN usuario u  ON u.id_usuario  = s.usuario_id_enviou
    INNER JOIN partida p  ON p.id_partida  = s.partida_id_partida
    INNER JOIN turma   ta ON ta.id_turma   = p.turma_id_time_a
    INNER JOIN turma   tb ON tb.id_turma   = p.turma_id_time_b
    INNER JOIN edicao_modalidade em ON em.id_edicao_modalidade = p.edicao_modalidade_id
    INNER JOIN modalidade m         ON m.id_modalidade = em.modalidade_id_modalidade
    WHERE s.status_sumula = 'pendente'
    ORDER BY s.data_envio_sumula DESC
")->fetchAll(PDO::FETCH_ASSOC);

// ── TODAS AS SÚMULAS (painel Súmulas) ────────────────────
// FIX: adicionados caminho_arquivo_sumula e partida_id_partida
//      que estavam faltando e causavam os erros no dashboard
$sumulas = $conn->query("
    SELECT
        s.id_sumula,
        s.partida_id_partida,
        s.nome_arquivo_sumula,
        s.caminho_arquivo_sumula,
        s.tipo_arquivo_sumula,
        s.data_envio_sumula,
        s.status_sumula,
        u.nome_usuario AS enviado_por,
        m.nome_modalidade,
        ta.nome_turma AS time_a,
        tb.nome_turma AS time_b
    FROM sumula s
    INNER JOIN usuario u  ON u.id_usuario  = s.usuario_id_enviou
    INNER JOIN partida p  ON p.id_partida  = s.partida_id_partida
    INNER JOIN turma   ta ON ta.id_turma   = p.turma_id_time_a
    INNER JOIN turma   tb ON tb.id_turma   = p.turma_id_time_b
    INNER JOIN edicao_modalidade em ON em.id_edicao_modalidade = p.edicao_modalidade_id
    INNER JOIN modalidade m         ON m.id_modalidade = em.modalidade_id_modalidade
    ORDER BY s.data_envio_sumula DESC
")->fetchAll(PDO::FETCH_ASSOC);

// ── ALUNOS & TURMAS ───────────────────────────────────────
$alunos = $conn->query("
    SELECT
        u.id_usuario,
        u.nome_usuario,
        u.email_usuario,
        u.genero_usuario,
        u.tipo_usuario,
        u.ativo_usuario,
        t.nome_turma
    FROM usuario u
    LEFT JOIN turma t ON t.id_turma = u.turma_id_turma
    WHERE u.tipo_usuario IN ('aluno','adm_sala')
      AND u.ativo_usuario = TRUE
    ORDER BY t.nome_turma ASC, u.nome_usuario ASC
")->fetchAll(PDO::FETCH_ASSOC);

// ── PROFESSORES (painel de gestão) ───────────────────────
// Exclui o próprio professor logado para evitar auto-remoção acidental
$stmtProf = $conn->prepare("
    SELECT
        u.id_usuario,
        u.nome_usuario,
        u.email_usuario,
        u.ativo_usuario,
        u.tipo_usuario
    FROM usuario u
    WHERE u.tipo_usuario IN ('professor', 'adm_geral')
      AND u.id_usuario != :uid
    ORDER BY u.tipo_usuario DESC, u.nome_usuario ASC
");
$stmtProf->execute([':uid' => $userId]);
$professores = $stmtProf->fetchAll(PDO::FETCH_ASSOC);

// ── FOTO DE PERFIL do professor logado ────────────────────
$stmtFoto = $conn->prepare("
    SELECT caminho_foto FROM foto_perfil
    WHERE usuario_id_usuario = :uid AND atual_foto = TRUE
    LIMIT 1
");
$stmtFoto->execute([':uid' => $userId]);
$fotoPerfil = $stmtFoto->fetchColumn() ?: '';

// ── SELECTS P/ MODAIS ─────────────────────────────────────
$edicoes_modal_select = $conn->query("
    SELECT
        em.id_edicao_modalidade,
        e.nome_edicao || ' — ' || m.nome_modalidade AS label
    FROM edicao_modalidade em
    INNER JOIN edicao     e ON e.id_edicao    = em.edicao_id_edicao
    INNER JOIN modalidade m ON m.id_modalidade = em.modalidade_id_modalidade
    ORDER BY e.ano_edicao DESC, m.nome_modalidade
")->fetchAll(PDO::FETCH_ASSOC);

$turmas_select = $conn->query("
    SELECT id_turma, nome_turma FROM turma ORDER BY nome_turma
")->fetchAll(PDO::FETCH_ASSOC);

$partidas_select = $conn->query("
    SELECT
        p.id_partida,
        m.nome_modalidade || ' — ' || ta.nome_turma || ' vs ' || tb.nome_turma AS label
    FROM partida p
    INNER JOIN turma ta ON ta.id_turma = p.turma_id_time_a
    INNER JOIN turma tb ON tb.id_turma = p.turma_id_time_b
    INNER JOIN edicao_modalidade em ON em.id_edicao_modalidade = p.edicao_modalidade_id
    INNER JOIN modalidade m         ON m.id_modalidade = em.modalidade_id_modalidade
    ORDER BY p.data_partida DESC
")->fetchAll(PDO::FETCH_ASSOC);