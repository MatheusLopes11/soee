<?php
session_start();
require_once __DIR__ . '/../../../backend/includes/conexao.php';
require_once __DIR__ . '/../../../backend/controllers/home.php';

AuthHome::exigirTipo(['professor']);

// FIX: $userId e $usuario_logado definidos ANTES de incluir os selects
$usuario_logado = AuthHome::getNome();
$userId         = AuthHome::getId();

include __DIR__ . '/../../../backend/model/selects/professor.php';
include __DIR__ . '/../../../backend/helpers/professor.php';

# HTML
include __DIR__ . '/../includes/doctype.php'; ?>
<head>
    <title>SOEE | Dashboard Professor</title>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="/soee/src/frontend/styles/dash-adm.css">
    <link rel="stylesheet" href="/soee/src/frontend/styles/dash-prof.css">
    <link rel="icon" type="image/png" href="/soee/src/frontend/assets/icons/logo-soee.png">
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;800&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>
<body>

<?php if ($flashMsg): ?>
<div class="toast-container" id="toast-container" style="position:fixed;bottom:28px;right:28px;z-index:9999;">
    <div class="toast <?= htmlspecialchars($flashTipo) ?>" style="display:flex;">
        <i class="fas <?= $flashTipo === 'sucesso' ? 'fa-check-circle' : 'fa-times-circle' ?>"></i>
        <span><?= htmlspecialchars($flashMsg) ?></span>
    </div>
</div>
<?php endif; ?>

<!-- ══════ SIDEBAR ══════ -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-logo">
        <div class="sidebar-logo-text">SOEE<span class="sidebar-logo-dot"></span></div>
        <div class="sidebar-logo-sub">Painel do Professor</div>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-group-label">Visão Geral</div>
        <a class="nav-item active" href="javascript:void(0)" data-painel="overview" onclick="trocarPainel(this)">
            <i class="fas fa-chart-line"></i> Dashboard
        </a>
        <a class="nav-item" href="javascript:void(0)" data-painel="agenda" onclick="trocarPainel(this)">
            <i class="fas fa-calendar-alt"></i> Agenda de Partidas
            <?php if (count($agenda)): ?>
                <span class="nav-badge"><?= count($agenda) ?></span>
            <?php endif; ?>
        </a>

        <div class="nav-group-label">Competições</div>
        <a class="nav-item" href="javascript:void(0)" data-painel="edicoes" onclick="trocarPainel(this)">
            <i class="fas fa-trophy"></i> Edições / Eventos
            <span class="nav-badge"><?= count($edicoes) ?></span>
        </a>
        <a class="nav-item" href="javascript:void(0)" data-painel="partidas" onclick="trocarPainel(this)">
            <i class="fas fa-calendar-days"></i> Partidas
        </a>
        <a class="nav-item" href="javascript:void(0)" data-painel="resultados" onclick="trocarPainel(this)">
            <i class="fas fa-flag-checkered"></i> Resultados
        </a>
        <a class="nav-item" href="/soee/src/frontend/views/site/classificacao.php">
            <i class="fas fa-trophy"></i> Classificação
        </a>

        <div class="nav-group-label">Gestão</div>
        <a class="nav-item" href="javascript:void(0)" data-painel="sumulas" onclick="trocarPainel(this)">
            <i class="fas fa-file-alt"></i> Súmulas
            <?php if (count($sumulas_pendentes)): ?>
                <span class="nav-badge"><?= count($sumulas_pendentes) ?></span>
            <?php endif; ?>
        </a>
        <a class="nav-item" href="javascript:void(0)" data-painel="alunos" onclick="trocarPainel(this)">
            <i class="fas fa-users"></i> Alunos
        </a>
        <a class="nav-item" href="javascript:void(0)" data-painel="modalidades" onclick="trocarPainel(this)">
            <i class="fas fa-futbol"></i> Modalidades
        </a>
        <a class="nav-item" href="javascript:void(0)" data-painel="professores" onclick="trocarPainel(this)">
            <i class="fas fa-chalkboard-teacher"></i> Professores
            <span class="nav-badge"><?= count($professores) ?></span>
        </a>
        <a class="nav-item" href="/soee/src/frontend/views/forms/feedback.php">
            <i class="fas fa-comment-dots"></i> Feedback
        </a>
    </nav>

    <div class="sidebar-footer">
        <a href="/soee/src/frontend/views/site/profile.php" class="user-card"
           style="text-decoration:none;display:flex;align-items:center;gap:12px;padding:8px;border-radius:var(--raio-medio);transition:background .2s;"
           onmouseover="this.style.background='rgba(255,255,255,0.07)'"
           onmouseout="this.style.background='none'">
            <div class="user-avatar">
                <?php if (!empty($fotoPerfil)): ?>
                    <img src="<?= htmlspecialchars($fotoPerfil) ?>" alt="Foto de perfil"
                         onerror="this.style.display='none';this.parentNode.innerHTML='<?= strtoupper(substr($usuario_logado, 0, 2)) ?>';"
                         style="width:100%;height:100%;object-fit:cover;border-radius:50%;">
                <?php else: ?>
                    <?= strtoupper(substr($usuario_logado, 0, 2)) ?>
                <?php endif; ?>
            </div>
            <div class="user-info">
                <strong><?= htmlspecialchars($usuario_logado) ?></strong>
                <span>Professor</span>
            </div>
        </a>
        <a href="/soee/src/backend/includes/logout.php"
           style="display:flex;align-items:center;gap:8px;margin-top:10px;padding:8px 10px;border-radius:var(--raio-medio);color:rgba(255,255,255,.4);font-size:.8rem;text-decoration:none;transition:color .2s;"
           onmouseover="this.style.color='#fca5a5'" onmouseout="this.style.color='rgba(255,255,255,.4)'">
            <i class="fas fa-right-from-bracket"></i> Sair da conta
        </a>
    </div>
</aside>

<!-- ══════ MAIN ══════ -->
<div class="main">

    <header class="topbar">
        <div class="topbar-title" id="topbar-titulo">Dashboard</div>
        <div class="topbar-search">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="Buscar aluno, turma, partida…" />
        </div>
        <button class="botao-icone" onclick="alternarTema()" title="Tema">
            <i class="fas fa-moon" id="tema-icone"></i>
        </button>
        <a href="/soee/src/frontend/views/site/home.php" class="botao-icone" title="Início">
            <i class="fas fa-home"></i>
        </a>
    </header>

    <div class="content">

        <!-- ════ PAINEL: OVERVIEW ════ -->
        <div class="painel active" id="painel-overview">
            <div class="kpi-grid">
                <div class="kpi-card azul">
                    <div class="kpi-icon"><i class="fas fa-users"></i></div>
                    <div class="kpi-num"><?= (int)$kpi_alunos ?></div>
                    <div class="kpi-label">Alunos Cadastrados</div>
                </div>
                <div class="kpi-card laranja">
                    <div class="kpi-icon"><i class="fas fa-futbol"></i></div>
                    <div class="kpi-num"><?= (int)$kpi_partidas ?></div>
                    <div class="kpi-label">Partidas Agendadas</div>
                </div>
                <div class="kpi-card verde">
                    <div class="kpi-icon"><i class="fas fa-check-circle"></i></div>
                    <div class="kpi-num"><?= (int)$kpi_realizadas ?></div>
                    <div class="kpi-label">Partidas Realizadas</div>
                </div>
                <div class="kpi-card roxo">
                    <div class="kpi-icon"><i class="fas fa-layer-group"></i></div>
                    <div class="kpi-num"><?= (int)$kpi_modalidades ?></div>
                    <div class="kpi-label">Modalidades Ativas</div>
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">
                <!-- Próximas Partidas -->
                <div class="secao-card">
                    <div class="secao-card-header">
                        <h3>Próximas Partidas</h3>
                        <button class="btn btn-primario btn-sm" onclick="trocarPainelById('agenda')">
                            <i class="fas fa-arrow-right"></i> Ver todas
                        </button>
                    </div>
                    <?php if (empty($agenda)): ?>
                        <p style="padding:16px;opacity:.6;">Nenhuma partida agendada.</p>
                    <?php else: ?>
                    <div class="agenda-lista">
                        <?php foreach (array_slice($agenda, 0, 4) as $p):
                            $dt = new DateTime($p['data_partida']); ?>
                        <div class="agenda-item">
                            <div class="agenda-data">
                                <strong><?= $dt->format('d') ?></strong>
                                <span><?= $dt->format('M') ?></span>
                            </div>
                            <div class="agenda-info">
                                <strong><?= htmlspecialchars($p['time_a'] . ' vs ' . $p['time_b'] . ' — ' . $p['nome_modalidade']) ?></strong>
                                <?php if ($p['local_partida']): ?>
                                    <span><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($p['local_partida']) ?></span>
                                <?php endif; ?>
                            </div>
                            <span class="agenda-hora"><?= fmtHora($p['hora_partida']) ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Súmulas Pendentes -->
                <div class="secao-card">
                    <div class="secao-card-header">
                        <h3>Súmulas Pendentes</h3>
                        <span class="secao-tag-mini"><?= count($sumulas_pendentes) ?> pendentes</span>
                    </div>
                    <?php if (empty($sumulas_pendentes)): ?>
                        <p style="padding:16px;opacity:.6;">Nenhuma súmula pendente.</p>
                    <?php else: ?>
                    <div class="tabela-wrap">
                        <table>
                            <thead>
                                <tr><th>Partida</th><th>Enviado por</th><th>Status</th><th>Ações</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice(array_values($sumulas_pendentes), 0, 3) as $s): ?>
                                <tr>
                                    <td><?= htmlspecialchars($s['nome_modalidade'] . ' — ' . $s['time_a'] . ' vs ' . $s['time_b']) ?></td>
                                    <td><?= htmlspecialchars($s['enviado_por']) ?></td>
                                    <td><?= badgeStatus($s['status_sumula']) ?></td>
                                    <td class="td-acoes">
                                        <button class="btn btn-ok btn-sm" onclick="validarSumula(<?= $s['id_sumula'] ?>, 'validada')"><i class="fas fa-check"></i></button>
                                        <button class="btn btn-perigo btn-sm" onclick="validarSumula(<?= $s['id_sumula'] ?>, 'rejeitada')"><i class="fas fa-times"></i></button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- ════ PAINEL: AGENDA ════ -->
        <div class="painel" id="painel-agenda">
            <div class="secao-card">
                <div class="secao-card-header">
                    <h3>Agenda Completa de Partidas</h3>
                    <button class="btn btn-primario btn-sm" onclick="abrirModal('modal-partida')">
                        <i class="fas fa-plus"></i> Agendar
                    </button>
                </div>
                <?php if (empty($agenda)): ?>
                    <p style="padding:24px;opacity:.6;">Nenhuma partida agendada no momento.</p>
                <?php else:
                    $por_dia = [];
                    foreach ($agenda as $p) { $por_dia[$p['data_partida']][] = $p; }
                    ksort($por_dia);
                ?>
                <div class="agenda-tabela-wrap">
                    <div class="agenda-colunas">
                        <?php foreach ($por_dia as $data => $pd):
                            $dt = new DateTime($data); ?>
                        <div class="agenda-coluna">
                            <div class="agenda-coluna-header">
                                <span class="agenda-col-diaSemana"><?= $dias_pt[$dt->format('l')] ?></span>
                                <span class="agenda-col-dia"><?= $dt->format('d') ?></span>
                                <span class="agenda-col-mes"><?= $meses_pt[$dt->format('F')] ?></span>
                            </div>
                            <div class="agenda-coluna-body">
                                <?php foreach ($pd as $p): ?>
                                <div class="agenda-slot">
                                    <span class="agenda-slot-hora"><?= fmtHora($p['hora_partida']) ?></span>
                                    <div class="agenda-slot-info">
                                        <strong><?= htmlspecialchars($p['time_a'] . ' vs ' . $p['time_b']) ?></strong>
                                        <span class="agenda-slot-mod"><?= htmlspecialchars($p['nome_modalidade']) ?></span>
                                        <?php if ($p['local_partida']): ?>
                                            <span class="agenda-slot-local">
                                                <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($p['local_partida']) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- ════ PAINEL: EDIÇÕES ════ -->
        <div class="painel" id="painel-edicoes">
            <div class="secao-card">
                <div class="secao-card-header">
                    <h3>Edições / Eventos</h3>
                    <div style="display:flex;gap:10px;align-items:center;">
                        <span class="secao-tag-mini"><?= count($edicoes) ?> registros</span>
                        <button class="btn btn-primario btn-sm" onclick="abrirModal('modal-edicao')">
                            <i class="fas fa-plus"></i> Nova Edição
                        </button>
                    </div>
                </div>
                <div class="tabela-wrap">
                    <table>
                        <thead>
                            <tr><th>#</th><th>Nome</th><th>Ano</th><th>Início</th><th>Fim</th><th>Status</th><th>Alterar Status</th><th>Sorteio</th></tr>
                        </thead>
                        <tbody>
                        <?php if (empty($edicoes)): ?>
                            <tr><td colspan="8" style="text-align:center;opacity:.6;padding:20px;">Nenhuma edição cadastrada.</td></tr>
                        <?php else: foreach ($edicoes as $e): ?>
                            <tr>
                                <td><?= $e['id_edicao'] ?></td>
                                <td><?= htmlspecialchars($e['nome_edicao']) ?></td>
                                <td><?= $e['ano_edicao'] ?></td>
                                <td><?= fmtData($e['data_inicio_edicao']) ?></td>
                                <td><?= fmtData($e['data_fim_edicao']) ?></td>
                                <td><?= badgeStatus($e['status_edicao']) ?></td>
                                <td>
                                    <select class="status-select"
                                            onchange="alterarStatusEdicao(<?= $e['id_edicao'] ?>, this.value, this)">
                                        <option value="planejamento"  <?= $e['status_edicao']==='planejamento'  ? 'selected':'' ?>>Planejamento</option>
                                        <option value="inscricoes"    <?= $e['status_edicao']==='inscricoes'    ? 'selected':'' ?>>Inscrições Abertas</option>
                                        <option value="em_andamento"  <?= $e['status_edicao']==='em_andamento'  ? 'selected':'' ?>>Em Andamento</option>
                                        <option value="encerrado"     <?= $e['status_edicao']==='encerrado'     ? 'selected':'' ?>>Encerrado</option>
                                    </select>
                                </td>
                                <td>
                                    <?php $mods = $modalidadesPorEdicao[$e['id_edicao']] ?? [];
                                    if (empty($mods)): ?>
                                        <span class="sorteio-sem-ins">Nenhuma modalidade</span>
                                    <?php else: ?>
                                    <div class="sorteio-cell">
                                        <?php foreach ($mods as $mod):
                                            $jaFeito = !empty($sorteiosGerados[$mod['id_edicao_modalidade']]); ?>
                                        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                                            <span class="sorteio-mod-nome">
                                                <i class="fas fa-futbol" style="font-size:.65rem;color:var(--laranja)"></i>
                                                <?= htmlspecialchars($mod['nome_modalidade']) ?>
                                                <span style="color:var(--texto-secundario);font-size:.68rem;">
                                                    (<?= $mod['turmas_inscritas'] ?> turma<?= $mod['turmas_inscritas'] != 1 ? 's' : '' ?>)
                                                </span>
                                            </span>
                                            <?php if ($jaFeito): ?>
                                                <span class="badge-sorteado"><i class="fas fa-check"></i> Sorteado</span>
                                            <?php elseif ($mod['turmas_inscritas'] >= 2): ?>
                                                <button class="btn-sortear"
                                                        id="btn-sortear-<?= $mod['id_edicao_modalidade'] ?>"
                                                        onclick="gerarSorteio(<?= $mod['id_edicao_modalidade'] ?>, '<?= addslashes(htmlspecialchars($mod['nome_modalidade'])) ?>', this)">
                                                    <i class="fas fa-shuffle"></i> Sortear
                                                </button>
                                            <?php else: ?>
                                                <span class="sorteio-sem-ins">Inscritos insuficientes</span>
                                            <?php endif; ?>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ════ PAINEL: PARTIDAS ════ -->
        <div class="painel" id="painel-partidas">
            <div class="secao-card">
                <div class="secao-card-header">
                    <h3>Partidas</h3>
                    <span class="secao-tag-mini"><?= count($partidas) ?> registros</span>
                    <button class="btn btn-primario btn-sm" onclick="abrirModal('modal-partida')">
                        <i class="fas fa-plus"></i> Agendar Partida
                    </button>
                </div>
                <div class="tabela-wrap">
                    <table>
                        <thead>
                            <tr><th>#</th><th>Modalidade</th><th>Time A</th><th>Time B</th><th>Data</th><th>Hora</th><th>Local</th><th>Fase</th><th>Status</th><th>Ações</th></tr>
                        </thead>
                        <tbody>
                        <?php if (empty($partidas)): ?>
                            <tr><td colspan="10" style="text-align:center;opacity:.6;padding:20px;">Nenhuma partida cadastrada.</td></tr>
                        <?php else: foreach ($partidas as $p): ?>
                            <tr>
                                <td><?= $p['id_partida'] ?></td>
                                <td><?= htmlspecialchars($p['nome_modalidade']) ?></td>
                                <td><?= htmlspecialchars($p['time_a']) ?></td>
                                <td><?= htmlspecialchars($p['time_b']) ?></td>
                                <td><?= fmtData($p['data_partida']) ?></td>
                                <td><?= fmtHora($p['hora_partida']) ?></td>
                                <td><?= htmlspecialchars($p['local_partida'] ?? '—') ?></td>
                                <td><span class="badge-status pendente"><?= $faseLabel[$p['fase_partida']] ?? ucfirst($p['fase_partida']) ?></span></td>
                                <td><?= badgeStatus($p['status_partida']) ?></td>
                                <td class="td-acoes">
                                    <button class="btn-editar-partida"
                                            onclick="abrirEditarPartida(<?= $p['id_partida'] ?>,'<?= $p['data_partida'] ?>','<?= substr($p['hora_partida'],0,5) ?>','<?= addslashes(htmlspecialchars($p['local_partida'] ?? '')) ?>')">
                                        <i class="fas fa-pen"></i> Editar
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ════ PAINEL: RESULTADOS ════ -->
        <div class="painel" id="painel-resultados">
            <div class="secao-card">
                <div class="secao-card-header">
                    <h3>Resultados</h3>
                    <span class="secao-tag-mini"><?= count($resultados) ?> partidas</span>
                </div>
                <div class="tabela-wrap">
                    <table>
                        <thead>
                            <tr><th>#</th><th>Partida</th><th>Data</th><th>Status</th><th class="td-resultado">Resultado</th></tr>
                        </thead>
                        <tbody>
                        <?php if (empty($resultados)): ?>
                            <tr><td colspan="5" style="text-align:center;opacity:.6;padding:20px;">Nenhuma partida encontrada.</td></tr>
                        <?php else: foreach ($resultados as $r): ?>
                            <tr>
                                <td><?= $r['id_partida'] ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($r['time_a'] . ' vs ' . $r['time_b']) ?></strong><br>
                                    <small style="color:var(--texto-secundario)"><?= htmlspecialchars($r['nome_modalidade']) ?></small>
                                </td>
                                <td><?= fmtData($r['data_partida'] ?? '') ?></td>
                                <td><?= badgeStatus($r['status_partida'] ?? 'agendada') ?></td>
                                <td class="td-resultado">
                                    <?php if (!is_null($r['id_resultado'])): ?>
                                        <span class="resultado-vencedor-badge">
                                            <i class="fas fa-trophy"></i>
                                            <?php
                                                if ($r['vencedor']) echo htmlspecialchars($r['vencedor']);
                                                elseif ($r['placar_time_a'] == $r['placar_time_b']) echo 'Empate';
                                                else echo $r['placar_time_a'] > $r['placar_time_b']
                                                    ? htmlspecialchars($r['time_a'])
                                                    : htmlspecialchars($r['time_b']);
                                            ?>
                                            (<?= $r['placar_time_a'] ?>–<?= $r['placar_time_b'] ?>)
                                        </span>
                                    <?php else: ?>
                                        <div class="resultado-inline-form">
                                            <span style="font-size:.78rem;font-weight:600;color:var(--texto-secundario);max-width:80px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?= htmlspecialchars($r['time_a']) ?>">
                                                <?= htmlspecialchars($r['time_a']) ?>
                                            </span>
                                            <input type="number" class="placar-input placar-a" min="0" max="99" placeholder="0">
                                            <span class="placar-x">×</span>
                                            <input type="number" class="placar-input placar-b" min="0" max="99" placeholder="0">
                                            <span style="font-size:.78rem;font-weight:600;color:var(--texto-secundario);max-width:80px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?= htmlspecialchars($r['time_b']) ?>">
                                                <?= htmlspecialchars($r['time_b']) ?>
                                            </span>
                                            <button class="btn-salvar-placar"
                                                    onclick="salvarResultadoInline(<?= $r['id_partida'] ?>,'<?= addslashes(htmlspecialchars($r['time_a'])) ?>','<?= addslashes(htmlspecialchars($r['time_b'])) ?>',this)">
                                                <i class="fas fa-check"></i> Salvar
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ══════ PAINEL: MODALIDADES ══════ -->
        <div class="painel" id="painel-modalidades">
            <div class="secao-card">
                <div class="secao-card-header">
                    <h3>Modalidades Esportivas</h3>
                    <span class="secao-tag-mini"><?= count($modalidades) ?> registros</span>
                    <a href="/soee/src/frontend/views/forms/criacao-esporte.php" class="btn btn-primario btn-sm">
                        <i class="fas fa-plus"></i> Nova Modalidade
                    </a>
                </div>
                <div class="tabela-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nome</th>
                                <th>Tipo</th>
                                <th>Formato</th>
                                <th>Participação</th>
                                <th>Min/Max</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($modalidades)): ?>
                            <tr><td colspan="8" style="text-align:center;opacity:.6;padding:20px;">Nenhuma modalidade cadastrada.</td></tr>
                            <?php else: foreach ($modalidades as $m): ?>
                            <tr>
                                <td><?= $m['id_modalidade'] ?></td>
                                <td><?= htmlspecialchars($m['nome_modalidade']) ?></td>
                                <td><?= htmlspecialchars($m['tipo_modalidade']) ?></td>
                                <td><?= htmlspecialchars($m['formato_modalidade']) ?></td>
                                <td><?= htmlspecialchars($m['tipo_participacao']) ?></td>
                                <td><?= $m['qtd_min_jogadores'] ?> / <?= $m['qtd_max_jogadores'] ?></td>
                                <td><?= $m['ativo_modalidade'] ? badgeStatus('ativo') : badgeStatus('inativo') ?></td>
                                <td class="td-acoes">
                                    <button class="btn btn-primario btn-sm" title="Editar Modalidade"
                                        onclick="abrirModalEditarModalidade({
                                            id_modalidade: <?= $m['id_modalidade'] ?>,
                                            nome_modalidade: '<?= addslashes(htmlspecialchars($m['nome_modalidade'])) ?>',
                                            tipo_modalidade: '<?= $m['tipo_modalidade'] ?>',
                                            formato_modalidade: '<?= $m['formato_modalidade'] ?>',
                                            tipo_participacao: '<?= $m['tipo_participacao'] ?>',
                                            genero_modalidade: '<?= $m['genero_modalidade'] ?? '' ?>',
                                            qtd_min_jogadores: <?= $m['qtd_min_jogadores'] ?>,
                                            qtd_max_jogadores: <?= $m['qtd_max_jogadores'] ?>,
                                            ativo_modalidade: <?= $m['ativo_modalidade'] ? '1' : '0' ?>,
                                            descricao_modalidade: '<?= addslashes(htmlspecialchars($m['descricao_modalidade'] ?? '')) ?>',
                                            regulamento_modalidade: '<?= addslashes(htmlspecialchars($m['regulamento_modalidade'] ?? '')) ?>',
                                            tipo_duracao: '<?= $m['tipo_duracao'] ?? '' ?>',
                                            duracao_minutos: '<?= $m['duracao_minutos'] ?? '' ?>',
                                            duracao_pontos: '<?= $m['duracao_pontos'] ?? '' ?>'
                                        })">
                                        <i class="fas fa-pen"></i>
                                    </button>
                                    <button class="btn btn-perigo btn-sm" title="Excluir Modalidade"
                                            onclick="excluirRegistro('modalidade', <?= $m['id_modalidade'] ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ════ PAINEL: SÚMULAS ════ -->
        <div class="painel" id="painel-sumulas">
            <div class="secao-card">
                <div class="secao-card-header">
                    <h3>Súmulas</h3>
                    <span class="secao-tag-mini"><?= count($sumulas) ?> registros</span>
                    <button class="btn btn-primario btn-sm" onclick="abrirModal('modal-sumula')">
                        <i class="fas fa-upload"></i> Enviar Súmula
                    </button>
                </div>
                <div class="tabela-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Partida</th>
                                <th>Enviado por</th>
                                <th>Arquivo</th>
                                <th>Tipo</th>
                                <th>Data Envio</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($sumulas)): ?>
                                <tr>
                                    <td colspan="8" style="text-align:center;opacity:.6;padding:20px;">
                                        Nenhuma súmula cadastrada.
                                    </td>
                                </tr>
                            <?php else: foreach ($sumulas as $s): ?>
                                <tr id="tr-sumula-<?= $s['id_sumula'] ?>">

                                    <td><?= $s['id_sumula'] ?></td>

                                    <td>
                                        <strong><?= htmlspecialchars($s['nome_modalidade']) ?></strong><br>
                                        <small style="color:var(--texto-secundario);">
                                            <?= htmlspecialchars($s['time_a'] . ' vs ' . $s['time_b']) ?>
                                        </small>
                                    </td>

                                    <td><?= htmlspecialchars($s['enviado_por'] ?? 'Desconhecido') ?></td>

                                    <!-- Arquivo da Súmula -->
                                    <td>
                                        <?php if (!empty($s['caminho_arquivo_sumula'])): ?>
                                            <?php
                                                // caminho_arquivo_sumula já contém o path completo
                                                // ex: /soee/src/frontend/assets/sumulas/sumula_abc.pdf
                                                $ext   = strtolower(pathinfo($s['caminho_arquivo_sumula'], PATHINFO_EXTENSION));
                                                $isPdf = ($ext === 'pdf');
                                            ?>
                                            <a href="/soee/src/frontend/assets/sumulas/<?= htmlspecialchars($s['caminho_arquivo_sumula']) ?>"
                                               target="_blank"
                                               class="btn btn-sm"
                                               style="background-color:var(--laranja);color:white;padding:4px 8px;border-radius:4px;text-decoration:none;font-size:.85rem;display:inline-flex;align-items:center;gap:5px;">
                                                <i class="fas <?= $isPdf ? 'fa-file-pdf' : 'fa-file-image' ?>"></i>
                                                <?= $isPdf ? 'Ver PDF' : 'Ver Arquivo' ?>
                                            </a>
                                        <?php else: ?>
                                            <span style="color:var(--vermelho);font-size:.85rem;">
                                                <i class="fas fa-times-circle"></i> Sem arquivo
                                            </span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- FIX: usa tipo_arquivo_sumula (nome correto no schema) -->
                                    <td>
                                        <span class="secao-tag-mini">
                                            <?= !empty($s['tipo_arquivo_sumula'])
                                                ? strtoupper(htmlspecialchars($s['tipo_arquivo_sumula']))
                                                : '—' ?>
                                        </span>
                                    </td>

                                    <!-- FIX: usa data_envio_sumula + guarda fallback para null -->
                                    <td>
                                        <?= !empty($s['data_envio_sumula'])
                                            ? date('d/m/Y H:i', strtotime($s['data_envio_sumula']))
                                            : '—' ?>
                                    </td>

                                    <td><?= badgeStatus($s['status_sumula']) ?></td>

                                    <td class="td-acoes">
                                        <!-- FIX: passa partida_id_partida corretamente -->
                                        <button class="btn btn-primario btn-sm" title="Editar Súmula"
                                                onclick="abrirModalEditarSumula({
                                                    id_sumula:  '<?= $s['id_sumula'] ?>',
                                                    id_partida: '<?= $s['partida_id_partida'] ?>',
                                                    status:     '<?= $s['status_sumula'] ?>'
                                                })">
                                            <i class="fas fa-pen"></i>
                                        </button>
                                        <button class="btn btn-perigo btn-sm" title="Excluir Súmula"
                                                onclick="excluirSumula(<?= $s['id_sumula'] ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>

                                </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ════ PAINEL: ALUNOS ════ -->
        <div class="painel" id="painel-alunos">
            <div class="secao-card">
                <div class="secao-card-header">
                    <h3>Alunos &amp; Turmas</h3>
                    <span class="secao-tag-mini"><?= count($alunos) ?> registros</span>
                </div>
                <div class="tabela-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nome</th>
                                <th>E-mail</th>
                                <th>Turma</th>
                                <th>Gênero</th>
                                <th>Cargo</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($alunos)): ?>
                            <tr><td colspan="8" style="text-align:center;opacity:.6;padding:20px;">Nenhum aluno encontrado.</td></tr>
                        <?php else:
                            $generoLabel = ['m' => 'Masc.', 'f' => 'Fem.', 'n' => '—'];
                            foreach ($alunos as $a):
                                $isAdm = $a['tipo_usuario'] === 'adm_sala'; ?>
                        <tr>
                            <td><?= $a['id_usuario'] ?></td>
                            <td style="font-weight:600;"><?= htmlspecialchars($a['nome_usuario']) ?></td>
                            <td style="color:var(--texto-secundario);font-size:.82rem;"><?= htmlspecialchars($a['email_usuario']) ?></td>
                            <td><?= htmlspecialchars($a['nome_turma'] ?? '—') ?></td>
                            <td><?= $generoLabel[$a['genero_usuario']] ?? '—' ?></td>
                            <td>
                                <?php if ($isAdm): ?>
                                    <span class="badge-cargo adm-sala"><i class="fas fa-star"></i> Adm. Sala</span>
                                <?php else: ?>
                                    <span class="badge-cargo aluno"><i class="fas fa-user-graduate"></i> Aluno</span>
                                <?php endif; ?>
                            </td>
                            <td><?= $a['ativo_usuario'] ? badgeStatus('ativo') : badgeStatus('inativo') ?></td>
                            <td class="td-acoes">
                                <?php if ($isAdm): ?>
                                    <button class="btn-acao remover" title="Remover cargo"
                                            onclick="elegerAdmSala(<?= $a['id_usuario'] ?>, '<?= addslashes(htmlspecialchars($a['nome_usuario'])) ?>')">
                                        <i class="fas fa-undo"></i>
                                    </button>
                                <?php else: ?>
                                    <button class="btn-acao eleger" title="Eleger como Adm. de Sala"
                                            onclick="elegerAdmSala(<?= $a['id_usuario'] ?>, '<?= addslashes(htmlspecialchars($a['nome_usuario'])) ?>')">
                                        <i class="fas fa-star"></i>
                                    </button>
                                <?php endif; ?>
                                <button class="btn-acao remover" title="Remover Aluno do Sistema"
                                        style="background-color:#dc3545;color:white;"
                                        onclick="removerAluno(<?= $a['id_usuario'] ?>, '<?= addslashes(htmlspecialchars($a['nome_usuario'])) ?>')">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ════ PAINEL: PROFESSORES ════ -->
        <div class="painel" id="painel-professores">
            <div class="secao-card">
                <div class="secao-card-header">
                    <h3>Gestão de Professores</h3>
                    <span class="secao-tag-mini"><?= count($professores) ?> registros</span>
                    <button class="btn btn-primario btn-sm" onclick="abrirModal('modal-promover-professor')">
                        <i class="fas fa-user-plus"></i> Promover Aluno
                    </button>
                    <button class="btn btn-sucesso btn-sm" onclick="abrirModal('modal-cadastrar-professor')">
                        <i class="fas fa-plus"></i> Novo Professor
                    </button>
                </div>
                <div class="tabela-wrap">
                    <table>
                        <thead>
                            <tr><th>#</th><th>Nome</th><th>E-mail</th><th>Cargo Atual</th><th>Status</th><th>Ações</th></tr>
                        </thead>
                        <tbody>
                        <?php if (empty($professores)): ?>
                            <tr><td colspan="6" style="text-align:center;opacity:.6;padding:20px;">Nenhum outro professor cadastrado.</td></tr>
                        <?php else: foreach ($professores as $prof): ?>
                            <tr id="tr-prof-<?= $prof['id_usuario'] ?>">
                                <td><?= $prof['id_usuario'] ?></td>
                                <td style="font-weight:600;"><?= htmlspecialchars($prof['nome_usuario']) ?></td>
                                <td style="color:var(--texto-secundario);font-size:.82rem;"><?= htmlspecialchars($prof['email_usuario']) ?></td>
                                <td>
                                    <?php if ($prof['tipo_usuario'] === 'adm_geral'): ?>
                                        <span class="badge-cargo adm-sala"><i class="fas fa-crown"></i> Adm. Geral</span>
                                    <?php else: ?>
                                        <span class="badge-cargo eleger"><i class="fas fa-chalkboard-teacher"></i> Professor</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= $prof['ativo_usuario'] ? badgeStatus('ativo') : badgeStatus('inativo') ?></td>
                                <td class="td-acoes">
                                    <?php if ($prof['tipo_usuario'] === 'professor'): ?>
                                        <button class="btn-acao remover" title="Excluir professor"
                                                onclick="excluirProfessor(<?= $prof['id_usuario'] ?>, '<?= htmlspecialchars(addslashes($prof['nome_usuario'])) ?>')">
                                            <i class="fas fa-trash"></i> Excluir
                                        </button>
                                    <?php else: ?>
                                        <span style="opacity:.4;font-size:.8rem;">Protegido</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div><!-- /content -->
</div><!-- /main -->


<!-- ════ MODAL: Nova Edição ════ -->
<div class="modal-overlay" id="modal-edicao">
    <div class="modal modal-edicao">
        <div class="modal-header">
            <h4><i class="fas fa-trophy" style="color:var(--laranja);margin-right:6px;"></i> Nova Edição / Evento</h4>
            <button class="modal-close" onclick="fecharModal('modal-edicao')"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <form action="/soee/src/backend/actions/salvar-edicao.php" method="POST" id="form-edicao">
                <div class="form-grid">
                    <div class="form-grupo span2">
                        <label class="form-label">Nome do Evento <span style="color:var(--laranja)">*</span></label>
                        <input class="form-input" type="text" name="nome_edicao" placeholder="Ex.: Interclasse 2026 — 1º Semestre" maxlength="80" required />
                    </div>
                    <div class="form-grupo">
                        <label class="form-label">Ano <span style="color:var(--laranja)">*</span></label>
                        <input class="form-input" type="number" name="ano_edicao" value="<?= date('Y') ?>" min="2020" max="2099" required />
                    </div>
                    <div class="form-grupo">
                        <label class="form-label">Status <span style="color:var(--laranja)">*</span></label>
                        <select class="form-select" name="status_edicao" required>
                            <option value="planejamento">Planejamento</option>
                            <option value="inscricoes">Inscrições Abertas</option>
                            <option value="em_andamento">Em Andamento</option>
                            <option value="encerrado">Encerrado</option>
                        </select>
                    </div>
                    <div class="form-grupo">
                        <label class="form-label">Data de Início <span style="color:var(--laranja)">*</span></label>
                        <input class="form-input" type="date" name="data_inicio_edicao" required />
                    </div>
                    <div class="form-grupo">
                        <label class="form-label">Data de Fim (opcional)</label>
                        <input class="form-input" type="date" name="data_fim_edicao" />
                    </div>
                    <div class="form-grupo span2">
                        <label class="form-label">Descrição (opcional)</label>
                        <textarea class="form-textarea" name="descricao_edicao" rows="3"></textarea>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secundario" onclick="fecharModal('modal-edicao')">Cancelar</button>
            <button class="btn btn-primario" onclick="document.getElementById('form-edicao').submit()"><i class="fas fa-save"></i> Criar Edição</button>
        </div>
    </div>
</div>

<!-- ════ MODAL: Agendar Partida ════ -->
<div class="modal-overlay" id="modal-partida">
    <div class="modal">
        <div class="modal-header">
            <h4>Agendar Partida</h4>
            <button class="modal-close" onclick="fecharModal('modal-partida')"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <form action="/soee/src/backend/actions/salvar-partida.php" method="POST" id="form-partida">
                <div class="form-grid">
                    <div class="form-grupo span2">
                        <label class="form-label">Edição / Modalidade</label>
                        <select class="form-select" name="edicao_modalidade_id" required>
                            <option value="">Selecionar…</option>
                            <?php foreach ($edicoes_modal_select as $em): ?>
                            <option value="<?= $em['id_edicao_modalidade'] ?>"><?= htmlspecialchars($em['label']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-grupo">
                        <label class="form-label">Time A</label>
                        <select class="form-select" name="turma_id_time_a" required>
                            <?php foreach ($turmas_select as $t): ?>
                            <option value="<?= $t['id_turma'] ?>"><?= htmlspecialchars($t['nome_turma']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-grupo">
                        <label class="form-label">Time B</label>
                        <select class="form-select" name="turma_id_time_b" required>
                            <?php foreach ($turmas_select as $t): ?>
                            <option value="<?= $t['id_turma'] ?>"><?= htmlspecialchars($t['nome_turma']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-grupo">
                        <label class="form-label">Data</label>
                        <input class="form-input" type="date" name="data_partida" required />
                    </div>
                    <div class="form-grupo">
                        <label class="form-label">Hora</label>
                        <input class="form-input" type="time" name="hora_partida" required />
                    </div>
                    <div class="form-grupo">
                        <label class="form-label">Local</label>
                        <input class="form-input" type="text" name="local_partida" placeholder="Ex.: Quadra A" />
                    </div>
                    <div class="form-grupo">
                        <label class="form-label">Fase</label>
                        <select class="form-select" name="fase_partida" required>
                            <option value="grupos">Grupos</option>
                            <option value="oitavas">Oitavas</option>
                            <option value="quartas">Quartas</option>
                            <option value="semi">Semifinal</option>
                            <option value="final">Final</option>
                            <option value="terceiro_lugar">3º Lugar</option>
                        </select>
                    </div>
                    <div class="form-grupo span2">
                        <label class="form-label">Observações</label>
                        <textarea class="form-textarea" name="observacoes_partida"></textarea>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secundario" onclick="fecharModal('modal-partida')">Cancelar</button>
            <button class="btn btn-primario" onclick="document.getElementById('form-partida').submit()"><i class="fas fa-save"></i> Salvar</button>
        </div>
    </div>
</div>

<!-- ════ MODAL: Editar Partida ════ -->
<div class="modal-overlay" id="modal-editar-partida">
    <div class="modal modal-editar-partida">
        <div class="modal-header">
            <h4><i class="fas fa-pen" style="color:var(--laranja);margin-right:6px;"></i> Editar Partida</h4>
            <button class="modal-close" onclick="fecharModal('modal-editar-partida')"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="edit-partida-id">
            <div class="form-grid">
                <div class="form-grupo">
                    <label class="form-label">Data <span style="color:var(--laranja)">*</span></label>
                    <input class="form-input" type="date" id="edit-partida-data" required />
                </div>
                <div class="form-grupo">
                    <label class="form-label">Hora <span style="color:var(--laranja)">*</span></label>
                    <input class="form-input" type="time" id="edit-partida-hora" required />
                </div>
                <div class="form-grupo span2">
                    <label class="form-label">Local</label>
                    <input class="form-input" type="text" id="edit-partida-local" placeholder="Ex.: Quadra A" />
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secundario" onclick="fecharModal('modal-editar-partida')">Cancelar</button>
            <button class="btn btn-primario" id="btn-salvar-edicao-partida"><i class="fas fa-save"></i> Salvar Alterações</button>
        </div>
    </div>
</div>

<!-- ════ MODAL: Enviar Súmula ════ -->
<div class="modal-overlay" id="modal-sumula">
    <div class="modal">
        <div class="modal-header">
            <h4>Enviar Súmula</h4>
            <button class="modal-close" onclick="fecharModal('modal-sumula')"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <form action="/soee/src/backend/actions/salvar-sumula.php" method="POST" enctype="multipart/form-data" id="form-sumula">
                <div class="form-grid">
                    <div class="form-grupo span2">
                        <label class="form-label">Partida</label>
                        <select class="form-select" name="partida_id_partida" required>
                            <option value="">Selecionar…</option>
                            <?php foreach ($partidas_select as $ps): ?>
                            <option value="<?= $ps['id_partida'] ?>"><?= htmlspecialchars($ps['label']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-grupo span2">
                        <label class="form-label">Arquivo (PDF / JPG / PNG)</label>
                        <input class="form-input" type="file" name="arquivo_sumula" accept=".pdf,.jpg,.jpeg,.png" required />
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secundario" onclick="fecharModal('modal-sumula')">Cancelar</button>
            <button class="btn btn-primario" onclick="document.getElementById('form-sumula').submit()"><i class="fas fa-upload"></i> Enviar</button>
        </div>
    </div>
</div>

<!-- ════ MODAL: Editar Súmula ════ -->
<div class="modal-overlay" id="modal-editar-sumula">
    <div class="modal">
        <div class="modal-header">
            <h4><i class="fas fa-edit" style="color:var(--laranja);margin-right:6px;"></i> Editar Súmula</h4>
            <button class="modal-close" onclick="fecharModal('modal-editar-sumula')"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <form id="form-editar-sumula" enctype="multipart/form-data">
                <input type="hidden" id="edit-sumula-id" name="id_sumula" />
                <div class="form-grid">
                    <div class="form-grupo span2">
                        <label class="form-label">Partida Vinculada <span style="color:var(--laranja)">*</span></label>
                        <select class="form-select" id="edit-sumula-partida" name="partida_id_partida" required>
                            <option value="">Selecione a Partida...</option>
                            <?php foreach ($partidas_select as $ps): ?>
                                <option value="<?= $ps['id_partida'] ?>"><?= htmlspecialchars($ps['label']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-grupo span2">
                        <label class="form-label">Status do Documento <span style="color:var(--laranja)">*</span></label>
                        <select class="form-select" id="edit-sumula-status" name="status_sumula" required>
                            <option value="pendente">Pendente</option>
                            <option value="validada">Validada</option>
                            <option value="rejeitada">Rejeitada</option>
                        </select>
                    </div>
                    <div class="form-grupo span2">
                        <label class="form-label">Substituir Arquivo (Opcional)</label>
                        <input class="form-input" type="file" id="edit-sumula-arquivo" name="arquivo_sumula" accept=".pdf,.jpg,.jpeg,.png" />
                        <small style="color:var(--texto-secundario);margin-top:4px;display:block;">
                            Deixe em branco para manter o arquivo atual.
                        </small>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secundario" onclick="fecharModal('modal-editar-sumula')">Cancelar</button>
            <button class="btn btn-primario" onclick="salvarAlteracoesSumula()">
                <i class="fas fa-save"></i> Salvar Mudanças
            </button>
        </div>
    </div>
</div>

<!-- ════ MODAL: Promover Aluno a Professor ════ -->
<div class="modal-overlay" id="modal-promover-professor">
    <div class="modal">
        <div class="modal-header">
            <h4><i class="fas fa-chalkboard-teacher" style="color:var(--laranja);margin-right:6px;"></i> Promover Aluno a Professor</h4>
            <button class="modal-close" onclick="fecharModal('modal-promover-professor')"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <div class="form-grid">
                <div class="form-grupo span2">
                    <label class="form-label">Selecionar Aluno <span style="color:var(--laranja)">*</span></label>
                    <select class="form-select" id="select-promover-usuario">
                        <option value="">Selecionar…</option>
                        <?php foreach ($alunos as $a): ?>
                        <option value="<?= $a['id_usuario'] ?>">
                            <?= htmlspecialchars($a['nome_usuario']) ?>
                            <?php if ($a['nome_turma']): ?>(<?= htmlspecialchars($a['nome_turma']) ?>)<?php endif; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-grupo span2">
                    <p style="font-size:.85rem;color:var(--texto-secundario);margin:0;">
                        O aluno selecionado receberá acesso ao painel de professor e poderá gerenciar partidas, resultados e súmulas.
                    </p>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secundario" onclick="fecharModal('modal-promover-professor')">Cancelar</button>
            <button class="btn btn-primario" onclick="promoverAlunoProfessor()">
                <i class="fas fa-user-plus"></i> Promover
            </button>
        </div>
    </div>
</div>

<!-- ════ MODAL: Cadastrar Professor ════ -->
<div class="modal-overlay" id="modal-cadastrar-professor">
    <div class="modal modal-edicao">
        <div class="modal-header">
            <h4><i class="fas fa-chalkboard-teacher" style="color:var(--laranja);margin-right:6px;"></i> Cadastrar Novo Professor</h4>
            <button class="modal-close" onclick="fecharModal('modal-cadastrar-professor')"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <form action="/soee/src/backend/actions/salvar-professor.php" method="POST" enctype="multipart/form-data" id="form-cadastrar-professor">
                <div class="form-grid">
                    <div class="form-grupo span2">
                        <label class="form-label">Nome Completo <span style="color:var(--laranja)">*</span></label>
                        <input class="form-input" type="text" name="nome_usuario" placeholder="Ex: João Silva" maxlength="100" required />
                    </div>
                    <div class="form-grupo">
                        <label class="form-label">E-mail <span style="color:var(--laranja)">*</span></label>
                        <input class="form-input" type="email" name="email_usuario" placeholder="joaosilva@email.com" maxlength="100" required />
                    </div>
                    <div class="form-grupo">
                        <label class="form-label">Senha <span style="color:var(--laranja)">*</span></label>
                        <input class="form-input" type="password" name="senha_usuario" placeholder="Mínimo 6 caracteres" minlength="6" required />
                    </div>
                    <div class="form-grupo">
                        <label class="form-label">Gênero <span style="color:var(--laranja)">*</span></label>
                        <select class="form-select" name="genero_usuario" required>
                            <option value="">Selecionar...</option>
                            <option value="m">Masculino</option>
                            <option value="f">Feminino</option>
                            <option value="n">Prefiro não responder</option>
                        </select>
                    </div>
                    <div class="form-grupo">
                        <label class="form-label">Foto de Perfil (Opcional)</label>
                        <input class="form-input" type="file" name="foto_usuario" accept="image/png, image/jpeg, image/jpg" />
                    </div>
                    <div class="form-grupo">
                        <label class="form-label">Status Inicial <span style="color:var(--laranja)">*</span></label>
                        <select class="form-select" name="ativo_usuario" required>
                            <option value="1">Ativo</option>
                            <option value="0">Inativo</option>
                        </select>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secundario" onclick="fecharModal('modal-cadastrar-professor')">Cancelar</button>
            <button class="btn btn-sucesso" onclick="document.getElementById('form-cadastrar-professor').submit()">
                <i class="fas fa-save"></i> Cadastrar Professor
            </button>
        </div>
    </div>
</div>

<!-- ════ MODAL: Sorteio ════ -->
<div class="modal-sorteio-overlay" id="modal-sorteio">
    <div class="modal-sorteio-box">
        <div class="modal-sorteio-header">
            <div class="modal-sorteio-titulo"><i class="fas fa-shuffle"></i> Resultado do Sorteio</div>
            <button class="modal-sorteio-fechar" onclick="fecharModalSorteio()"><i class="fas fa-times"></i></button>
        </div>
        <div id="sorteio-loading" style="text-align:center;padding:28px;color:var(--texto-secundario);">
            <i class="fas fa-spinner fa-spin" style="font-size:1.6rem;display:block;margin-bottom:12px;color:var(--laranja)"></i>
            Sorteando partidas aleatoriamente…
        </div>
        <div id="sorteio-resultado" style="display:none;"></div>
        <div id="sorteio-footer" style="display:none;margin-top:20px;text-align:right;">
            <button class="btn btn-primario" onclick="fecharModalSorteio()"><i class="fas fa-check"></i> Fechar</button>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════
     MODAL — Editar Modalidade
══════════════════════════════════════════════════ -->
<div class="modal-overlay" id="modal-modalidade" onclick="fecharModalPeloFundo(event)">
    <div class="modal modal-edicao">
        <div class="modal-header">
            <h4>
                <i class="fas fa-trophy" style="color:var(--laranja);margin-right:6px;"></i>
                <span id="modal-modalidade-titulo">Editar Modalidade</span>
            </h4>
            <button type="button" class="modal-close" onclick="fecharModal('modal-modalidade')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <div class="form-grid">
                <input type="hidden" id="inp-id-modalidade">
                <div class="form-grupo span2">
                    <label class="form-label">Nome da Modalidade *</label>
                    <input class="form-input" type="text" id="inp-nome-modalidade" maxlength="60">
                </div>
                <div class="form-grupo">
                    <label class="form-label">Ambiente/Tipo *</label>
                    <select class="form-select" id="inp-tipo-modalidade">
                        <option value="">Selecionar...</option>
                        <option value="quadra">Quadra</option>
                        <option value="mesa">Mesa</option>
                        <option value="outro">Outro</option>
                    </select>
                </div>
                <div class="form-grupo">
                    <label class="form-label">Tipo Participação *</label>
                    <select class="form-select" id="inp-tipo-participacao">
                        <option value="">Selecionar...</option>
                        <option value="solo">Solo</option>
                        <option value="dupla">Dupla</option>
                        <option value="trio">Trio</option>
                        <option value="time">Time</option>
                    </select>
                </div>
                <div class="form-grupo">
                    <label class="form-label">Formato *</label>
                    <select class="form-select" id="inp-formato-modalidade">
                        <option value="">Selecionar...</option>
                        <option value="mata_mata">Mata-mata</option>
                        <option value="grupos">Grupos</option>
                        <option value="grupos_mata_mata">Grupos + Mata-mata</option>
                        <option value="todos_contra_todos">Todos contra todos</option>
                    </select>
                </div>
                <div class="form-grupo">
                    <label class="form-label">Gênero *</label>
                    <select class="form-select" id="inp-genero-modalidade">
                        <option value="misto">Misto</option>
                        <option value="masculino">Masculino</option>
                        <option value="feminino">Feminino</option>
                    </select>
                </div>
                <div class="form-grupo">
                    <label class="form-label">Tipo duração</label>
                    <select class="form-select" id="inp-tipo-duracao" onchange="toggleDuracao(this.value)">
                        <option value="">Selecione...</option>
                        <option value="minutos">Minutos</option>
                        <option value="pontos">Pontos</option>
                    </select>
                </div>
                <div class="form-grupo">
                    <label class="form-label">Mínimo jogadores *</label>
                    <input class="form-input" type="number" id="inp-qtd-min">
                </div>
                <div class="form-grupo">
                    <label class="form-label">Máximo jogadores *</label>
                    <input class="form-input" type="number" id="inp-qtd-max">
                </div>
                <div class="form-grupo span2" id="grupo-dur-minutos" style="display:none">
                    <label class="form-label">Duração minutos</label>
                    <input class="form-input" id="inp-dur-minutos">
                </div>
                <div class="form-grupo span2" id="grupo-dur-pontos" style="display:none">
                    <label class="form-label">Pontuação máxima</label>
                    <input class="form-input" type="number" id="inp-dur-pontos">
                </div>
                <div class="form-grupo span2">
                    <label class="form-label">Descrição</label>
                    <textarea class="form-input" id="inp-descricao-modalidade"></textarea>
                </div>
                <div class="form-grupo span2">
                    <label class="form-label">Status *</label>
                    <select class="form-select" id="inp-ativo">
                        <option value="1">Ativo</option>
                        <option value="0">Inativo</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secundario" onclick="fecharModal('modal-modalidade')">Cancelar</button>
            <button type="button" class="btn btn-sucesso" onclick="salvarAlteracoesModalidade()">
                <i class="fas fa-save"></i> Salvar Alterações
            </button>
        </div>
    </div>
</div>

<div class="toast-container" id="toast-container"></div>

<script src="/soee/src/frontend/scripts/adm.js"></script>
<script src="/soee/src/frontend/scripts/dash-prof.js"></script>
<script>
// ══════════════════════════════════════════════════════════
//  PROFESSORES
// ══════════════════════════════════════════════════════════

function gerenciarProfessor(userId, acao, nome) {
    const msg = acao === 'rebaixar'
        ? `Remover "${nome}" do cargo de professor?`
        : `Promover "${nome}" a professor?`;
    if (!confirm(msg)) return;

    fetch('/soee/src/backend/actions/eleger-professor.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `usuario_id=${userId}&acao=${acao}`
    })
    .then(r => r.json())
    .then(data => {
        if (data.ok) {
            mostrarToast(data.msg, 'sucesso');
            setTimeout(() => location.reload(), 1200);
        } else {
            mostrarToast(data.erro || 'Erro ao processar.', 'erro');
        }
    })
    .catch(() => mostrarToast('Erro de conexão.', 'erro'));
}

function promoverAlunoProfessor() {
    const sel    = document.getElementById('select-promover-usuario');
    const userId = sel?.value;
    if (!userId) { mostrarToast('Selecione um aluno.', 'erro'); return; }
    const nome = sel.options[sel.selectedIndex].text;
    fecharModal('modal-promover-professor');
    gerenciarProfessor(parseInt(userId), 'promover', nome);
}

async function excluirProfessor(id, nome) {
    if (!confirm(`Tem certeza que deseja excluir o professor "${nome}"?\n\nTodos os dados serão removidos permanentemente.`)) return;

    const dados = new FormData();
    dados.append('id_usuario', id);

    try {
        const resposta = await fetch('/soee/src/backend/actions/excluir-professor.php', {
            method: 'POST',
            body: dados,
            credentials: 'same-origin'
        });
        const retorno = await resposta.json();
        alert(retorno.message);
        if (retorno.success) {
            const linha = document.getElementById('tr-prof-' + id);
            if (linha) linha.remove();
        }
    } catch (error) {
        console.error(error);
        alert('Erro de comunicação com o servidor.');
    }
}

// ══════════════════════════════════════════════════════════
//  ALUNOS
// ══════════════════════════════════════════════════════════

function removerAluno(userId, nome) {
    if (!confirm(`ATENÇÃO: Deseja REALMENTE deletar o aluno "${nome}" permanentemente?\nO e-mail e todos os registros associados serão apagados definitivamente.`)) return;

    fetch('/soee/src/backend/actions/remover-aluno.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `usuario_id=${userId}&acao=remover`
    })
    .then(r => r.json())
    .then(data => {
        if (data.ok) {
            mostrarToast(data.msg, 'sucesso');
            const linhas = document.querySelectorAll(`button[onclick*="removerAluno(${userId}"]`);
            linhas.forEach(btn => {
                const tr = btn.closest('tr');
                if (tr) {
                    tr.style.transition = 'opacity .4s';
                    tr.style.opacity    = '0';
                    setTimeout(() => tr.remove(), 400);
                }
            });
            const contador = document.querySelector('#painel-alunos .secao-tag-mini');
            if (contador) {
                const total = parseInt(contador.textContent) || 0;
                if (total > 0) contador.textContent = `${total - 1} registros`;
            }
        } else {
            mostrarToast(data.erro || 'Erro ao processar remoção.', 'erro');
        }
    })
    .catch(() => mostrarToast('Erro de conexão com o servidor.', 'erro'));
}

// ══════════════════════════════════════════════════════════
//  MODALIDADES
// ══════════════════════════════════════════════════════════

function toggleDuracao(valor) {
    document.getElementById('grupo-dur-minutos').style.display = valor === 'minutos' ? 'block' : 'none';
    document.getElementById('grupo-dur-pontos').style.display  = valor === 'pontos'  ? 'block' : 'none';
}

function abrirModalEditarModalidade(md) {
    document.getElementById('modal-modalidade-titulo').innerHTML =
        '<i class="fa-solid fa-pen"></i> Editar Modalidade';

    document.getElementById('inp-id-modalidade').value        = md.id_modalidade;
    document.getElementById('inp-nome-modalidade').value      = md.nome_modalidade   || '';
    document.getElementById('inp-tipo-modalidade').value      = md.tipo_modalidade   || '';
    document.getElementById('inp-tipo-participacao').value    = md.tipo_participacao  || '';
    document.getElementById('inp-formato-modalidade').value   = md.formato_modalidade || '';
    document.getElementById('inp-genero-modalidade').value    = md.genero_modalidade  || '';
    document.getElementById('inp-qtd-min').value              = md.qtd_min_jogadores  || '';
    document.getElementById('inp-qtd-max').value              = md.qtd_max_jogadores  || '';
    document.getElementById('inp-descricao-modalidade').value = md.descricao_modalidade || '';
    document.getElementById('inp-ativo').value                = md.ativo_modalidade ? '1' : '0';
    document.getElementById('inp-tipo-duracao').value         = md.tipo_duracao       || '';
    document.getElementById('inp-dur-minutos').value          = md.duracao_minutos    || '';
    document.getElementById('inp-dur-pontos').value           = md.duracao_pontos     || '';

    toggleDuracao(md.tipo_duracao || '');

    const modal = document.getElementById('modal-modalidade');
    modal.style.display = 'flex';
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
}

function fecharModal(id) {
    const modal = document.getElementById(id);
    if (!modal) return;
    modal.style.display = 'none';
    modal.classList.remove('active', 'open');
    document.body.style.overflow = '';
}

function fecharModalPeloFundo(event) {
    if (event.target.id === 'modal-modalidade') fecharModal('modal-modalidade');
}

function salvarAlteracoesModalidade() {
    const dados = new FormData();
    dados.append('id_modalidade',       document.getElementById('inp-id-modalidade').value);
    dados.append('nome_modalidade',     document.getElementById('inp-nome-modalidade').value);
    dados.append('tipo_modalidade',     document.getElementById('inp-tipo-modalidade').value);
    dados.append('tipo_participacao',   document.getElementById('inp-tipo-participacao').value);
    dados.append('formato_modalidade',  document.getElementById('inp-formato-modalidade').value);
    dados.append('genero_modalidade',   document.getElementById('inp-genero-modalidade').value);
    dados.append('tipo_duracao',        document.getElementById('inp-tipo-duracao').value);
    dados.append('qtd_min_jogadores',   document.getElementById('inp-qtd-min').value);
    dados.append('qtd_max_jogadores',   document.getElementById('inp-qtd-max').value);
    dados.append('duracao_minutos',     document.getElementById('inp-dur-minutos').value);
    dados.append('duracao_pontos',      document.getElementById('inp-dur-pontos').value);
    dados.append('descricao_modalidade',document.getElementById('inp-descricao-modalidade').value);
    dados.append('ativo_modalidade',    document.getElementById('inp-ativo').value);

    fetch('/soee/src/backend/actions/editar-modalidade.php', { method: 'POST', body: dados })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                alert('Modalidade atualizada!');
                fecharModal('modal-modalidade');
                location.reload();
            } else {
                alert(res.message);
            }
        })
        .catch(err => { console.error(err); alert('Erro de comunicação com servidor'); });
}

// ══════════════════════════════════════════════════════════
//  SÚMULAS
// ══════════════════════════════════════════════════════════

/**
 * Abre o modal de edição e pré-preenche os campos com os dados da linha.
 * FIX: id_partida agora vem de partida_id_partida (campo correto do schema).
 */
function abrirModalEditarSumula(dados) {
    document.getElementById('edit-sumula-id').value      = dados.id_sumula;
    document.getElementById('edit-sumula-partida').value = dados.id_partida;
    document.getElementById('edit-sumula-status').value  = dados.status;
    document.getElementById('edit-sumula-arquivo').value = ''; // limpa seleção anterior
    abrirModal('modal-editar-sumula');
}

/**
 * Envia as alterações da súmula via fetch com FormData (suporta upload de arquivo).
 */
async function salvarAlteracoesSumula() {
    const form     = document.getElementById('form-editar-sumula');
    const formData = new FormData(form);

    try {
        const resposta = await fetch('/soee/src/backend/actions/editar-sumula.php', {
            method: 'POST',
            body: formData
        });
        const retorno = await resposta.json();

        if (retorno.success) {
            mostrarToast('Súmula atualizada com sucesso!', 'sucesso');
            fecharModal('modal-editar-sumula');
            setTimeout(() => location.reload(), 1200);
        } else {
            mostrarToast(retorno.message || 'Erro ao processar alteração.', 'erro');
        }
    } catch (err) {
        console.error(err);
        mostrarToast('Erro de comunicação com o servidor.', 'erro');
    }
}

/**
 * Remove permanentemente a súmula e o arquivo físico associado.
 */
async function excluirSumula(id) {
    if (!confirm(`Tem certeza que deseja excluir a Súmula #${id}?\nO arquivo físico associado também será apagado permanentemente.`)) return;

    const dados = new FormData();
    dados.append('id_sumula', id);

    try {
        const resposta = await fetch('/soee/src/backend/actions/excluir-sumula.php', {
            method: 'POST',
            body: dados
        });
        const retorno = await resposta.json();

        if (retorno.success) {
            mostrarToast(retorno.message, 'sucesso');
            const linha = document.getElementById('tr-sumula-' + id);
            if (linha) {
                linha.style.transition = 'opacity .4s';
                linha.style.opacity    = '0';
                setTimeout(() => linha.remove(), 400);
            }
        } else {
            mostrarToast(retorno.message || 'Erro ao remover súmula.', 'erro');
        }
    } catch (err) {
        console.error(err);
        mostrarToast('Erro ao tentar remover a súmula.', 'erro');
    }
}
</script>

<script src="/soee/src/frontend/scripts/responsive.js"></script>

<?php include __DIR__ . '/../includes/end.php'; ?>