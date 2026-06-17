<?php



$tipoIcons = [
    'quadra' => 'fa-basketball',
    'mesa'   => 'fa-table-tennis-paddle-ball',
    'campo'  => 'fa-futbol',
    'outro'  => 'fa-medal',
];

$faseLabel = [
    'grupos'         => 'Fase de Grupos',
    'oitavas'        => 'Oitavas de Final',
    'quartas'        => 'Quartas de Final',
    'semi'           => 'Semifinais',
    'terceiro_lugar' => '3º Lugar',
    'final'          => 'Grande Final',
];

$formatoLabel = [
    'grupos'             => 'Somente Grupos',
    'mata_mata'          => 'Somente Mata-Mata',
    'grupos_mata_mata'   => 'Grupos + Mata-Mata',
    'todos_contra_todos' => 'Todos Contra Todos',
];

$participacaoLabel = [
    'solo'  => 'Individual',
    'dupla' => 'Dupla',
    'trio'  => 'Trio',
    'time'  => 'Times por Turma',
];

$faseOrdemMata = ['oitavas', 'quartas', 'semi', 'terceiro_lugar', 'final'];

// FIX: $formato e $esporte podem ser null; usar null-coalescing seguro
$temMataMata  = ($formato ?? null) && in_array($formato, ['mata_mata', 'grupos_mata_mata']);
$icone        = $tipoIcons[$esporte['tipo_modalidade'] ?? 'outro'] ?? 'fa-medal';

// FIX: $participacao já vem definido em classificacao.php (select), mas garantimos fallback
$participacao = $participacao ?? ($esporte['tipo_participacao'] ?? 'time');
$ehIndividual = in_array($participacao, ['solo', 'dupla', 'trio']);

// Quantos classificam por grupo para mata-mata
$classificamPorGrupo = 2;

if (!function_exists('fmtData')) {
    function fmtData($d)     { return $d ? date('d/m', strtotime($d)) : '—'; }
}
if (!function_exists('fmtDataLong')) {
    function fmtDataLong($d) { return $d ? date('d/m/Y', strtotime($d)) : '—'; }
}
if (!function_exists('fmtHora')) {
    function fmtHora($h)     { return $h ? substr($h, 0, 5) : ''; }
}

// ── Paleta de cores para avatares ─────────────────────
$avatarColors = [
    '#e63946','#f4a261','#2a9d8f','#457b9d','#7b2d8b',
    '#f77f00','#4cc9f0','#06d6a0','#ef476f','#118ab2',
];

/**
 * Retorna o HTML de um avatar colorido com duas iniciais.
 * - Duplas ("Ana Sardi & Clara Cenni"): inicial de cada pessoa → "AC"
 * - Nome simples: inicial do primeiro + inicial do último sobrenome → "AS"
 */
if (!function_exists('avatar')) {
    function avatar(string $nome): string {
        global $avatarColors;

        if (empty(trim($nome))) {
            return '<span class="av" style="background:#888">??</span>';
        }

        // Se for dupla/trio (contém " & "), pega a inicial de cada lado
        if (str_contains($nome, ' & ')) {
            $partes = explode(' & ', $nome, 2);
            $i1 = mb_strtoupper(mb_substr(trim($partes[0]), 0, 1));
            $i2 = mb_strtoupper(mb_substr(trim($partes[1]), 0, 1));
            $iniciais = $i1 . $i2;
        } else {
            // Nome simples: inicial do primeiro + inicial do último sobrenome
            $palavras = array_values(array_filter(explode(' ', trim($nome))));
            $i1 = mb_strtoupper(mb_substr($palavras[0], 0, 1));
            $i2 = count($palavras) > 1
                ? mb_strtoupper(mb_substr($palavras[count($palavras) - 1], 0, 1))
                : mb_strtoupper(mb_substr($palavras[0], 1, 1));
            $iniciais = $i1 . $i2;
        }

        $cor = $avatarColors[array_sum(array_map('ord', str_split($nome))) % count($avatarColors)];

        return '<span class="av" style="background:' . $cor . '">'
             . htmlspecialchars($iniciais)
             . '</span>';
    }
}

/**
 * Retorna o nome de exibição de uma dupla/trio a partir de
 * uma linha de classificação que já possui nome_dupla_exibicao.
 * Se não houver, usa nome_exibicao padrão.
 */
function getNomeExibicao(array $row): string {
    return !empty($row['nome_dupla_exibicao'])
        ? $row['nome_dupla_exibicao']
        : ($row['nome_exibicao'] ?? '');
}

// ─────────────────────────────────────────────────────
//  QUERY PRINCIPAL: monta $grupos com suporte a duplas
// ─────────────────────────────────────────────────────

function montarGruposClassificacao(
    PDO    $conn,
    int    $modalidadeId,
    string $participacao
): array {

    $ehIndividual  = in_array($participacao, ['solo', 'dupla', 'trio']);
    $ehDuplaOuTrio = in_array($participacao, ['dupla', 'trio']);

    if ($ehDuplaOuTrio) {
        // ── Dupla / Trio ─────────────────────────────────
        $stmt = $conn->prepare("
            SELECT
                c.id_classificacao,
                c.grupo_classificacao,
                c.pontos,
                c.vitorias,
                c.derrotas,
                c.empates,
                c.pontos_pro,
                c.pontos_contra,
                c.saldo,
                c.jogos,
                c.nome_dupla_exibicao,
                c.grupo_dupla_id,
                c.usuario_id_participante,
                u.nome_usuario,
                t.nome_turma AS subtitulo
            FROM classificacao c
            INNER JOIN usuario u ON u.id_usuario = c.usuario_id_participante
            INNER JOIN turma   t ON t.id_turma   = c.turma_id_turma
            WHERE c.edicao_modalidade_id = :emId
            ORDER BY c.grupo_classificacao ASC,
                     c.pontos DESC,
                     c.saldo  DESC,
                     c.vitorias DESC
        ");
        $stmt->execute([':emId' => $modalidadeId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $duplasPorGrupo = [];
        foreach ($rows as $row) {
            $gd    = $row['grupo_dupla_id'];
            $grupo = $row['grupo_classificacao'] ?? 'A';

            if ($gd) {
                if (!isset($duplasPorGrupo[$grupo][$gd])) {
                    $duplasPorGrupo[$grupo][$gd] = $row;
                    $duplasPorGrupo[$grupo][$gd]['_membros'] = [$row['nome_usuario']];
                } else {
                    $duplasPorGrupo[$grupo][$gd]['_membros'][] = $row['nome_usuario'];
                }
            } else {
                $duplasPorGrupo[$grupo]['_solo_' . $row['id_classificacao']] = $row;
                $duplasPorGrupo[$grupo]['_solo_' . $row['id_classificacao']]['_membros'] = [$row['nome_usuario']];
            }
        }

        $grupos = [];
        foreach ($duplasPorGrupo as $grupo => $duplas) {
            $lista = [];
            foreach ($duplas as $dupla) {
                $membros = $dupla['_membros'] ?? [$dupla['nome_usuario']];
                $nomeExibicao = !empty($dupla['nome_dupla_exibicao'])
                    ? $dupla['nome_dupla_exibicao']
                    : implode(' & ', $membros);

                $lista[] = array_merge($dupla, [
                    'nome_exibicao' => $nomeExibicao,
                    'subtitulo'     => $dupla['subtitulo'] ?? '',
                ]);
            }
            usort($lista, fn($a, $b) =>
                $b['pontos']   <=> $a['pontos']   ?:
                $b['saldo']    <=> $a['saldo']     ?:
                $b['vitorias'] <=> $a['vitorias']
            );
            $grupos[$grupo] = $lista;
        }

        return $grupos;

    } elseif ($ehIndividual) {
        // ── Solo ─────────────────────────────────────────
        $stmt = $conn->prepare("
            SELECT
                c.id_classificacao,
                c.grupo_classificacao,
                c.pontos, c.vitorias, c.derrotas, c.empates,
                c.pontos_pro, c.pontos_contra, c.saldo, c.jogos,
                c.usuario_id_participante,
                u.nome_usuario AS nome_exibicao,
                t.nome_turma   AS subtitulo
            FROM classificacao c
            INNER JOIN usuario u ON u.id_usuario = c.usuario_id_participante
            INNER JOIN turma   t ON t.id_turma   = c.turma_id_turma
            WHERE c.edicao_modalidade_id = :emId
            ORDER BY c.grupo_classificacao ASC,
                     c.pontos DESC, c.saldo DESC, c.vitorias DESC
        ");
        $stmt->execute([':emId' => $modalidadeId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $grupos = [];
        foreach ($rows as $row) {
            $grupos[$row['grupo_classificacao'] ?? 'A'][] = $row;
        }
        return $grupos;

    } else {
        // ── Por time / turma ─────────────────────────────
        $stmt = $conn->prepare("
            SELECT
                c.id_classificacao,
                c.grupo_classificacao,
                c.pontos, c.vitorias, c.derrotas, c.empates,
                c.pontos_pro, c.pontos_contra, c.saldo, c.jogos,
                t.nome_turma AS nome_exibicao,
                NULL::text   AS subtitulo
            FROM classificacao c
            INNER JOIN turma t ON t.id_turma = c.turma_id_turma
            WHERE c.edicao_modalidade_id = :emId
              AND c.usuario_id_participante IS NULL
            ORDER BY c.grupo_classificacao ASC,
                     c.pontos DESC, c.saldo DESC, c.vitorias DESC
        ");
        $stmt->execute([':emId' => $modalidadeId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $grupos = [];
        foreach ($rows as $row) {
            $grupos[$row['grupo_classificacao'] ?? 'A'][] = $row;
        }
        return $grupos;
    }
}

// ─────────────────────────────────────────────────────
//  PARTIDAS: monta nome de exibição para duplas
// ─────────────────────────────────────────────────────

/**
 * Dado um array de partidas e a conexão PDO, enriquece cada
 * partida com time_a / time_b como nome da dupla quando aplicável.
 */
function enriquecerPartidasComNomeDupla(
    array  &$partidas,
    PDO    $conn,
    string $participacao
): void {
    if (!in_array($participacao, ['dupla', 'trio'])) return;
    if (empty($partidas)) return;

    $uids = [];
    foreach ($partidas as $p) {
        if (!empty($p['usuario_id_time_a'])) $uids[] = (int)$p['usuario_id_time_a'];
        if (!empty($p['usuario_id_time_b'])) $uids[] = (int)$p['usuario_id_time_b'];
    }
    $uids = array_unique($uids);
    if (empty($uids)) return;

    $emId = $partidas[0]['edicao_modalidade_id'] ?? 0;
    if (!$emId) return;

    $placeholders = implode(',', array_fill(0, count($uids), '?'));
    $stmt = $conn->prepare("
        SELECT
            i.usuario_id_usuario,
            i.grupo_dupla_id,
            string_agg(u2.nome_usuario, ' & ' ORDER BY u2.id_usuario) AS nome_dupla
        FROM inscricao i
        INNER JOIN inscricao i2 ON i2.grupo_dupla_id = i.grupo_dupla_id
                                AND i2.edicao_modalidade_id = i.edicao_modalidade_id
        INNER JOIN usuario u2   ON u2.id_usuario = i2.usuario_id_usuario
        WHERE i.edicao_modalidade_id = ?
          AND i.usuario_id_usuario IN ($placeholders)
          AND i.grupo_dupla_id IS NOT NULL
          AND i.status_inscricao = 'ativa'
        GROUP BY i.usuario_id_usuario, i.grupo_dupla_id
    ");
    $stmt->execute(array_merge([$emId], $uids));

    $nomePorUid = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $nomePorUid[(int)$row['usuario_id_usuario']] = $row['nome_dupla'];
    }

    foreach ($partidas as &$p) {
        if (!empty($p['usuario_id_time_a']) && isset($nomePorUid[(int)$p['usuario_id_time_a']])) {
            $p['time_a'] = $nomePorUid[(int)$p['usuario_id_time_a']];
        }
        if (!empty($p['usuario_id_time_b']) && isset($nomePorUid[(int)$p['usuario_id_time_b']])) {
            $p['time_b'] = $nomePorUid[(int)$p['usuario_id_time_b']];
        }
    }
    unset($p);
}

// ── AUTH ─────────────────────────────────────────────────
$logado = !empty($_SESSION['usuario_id']);
$tipo   = $_SESSION['usuario_tipo'] ?? '';
$destDash = match($tipo) {
    'professor' => '/soee/src/frontend/views/dashboards/professor.php',
    'adm_sala'  => '/soee/src/frontend/views/dashboards/adm-sala.php',
    'adm_geral' => '/soee/src/frontend/views/dashboards/adm.php',
    default     => '/soee/src/frontend/views/dashboards/aluno.php',
};