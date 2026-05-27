<?php
session_start();
require_once __DIR__ . '/../../../backend/includes/conexao.php';
require_once __DIR__ . '/../../../backend/controllers/home.php';

AuthHome::exigirTipo(['aluno']);

$userId   = AuthHome::getId();
$userNome = AuthHome::getNome();

include __DIR__ . '/../../../backend/model/selects/aluno.php';

# HTML
include __DIR__ . '/../includes/doctype.php'; ?>
<head>
    <title>SOEE | Dashboard Aluno</title>
    <link rel="stylesheet" href="/soee/src/frontend/styles/dash-user.css">
    <?php include __DIR__ . '/../includes/head.php'; ?>
    <link rel="stylesheet" href="/soee/src/frontend/styles/aluno.css">
</head>
<body>

<div class="dash-wrapper">

    <!-- SIDEBAR -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-logo">
            <div class="sidebar-logo-icon"><i class="fa-solid fa-trophy"></i></div>
            <span class="sidebar-logo-text">SOEE</span>
        </div>
        <nav class="sidebar-nav">

            <div class="nav-section-label">Principal</div>
            
            <a class="nav-item active" href="javascript:void(0)" data-painel="overview" onclick="trocarPainel(this)">
                <i class="fa-solid fa-house"></i> Visão Geral
            </a>

            <a class="nav-item" href="javascript:void(0)" data-painel="partidas" onclick="trocarPainel(this)">
                <i class="fa-solid fa-calendar-days"></i> Partidas
            </a>

            <a class="nav-item" href="javascript:void(0)" data-painel="times" onclick="trocarPainel(this)">
                <i class="fa-solid fa-shield-halved"></i> Times
            </a>

            <a class="nav-item" href="/soee/src/frontend/views/site/classificacao.php">
                <i class="fas fa-trophy"></i> Classificação
            </a>

            <a class="nav-item" href="javascript:void(0)" data-painel="meutime" onclick="trocarPainel(this)">
                <i class="fa-solid fa-people-group"></i> Meu Time
            </a>
                                                                                                                           
            <a class="nav-item" href="javascript:void(0)" data-painel="inscricoes" onclick="trocarPainel(this)">
                <i class="fa-solid fa-clipboard-list"></i> Inscrições
                <?php if(count($modalidades) > 0): ?>
                <span class="nav-badge"><?= count($modalidades) ?></span>
                <?php endif; ?>
            </a>

            <div class="nav-section-label" style="margin-top:16px">Conta</div>

            <a class="nav-item" href="/soee/src/frontend/views/site/profile.php">
                <i class="fa-solid fa-user"></i> Perfil
            </a>

        </nav>
        <div class="sidebar-user">
            <a href="/soee/src/frontend/views/site/profile.php"
               style="display:flex;align-items:center;gap:10px;text-decoration:none;flex:1;min-width:0;">
                <div class="user-avatar">
                    <?php if ($fotoPerfil): ?>
                        <img src="<?= htmlspecialchars($fotoPerfil) ?>"
                             alt="Foto" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">
                    <?php else: ?>
                        <?= $inicial ?>
                    <?php endif; ?>
                </div>
                <div class="user-info">
                    <div class="user-name"><?= htmlspecialchars(explode(' ', $userNome)[0]) ?></div>
                    <div class="user-role"><?= htmlspecialchars($nomeTurma) ?></div>
                </div>
            </a>
        </div>
    </aside>

    <!-- MAIN -->
    <main class="dash-main">
        <header class="topbar">
            <div class="topbar-title" id="topbar-titulo">Visão <span>Geral</span></div>
            <a href="/soee/src/backend/includes/logout.php"
               style="margin-left:auto;color:#ef4444;display:flex;align-items:center;gap:6px;text-decoration:none;font-size:.88rem;font-weight:600;">
                <i class="fa-solid fa-right-from-bracket"></i> Sair
            </a>
<button class="botao-icone" onclick="alternarTema()" title="Tema">
  <i class="fas fa-moon" id="tema-icone"></i>
</button>
        <a href="/soee/src/frontend/views/site/home.php" class="botao-icone" title="Início">
            <i class="fas fa-home"></i>
        </a>
        </header>

        <div class="dash-content">

            <!-- ══════ OVERVIEW ══════ -->
            <div class="painel active" id="painel-overview">
                <div class="proxima-card" style="background:linear-gradient(135deg,#0f2d3d,#1e5671,#2c7da3);margin-bottom:28px;">
                    <div style="font-size:.75rem;opacity:.6;text-transform:uppercase;letter-spacing:.1em;margin-bottom:6px;">
                        <?= $generoUser === 'f' ? 'Bem-vinda de volta' : 'Bem-vindo de volta' ?>
                    </div>
                    <div style="font-family:'Playfair Display',serif;font-size:1.6rem;font-weight:800;margin-bottom:4px;">
                        Olá, <?= htmlspecialchars(explode(' ', $userNome)[0]) ?>!
                    </div>
                    <div style="opacity:.7;font-size:.88rem;">
                        <?= htmlspecialchars($nomeTurma) ?>
                        <?= $siglaCurso ? '— '.htmlspecialchars($siglaCurso) : '' ?>
                    </div>
                    <?php if(empty($inscricoes)): ?>
                    <div style="margin-top:16px;">
                        <a href="javascript:void(0)" onclick="trocarPainelById('inscricoes')"
                           style="background:#f97316;color:white;padding:8px 18px;border-radius:10px;text-decoration:none;font-weight:700;font-size:.85rem;">
                            <i class="fa-solid fa-plus"></i> Inscrever-se em um esporte
                        </a>
                    </div>
                    <?php endif; ?>
                </div>

                <?php if (!empty($classificacoes)): ?>
                <?php $cl = reset($classificacoes); ?>
                <div class="stat-resumo">
                    <div class="stat-resumo-card">
                        <strong style="color:#22c55e"><?= $cl['vitorias'] ?></strong>
                        <span>Vitórias</span>
                    </div>
                    <div class="stat-resumo-card">
                        <strong style="color:#f59e0b"><?= $cl['empates'] ?></strong>
                        <span>Empates</span>
                    </div>
                    <div class="stat-resumo-card">
                        <strong style="color:#ef4444"><?= $cl['derrotas'] ?></strong>
                        <span>Derrotas</span>
                    </div>
                    <div class="stat-resumo-card">
                        <strong style="color:#8b5cf6"><?= $cl['pontos'] ?></strong>
                        <span>Pontos</span>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($proximaPartida): ?>
                <div class="proxima-card" style="margin-bottom:28px;">
                    <div class="pp-label"><i class="fa-solid fa-clock"></i> Próxima Partida</div>
                    <div class="pp-times">
                        <span class="<?= $proximaPartida['turma_id_time_a'] == $turmaId ? 'pp-mine' : '' ?>">
                            <?= htmlspecialchars($proximaPartida['nome_time_a']) ?>
                        </span>
                        <span class="pp-vs">VS</span>
                        <span class="<?= $proximaPartida['turma_id_time_b'] == $turmaId ? 'pp-mine' : '' ?>">
                            <?= htmlspecialchars($proximaPartida['nome_time_b']) ?>
                        </span>
                    </div>
                    <div class="pp-meta">
                        <span><i class="fa-solid fa-calendar"></i> <?= date('d/m/Y', strtotime($proximaPartida['data_partida'])) ?></span>
                        <span><i class="fa-solid fa-clock"></i> <?= substr($proximaPartida['hora_partida'], 0, 5) ?></span>
                        <?php if ($proximaPartida['local_partida']): ?>
                        <span><i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars($proximaPartida['local_partida']) ?></span>
                        <?php endif; ?>
                        <span><i class="fa-solid fa-futbol"></i> <?= htmlspecialchars($proximaPartida['nome_modalidade']) ?></span>
                    </div>
                </div>
                <?php else: ?>
                <div class="modal-card" style="margin-bottom:28px;text-align:center;opacity:.6;">
                    <i class="fa-solid fa-calendar-xmark" style="font-size:1.5rem;margin-bottom:8px;display:block;"></i>
                    Nenhuma partida agendada para sua turma.
                </div>
                <?php endif; ?>

                <?php if (!empty($inscricoes)): ?>
                <div class="modal-card">
                    <h4><i class="fa-solid fa-clipboard-check" style="color:#22c55e"></i> Suas Inscrições</h4>
                    <?php foreach ($inscricoes as $ins): ?>
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--borda,#e2e8f0);">
                        <div>
                            <strong style="font-size:.88rem"><?= htmlspecialchars($ins['nome_modalidade']) ?></strong>
                            <?php if ($ins['nome_camisa_inscricao']): ?>
                            <span style="font-size:.75rem;color:var(--texto-2,#64748b);margin-left:8px">
                                <i class="fa-solid fa-shirt" style="font-size:.65rem"></i> <?= htmlspecialchars($ins['nome_camisa_inscricao']) ?>
                            </span>
                            <?php endif; ?>
                        </div>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <?php if ($ins['capitao_inscricao']): ?>
                            <span style="font-size:.72rem;color:#f59e0b;font-weight:700"><i class="fa-solid fa-star"></i> Capitão</span>
                            <?php endif; ?>
                            <?php if ($ins['numero_camisa_inscricao']): ?>
                            <span style="font-size:.75rem;background:var(--fundo,#f0f4f8);padding:2px 8px;border-radius:6px;">#<?= $ins['numero_camisa_inscricao'] ?></span>
                            <?php endif; ?>
                            <span class="inscricao-badge"><i class="fa-solid fa-check"></i> Ativo</span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- ══════ PARTIDAS ══════ -->
            <div class="painel" id="painel-partidas">
                <div class="modal-card">
                    <h4><i class="fa-solid fa-calendar-days" style="color:#f97316"></i> Partidas da Turma</h4>
                    <?php if (empty($partidas)): ?>
                        <div class="empty"><i class="fa-solid fa-calendar-xmark"></i>Nenhuma partida encontrada.</div>
                    <?php else: ?>
                        <?php foreach ($partidas as $p):
                            $temPlacar = $p['status_partida'] === 'realizada' && isset($p['placar_time_a']); ?>
                        <div class="partida-row">
                            <div class="partida-dot <?= $p['status_partida'] ?>"></div>
                            <div class="partida-info">
                                <div class="partida-times">
                                    <span <?= $p['turma_id_time_a'] == $turmaId ? 'style="color:var(--laranja,#f97316);font-weight:800"' : '' ?>>
                                        <?= htmlspecialchars($p['time_a']) ?>
                                    </span>
                                    <span style="opacity:.5;margin:0 6px">vs</span>
                                    <span <?= $p['turma_id_time_b'] == $turmaId ? 'style="color:var(--laranja,#f97316);font-weight:800"' : '' ?>>
                                        <?= htmlspecialchars($p['time_b']) ?>
                                    </span>
                                </div>
                                <div class="partida-meta">
                                    <?= htmlspecialchars($p['nome_modalidade']) ?>
                                    &middot; <?= $faseLabel[$p['fase_partida']] ?? $p['fase_partida'] ?>
                                    &middot; <?= date('d/m/Y', strtotime($p['data_partida'])) ?>
                                    <?php if ($p['hora_partida']): ?>
                                    &middot; <?= substr($p['hora_partida'], 0, 5) ?>
                                    <?php endif; ?>
                                    <?php if ($p['local_partida']): ?>
                                    &middot; <?= htmlspecialchars($p['local_partida']) ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="partida-placar">
                                <?= $temPlacar
                                    ? $p['placar_time_a'].' x '.$p['placar_time_b']
                                    : ($statusLabel[$p['status_partida']] ?? $p['status_partida']) ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- ══════ TIMES ══════ -->
            <div class="painel" id="painel-times">
    <?php if (empty($inscricoes)): ?>
        <div class="empty"><i class="fa-solid fa-shield-halved"></i>Inscreva-se em uma modalidade para ver os times.</div>
    <?php else: ?>
        <?php foreach ($inscricoes as $insc):
            $emId  = $insc['edicao_modalidade_id'];
            $times = $todosOsTimes[$emId] ?? []; ?>
        <div class="modal-card">
            <h4><i class="fa-solid fa-shield-halved" style="color:#f97316"></i> <?= htmlspecialchars($insc['nome_modalidade']) ?></h4>
            <?php if (empty($times)): ?>
                <div class="empty"><i class="fa-solid fa-users-slash"></i>Nenhum time inscrito.</div>
            <?php else: ?>
                <?php foreach ($times as $time): ?>
                <div class="time-item" style="align-items:center;">
                    <span class="<?= $time['id_turma'] == $turmaId ? 'time-mine' : '' ?>">
                        <?php if ($time['id_turma'] == $turmaId): ?>
                        <i class="fa-solid fa-star" style="color:#f59e0b;font-size:.7rem;margin-right:4px"></i>
                        <?php endif; ?>
                        <?= htmlspecialchars($time['nome_turma']) ?>
                        <?= $time['id_turma'] == $turmaId ? ' (seu time)' : '' ?>
                    </span>
                    <div style="display:flex;align-items:center;gap:10px;">
                        <span style="font-size:.78rem;color:var(--texto-2,#64748b)"><?= $time['total_inscritos'] ?> inscritos</span>
                        <!-- BOTÃO VER TIME -->
                        <a href="/soee/src/frontend/views/site/team.php?turma=<?= $time['id_turma'] ?>&em=<?= $emId ?>"
                           style="display:inline-flex;align-items:center;gap:5px;
                                  background:rgba(30,86,113,.08);
                                  border:1px solid rgba(30,86,113,.18);
                                  color:var(--azul-2,#2c7da3);
                                  border-radius:8px;
                                  padding:5px 12px;
                                  font-size:.75rem;
                                  font-weight:700;
                                  text-decoration:none;
                                  transition:all .2s;"
                           onmouseover="this.style.background='var(--azul-2,#2c7da3)';this.style.color='#fff';"
                           onmouseout="this.style.background='rgba(30,86,113,.08)';this.style.color='var(--azul-2,#2c7da3)';">
                            <i class="fa-solid fa-users" style="font-size:.7rem"></i> Ver time
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

            <!-- ══════ CLASSIFICAÇÃO ══════ -->
            <div class="painel" id="painel-classificacao">
                <?php if (empty($inscricoes)): ?>
                    <div class="empty"><i class="fa-solid fa-ranking-star"></i>Inscreva-se em uma modalidade para ver a classificação.</div>
                <?php else: ?>
                    <?php foreach ($inscricoes as $insc):
                        $emId    = $insc['edicao_modalidade_id'];
                        $ranking = $rankingGeral[$emId] ?? []; ?>
                    <div class="modal-card">
                        <h4><i class="fa-solid fa-ranking-star" style="color:#f97316"></i> <?= htmlspecialchars($insc['nome_modalidade']) ?></h4>
                        <?php if (empty($ranking)): ?>
                            <div class="empty"><i class="fa-solid fa-ranking-star"></i>Sem dados de classificação.</div>
                        <?php else: ?>
                        <div style="overflow-x:auto;">
                            <table class="ranking-table">
                                <thead>
                                    <tr><th>#</th><th style="text-align:left">Turma</th><th>J</th><th>V</th><th>E</th><th>D</th><th>GP</th><th>GC</th><th>SG</th><th>PTS</th></tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($ranking as $pos => $r): ?>
                                    <tr class="<?= $r['nome_turma'] === $nomeTurma ? 'minha-turma' : '' ?>">
                                        <td><?= $pos + 1 ?>º</td>
                                        <td style="text-align:left;font-weight:600"><?= htmlspecialchars($r['nome_turma']) ?></td>
                                        <td><?= $r['jogos'] ?></td>
                                        <td><?= $r['vitorias'] ?></td>
                                        <td><?= $r['empates'] ?></td>
                                        <td><?= $r['derrotas'] ?></td>
                                        <td><?= $r['pontos_pro'] ?></td>
                                        <td><?= $r['pontos_contra'] ?></td>
                                        <td><?= $r['saldo'] >= 0 ? '+'.$r['saldo'] : $r['saldo'] ?></td>
                                        <td style="font-weight:800"><?= $r['pontos'] ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- ══════ MEU TIME ══════ -->
            <div class="painel" id="painel-meutime">
                <?php if (empty($inscricoes)): ?>
                    <div class="empty"><i class="fa-solid fa-people-group"></i>Inscreva-se em uma modalidade para ver seu elenco.</div>
                <?php else: ?>
                    <?php foreach ($inscricoes as $insc):
                        $emId      = $insc['edicao_modalidade_id'];
                        $jogadores = $elenco[$emId] ?? [];
                        $cl        = $classificacoes[$emId] ?? null; ?>
                    <div class="modal-card">
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;flex-wrap:wrap;gap:8px;">
                            <h4 style="margin:0">
                                <i class="fa-solid fa-people-group" style="color:#f97316"></i>
                                <?= htmlspecialchars($nomeTurma) ?> — <?= htmlspecialchars($insc['nome_modalidade']) ?>
                            </h4>
                            <?php if ($cl): ?>
                            <div style="display:flex;gap:12px;font-size:.8rem;">
                                <span><strong style="color:#22c55e"><?= $cl['vitorias'] ?>V</strong></span>
                                <span><strong style="color:#f59e0b"><?= $cl['empates'] ?>E</strong></span>
                                <span><strong style="color:#ef4444"><?= $cl['derrotas'] ?>D</strong></span>
                                <span><strong style="color:#8b5cf6"><?= $cl['pontos'] ?> pts</strong></span>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php if (empty($jogadores)): ?>
                            <div class="empty"><i class="fa-solid fa-user-slash"></i>Nenhum jogador inscrito ainda.</div>
                        <?php else: ?>
                            <?php foreach ($jogadores as $j): ?>
                            <div class="player-item">
                                <div class="player-avatar">
                                    <?php if (!empty($j['foto_perfil_usuario'])): ?>
                                        <img src="<?= htmlspecialchars($j['foto_perfil_usuario']) ?>" alt="">
                                    <?php else: ?>
                                        <?= mb_strtoupper(mb_substr($j['nome_usuario'], 0, 1)) ?>
                                    <?php endif; ?>
                                </div>
                                <div style="flex:1">
                                    <div style="font-weight:600;font-size:.88rem"><?= htmlspecialchars($j['nome_usuario']) ?></div>
                                    <?php if ($j['nome_camisa_inscricao']): ?>
                                    <div style="font-size:.72rem;color:var(--texto-2,#64748b)">
                                        <i class="fa-solid fa-shirt" style="font-size:.65rem"></i>
                                        <?= htmlspecialchars($j['nome_camisa_inscricao']) ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <div style="display:flex;align-items:center;gap:8px;">
                                    <?php if ($j['capitao_inscricao']): ?>
                                    <span style="font-size:.7rem;color:#f59e0b;font-weight:700"><i class="fa-solid fa-star"></i> Cap.</span>
                                    <?php endif; ?>
                                    <?php if ($j['numero_camisa_inscricao']): ?>
                                    <span style="font-size:.75rem;background:var(--fundo,#f0f4f8);padding:2px 8px;border-radius:6px;">#<?= $j['numero_camisa_inscricao'] ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- ══════ INSCRIÇÕES ══════ -->
            <div class="painel" id="painel-inscricoes">

                <?php if (!empty($inscricoes)): ?>
                <div class="modal-card">
                    <h4><i class="fa-solid fa-clipboard-check" style="color:#22c55e"></i> Minhas Inscrições Ativas</h4>
                    <?php foreach ($inscricoes as $ins): ?>
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 0;border-bottom:1px solid var(--borda,#e2e8f0);">
                        <div>
                            <strong style="font-size:.9rem"><?= htmlspecialchars($ins['nome_modalidade']) ?></strong>
                            <?php if ($ins['nome_camisa_inscricao']): ?>
                            <div style="font-size:.75rem;color:var(--texto-2,#64748b)">
                                <i class="fa-solid fa-shirt" style="font-size:.65rem"></i>
                                <?= htmlspecialchars($ins['nome_camisa_inscricao']) ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <?php if ($ins['capitao_inscricao']): ?>
                            <span style="font-size:.72rem;color:#f59e0b;font-weight:700"><i class="fa-solid fa-star"></i> Capitão</span>
                            <?php endif; ?>
                            <span class="inscricao-badge"><i class="fa-solid fa-check"></i> Ativo</span>
                            <button class="btn-cancelar-insc"
                                onclick="cancelarInscricao(<?= $ins['id_inscricao'] ?>, '<?= addslashes(htmlspecialchars($ins['nome_modalidade'])) ?>')">
                                <i class="fa-solid fa-times"></i> Cancelar
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <!-- Modalidades disponíveis -->
                <div class="modal-card">
                    <h4><i class="fa-solid fa-futbol" style="color:#f97316"></i> Modalidades Disponíveis para Inscrição</h4>

                    <?php if ($nomeCamisaSalvo): ?>
                    <div style="margin-bottom:16px;padding:10px 14px;background:rgba(34,197,94,.08);border:1px solid rgba(34,197,94,.2);border-radius:10px;font-size:.82rem;color:var(--texto,#1e293b);">
                        <i class="fa-solid fa-shirt" style="color:#22c55e"></i>
                        Nome de camisa salvo: <strong><?= htmlspecialchars($nomeCamisaSalvo) ?></strong>
                        — preenchido automaticamente nos formulários abaixo.
                    </div>
                    <?php endif; ?>

                    <?php if (empty($modalidades)): ?>
                        <div class="empty"><i class="fa-solid fa-futbol"></i>Nenhuma modalidade com inscrições abertas.</div>
                    <?php else: ?>
                        <?php foreach ($modalidades as $md):
                            $jaInscrito    = in_array($md['id_edicao_modalidade'], $modalidadesInscritas);
                            $generoMod     = $md['genero_modalidade'];
                            $bloqueado     = false;
                            $motivoBloqueio = '';

                            if ($generoMod === 'masculino' && $generoUser !== 'm') {
                                $bloqueado      = true;
                                $motivoBloqueio = 'Modalidade masculina';
                            } elseif ($generoMod === 'feminino' && $generoUser !== 'f') {
                                $bloqueado      = true;
                                $motivoBloqueio = 'Modalidade feminina';
                            }

                            // Badge de gênero
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
                                        <strong><?= htmlspecialchars($md['nome_modalidade']) ?></strong>
                                        <?= $generoBadge ?>
                                    </div>
                                    <div style="font-size:.75rem;color:var(--texto-2,#64748b);margin-top:3px;">
                                        <?= htmlspecialchars($md['nome_edicao']) ?>
                                        &middot; <?= ucfirst($md['tipo_participacao']) ?>
                                        &middot; Inscrições até <?= date('d/m/Y', strtotime($md['data_fim_inscricao'])) ?>
                                    </div>
                                </div>
                                <?php if ($jaInscrito): ?>
                                    <span class="inscricao-badge"><i class="fa-solid fa-check"></i> Inscrito</span>
                                <?php elseif ($bloqueado): ?>
                                    <span class="genero-bloqueado"><i class="fa-solid fa-ban"></i> <?= $motivoBloqueio ?></span>
                                <?php endif; ?>
                            </div>

                            <?php if (!$jaInscrito && !$bloqueado): ?>
                            <form onsubmit="enviarInscricao(event, <?= $md['id_edicao_modalidade'] ?>)">
                                <div style="display:grid;grid-template-columns:1fr 1fr auto;gap:8px;align-items:end;">
                                    <div>
                                        <label class="insc-label">
                                            <i class="fa-solid fa-shirt" style="font-size:.7rem"></i>
                                            Nome da Camisa <span style="font-weight:400;color:var(--texto-2,#64748b)">(opcional)</span>
                                        </label>
                                        <input type="text" name="nome_camisa"
                                               class="insc-input"
                                               placeholder="Ex: Cafu, Neymar…"
                                               value="<?= htmlspecialchars($nomeCamisaSalvo) ?>"
                                               maxlength="20">
                                    </div>
                                    <div>
                                        <label class="insc-label">Nº Camisa <span style="font-weight:400;color:var(--texto-2,#64748b)">(opcional)</span></label>
                                        <input type="number" name="camisa" min="1" max="99"
                                               class="insc-input"
                                               placeholder="Ex: 10">
                                    </div>
                                    <button type="submit" class="btn-inscrever">
                                        <i class="fa-solid fa-plus"></i> Inscrever
                                    </button>
                                </div>
                            </form>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

        </div><!-- /dash-content -->
    </main>
</div>

<script src="../../scripts/dash-user.js"></script>
<script src="../../scripts/aluno.js"></script>

<?php include __DIR__ . '/../includes/end.php'; ?>