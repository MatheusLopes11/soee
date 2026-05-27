<?php
session_start();
require_once __DIR__ . '/../../../backend/includes/conexao.php';
require_once __DIR__ . '/../../../backend/controllers/home.php';

AuthHome::exigirTipo(['adm_sala']);

$userId   = AuthHome::getId();
$userNome = AuthHome::getNome();

include __DIR__ . '/../../../backend/model/selects/adm-sala.php';
include __DIR__ . '/../../../backend/helpers/adm-sala.php';
?>

<!-- ( HTML ) -->
<?php include __DIR__ . '/../includes/doctype.php'; ?>
<head>
    <title>SOEE | Dashboard — ADM Sala</title>
    <link rel="stylesheet" href="/soee/src/frontend/styles/dash-adm-sala.css">
    <?php include __DIR__ . '/../includes/head.php'; ?>
</head>

<body>

<?php if ($flashMsg): ?>
<div class="flash-toast <?= htmlspecialchars($flashTipo) ?>" id="flashToast">
    <i class="fa-solid <?= $flashTipo === 'sucesso' ? 'fa-circle-check' : 'fa-circle-xmark' ?>"></i>
    <?= htmlspecialchars($flashMsg) ?>
</div>
<?php endif; ?>

<aside class="sidebar" id="sidebar">
    <div class="sidebar-logo">
        <a href="/soee/src/frontend/views/site/home.php">S<span>O</span>EE</a>
        <small>ADM de Sala</small>
    </div>

    <?php if ($turmaId): ?>
    <div class="sidebar-turma">
        <div class="turma-label">Sua Turma</div>
        <div class="turma-nome"><?= htmlspecialchars($userData['nome_turma'] ?? '—') ?></div>
        <div class="turma-curso"><?= htmlspecialchars($userData['sigla_curso'] ?? '') ?> &middot; <?= ucfirst($userData['periodo_turma'] ?? '') ?></div>
    </div>
    <?php endif; ?>

    <a href="/soee/src/frontend/views/site/profile.php" class="sidebar-perfil"
       style="text-decoration:none;display:flex;align-items:center;gap:12px;padding:16px;border-bottom:1px solid rgba(255,255,255,.08);margin-top:12px;transition:background .2s;"
       onmouseover="this.style.background='rgba(255,255,255,0.05)'"
       onmouseout="this.style.background='none'">
        <div class="perfil-avatar">
            <?php if (!empty($userData['foto_perfil_usuario'])): ?>
                <img src="<?= htmlspecialchars($userData['foto_perfil_usuario']) ?>" alt="">
            <?php else: ?>
                <i class="fa-solid fa-user-shield"></i>
            <?php endif; ?>
        </div>
        <div class="perfil-info">
            <div class="perfil-nome"><?= htmlspecialchars($userNome) ?></div>
            <div class="perfil-cargo">ADM de Sala</div>
        </div>
    </a>

    <nav class="sidebar-nav">
        <div class="nav-secao">Painel</div>
        <a href="javascript:void(0)" class="nav-item ativo" data-painel="overview" onclick="trocarPainel(this)">
            <i class="fa-solid fa-gauge"></i> Dashboard
        </a>
        <a href="/soee/src/frontend/views/site/home.php" class="nav-item">
            <i class="fa-solid fa-house"></i> Início
        </a>

        <div class="nav-secao">Minha Sala</div>
        <a href="javascript:void(0)" class="nav-item" data-painel="alunos" onclick="trocarPainel(this)">
            <i class="fa-solid fa-users"></i> Alunos
            <span class="nav-badge"><?= count($alunos) ?></span>
        </a>
        <a href="javascript:void(0)" class="nav-item" data-painel="inscricoes" onclick="trocarPainel(this)">
            <i class="fa-solid fa-clipboard-list"></i> Inscrições
            <span class="nav-badge"><?= $stats['total_inscricoes'] ?></span>
        </a>
        <a href="javascript:void(0)" class="nav-item" data-painel="partidas" onclick="trocarPainel(this)">
            <i class="fa-solid fa-calendar-days"></i> Partidas
        </a>

        <a class="nav-item" href="/soee/src/frontend/views/site/classificacao.php">
          <i class="fas fa-trophy"></i> Classificação
        </a>

        <div class="nav-secao">Outros</div>

        <a href="javascript:void(0)" class="nav-item" data-painel="modalidades" onclick="trocarPainel(this)">
            <i class="fa-solid fa-futbol"></i> Modalidades
            <span class="nav-badge"><?= count($todasModalidades) ?></span>
        </a>

        <a class="nav-item" href="/soee/src/frontend/views/site/classificacao.php">
          <i class="fas fa-trophy"></i> Classificação
        </a>

        <a href="javascript:void(0)" class="nav-item" data-painel="minhas-inscricoes" onclick="trocarPainel(this)">
            <i class="fa-solid fa-shirt"></i> Minhas Inscrições
            <?php if (count($minhasInscricoes) > 0): ?>
            <span class="nav-badge"><?= count($minhasInscricoes) ?></span>
            <?php endif; ?>
        </a>

        <a href="/soee/src/frontend/views/forms/feedback.php" class="nav-item">
            <i class="fa-solid fa-comment-dots"></i> Feedback
        </a>
    </nav>

    <div class="sidebar-rodape">
        <a href="/soee/src/backend/includes/logout.php" class="btn-sair">
            <i class="fa-solid fa-right-from-bracket"></i> Sair da conta
        </a>
    </div>
</aside>

<div class="conteudo">
    <header class="topbar">
        <button class="btn-icone" id="toggleSidebar" aria-label="Menu">
            <i class="fa-solid fa-bars"></i>
        </button>
        <div class="topbar-titulo" id="topbar-titulo">Dashboard</div>
        <?php if ($turmaId): ?>
            <span class="topbar-turma"><?= htmlspecialchars($userData['nome_turma'] ?? '') ?></span>
        <?php endif; ?>
        <div class="topbar-acoes">
            <button class="btn-icone" id="toggleTema" aria-label="Tema">
                <i class="fa-solid fa-moon" id="iconeTema"></i>
            </button>
        </div>
    </header>

    <main class="pagina">

        <!-- ══════════════════════ OVERVIEW ══════════════════════ -->
        <div class="painel active" id="painel-overview">
            <div class="boas-vindas">
                <div class="bv-esq">
                    <?php if (!empty($userData['nome_turma'])): ?>
                    <div class="bv-turma-badge">
                        <div class="bv-turma-sigla"><?= htmlspecialchars($userData['nome_turma']) ?></div>
                        <div class="bv-turma-periodo"><?= ucfirst($userData['periodo_turma'] ?? '') ?></div>
                    </div>
                    <?php endif; ?>
                    <div class="bv-texto">
                        <h2>Olá, <?= htmlspecialchars(explode(' ', $userNome)[0]) ?>!</h2>
                        <p>Gerencie sua sala, inscrições e acompanhe o desempenho nos interclasses.</p>
                    </div>
                </div>
                <div class="bv-acoes">
                    <a href="javascript:void(0)" onclick="trocarPainelById('inscricoes')" class="btn-acesso-rapido">
                        <i class="fa-solid fa-user-plus"></i> Inscrever Aluno
                    </a>
                </div>
            </div>

            <div class="stats-grid">
                <div class="stat-card azul">
                    <div class="stat-icone"><i class="fa-solid fa-users"></i></div>
                    <div class="stat-valor"><?= $stats['total_alunos'] ?></div>
                    <div class="stat-label">Alunos na Sala</div>
                </div>
                <div class="stat-card laranja">
                    <div class="stat-icone"><i class="fa-solid fa-clipboard-check"></i></div>
                    <div class="stat-valor"><?= $stats['total_inscricoes'] ?></div>
                    <div class="stat-label">Inscrições Ativas</div>
                </div>
                <div class="stat-card verde">
                    <div class="stat-icone"><i class="fa-solid fa-flag-checkered"></i></div>
                    <div class="stat-valor"><?= $stats['partidas_realizadas'] ?></div>
                    <div class="stat-label">Partidas Jogadas</div>
                </div>
                <div class="stat-card amarelo">
                    <div class="stat-icone"><i class="fa-solid fa-calendar-check"></i></div>
                    <div class="stat-valor"><?= $stats['proximas_partidas'] ?></div>
                    <div class="stat-label">Próximas Partidas</div>
                </div>
            </div>

            <div class="grid-2" style="margin-bottom:24px;">
                <!-- Partidas recentes -->
                <div class="card">
                    <div class="card-header">
                        <div class="card-titulo"><i class="fa-solid fa-calendar"></i> Partidas da Turma</div>
                        <a href="javascript:void(0)" onclick="trocarPainelById('partidas')" class="card-link">Ver todas</a>
                    </div>
                    <?php if (empty($partidas)): ?>
                        <div class="empty-state"><i class="fa-solid fa-calendar-xmark"></i>Nenhuma partida encontrada</div>
                    <?php else: ?>
                        <?php foreach (array_slice($partidas, 0, 4) as $p):
                            $temPlacar = isset($p['placar_time_a']) && $p['status_partida'] === 'realizada'; ?>
                        <div class="partida-item">
                            <div class="partida-status-dot <?= $p['status_partida'] ?>"></div>
                            <div class="partida-info">
                                <div class="partida-times"><?= htmlspecialchars($p['time_a']) ?> x <?= htmlspecialchars($p['time_b']) ?></div>
                                <div class="partida-detalhe">
                                    <?= htmlspecialchars($p['nome_modalidade']) ?>
                                    &middot; <?= $faseLabel[$p['fase_partida']] ?? $p['fase_partida'] ?>
                                    &middot; <?= date('d/m/Y', strtotime($p['data_partida'])) ?>
                                    <?php if ($p['hora_partida'] && $p['status_partida'] === 'agendada'): ?>
                                        &middot; <?= substr($p['hora_partida'], 0, 5) ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="partida-placar <?= !$temPlacar ? 'agendada' : '' ?>">
                                <?= $temPlacar
                                    ? $p['placar_time_a'].' x '.$p['placar_time_b']
                                    : ($statusPartidaLabel[$p['status_partida']] ?? $p['status_partida']) ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div style="display:flex;flex-direction:column;gap:24px;">
                    <!-- Classificação resumida -->
                    <div class="card">
                        <div class="card-header">
                            <div class="card-titulo"><i class="fa-solid fa-ranking-star"></i> Classificação</div>
                            <a href="javascript:void(0)" onclick="trocarPainelById('classificacao')" class="card-link">Ver todas</a>
                        </div>
                        <?php if (empty($classificacoes)): ?>
                            <div class="empty-state"><i class="fa-solid fa-ranking-star"></i>Sem classificação</div>
                        <?php else: ?>
                            <?php foreach ($classificacoes as $cl): ?>
                            <div class="classif-item">
                                <div class="classif-info">
                                    <div class="classif-modalidade"><?= htmlspecialchars($cl['nome_modalidade']) ?></div>
                                    <div class="classif-edicao"><?= htmlspecialchars($cl['nome_edicao']) ?></div>
                                </div>
                                <div class="classif-stats">
                                    <div class="cstat"><div class="cstat-val" style="color:var(--laranja)"><?= $cl['pontos'] ?></div><div class="cstat-label">Pts</div></div>
                                    <div class="cstat"><div class="cstat-val" style="color:var(--verde)"><?= $cl['vitorias'] ?></div><div class="cstat-label">V</div></div>
                                    <div class="cstat"><div class="cstat-val" style="color:var(--vermelho)"><?= $cl['derrotas'] ?></div><div class="cstat-label">D</div></div>
                                    <div class="cstat"><div class="cstat-val"><?= $cl['saldo'] >= 0 ? '+'.$cl['saldo'] : $cl['saldo'] ?></div><div class="cstat-label">Saldo</div></div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Modalidades abertas -->
                    <div class="card">
                        <div class="card-header">
                            <div class="card-titulo"><i class="fa-solid fa-futbol"></i> Modalidades Abertas</div>
                            <a href="javascript:void(0)" onclick="trocarPainelById('modalidades')" class="card-link">Ver todas</a>
                        </div>
                        <?php if (empty($modalidades)): ?>
                            <div class="empty-state"><i class="fa-solid fa-futbol"></i>Nenhuma aberta</div>
                        <?php else: ?>
                            <?php foreach ($modalidades as $md):
                                $fim     = new DateTime($md['data_fim_inscricao']);
                                $hoje    = new DateTime();
                                $diff    = (int) $hoje->diff($fim)->days;
                                $urgente = $diff <= 3; ?>
                            <div class="modal-inscricao-item">
                                <div class="mi-topo">
                                    <span class="mi-nome"><?= htmlspecialchars($md['nome_modalidade']) ?></span>
                                    <span class="badge-status <?= $md['status_edicao_modalidade'] ?>">
                                        <?= $md['status_edicao_modalidade'] === 'inscricoes' ? 'Inscrições' : 'Em Andamento' ?>
                                    </span>
                                </div>
                                <div class="mi-prazo <?= $urgente ? 'urgente' : '' ?>">
                                    <?php if ($md['status_edicao_modalidade'] === 'inscricoes'): ?>
                                        Inscrições até <?= $fim->format('d/m/Y') ?>
                                        <?= $urgente ? ' — '.$diff.' dia(s)!' : '' ?>
                                    <?php else: ?>
                                        <?= ucfirst($md['tipo_participacao']) ?> &middot; <?= htmlspecialchars($md['nome_edicao']) ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="grid-2">
                <!-- Alunos resumido -->
                <div class="card">
                    <div class="card-header">
                        <div class="card-titulo"><i class="fa-solid fa-users"></i> Alunos da Turma</div>
                        <a href="javascript:void(0)" onclick="trocarPainelById('alunos')" class="card-link">Ver todos</a>
                    </div>
                    <div class="busca-aluno">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" id="buscaAlunoOverview" placeholder="Buscar aluno..." autocomplete="off">
                    </div>
                    <div class="tabela-alunos-lista" id="listaAlunosOverview">
                        <?php if (empty($alunos)): ?>
                            <div class="empty-state"><i class="fa-solid fa-user-slash"></i>Nenhum aluno na turma</div>
                        <?php else: ?>
                            <?php foreach (array_slice($alunos, 0, 6) as $a): ?>
                            <div class="aluno-item" data-nome="<?= strtolower(htmlspecialchars($a['nome_usuario'])) ?>">
                                <div class="aluno-avatar">
                                    <?php if (!empty($a['foto_perfil_usuario'])): ?>
                                        <img src="<?= htmlspecialchars($a['foto_perfil_usuario']) ?>" alt="">
                                    <?php else: ?>
                                        <i class="fa-solid fa-user"></i>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <div class="aluno-nome"><?= htmlspecialchars($a['nome_usuario']) ?></div>
                                    <div class="aluno-email"><?= htmlspecialchars($a['email_usuario']) ?></div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Inscrições recentes -->
                <div class="card">
                    <div class="card-header">
                        <div class="card-titulo"><i class="fa-solid fa-clipboard-list"></i> Inscrições Recentes</div>
                        <a href="javascript:void(0)" onclick="trocarPainelById('inscricoes')" class="card-link">Ver todas</a>
                    </div>
                    <?php if (empty($inscricoes)): ?>
                        <div class="empty-state"><i class="fa-solid fa-clipboard-list"></i>Nenhuma inscrição</div>
                    <?php else: ?>
                        <?php foreach (array_slice($inscricoes, 0, 5) as $ins): ?>
                        <div class="inscricao-item">
                            <div class="ins-info">
                                <div class="ins-nome"><?= htmlspecialchars($ins['nome_usuario']) ?></div>
                                <div class="ins-detalhe">
                                    <?= htmlspecialchars($ins['nome_modalidade']) ?>
                                    <?php if ($ins['posicao_inscricao']): ?>
                                        &middot; <?= htmlspecialchars($ins['posicao_inscricao']) ?>
                                    <?php endif; ?>
                                    <?php if ($ins['capitao_inscricao']): ?>
                                        &middot; <span style="color:var(--laranja);font-weight:700">
                                            <i class="fa-solid fa-star" style="font-size:.65rem"></i> Capitão
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php if ($ins['numero_camisa_inscricao']): ?>
                                <div class="ins-camisa">#<?= $ins['numero_camisa_inscricao'] ?></div>
                            <?php endif; ?>
                            <span class="badge-status <?= $ins['status_inscricao'] ?>"><?= ucfirst($ins['status_inscricao']) ?></span>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- ══════════════════════ ALUNOS ══════════════════════ -->
        <div class="painel" id="painel-alunos">
            <div class="card">
                <div class="card-header">
                    <div class="card-titulo"><i class="fa-solid fa-users"></i> Alunos da Turma</div>
                    <span style="font-size:.8rem;color:var(--texto-2)"><?= count($alunos) ?> aluno(s)</span>
                </div>
                <div class="busca-aluno">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" id="buscaAluno" placeholder="Buscar aluno..." autocomplete="off">
                </div>
                <div class="tabela-alunos-lista" id="listaAlunos">
                    <?php if (empty($alunos)): ?>
                        <div class="empty-state"><i class="fa-solid fa-user-slash"></i>Nenhum aluno na turma</div>
                    <?php else: ?>
                        <?php foreach ($alunos as $a): ?>
                        <div class="aluno-item" data-nome="<?= strtolower(htmlspecialchars($a['nome_usuario'])) ?>">
                            <div class="aluno-avatar">
                                <?php if (!empty($a['foto_perfil_usuario'])): ?>
                                    <img src="<?= htmlspecialchars($a['foto_perfil_usuario']) ?>" alt="">
                                <?php else: ?>
                                    <i class="fa-solid fa-user"></i>
                                <?php endif; ?>
                            </div>
                            <div>
                                <div class="aluno-nome"><?= htmlspecialchars($a['nome_usuario']) ?></div>
                                <div class="aluno-email"><?= htmlspecialchars($a['email_usuario']) ?></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- ══════════════════════ INSCRIÇÕES ══════════════════════ -->
        <div class="painel" id="painel-inscricoes">
            <div class="card">
                <div class="card-header">
                    <div class="card-titulo"><i class="fa-solid fa-clipboard-list"></i> Inscrições da Turma</div>
                    <span style="font-size:.8rem;color:var(--texto-2)"><?= count($inscricoes) ?> registro(s)</span>
                </div>
                <?php if (empty($inscricoes)): ?>
                    <div class="empty-state"><i class="fa-solid fa-clipboard-list"></i>Nenhuma inscrição</div>
                <?php else: ?>
                    <?php foreach ($inscricoes as $ins): ?>
                    <div class="inscricao-item">
                        <div class="ins-info">
                            <div class="ins-nome"><?= htmlspecialchars($ins['nome_usuario']) ?></div>
                            <div class="ins-detalhe">
                                <?= htmlspecialchars($ins['nome_modalidade']) ?>
                                <?php if ($ins['posicao_inscricao']): ?>
                                    &middot; <?= htmlspecialchars($ins['posicao_inscricao']) ?>
                                <?php endif; ?>
                                <?php if ($ins['capitao_inscricao']): ?>
                                    &middot; <span style="color:var(--laranja);font-weight:700">
                                        <i class="fa-solid fa-star" style="font-size:.65rem"></i> Capitão
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php if ($ins['numero_camisa_inscricao']): ?>
                            <div class="ins-camisa">#<?= $ins['numero_camisa_inscricao'] ?></div>
                        <?php endif; ?>
                        <span class="badge-status <?= $ins['status_inscricao'] ?>"><?= ucfirst($ins['status_inscricao']) ?></span>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- ══════════════════════ PARTIDAS ══════════════════════ -->
        <div class="painel" id="painel-partidas">
            <div class="card">
                <div class="card-header">
                    <div class="card-titulo"><i class="fa-solid fa-calendar"></i> Partidas da Turma</div>
                    <span style="font-size:.8rem;color:var(--texto-2)"><?= count($partidas) ?> registro(s)</span>
                </div>
                <?php if (empty($partidas)): ?>
                    <div class="empty-state"><i class="fa-solid fa-calendar-xmark"></i>Nenhuma partida encontrada</div>
                <?php else: ?>
                    <?php foreach ($partidas as $p):
                        $temPlacar = isset($p['placar_time_a']) && $p['status_partida'] === 'realizada'; ?>
                    <div class="partida-item">
                        <div class="partida-status-dot <?= $p['status_partida'] ?>"></div>
                        <div class="partida-info">
                            <div class="partida-times"><?= htmlspecialchars($p['time_a']) ?> x <?= htmlspecialchars($p['time_b']) ?></div>
                            <div class="partida-detalhe">
                                <?= htmlspecialchars($p['nome_modalidade']) ?>
                                &middot; <?= $faseLabel[$p['fase_partida']] ?? $p['fase_partida'] ?>
                                &middot; <?= date('d/m/Y', strtotime($p['data_partida'])) ?>
                                <?php if ($p['hora_partida'] && $p['status_partida'] === 'agendada'): ?>
                                    &middot; <?= substr($p['hora_partida'], 0, 5) ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="partida-placar <?= !$temPlacar ? 'agendada' : '' ?>">
                            <?= $temPlacar
                                ? $p['placar_time_a'].' x '.$p['placar_time_b']
                                : ($statusPartidaLabel[$p['status_partida']] ?? $p['status_partida']) ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- ══════════════════════ CLASSIFICAÇÃO ══════════════════════ -->
        <div class="painel" id="painel-classificacao">
            <div class="card">
                <div class="card-header">
                    <div class="card-titulo"><i class="fa-solid fa-ranking-star"></i> Classificação</div>
                </div>
                <?php if (empty($classificacoes)): ?>
                    <div class="empty-state"><i class="fa-solid fa-ranking-star"></i>Sem classificação disponível</div>
                <?php else: ?>
                    <?php foreach ($classificacoes as $cl): ?>
                    <div class="classif-item">
                        <div class="classif-info">
                            <div class="classif-modalidade"><?= htmlspecialchars($cl['nome_modalidade']) ?></div>
                            <div class="classif-edicao"><?= htmlspecialchars($cl['nome_edicao']) ?></div>
                        </div>
                        <div class="classif-stats">
                            <div class="cstat"><div class="cstat-val" style="color:var(--laranja)"><?= $cl['pontos'] ?></div><div class="cstat-label">Pts</div></div>
                            <div class="cstat"><div class="cstat-val" style="color:var(--verde)"><?= $cl['vitorias'] ?></div><div class="cstat-label">V</div></div>
                            <div class="cstat"><div class="cstat-val" style="color:var(--vermelho)"><?= $cl['derrotas'] ?></div><div class="cstat-label">D</div></div>
                            <div class="cstat"><div class="cstat-val"><?= $cl['empates'] ?></div><div class="cstat-label">E</div></div>
                            <div class="cstat"><div class="cstat-val"><?= $cl['jogos'] ?></div><div class="cstat-label">J</div></div>
                            <div class="cstat"><div class="cstat-val"><?= $cl['saldo'] >= 0 ? '+'.$cl['saldo'] : $cl['saldo'] ?></div><div class="cstat-label">Saldo</div></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- ══════════════════════ MODALIDADES ══════════════════════ -->
        <div class="painel" id="painel-modalidades">

            <div class="stats-grid" style="margin-bottom:24px;">
                <div class="stat-card azul">
                    <div class="stat-icone"><i class="fa-solid fa-layer-group"></i></div>
                    <div class="stat-valor"><?= count($todasModalidades) ?></div>
                    <div class="stat-label">Total de Modalidades</div>
                </div>
                <div class="stat-card verde">
                    <div class="stat-icone"><i class="fa-solid fa-circle-check"></i></div>
                    <div class="stat-valor"><?= count(array_filter($todasModalidades, fn($m) => $m['ativo_modalidade'])) ?></div>
                    <div class="stat-label">Modalidades Ativas</div>
                </div>
                <div class="stat-card laranja">
                    <div class="stat-icone"><i class="fa-solid fa-clock"></i></div>
                    <div class="stat-valor"><?= count($modalidades) ?></div>
                    <div class="stat-label">Inscrições Abertas</div>
                </div>
                <div class="stat-card amarelo">
                    <div class="stat-icone"><i class="fa-solid fa-trophy"></i></div>
                    <div class="stat-valor"><?= count($edicoesAtivas) ?></div>
                    <div class="stat-label">Edições Disponíveis</div>
                </div>
            </div>

            <!-- Lista completa -->
            <div class="card" style="margin-bottom:24px;">
                <div class="card-header">
                    <div class="card-titulo"><i class="fa-solid fa-futbol"></i> Todas as Modalidades</div>
                    <a href="/soee/src/frontend/views/forms/criacao-esporte.php" class="btn-nova-modalidade">
                        <i class="fa-solid fa-plus"></i> Nova Modalidade
                    </a>
                </div>

                <div class="busca-aluno" style="margin:0 0 16px;">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" id="filtroModalidade"
                           placeholder="Filtrar por nome ou tipo…"
                           autocomplete="off"
                           oninput="filtrarModalidades(this.value)">
                </div>

                <div class="modalidades-grid" id="gridModalidades">
                    <?php if (empty($todasModalidades)): ?>
                        <div class="empty-state" style="grid-column:1/-1">
                            <i class="fa-solid fa-futbol"></i>Nenhuma modalidade cadastrada
                        </div>
                    <?php else: ?>
                        <?php foreach ($todasModalidades as $md):
                            $icon   = $tipoIcons[$md['tipo_modalidade']] ?? 'fa-star';
                            $ativo  = (bool) $md['ativo_modalidade'];
                            $mdJson = htmlspecialchars(json_encode($md), ENT_QUOTES, 'UTF-8');
                        ?>
                        <div class="modalidade-card <?= $ativo ? '' : 'inativa' ?>"
                             data-nome="<?= strtolower(htmlspecialchars($md['nome_modalidade'])) ?>"
                             data-tipo="<?= htmlspecialchars($md['tipo_modalidade']) ?>">

                            <div class="mc-topo">
                                <div class="mc-icone"><i class="fa-solid <?= $icon ?>"></i></div>
                                <?php if ($ativo): ?>
                                    <span class="badge-ativa">Ativa</span>
                                <?php else: ?>
                                    <span class="badge-inativa">Inativa</span>
                                <?php endif; ?>
                            </div>

                            <div class="mc-nome"><?= htmlspecialchars($md['nome_modalidade']) ?></div>

                            <?php if (!empty($md['descricao_modalidade'])): ?>
                                <div class="mc-desc">
                                    <?= htmlspecialchars(mb_substr($md['descricao_modalidade'], 0, 90)) ?>…
                                </div>
                            <?php endif; ?>

                            <div class="mc-meta">
                                <span><i class="fa-solid fa-sitemap"></i> <?= $formatoLabel[$md['formato_modalidade']] ?? $md['formato_modalidade'] ?></span>
                                <span><i class="fa-solid fa-users"></i> <?= $participacaoLabel[$md['tipo_participacao']] ?? $md['tipo_participacao'] ?></span>
                                <span><i class="fa-solid fa-shirt"></i> <?= $md['qtd_min_jogadores'] ?>–<?= $md['qtd_max_jogadores'] ?> jog.</span>
                            </div>

                            <div class="mc-rodape">
                                <button class="mc-btn-editar"
                                        onclick="abrirModalEditarModalidade(<?= $mdJson ?>)">
                                    <i class="fa-solid fa-pen"></i> Editar
                                </button>
                                <button class="mc-btn-inscricao"
                                        onclick="abrirModalVincularEdicao(<?= (int)$md['id_modalidade'] ?>, '<?= addslashes(htmlspecialchars($md['nome_modalidade'])) ?>')">
                                    <i class="fa-solid fa-link"></i> Vincular Edição
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Inscrições abertas agora -->
            <?php if (!empty($modalidades)): ?>
            <div class="card">
                <div class="card-header">
                    <div class="card-titulo"><i class="fa-solid fa-clock"></i> Com Inscrições Abertas Agora</div>
                </div>
                <?php foreach ($modalidades as $md):
                    $fim     = new DateTime($md['data_fim_inscricao']);
                    $hoje    = new DateTime();
                    $diff    = (int) $hoje->diff($fim)->days;
                    $urgente = $diff <= 3; ?>
                <div class="modal-inscricao-item">
                    <div class="mi-topo">
                        <span class="mi-nome"><?= htmlspecialchars($md['nome_modalidade']) ?></span>
                        <span class="badge-status <?= $md['status_edicao_modalidade'] ?>">
                            <?= $md['status_edicao_modalidade'] === 'inscricoes' ? 'Inscrições' : 'Em Andamento' ?>
                        </span>
                    </div>
                    <div class="mi-prazo <?= $urgente ? 'urgente' : '' ?>">
                        <?php if ($md['status_edicao_modalidade'] === 'inscricoes'): ?>
                            Inscrições até <?= $fim->format('d/m/Y') ?>
                            <?= $urgente ? ' — '.$diff.' dia(s)!' : '' ?>
                        <?php else: ?>
                            <?= ucfirst($md['tipo_participacao']) ?> &middot; <?= htmlspecialchars($md['nome_edicao']) ?>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

        </div><!-- /painel-modalidades -->
         
        <!-- ══════════════════════ MINHAS INSCRIÇÕES (ADM) ══════════════════════ -->
        <div class="painel" id="painel-minhas-inscricoes">
 
            <!-- Inscrições ativas -->
            <?php if (!empty($minhasInscricoes)): ?>
            <div class="card" style="margin-bottom:24px;">
                <div class="card-header">
                    <div class="card-titulo">
                        <i class="fa-solid fa-clipboard-check" style="color:#22c55e"></i>
                        Minhas Inscrições Ativas
                    </div>
                    <span style="font-size:.8rem;color:var(--texto-2)"><?= count($minhasInscricoes) ?> inscri&ccedil;&atilde;o(ões)</span>
                </div>
                <?php foreach ($minhasInscricoes as $ins): ?>
                <div class="inscricao-item">
                    <div class="ins-info">
                        <div class="ins-nome"><?= htmlspecialchars($ins['nome_modalidade']) ?></div>
                        <div class="ins-detalhe">
                            <?= htmlspecialchars($ins['nome_edicao']) ?>
                            <?php if (!empty($ins['nome_camisa_inscricao'])): ?>
                                &middot; <i class="fa-solid fa-shirt" style="font-size:.65rem"></i>
                                <?= htmlspecialchars(strtoupper($ins['nome_camisa_inscricao'])) ?>
                            <?php endif; ?>
                            <?php if ($ins['posicao_inscricao']): ?>
                                &middot; <?= htmlspecialchars($ins['posicao_inscricao']) ?>
                            <?php endif; ?>
                            <?php if ($ins['capitao_inscricao']): ?>
                                &middot; <span style="color:var(--laranja);font-weight:700">
                                    <i class="fa-solid fa-star" style="font-size:.65rem"></i> Capitão
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php if ($ins['numero_camisa_inscricao']): ?>
                        <div class="ins-camisa">#<?= $ins['numero_camisa_inscricao'] ?></div>
                    <?php endif; ?>
                    <div style="display:flex;align-items:center;gap:8px;">
                        <span class="badge-status ativa">Ativa</span>
                        <button class="btn-cancelar-insc-adm"
                                onclick="cancelarInscricaoAdm(<?= $ins['id_inscricao'] ?>, '<?= addslashes(htmlspecialchars($ins['nome_modalidade'])) ?>')"
                                style="background:rgba(239,68,68,.1);color:#ef4444;
                                       border:1px solid rgba(239,68,68,.25);
                                       padding:5px 12px;border-radius:8px;
                                       font-size:.75rem;font-weight:700;cursor:pointer;
                                       font-family:inherit;transition:background .2s;">
                            <i class="fa-solid fa-times"></i> Cancelar
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
 
            <!-- Modalidades disponíveis -->
            <div class="card">
                <div class="card-header">
                    <div class="card-titulo">
                        <i class="fa-solid fa-futbol" style="color:var(--laranja)"></i>
                        Modalidades Disponíveis
                    </div>
                </div>
 
                <?php if ($nomeCamisaAdm): ?>
                <div style="margin:0 0 16px;padding:10px 14px;
                            background:rgba(34,197,94,.08);border:1px solid rgba(34,197,94,.2);
                            border-radius:10px;font-size:.82rem;color:var(--texto);">
                    <i class="fa-solid fa-shirt" style="color:#22c55e"></i>
                    Nome de camisa salvo: <strong><?= htmlspecialchars($nomeCamisaAdm) ?></strong>
                    — preenchido automaticamente abaixo.
                </div>
                <?php endif; ?>
 
                <?php
                // Filtra modalidades abertas — mesma lógica do dash-user
                $modalidadesAdm = array_filter($modalidades, function($md) use ($minhasEmIds, $generoAdm) {
                    // Filtra gênero
                    $gm = $md['genero_modalidade'] ?? 'misto';
                    if ($gm === 'masculino' && $generoAdm !== 'm') return false;
                    if ($gm === 'feminino'  && $generoAdm !== 'f') return false;
                    return true;
                });
 
                if (empty($modalidades)): ?>
                    <div class="empty-state"><i class="fa-solid fa-futbol"></i>Nenhuma modalidade com inscrições abertas.</div>
                <?php else: ?>
                    <?php foreach ($modalidades as $md):
                        $jaInscrito     = in_array($md['id_edicao_modalidade'], $minhasEmIds);
                        $generoMod      = $md['genero_modalidade'] ?? 'misto';
                        $bloqueado      = false;
                        $motivoBloqueio = '';
 
                        if ($generoMod === 'masculino' && $generoAdm !== 'm') {
                            $bloqueado      = true;
                            $motivoBloqueio = 'Modalidade masculina';
                        } elseif ($generoMod === 'feminino' && $generoAdm !== 'f') {
                            $bloqueado      = true;
                            $motivoBloqueio = 'Modalidade feminina';
                        }
 
                        $generoBadge = match($generoMod) {
                            'masculino' => '<span style="font-size:.7rem;background:rgba(59,130,246,.1);color:#2563eb;border-radius:999px;padding:2px 8px;border:1px solid rgba(59,130,246,.2)"><i class="fa-solid fa-mars"></i> Masculino</span>',
                            'feminino'  => '<span style="font-size:.7rem;background:rgba(236,72,153,.1);color:#be185d;border-radius:999px;padding:2px 8px;border:1px solid rgba(236,72,153,.2)"><i class="fa-solid fa-venus"></i> Feminino</span>',
                            default     => '<span style="font-size:.7rem;background:rgba(100,116,139,.1);color:#475569;border-radius:999px;padding:2px 8px;border:1px solid rgba(100,116,139,.2)"><i class="fa-solid fa-venus-mars"></i> Misto</span>',
                        };
                    ?>
                    <div style="padding:16px 0;border-bottom:1px solid var(--borda,#e2e8f0);">
                        <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:10px;gap:8px;flex-wrap:wrap;">
                            <div>
                                <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                                    <strong style="font-size:.9rem"><?= htmlspecialchars($md['nome_modalidade']) ?></strong>
                                    <?= $generoBadge ?>
                                </div>
                                <div style="font-size:.75rem;color:var(--texto-2);margin-top:3px;">
                                    <?= htmlspecialchars($md['nome_edicao']) ?>
                                    &middot; <?= ucfirst($md['tipo_participacao']) ?>
                                    &middot; Inscrições até <?= date('d/m/Y', strtotime($md['data_fim_inscricao'])) ?>
                                </div>
                            </div>
                            <?php if ($jaInscrito): ?>
                                <span style="display:inline-flex;align-items:center;gap:6px;
                                             background:rgba(34,197,94,.12);color:#15803d;
                                             border:1px solid rgba(34,197,94,.25);
                                             border-radius:999px;padding:4px 12px;
                                             font-size:.75rem;font-weight:700;">
                                    <i class="fa-solid fa-check"></i> Inscrito
                                </span>
                            <?php elseif ($bloqueado): ?>
                                <span style="display:inline-flex;align-items:center;gap:6px;
                                             background:rgba(239,68,68,.08);color:#ef4444;
                                             border:1px solid rgba(239,68,68,.2);
                                             border-radius:999px;padding:4px 12px;
                                             font-size:.75rem;font-weight:600;">
                                    <i class="fa-solid fa-ban"></i> <?= $motivoBloqueio ?>
                                </span>
                            <?php endif; ?>
                        </div>
 
                        <?php if (!$jaInscrito && !$bloqueado): ?>
                        <form onsubmit="enviarInscricaoAdm(event, <?= $md['id_edicao_modalidade'] ?>)">
                            <div style="display:grid;grid-template-columns:1fr 1fr auto;gap:8px;align-items:end;">
                                <div>
                                    <label style="font-size:.75rem;font-weight:600;display:block;margin-bottom:4px;">
                                        <i class="fa-solid fa-shirt" style="font-size:.7rem"></i>
                                        Nome da Camisa
                                        <span style="font-weight:400;color:var(--texto-2)">(opcional)</span>
                                    </label>
                                    <input type="text" name="nome_camisa"
                                           style="width:100%;padding:8px 10px;border:1px solid var(--borda,#e2e8f0);
                                                  border-radius:8px;background:var(--fundo,#f0f4f8);
                                                  color:var(--texto,#1e293b);font-family:inherit;font-size:.85rem;
                                                  text-transform:uppercase;letter-spacing:.05em;"
                                           placeholder="Ex: SILVA"
                                           value="<?= htmlspecialchars($nomeCamisaAdm) ?>"
                                           maxlength="20"
                                           oninput="this.value=this.value.toUpperCase().replace(/[^A-ZÁÉÍÓÚÀÂÊÔÃÕÜÇ ]/g,'')">
                                </div>
                                <div>
                                    <label style="font-size:.75rem;font-weight:600;display:block;margin-bottom:4px;">
                                        Nº Camisa <span style="font-weight:400;color:var(--texto-2)">(opcional)</span>
                                    </label>
                                    <input type="number" name="camisa" min="1" max="99"
                                           style="width:100%;padding:8px 10px;border:1px solid var(--borda,#e2e8f0);
                                                  border-radius:8px;background:var(--fundo,#f0f4f8);
                                                  color:var(--texto,#1e293b);font-family:inherit;font-size:.85rem;"
                                           placeholder="Ex: 10">
                                </div>
                                <button type="submit"
                                        style="background:#22c55e;color:white;border:none;
                                               padding:10px 20px;border-radius:10px;
                                               font-weight:700;cursor:pointer;font-size:.88rem;
                                               font-family:inherit;transition:background .2s;white-space:nowrap;"
                                        onmouseover="this.style.background='#16a34a'"
                                        onmouseout="this.style.background='#22c55e'">
                                    <i class="fa-solid fa-plus"></i> Inscrever
                                </button>
                            </div>
                        </form>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div><!-- /painel-minhas-inscricoes -->
 
    </main>
</div>

<!-- ══════════════════════════════════════════════════
     MODAL — Editar Modalidade
══════════════════════════════════════════════════ -->
<div class="modal-overlay-sala" id="modal-modalidade" onclick="fecharSeOverlay(event,'modal-modalidade')">
    <div class="modal-sala">
        <div class="modal-sala-header">
            <h3 id="modal-modalidade-titulo">
                <i class="fa-solid fa-pen"></i> Editar Modalidade
            </h3>
            <button class="modal-sala-fechar" onclick="fecharModal('modal-modalidade')" aria-label="Fechar">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="modal-sala-body">
            <form id="form-modalidade"
                  action="/soee/src/backend/actions/salvar-modalidade.php"
                  method="POST"
                  enctype="multipart/form-data">
                <input type="hidden" name="id_modalidade" id="inp-id-modalidade">
                <input type="hidden" name="origem_foto" value="upload">

                <div class="form-sala-grid">

                    <div class="form-sala-grupo span2">
                        <label class="form-sala-label">Nome <span class="obrig">*</span></label>
                        <input class="form-sala-input" type="text" name="nome_modalidade"
                               id="inp-nome" placeholder="Ex.: Futsal, Vôlei…" required maxlength="60">
                    </div>

                    <div class="form-sala-grupo">
                        <label class="form-sala-label">Tipo <span class="obrig">*</span></label>
                        <select class="form-sala-select" name="tipo_modalidade" id="inp-tipo" required>
                            <option value="">Selecionar…</option>
                            <option value="quadra">Quadra</option>
                            <option value="mesa">Mesa</option>
                            <option value="outro">Outro</option>
                        </select>
                    </div>

                    <div class="form-sala-grupo">
                        <label class="form-sala-label">Formato <span class="obrig">*</span></label>
                        <select class="form-sala-select" name="formato_modalidade" id="inp-formato" required>
                            <option value="">Selecionar…</option>
                            <option value="mata_mata">Mata-mata</option>
                            <option value="grupos">Grupos</option>
                            <option value="grupos_mata_mata">Grupos + Mata-mata</option>
                            <option value="todos_contra_todos">Todos contra todos</option>
                        </select>
                    </div>

                    <div class="form-sala-grupo">
                        <label class="form-sala-label">Participação <span class="obrig">*</span></label>
                        <select class="form-sala-select" name="tipo_participacao" id="inp-participacao" required>
                            <option value="">Selecionar…</option>
                            <option value="solo">Individual (Solo)</option>
                            <option value="dupla">Dupla</option>
                            <option value="trio">Trio</option>
                            <option value="time">Time</option>
                        </select>
                    </div>

                    <div class="form-sala-grupo">
                        <label class="form-sala-label">Gênero <span class="obrig">*</span></label>
                        <select class="form-sala-select" name="genero_modalidade" id="inp-genero" required>
                            <option value="">Selecionar…</option>
                            <option value="masculino">Masculino</option>
                            <option value="feminino">Feminino</option>
                            <option value="misto">Misto</option>
                        </select>
                    </div>

                    <div class="form-sala-grupo">
                        <label class="form-sala-label">Mín. jogadores <span class="obrig">*</span></label>
                        <input class="form-sala-input" type="number" name="qtd_min_jogadores"
                               id="inp-min" min="1" max="99" placeholder="5" required>
                    </div>

                    <div class="form-sala-grupo">
                        <label class="form-sala-label">Máx. jogadores <span class="obrig">*</span></label>
                        <input class="form-sala-input" type="number" name="qtd_max_jogadores"
                               id="inp-max" min="1" max="99" placeholder="7" required>
                    </div>

                    <div class="form-sala-grupo span2">
                        <label class="form-sala-label">Tipo de Duração</label>
                        <select class="form-sala-select" name="tipo_duracao" id="inp-tipo-duracao"
                                onchange="toggleDuracao(this.value)">
                            <option value="">Sem limite definido</option>
                            <option value="minutos">Por tempo (minutos)</option>
                            <option value="pontos">Por pontuação</option>
                        </select>
                    </div>

                    <div class="form-sala-grupo span2" id="grupo-dur-minutos" style="display:none">
                        <label class="form-sala-label">Duração (ex: 2x15 ou 30)</label>
                        <input class="form-sala-input" type="text" name="duracao_minutos"
                               id="inp-dur-minutos" placeholder="Ex.: 2x15">
                    </div>

                    <div class="form-sala-grupo span2" id="grupo-dur-pontos" style="display:none">
                        <label class="form-sala-label">Pontos para vencer</label>
                        <input class="form-sala-input" type="number" name="duracao_pontos"
                               id="inp-dur-pontos" min="1" max="255" placeholder="Ex.: 21">
                    </div>

                    <div class="form-sala-grupo span2">
                        <label class="form-sala-label">Foto da Modalidade (opcional — JPG, PNG, WEBP, até 5 MB)</label>
                        <input class="form-sala-input" type="file" name="foto_arquivo"
                               accept=".jpg,.jpeg,.png,.webp,.gif">
                    </div>

                    <div class="form-sala-grupo span2">
                        <label class="form-sala-label">Descrição</label>
                        <textarea class="form-sala-textarea" name="descricao_modalidade"
                                  id="inp-desc" placeholder="Regras básicas, observações…" rows="3"></textarea>
                    </div>

                    <div class="form-sala-grupo span2">
                        <label class="form-sala-label">Regulamento</label>
                        <textarea class="form-sala-textarea" name="regulamento_modalidade"
                                  id="inp-regul" placeholder="Regulamento completo…" rows="4"></textarea>
                    </div>

                    <div class="form-sala-grupo span2">
                        <label class="toggle-label">
                            <input type="checkbox" name="ativo_modalidade" id="inp-ativo" value="1" checked>
                            <span class="toggle-track"></span>
                            Modalidade ativa
                        </label>
                    </div>

                </div>
            </form>
        </div>
        <div class="modal-sala-footer">
            <button class="btn-modal-cancelar" onclick="fecharModal('modal-modalidade')">Cancelar</button>
            <button class="btn-modal-salvar" onclick="document.getElementById('form-modalidade').submit()">
                <i class="fa-solid fa-floppy-disk"></i> Salvar Modalidade
            </button>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════
     MODAL — Vincular Modalidade a uma Edição
══════════════════════════════════════════════════ -->
<div class="modal-overlay-sala" id="modal-vincular" onclick="fecharSeOverlay(event,'modal-vincular')">
    <div class="modal-sala modal-sala-sm">
        <div class="modal-sala-header">
            <h3><i class="fa-solid fa-link"></i> Vincular à Edição</h3>
            <button class="modal-sala-fechar" onclick="fecharModal('modal-vincular')" aria-label="Fechar">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="modal-sala-body">
            <form id="form-vincular"
            action="/soee/src/backend/actions/salvar-edicao-modalidade.php"
            method="POST">
                <input type="hidden" name="modalidade_id_modalidade" id="vinc-modal-id">

                <div class="form-sala-grid">

                    <div class="form-sala-grupo span2">
                        <label class="form-sala-label">Modalidade</label>
                        <input class="form-sala-input" type="text" id="vinc-modal-nome" disabled>
                    </div>

                    <div class="form-sala-grupo span2">
                        <label class="form-sala-label">Edição <span class="obrig">*</span></label>
                        <?php if (empty($edicoesAtivas)): ?>
                            <p style="font-size:.82rem;color:var(--vermelho);padding:8px;background:rgba(239,68,68,.08);border-radius:8px;">
                                <i class="fa-solid fa-triangle-exclamation"></i>
                                Nenhuma edição encontrada. Peça ao professor para criar uma edição primeiro.
                            </p>
                        <?php else: ?>
                        <select class="form-sala-select" name="edicao_id_edicao" required>
                            <option value="">Selecionar edição…</option>
                            <?php foreach ($edicoesAtivas as $ed): ?>
                            <option value="<?= $ed['id_edicao'] ?>">
                                <?= htmlspecialchars($ed['nome_edicao']) ?> (<?= $ed['ano_edicao'] ?>)
                                — <?= $statusEdicaoLabel[$ed['status_edicao']] ?? $ed['status_edicao'] ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <?php endif; ?>
                    </div>

                    <div class="form-sala-grupo">
                        <label class="form-sala-label">Início das inscrições <span class="obrig">*</span></label>
                        <input class="form-sala-input" type="date" name="data_inicio_inscricao" required>
                    </div>

                    <div class="form-sala-grupo">
                        <label class="form-sala-label">Fim das inscrições <span class="obrig">*</span></label>
                        <input class="form-sala-input" type="date" name="data_fim_inscricao" required>
                    </div>

                </div>
            </form>
        </div>
        <div class="modal-sala-footer">
            <button class="btn-modal-cancelar" onclick="fecharModal('modal-vincular')">Cancelar</button>
            <button class="btn-modal-salvar" onclick="document.getElementById('form-vincular').submit()">
                <i class="fa-solid fa-link"></i> Vincular
            </button>
        </div>
    </div>
</div>

<script src="/soee/src/frontend/scripts/dash-adm-sala.js"></script>

<?php include __DIR__ . '/../includes/end.php'; ?>