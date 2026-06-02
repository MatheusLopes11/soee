<?php
    include __DIR__ . '/../../../backend/model/selects/classificacao.php';
    include __DIR__ . '/../../../backend/helpers/classificacao.php';
    include __DIR__ . '/../includes/doctype.php';
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SOEE | <?= htmlspecialchars($esporte['nome_modalidade'] ?? 'Campeonato') ?></title>
    <link rel="icon" type="image/png" href="/soee/src/frontend/assets/icons/logo-soee.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;800&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600;9..40,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="/soee/src/frontend/styles/classificacao.css">
    <script>
        (function () {
            const t = localStorage.getItem('theme');
            if (t) document.documentElement.setAttribute('data-theme', t);
        })();
    </script>
</head>
<body>

<div class="cursor-dot"  id="cursorDot"></div>
<div class="cursor-ring" id="cursorRing"></div>

<div id="loader">
    <div class="loader-inner">
        <div class="loader-logo-text">SOEE</div>
        <div class="loader-logo-sub">Carregando campeonato</div>
        <div class="loader-bar"><div class="loader-bar-fill"></div></div>
    </div>
</div>

<header class="topbar">
    <div class="topbar-inner">

        <a href="/soee/src/frontend/views/site/home.php" class="topbar-logo">
            S<span>O</span>EE
        </a>

        <div class="topbar-center">
            <?php if ($esporte): ?>
                <i class="fa-solid <?= $icone ?>"></i>
                <span><?= htmlspecialchars($esporte['nome_modalidade']) ?></span>
                <?php if (!empty($esporte['nome_edicao'])): ?>
                    <span class="topbar-edicao">
                        <?= htmlspecialchars($esporte['nome_edicao']) ?> · <?= $esporte['ano_edicao'] ?>
                    </span>
                <?php endif; ?>
            <?php else: ?>
                <span>Campeonatos</span>
            <?php endif; ?>
        </div>

        <div class="topbar-acoes">
            <button id="toggleTema" class="btn-icone-top" aria-label="Alternar tema">
                <i class="fa-solid fa-moon" id="iconeTema"></i>
            </button>

            <?php if ($logado): ?>
                <a href="<?= $destDash ?>" class="btn-dash-top">
                    <i class="fa-solid fa-gauge"></i> Dashboard
                </a>
            <?php else: ?>
                <a href="/soee/index.php" class="btn-dash-top">
                    <i class="fa-solid fa-right-to-bracket"></i> Voltar
                </a>
            <?php endif; ?>
        </div>

    </div>
</header>

<div class="app-layout">

    <aside class="sidebar" id="sidebar">

        <div class="sidebar-titulo">
            <i class="fa-solid fa-layer-group"></i> Modalidades em Andamento
        </div>

        <nav class="sidebar-nav">
            <?php if (empty($esportes)): ?>
                <div class="sidebar-vazio">
                    <i class="fa-solid fa-clock"></i>
                    <span>Nenhuma modalidade em andamento no momento.</span>
                </div>
            <?php else: ?>
                <?php foreach ($esportes as $esp):
                    $ico      = $tipoIcons[$esp['tipo_modalidade'] ?? 'outro'] ?? 'fa-medal';
                    $ativo    = (int) $esp['id_modalidade'] === $modalidadeId;
                    $fmtBadge = [
                        'grupos'             => 'Grupos',
                        'mata_mata'          => 'Mata-Mata',
                        'grupos_mata_mata'   => 'G + MM',
                        'todos_contra_todos' => 'Todos × Todos',
                    ][$esp['formato_modalidade'] ?? ''] ?? '';
                ?>
                <a href="?id=<?= $esp['id_modalidade'] ?>"
                   class="sidebar-item <?= $ativo ? 'ativo' : '' ?>">

                    <div class="sidebar-item-icone">
                        <i class="fa-solid <?= $ico ?>"></i>
                    </div>

                    <div class="sidebar-item-info">
                        <span class="sidebar-item-nome">
                            <?= htmlspecialchars($esp['nome_modalidade']) ?>
                        </span>
                        <div class="sidebar-badges">
                            <span class="sidebar-badge andamento">
                                <i class="fa-solid fa-circle" style="font-size:.35rem;vertical-align:middle"></i>
                                Em Andamento
                            </span>
                            <?php if ($fmtBadge): ?>
                                <span class="sidebar-badge formato"><?= $fmtBadge ?></span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if ($ativo): ?>
                        <div class="sidebar-ativo-bar"></div>
                    <?php endif; ?>
                </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </nav>

    </aside>

    <main class="conteudo" id="conteudo">

        <?php if (isset($_GET['sucesso'])): ?>
            <div style="background-color: #d4edda; color: #155724; padding: 15px; margin-bottom: 20px; border-radius: 5px; border: 1px solid #c3e6cb; font-family: 'DM Sans', sans-serif;">
                <i class="fa-solid fa-circle-check"></i> O placar foi <strong><?= htmlspecialchars($_GET['sucesso']); ?></strong> com sucesso! 
                Se este era o último jogo, a próxima fase já foi montada!
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['erro']) && $_GET['erro'] === 'automacao_falhou'): ?>
            <div style="background-color: #f8d7da; color: #721c24; padding: 15px; margin-bottom: 20px; border-radius: 5px; border: 1px solid #f5c6cb; font-family: 'DM Sans', sans-serif;">
                <i class="fa-solid fa-circle-exclamation"></i> <strong>Atenção:</strong> O placar não pôde ser salvo porque ocorreu um erro ao tentar gerar a tabela do mata-mata automaticamente. Verifique os dados da classificação.
            </div>
        <?php endif; ?>

        <?php if (!$esporte): ?>
        <div class="empty-page">
            <div class="empty-page-inner">
                <i class="fa-solid fa-trophy"></i>
                <h2>Nenhum campeonato em andamento</h2>
                <p>Quando as inscrições fecharem e o campeonato iniciar, as classificações e partidas aparecerão aqui.</p>
                <?php if ($logado && in_array($tipo, ['professor', 'adm_geral'])): ?>
                    <a href="<?= $destDash ?>" class="btn-dash-top" style="margin-top:24px;display:inline-flex;">
                        <i class="fa-solid fa-gauge"></i> Ir para o Dashboard
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <?php else: ?>

        <div class="esporte-hero">
            <div class="hero-bg"></div>
            <div class="hero-inner">
                <div class="hero-icone">
                    <i class="fa-solid <?= $icone ?>"></i>
                </div>
                <div class="hero-texto">
                    <h1><?= htmlspecialchars($esporte['nome_modalidade']) ?></h1>
                    <?php if (!empty($esporte['descricao_modalidade'])): ?>
                        <p><?= htmlspecialchars(mb_substr($esporte['descricao_modalidade'], 0, 160)) ?></p>
                    <?php endif; ?>
                    <div class="hero-meta">
                        <span>
                            <i class="fa-solid fa-sitemap"></i>
                            <?= $formatoLabel[$formato] ?? ucfirst($formato) ?>
                        </span>
                        <span>
                            <i class="fa-solid fa-user"></i>
                            <?= $participacaoLabel[$participacao] ?? ucfirst($participacao) ?>
                        </span>
                        <?php if (!empty($esporte['nome_edicao'])): ?>
                            <span>
                                <i class="fa-solid fa-trophy"></i>
                                <?= htmlspecialchars($esporte['nome_edicao']) ?>
                            </span>
                        <?php endif; ?>
                        <span class="hero-status em_andamento">
                            <i class="fa-solid fa-circle" style="font-size:.45rem;vertical-align:middle"></i>
                            Em Andamento
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="tabs-bar">
            <?php if ($temGrupos): ?>
            <button class="tab ativo" data-tab="grupos" onclick="trocarTab(this,'grupos')">
                <i class="fa-solid fa-table-cells"></i>
                <?= $formato === 'todos_contra_todos' ? 'Classificação' : 'Fase de Grupos' ?>
            </button>
            <?php endif; ?>

            <?php if ($temMataMata): ?>
            <button class="tab <?= !$temGrupos ? 'ativo' : '' ?>"
                    data-tab="chaveamento"
                    onclick="trocarTab(this,'chaveamento')">
                <i class="fa-solid fa-trophy"></i> Mata-Mata
            </button>
            <?php endif; ?>

            <?php if (!$temGrupos && !$temMataMata): ?>
            <button class="tab ativo" data-tab="partidas" onclick="trocarTab(this,'partidas')">
                <i class="fa-solid fa-calendar-days"></i> Partidas
            </button>
            <?php else: ?>
            <button class="tab" data-tab="partidas" onclick="trocarTab(this,'partidas')">
                <i class="fa-solid fa-calendar-days"></i> Todas as Partidas
            </button>
            <?php endif; ?>
        </div>

        <?php if ($temGrupos): ?>
        <div class="tab-conteudo ativo" id="tab-grupos">

            <?php if (empty($grupos)): ?>
                <div class="empty-state">
                    <i class="fa-solid fa-table-cells"></i>
                    <p>O sorteio ainda não foi realizado ou nenhuma partida foi registrada.</p>
                </div>
            <?php else: ?>

                <?php if ($ehIndividual): ?>
                <div class="aviso-individual">
                    <i class="fa-solid fa-circle-info"></i>
                    <?php if ($participacao === 'solo'): ?>
                        Modalidade individual — cada aluno compete por conta própria.
                    <?php elseif ($participacao === 'dupla'): ?>
                        Modalidade em duplas — cada dupla compete por conta própria.
                    <?php else: ?>
                        Modalidade em trios — cada trio compete por conta própria.
                    <?php endif; ?>
                    A turma exibida é apenas a origem do participante.
                </div>
                <?php endif; ?>

                <div class="grupos-grid">
                    <?php foreach ($grupos as $nomeGrupo => $times): ?>
                    <div class="grupo-card reveal">

                        <div class="grupo-header">
                            <div class="grupo-letra">
                                <?= $formato === 'todos_contra_todos' ? '#' : $nomeGrupo ?>
                            </div>
                            <div>
                                <div class="grupo-titulo">
                                    <?= $formato === 'todos_contra_todos'
                                        ? 'Classificação Geral'
                                        : 'Grupo ' . $nomeGrupo ?>
                                </div>
                                <div class="grupo-sub">
                                    <?= count($times) ?> time<?= count($times) > 1 ? 's' : '' ?>
                                </div>
                            </div>
                            <?php if ($formato !== 'todos_contra_todos'): ?>
                            <div class="grupo-qualifica">
                                <i class="fa-solid fa-arrow-up"></i>
                                Top <?= $classificamPorGrupo ?> avançam
                            </div>
                            <?php endif; ?>
                        </div>

                        <div class="grupo-tabela-wrap">
                            <table class="grupo-tabela">
                                <thead>
                                    <tr>
                                        <th class="th-pos">#</th>
                                        <th class="th-time"><?= $ehIndividual ? 'Participante' : 'Time / Turma' ?></th>
                                        <th title="Jogos realizados">J</th>
                                        <th title="Vitórias">V</th>
                                        <th title="Empates">E</th>
                                        <th title="Derrotas">D</th>
                                        <th title="Points pro">GP</th>
                                        <th title="Points contra">GC</th>
                                        <th title="Saldo">SG</th>
                                        <th title="Pontos na tabela">PTS</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($times as $pos => $time):
                                        $classifica = $pos < $classificamPorGrupo
                                                      && $formato !== 'grupos'
                                                      && $formato !== 'todos_contra_todos';
                                        $lider  = $pos === 0;
                                        $saldo  = (int) $time['saldo'];
                                    ?>
                                    <tr class="<?= $classifica ? 'classificado' : '' ?> <?= $lider ? 'lider' : '' ?>">
                                        <td class="td-pos">
                                            <?php if ($pos === 0): ?>
                                                <span class="pos-badge ouro"  title="1º lugar">1</span>
                                            <?php elseif ($pos === 1): ?>
                                                <span class="pos-badge prata" title="2º lugar">2</span>
                                            <?php elseif ($pos === 2): ?>
                                                <span class="pos-badge bronze" title="3º lugar">3</span>
                                            <?php else: ?>
                                                <span class="pos-badge"><?= $pos + 1 ?></span>
                                            <?php endif; ?>
                                        </td>

                                        <td class="td-time">
                                            <div class="time-col">
                                                <div class="time-avatar">
                                                    <?= avatar($time['nome_exibicao']) ?>
                                                </div>
                                                <div class="time-info">
                                                    <span class="time-nome">
                                                        <?= htmlspecialchars($time['nome_exibicao']) ?>
                                                    </span>
                                                    <?php if ($ehIndividual && !empty($time['subtitulo'])): ?>
                                                        <span class="time-turma-origem">
                                                            <?= htmlspecialchars($time['subtitulo']) ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                                <?php if ($classifica): ?>
                                                    <span class="badge-classifica" title="Classificado">
                                                        <i class="fa-solid fa-check"></i>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </td>

                                        <td class="td-num"><?= (int) $time['jogos'] ?></td>
                                        <td class="td-num verde"><?= (int) $time['vitorias'] ?></td>
                                        <td class="td-num"><?= (int) $time['empates'] ?></td>
                                        <td class="td-num vermelho"><?= (int) $time['derrotas'] ?></td>
                                        <td class="td-num"><?= (int) $time['pontos_pro'] ?></td>
                                        <td class="td-num"><?= (int) $time['pontos_contra'] ?></td>
                                        <td class="td-num <?= $saldo > 0 ? 'verde' : ($saldo < 0 ? 'vermelho' : '') ?>">
                                            <?= $saldo > 0 ? '+' . $saldo : $saldo ?>
                                        </td>
                                        <td class="td-pts">
                                            <span class="pts" data-target="<?= (int) $time['pontos'] ?>">0</span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <?php
                        $jogosGrupo = array_filter(
                            $partidas_fase['grupos'] ?? [],
                            fn($p) => $p['grupo_partida'] === $nomeGrupo
                        );
                        if (!empty($jogosGrupo)):
                        ?>
                        <div class="grupo-jogos">
                            <div class="grupo-jogos-titulo">
                                <i class="fa-solid fa-futbol"></i>
                                Jogos do Grupo <?= $nomeGrupo ?>
                            </div>
                            <?php foreach ($jogosGrupo as $jogo):
                                $realizada = $jogo['status_partida'] === 'realizada';
                            ?>
                            <div class="jogo-mini <?= $realizada ? 'realizada' : '' ?>">
                                <div class="jogo-participante-col">
                                    <span class="jogo-time <?= (!empty($jogo['vencedor']) && $jogo['vencedor'] === $jogo['time_a']) ? 'vencedor' : '' ?>">
                                        <?= htmlspecialchars($jogo['time_a']) ?>
                                    </span>
                                    <?php if ($ehIndividual && !empty($jogo['turma_time_a'])): ?>
                                        <span class="jogo-turma-label"><?= htmlspecialchars($jogo['turma_time_a']) ?></span>
                                    <?php endif; ?>
                                </div>
                             
                                <div class="jogo-placar">
                                    <?php if ($realizada): ?>
                                        <strong><?= $jogo['placar_time_a'] ?></strong>
                                        <span class="jogo-x">×</span>
                                        <strong><?= $jogo['placar_time_b'] ?></strong>
                                    <?php else: ?>
                                        <span class="jogo-data"><?= fmtData($jogo['data_partida']) ?></span>
                                        <?php if ($jogo['hora_partida']): ?>
                                            <span class="jogo-hora"><?= fmtHora($jogo['hora_partida']) ?></span>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                             
                                <div class="jogo-participante-col direita">
                                    <span class="jogo-time direita <?= (!empty($jogo['vencedor']) && $jogo['vencedor'] === $jogo['time_b']) ? 'vencedor' : '' ?>">
                                        <?= htmlspecialchars($jogo['time_b']) ?>
                                    </span>
                                    <?php if ($ehIndividual && !empty($jogo['turma_time_b'])): ?>
                                        <span class="jogo-turma-label"><?= htmlspecialchars($jogo['turma_time_b']) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>

                    </div>
                    <?php endforeach; ?>
                </div>

            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if ($temMataMata): ?>
        <div class="tab-conteudo <?= !$temGrupos ? 'ativo' : '' ?>" id="tab-chaveamento">

            <?php
            $temFases = false;
            foreach ($faseOrdemMata as $f) {
                if (!empty($partidas_fase[$f])) { $temFases = true; break; }
            }
            ?>

            <?php if (!$temFases): ?>
                <div class="empty-state">
                    <i class="fa-solid fa-trophy"></i>
                    <p>O mata-mata ainda não começou. Aguarde o fim da fase de grupos.</p>
                </div>
            <?php else: ?>

            <div class="chaveamento-wrap">
                <div class="bracket" id="bracket-root">
                    <?php foreach ($faseOrdemMata as $fase):
                        if (empty($partidas_fase[$fase])) continue;
                        $isFinal = $fase === 'final';
                        $is3o    = $fase === 'terceiro_lugar';
                    ?>
                    <div class="bracket-coluna <?= $isFinal ? 'coluna-final' : '' ?> <?= $is3o ? 'coluna-3o' : '' ?>">

                        <div class="bracket-fase-label">
                            <?= $isFinal ? '<i class="fa-solid fa-crown"></i>' : '' ?>
                            <?= $faseLabel[$fase] ?? ucfirst($fase) ?>
                        </div>

                        <div class="bracket-jogos">
                            <?php foreach ($partidas_fase[$fase] as $jogo):
                                $realizada = $jogo['status_partida'] === 'realizada';
                                $wo        = $jogo['status_partida'] === 'wo';
                                $winA      = !empty($jogo['vencedor']) && $jogo['vencedor'] === $jogo['time_a'];
                                $winB      = !empty($jogo['vencedor']) && $jogo['vencedor'] === $jogo['time_b'];
                            ?>
                            <div class="bracket-jogo <?= $isFinal ? 'jogo-final' : '' ?> <?= $realizada ? 'realizada' : '' ?> reveal"
                                 data-partida="<?= $jogo['id_partida'] ?>">

                                <div class="bracket-time <?= $winA ? 'vencedor' : ($realizada && !$winA ? 'perdedor' : '') ?>">
                                    <div class="bracket-avatar">
                                        <?= avatar($jogo['time_a'] ?? '?') ?>
                                    </div>
                                    <span><?= htmlspecialchars($jogo['time_a'] ?: 'A definir') ?></span>
                                    <?php if ($realizada): ?>
                                        <strong class="bracket-placar"><?= $jogo['placar_time_a'] ?></strong>
                                    <?php endif; ?>
                                    <?php if ($winA): ?>
                                        <i class="fa-solid fa-check bracket-check"></i>
                                    <?php endif; ?>
                                </div>

                                <div class="bracket-sep">
                                    <?php if ($wo): ?>
                                        <span class="bracket-wo">W.O.</span>
                                    <?php elseif (!$realizada): ?>
                                        <span class="bracket-data">
                                            <?= fmtData($jogo['data_partida']) ?>
                                            <?php if ($jogo['hora_partida']): ?>
                                                <?= fmtHora($jogo['hora_partida']) ?>
                                            <?php endif; ?>
                                        </span>
                                    <?php else: ?>
                                        <span>×</span>
                                    <?php endif; ?>
                                </div>

                                <div class="bracket-time <?= $winB ? 'vencedor' : ($realizada && !$winB ? 'perdedor' : '') ?>">
                                    <div class="bracket-avatar">
                                        <?= avatar($jogo['time_b'] ?? '?') ?>
                                    </div>
                                    <span><?= htmlspecialchars($jogo['time_b'] ?: 'A definir') ?></span>
                                    <?php if ($realizada): ?>
                                        <strong class="bracket-placar"><?= $jogo['placar_time_b'] ?></strong>
                                    <?php endif; ?>
                                    <?php if ($winB): ?>
                                        <i class="fa-solid fa-check bracket-check"></i>
                                    <?php endif; ?>
                                </div>

                                <?php if (!empty($jogo['local_partida'])): ?>
                                <div class="bracket-local">
                                    <i class="fa-solid fa-location-dot"></i>
                                    <?= htmlspecialchars($jogo['local_partida']) ?>
                                </div>
                                <?php endif; ?>

                            </div>
                            <?php endforeach; ?>
                        </div>

                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <?php endif; ?>
        </div>
        <?php endif; ?>

        <div class="tab-conteudo <?= !$temGrupos && !$temMataMata ? 'ativo' : '' ?>"
             id="tab-partidas">

            <?php if (empty($todasPartidas)): ?>
                <div class="empty-state">
                    <i class="fa-solid fa-calendar-xmark"></i>
                    <p>Nenhuma partida cadastrada ainda.</p>
                </div>
            <?php else: ?>
            <div class="partidas-lista">
                <?php $fasesExibir = ['grupos','oitavas','quartas','semi','terceiro_lugar','final'];
                foreach ($fasesExibir as $fase):
                    if (empty($partidas_fase[$fase])) continue;
                ?>
                <div class="partidas-secao reveal">
                    <div class="partidas-secao-titulo">
                        <?php if ($fase === 'final'): ?>
                            <i class="fa-solid fa-crown"></i>
                        <?php elseif ($fase === 'grupos'): ?>
                            <i class="fa-solid fa-table-cells"></i>
                        <?php else: ?>
                            <i class="fa-solid fa-trophy"></i>
                        <?php endif; ?>
                        <?= $faseLabel[$fase] ?>
                        <span class="partidas-count">
                            <?= count($partidas_fase[$fase]) ?> jogo(s)
                        </span>
                    </div>

                    <?php foreach ($partidas_fase[$fase] as $p):
                        $realizada = $p['status_partida'] === 'realizada';
                        $wo        = $p['status_partida'] === 'wo';
                        $winA      = !empty($p['vencedor']) && $p['vencedor'] === $p['time_a'];
                        $winB      = !empty($p['vencedor']) && $p['vencedor'] === $p['time_b'];
                    ?>
                    <div class="partida-row <?= $realizada ? 'realizada' : '' ?> <?= $fase === 'final' ? 'partida-final' : '' ?>">

                        <div class="pr-times">
                            <span class="pr-time <?= $winA ? 'vencedor' : '' ?>">
                                <span class="pr-avatar"><?= avatar($p['time_a']) ?></span>
                                <?= htmlspecialchars($p['time_a']) ?>
                            </span>

                            <div class="pr-placar">
                                <?php if ($realizada): ?>
                                    <strong><?= $p['placar_time_a'] ?></strong>
                                    <span class="pr-x">×</span>
                                    <strong><?= $p['placar_time_b'] ?></strong>
                                <?php elseif ($wo): ?>
                                    <span class="pr-wo">W.O.</span>
                                <?php else: ?>
                                    <span class="pr-vs">VS</span>
                                <?php endif; ?>
                            </div>

                            <span class="pr-time direita <?= $winB ? 'vencedor' : '' ?>">
                                <?= htmlspecialchars($p['time_b']) ?>
                                <span class="pr-avatar"><?= avatar($p['time_b']) ?></span>
                            </span>
                        </div>

                        <div class="pr-meta">
                            <span>
                                <i class="fa-solid fa-calendar"></i>
                                <?= fmtDataLong($p['data_partida']) ?>
                            </span>
                            <?php if ($p['hora_partida']): ?>
                                <span>
                                    <i class="fa-solid fa-clock"></i>
                                    <?= fmtHora($p['hora_partida']) ?>
                                </span>
                            <?php endif; ?>
                            <?php if ($p['local_partida']): ?>
                                <span>
                                    <i class="fa-solid fa-location-dot"></i>
                                    <?= htmlspecialchars($p['local_partida']) ?>
                                </span>
                            <?php endif; ?>
                            <?php if (!empty($p['grupo_partida'])): ?>
                                <span>
                                    <i class="fa-solid fa-layer-group"></i>
                                    Grupo <?= htmlspecialchars($p['grupo_partida']) ?>
                                </span>
                            <?php endif; ?>
                            <span class="pr-badge <?= $p['status_partida'] ?>">
                                <?= [
                                    'agendada'  => 'Agendada',
                                    'realizada' => 'Realizada',
                                    'cancelada' => 'Cancelada',
                                    'wo'        => 'W.O.',
                                ][$p['status_partida']] ?? ucfirst($p['status_partida']) ?>
                            </span>
                        </div>

                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <?php endif; // fim $esporte ?>
    </main>
</div>

<button class="sidebar-toggle-mobile" id="sidebarToggle" aria-label="Abrir menu">
    <i class="fa-solid fa-bars"></i>
</button>

<script src="/soee/src/frontend/scripts/classificacao.js"></script>

<?php 
    include __DIR__ . '/../includes/end.php'; 
?>