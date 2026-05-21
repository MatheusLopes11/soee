<?php
// ── DADOS DO USUÁRIO + TURMA + FOTO ──────────────────────
$stmtUser = $conn->prepare("
    SELECT u.id_usuario, u.nome_usuario, u.genero_usuario,
           t.id_turma, t.nome_turma, t.periodo_turma,
           c.nome_curso, c.sigla_curso,
           fp.caminho_foto
    FROM usuario u
    LEFT JOIN turma t ON t.id_turma = u.turma_id_turma
    LEFT JOIN curso c ON c.id_curso = t.curso_id_curso
    LEFT JOIN foto_perfil fp ON fp.usuario_id_usuario = u.id_usuario AND fp.atual_foto = 1
    WHERE u.id_usuario = :id
    LIMIT 1
");
$stmtUser->execute([':id' => $userId]);
$userData = $stmtUser->fetch(PDO::FETCH_ASSOC);

$nomeTurma  = $userData['nome_turma']   ?? 'Sem turma';
$siglaCurso = $userData['sigla_curso']  ?? '';
$turmaId    = $userData['id_turma']     ?? null;
$fotoPerfil = $userData['caminho_foto'] ?? null;
$inicial    = mb_strtoupper(mb_substr($userNome, 0, 1));
$generoUser = $userData['genero_usuario'] ?? 'n';

// ── NOME DA CAMISA SALVO ANTERIORMENTE ───────────────────
// Busca o último nome de camisa que o aluno usou em qualquer inscrição ativa
$stmtNomeCamisa = $conn->prepare("
    SELECT nome_camisa_inscricao
    FROM inscricao
    WHERE usuario_id_usuario = :id
      AND nome_camisa_inscricao IS NOT NULL
      AND nome_camisa_inscricao != ''
    ORDER BY data_inscricao DESC
    LIMIT 1
");
$stmtNomeCamisa->execute([':id' => $userId]);
$nomeCamisaSalvo = $stmtNomeCamisa->fetchColumn() ?: '';

// ── INSCRIÇÕES ATIVAS DO ALUNO ────────────────────────────
$stmtInsc = $conn->prepare("
    SELECT i.id_inscricao, i.numero_camisa_inscricao, i.nome_camisa_inscricao,
           i.posicao_inscricao, i.capitao_inscricao,
           i.edicao_modalidade_id, i.status_inscricao,
           m.nome_modalidade, m.id_modalidade
    FROM inscricao i
    INNER JOIN edicao_modalidade em ON em.id_edicao_modalidade = i.edicao_modalidade_id
    INNER JOIN modalidade m ON m.id_modalidade = em.modalidade_id_modalidade
    WHERE i.usuario_id_usuario = :id AND i.status_inscricao = 'ativa'
    ORDER BY i.data_inscricao DESC
");
$stmtInsc->execute([':id' => $userId]);
$inscricoes = $stmtInsc->fetchAll(PDO::FETCH_ASSOC);
$modalidadesInscritas = array_column($inscricoes, 'edicao_modalidade_id');

// ── MODALIDADES DISPONÍVEIS PARA INSCRIÇÃO ────────────────
$stmtMod = $conn->query("
    SELECT m.id_modalidade, m.nome_modalidade, m.tipo_participacao,
           m.tipo_modalidade, m.genero_modalidade, em.id_edicao_modalidade,
           em.status_edicao_modalidade,
           em.data_inicio_inscricao, em.data_fim_inscricao,
           e.nome_edicao
    FROM modalidade m
    INNER JOIN edicao_modalidade em ON em.modalidade_id_modalidade = m.id_modalidade
    INNER JOIN edicao e ON e.id_edicao = em.edicao_id_edicao
    WHERE m.ativo_modalidade = 1
      AND em.status_edicao_modalidade = 'inscricoes'
      AND e.status_edicao != 'encerrado'
    ORDER BY m.nome_modalidade
");
$modalidades = $stmtMod->fetchAll(PDO::FETCH_ASSOC);

// ── PRÓXIMA PARTIDA DA TURMA ──────────────────────────────
$proximaPartida = null;
if ($turmaId) {
    $stmtProx = $conn->prepare("
        SELECT p.*, m.nome_modalidade,
               ta.nome_turma AS nome_time_a,
               tb.nome_turma AS nome_time_b
        FROM partida p
        INNER JOIN edicao_modalidade em ON em.id_edicao_modalidade = p.edicao_modalidade_id
        INNER JOIN modalidade m ON m.id_modalidade = em.modalidade_id_modalidade
        INNER JOIN turma ta ON ta.id_turma = p.turma_id_time_a
        INNER JOIN turma tb ON tb.id_turma = p.turma_id_time_b
        WHERE (p.turma_id_time_a = :t1 OR p.turma_id_time_b = :t2)
          AND p.status_partida = 'agendada'
          AND p.data_partida >= CURDATE()
        ORDER BY p.data_partida ASC, p.hora_partida ASC
        LIMIT 1
    ");
    $stmtProx->execute([':t1' => $turmaId, ':t2' => $turmaId]);
    $proximaPartida = $stmtProx->fetch(PDO::FETCH_ASSOC);
}

// ── TODAS AS PARTIDAS DA TURMA ────────────────────────────
$partidas = [];
if ($turmaId) {
    $stmtPart = $conn->prepare("
        SELECT p.id_partida, p.data_partida, p.hora_partida,
               p.local_partida, p.fase_partida, p.status_partida,
               p.turma_id_time_a, p.turma_id_time_b,
               ta.nome_turma AS time_a, tb.nome_turma AS time_b,
               m.nome_modalidade,
               r.placar_time_a, r.placar_time_b
        FROM partida p
        INNER JOIN turma ta ON ta.id_turma = p.turma_id_time_a
        INNER JOIN turma tb ON tb.id_turma = p.turma_id_time_b
        INNER JOIN edicao_modalidade em ON em.id_edicao_modalidade = p.edicao_modalidade_id
        INNER JOIN modalidade m ON m.id_modalidade = em.modalidade_id_modalidade
        LEFT JOIN resultado r ON r.partida_id_partida = p.id_partida
        WHERE (p.turma_id_time_a = :t1 OR p.turma_id_time_b = :t2)
        ORDER BY p.data_partida DESC, p.hora_partida DESC
    ");
    $stmtPart->execute([':t1' => $turmaId, ':t2' => $turmaId]);
    $partidas = $stmtPart->fetchAll(PDO::FETCH_ASSOC);
}

// ── CLASSIFICAÇÃO POR MODALIDADE ──────────────────────────
$classificacoes = [];
if ($turmaId && !empty($inscricoes)) {
    foreach ($inscricoes as $insc) {
        $emId = $insc['edicao_modalidade_id'];
        $stmtCl = $conn->prepare("
            SELECT cl.pontos, cl.vitorias, cl.derrotas, cl.empates,
                   cl.jogos, cl.saldo, cl.pontos_pro, cl.pontos_contra,
                   m.nome_modalidade
            FROM classificacao cl
            INNER JOIN edicao_modalidade em ON em.id_edicao_modalidade = cl.edicao_modalidade_id
            INNER JOIN modalidade m ON m.id_modalidade = em.modalidade_id_modalidade
            WHERE cl.edicao_modalidade_id = :emid AND cl.turma_id_turma = :turma
            LIMIT 1
        ");
        $stmtCl->execute([':emid' => $emId, ':turma' => $turmaId]);
        $row = $stmtCl->fetch(PDO::FETCH_ASSOC);
        if ($row) $classificacoes[$emId] = $row;
    }
}

// ── CLASSIFICAÇÃO GERAL ───────────────────────────────────
$rankingGeral = [];
if (!empty($inscricoes)) {
    foreach ($inscricoes as $insc) {
        $emId = $insc['edicao_modalidade_id'];
        $stmtRk = $conn->prepare("
            SELECT cl.pontos, cl.vitorias, cl.derrotas, cl.empates,
                   cl.jogos, cl.saldo, cl.pontos_pro, cl.pontos_contra,
                   t.nome_turma, m.nome_modalidade
            FROM classificacao cl
            INNER JOIN turma t ON t.id_turma = cl.turma_id_turma
            INNER JOIN edicao_modalidade em ON em.id_edicao_modalidade = cl.edicao_modalidade_id
            INNER JOIN modalidade m ON m.id_modalidade = em.modalidade_id_modalidade
            WHERE cl.edicao_modalidade_id = :emid
            ORDER BY cl.pontos DESC, cl.saldo DESC, cl.vitorias DESC
        ");
        $stmtRk->execute([':emid' => $emId]);
        $rankingGeral[$emId] = $stmtRk->fetchAll(PDO::FETCH_ASSOC);
    }
}

// ── ELENCO DA TURMA ───────────────────────────────────────
$elenco = [];
if ($turmaId && !empty($inscricoes)) {
    foreach ($inscricoes as $insc) {
        $emId = $insc['edicao_modalidade_id'];
        $stmtEl = $conn->prepare("
            SELECT u.id_usuario, u.nome_usuario, u.foto_perfil_usuario,
                   i.numero_camisa_inscricao, i.nome_camisa_inscricao,
                   i.posicao_inscricao, i.capitao_inscricao
            FROM inscricao i
            INNER JOIN usuario u ON u.id_usuario = i.usuario_id_usuario
            WHERE i.edicao_modalidade_id = :emid
              AND u.turma_id_turma = :turma
              AND i.status_inscricao = 'ativa'
            ORDER BY i.capitao_inscricao DESC, u.nome_usuario ASC
        ");
        $stmtEl->execute([':emid' => $emId, ':turma' => $turmaId]);
        $elenco[$emId] = $stmtEl->fetchAll(PDO::FETCH_ASSOC);
    }
}

// ── TODOS OS TIMES ────────────────────────────────────────
$todosOsTimes = [];
if (!empty($inscricoes)) {
    foreach ($inscricoes as $insc) {
        $emId = $insc['edicao_modalidade_id'];
        $stmtTimes = $conn->prepare("
            SELECT DISTINCT t.id_turma, t.nome_turma,
                   COUNT(i.id_inscricao) AS total_inscritos
            FROM inscricao i
            INNER JOIN usuario u ON u.id_usuario = i.usuario_id_usuario
            INNER JOIN turma t ON t.id_turma = u.turma_id_turma
            WHERE i.edicao_modalidade_id = :emid
              AND i.status_inscricao = 'ativa'
            GROUP BY t.id_turma, t.nome_turma
            ORDER BY t.nome_turma
        ");
        $stmtTimes->execute([':emid' => $emId]);
        $todosOsTimes[$emId] = $stmtTimes->fetchAll(PDO::FETCH_ASSOC);
    }
}

$faseLabel = [
    'grupos'=>'Grupos','oitavas'=>'Oitavas','quartas'=>'Quartas',
    'semi'=>'Semi','final'=>'Final','terceiro_lugar'=>'3º Lugar',
];
$statusLabel = [
    'agendada'=>'Agendada','realizada'=>'Realizada',
    'cancelada'=>'Cancelada','wo'=>'W.O.',
];
?>