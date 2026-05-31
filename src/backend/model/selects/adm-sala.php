<?php
$stmtUser = $conn->prepare("
    SELECT 
        u.id_usuario, u.nome_usuario, u.email_usuario,
        u.foto_perfil_usuario, u.genero_usuario,
        u.turma_id_turma AS turma_id,
        t.nome_turma, t.ano_serie_turma, t.periodo_turma,
        c.nome_curso, c.sigla_curso
    FROM usuario u
    LEFT JOIN turma t ON t.id_turma = u.turma_id_turma
    LEFT JOIN curso c ON c.id_curso = t.curso_id_curso
    WHERE u.id_usuario = :id
    LIMIT 1
");
$stmtUser->execute([':id' => $userId]);
$userData = $stmtUser->fetch(PDO::FETCH_ASSOC);
if (!$userData) die("Usuário não encontrado.");

$turmaId = (int) ($userData['turma_id'] ?? 0);

// ── ALUNOS DA TURMA ──────────────────────────────────────────────
$stmtAlunos = $conn->prepare("
    SELECT u.id_usuario, u.nome_usuario, u.email_usuario, u.foto_perfil_usuario
    FROM usuario u
    WHERE u.turma_id_turma = :turma
      AND u.tipo_usuario = 'aluno'
      AND u.ativo_usuario
    ORDER BY u.nome_usuario ASC
");
$stmtAlunos->execute([':turma' => $turmaId]);
$alunos = $stmtAlunos->fetchAll(PDO::FETCH_ASSOC);

// ── INSCRIÇÕES ────────────────────────────────────────────────────
$stmtInscricoes = $conn->prepare("
    SELECT i.id_inscricao, i.numero_camisa_inscricao, i.posicao_inscricao,
           i.capitao_inscricao, i.data_inscricao, i.status_inscricao,
           u.nome_usuario, m.nome_modalidade, e.nome_edicao
    FROM inscricao i
    INNER JOIN usuario u ON u.id_usuario = i.usuario_id_usuario
    INNER JOIN edicao_modalidade em ON em.id_edicao_modalidade = i.edicao_modalidade_id
    INNER JOIN modalidade m ON m.id_modalidade = em.modalidade_id_modalidade
    INNER JOIN edicao e ON e.id_edicao = em.edicao_id_edicao
    WHERE u.turma_id_turma = :turma
    ORDER BY i.data_inscricao DESC
");
$stmtInscricoes->execute([':turma' => $turmaId]);
$inscricoes = $stmtInscricoes->fetchAll(PDO::FETCH_ASSOC);

// ── PARTIDAS ──────────────────────────────────────────────────────
$stmtPartidas = $conn->prepare("
    SELECT p.id_partida, p.data_partida, p.hora_partida,
           p.local_partida, p.fase_partida, p.status_partida,
           ta.nome_turma AS time_a, tb.nome_turma AS time_b,
           m.nome_modalidade, r.placar_time_a, r.placar_time_b
    FROM partida p
    INNER JOIN turma ta ON ta.id_turma = p.turma_id_time_a
    INNER JOIN turma tb ON tb.id_turma = p.turma_id_time_b
    INNER JOIN edicao_modalidade em ON em.id_edicao_modalidade = p.edicao_modalidade_id
    INNER JOIN modalidade m ON m.id_modalidade = em.modalidade_id_modalidade
    LEFT JOIN resultado r ON r.partida_id_partida = p.id_partida
    WHERE (p.turma_id_time_a = :turma1 OR p.turma_id_time_b = :turma2)
    ORDER BY p.data_partida DESC, p.hora_partida DESC
");
$stmtPartidas->execute([':turma1' => $turmaId, ':turma2' => $turmaId]);
$partidas = $stmtPartidas->fetchAll(PDO::FETCH_ASSOC);

// ── CLASSIFICAÇÃO ─────────────────────────────────────────────────
$stmtClassif = $conn->prepare("
    SELECT cl.pontos, cl.vitorias, cl.derrotas, cl.empates,
           cl.pontos_pro, cl.pontos_contra, cl.saldo, cl.jogos,
           m.nome_modalidade, e.nome_edicao
    FROM classificacao cl
    INNER JOIN edicao_modalidade em ON em.id_edicao_modalidade = cl.edicao_modalidade_id
    INNER JOIN modalidade m ON m.id_modalidade = em.modalidade_id_modalidade
    INNER JOIN edicao e ON e.id_edicao = em.edicao_id_edicao
    WHERE cl.turma_id_turma = :turma
    ORDER BY cl.pontos DESC
");
$stmtClassif->execute([':turma' => $turmaId]);
$classificacoes = $stmtClassif->fetchAll(PDO::FETCH_ASSOC);

// ── STATS ─────────────────────────────────────────────────────────
$stmtStats = $conn->prepare("
    SELECT
        (SELECT COUNT(*) FROM usuario
         WHERE turma_id_turma = :t1
           AND tipo_usuario = 'aluno'
           AND ativo_usuario) AS total_alunos,

        (SELECT COUNT(*) FROM inscricao i
         INNER JOIN usuario u ON u.id_usuario = i.usuario_id_usuario
         WHERE u.turma_id_turma = :t2
           AND i.status_inscricao = 'ativa') AS total_inscricoes,

        (SELECT COUNT(*) FROM partida
         WHERE (turma_id_time_a = :t3 OR turma_id_time_b = :t3b)
           AND status_partida = 'realizada') AS partidas_realizadas,

        (SELECT COUNT(*) FROM partida
         WHERE (turma_id_time_a = :t4 OR turma_id_time_b = :t4b)
           AND status_partida = 'agendada'
           AND data_partida >= CURRENT_DATE) AS proximas_partidas
");
$stmtStats->execute([
    ':t1' => $turmaId, ':t2'  => $turmaId,
    ':t3' => $turmaId, ':t3b' => $turmaId,
    ':t4' => $turmaId, ':t4b' => $turmaId,
]);
$stats = $stmtStats->fetch(PDO::FETCH_ASSOC);

// ── MODALIDADES COM INSCRIÇÕES ABERTAS (widget) ───────────────────
$stmtModalidades = $conn->query("
    SELECT m.id_modalidade, m.nome_modalidade, m.tipo_participacao,
           em.id_edicao_modalidade, em.data_inicio_inscricao, em.data_fim_inscricao,
           em.status_edicao_modalidade, e.nome_edicao
    FROM edicao_modalidade em
    INNER JOIN modalidade m ON m.id_modalidade = em.modalidade_id_modalidade
    INNER JOIN edicao e ON e.id_edicao = em.edicao_id_edicao
    WHERE em.status_edicao_modalidade IN ('inscricoes', 'em_andamento')
      AND e.status_edicao != 'encerrado'
    ORDER BY em.data_fim_inscricao ASC
    LIMIT 6
");
$modalidades = $stmtModalidades->fetchAll(PDO::FETCH_ASSOC);

// ── TODAS AS MODALIDADES (painel completo) ────────────────────────
$stmtTodasModalidades = $conn->query("
    SELECT m.id_modalidade, m.nome_modalidade, m.descricao_modalidade,
           m.tipo_modalidade, m.formato_modalidade, m.tipo_participacao,
           m.qtd_min_jogadores, m.qtd_max_jogadores, m.ativo_modalidade,
           m.tipo_duracao, m.duracao_minutos, m.duracao_pontos,
           m.regulamento_modalidade
    FROM modalidade m
    ORDER BY m.nome_modalidade ASC
");
$todasModalidades = $stmtTodasModalidades->fetchAll(PDO::FETCH_ASSOC);

// ── INSCRIÇÕES ATIVAS DO PRÓPRIO ADM ─────────────────────────────
$stmtMinhasInsc = $conn->prepare("
    SELECT i.id_inscricao, i.numero_camisa_inscricao, i.nome_camisa_inscricao,
           i.posicao_inscricao, i.capitao_inscricao, i.status_inscricao,
           i.edicao_modalidade_id,
           m.nome_modalidade, e.nome_edicao
    FROM inscricao i
    INNER JOIN edicao_modalidade em ON em.id_edicao_modalidade = i.edicao_modalidade_id
    INNER JOIN modalidade m         ON m.id_modalidade = em.modalidade_id_modalidade
    INNER JOIN edicao e             ON e.id_edicao     = em.edicao_id_edicao
    WHERE i.usuario_id_usuario = :id
      AND i.status_inscricao   = 'ativa'
    ORDER BY i.data_inscricao DESC
");
$stmtMinhasInsc->execute([':id' => $userId]);
$minhasInscricoes = $stmtMinhasInsc->fetchAll(PDO::FETCH_ASSOC);
$minhasEmIds = array_column($minhasInscricoes, 'edicao_modalidade_id');
 
// Nome de camisa mais recente do ADM (pré-preenche o form)
$stmtNomeCamisaAdm = $conn->prepare("
    SELECT nome_camisa_inscricao
    FROM inscricao
    WHERE usuario_id_usuario  = :id
      AND nome_camisa_inscricao IS NOT NULL
      AND nome_camisa_inscricao != ''
    ORDER BY data_inscricao DESC
    LIMIT 1
");
$stmtNomeCamisaAdm->execute([':id' => $userId]);
$nomeCamisaAdm = $stmtNomeCamisaAdm->fetchColumn() ?: '';
 
// Gênero do ADM (para filtrar modalidades por gênero)
$generoAdm = $userData['genero_usuario'] ?? 'n';
 
// ── TODAS AS EDIÇÕES (para vincular — incluindo todas) ────────────
// Buscamos TODAS as edições disponíveis (criadas pelo professor ou adm)
$stmtEdicoes = $conn->query("
    SELECT id_edicao, nome_edicao, ano_edicao, status_edicao
    FROM edicao
    ORDER BY ano_edicao DESC, id_edicao DESC
");
$edicoesAtivas = $stmtEdicoes->fetchAll(PDO::FETCH_ASSOC);
?>