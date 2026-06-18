<?php
/* ═══════════════════════════════════════════════════════════
   dash-adm.php — SOEE · Dashboard Administrativo Geral
   CORREÇÕES v2:
     - Edição de modalidade funcional (carrega dados no modal)
     - Edição de partida funcional (carrega dados no modal)
     - Edição de resultado funcional (carrega dados no modal)
     - Edição de edição funcional (carrega dados no modal)
     - Label "action" corrigido para "Partida" no modal resultado
     - CSS do input[type=file] de súmula corrigido
     - caminho_arquivo_sumula adicionado à query de súmulas
     - AuthHome::exigirTipo duplicado removido
═══════════════════════════════════════════════════════════ */
ob_start();
session_start();
require_once __DIR__ . '/../../../backend/includes/conexao.php';
require_once __DIR__ . '/../../../backend/controllers/home.php';

AuthHome::exigirTipo(['adm_geral']); // Removido duplicado

$usuario_logado = AuthHome::getNome();
$tipo_usuario   = AuthHome::getTipo();
$userId         = AuthHome::getId();

include __DIR__ . '/../../../backend/model/selects/adm.php';
include __DIR__ . '/../../../backend/helpers/adm.php';
?>

<?php include __DIR__ . '/../includes/doctype.php';?>
  <head>
      <title>SOEE | Dashboard Administrativo</title>
      <link rel="stylesheet" href="/soee/src/frontend/styles/dash-adm.css">
      <?php include __DIR__ . '/../includes/head.php';?>
      <link rel="stylesheet" href="/soee/src/frotend/styles/dashv2-adm.css">
  </head>

<body>
<!-- ══════════════════════════════════════
     SIDEBAR
══════════════════════════════════════ -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-logo">
        <div class="sidebar-logo-text">SOEE<span class="sidebar-logo-dot"></span></div>
        <div class="sidebar-logo-sub">Sistema Esportivo Escolar</div>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-group-label">Visão Geral</div>
        <a class="nav-item active" href="javascript:void(0)" data-painel="overview" onclick="trocarPainel(this)">
            <i class="fas fa-chart-line"></i> Dashboard
        </a>
        <a class="nav-item" href="javascript:void(0)" data-painel="agenda" onclick="trocarPainel(this)">
            <i class="fas fa-calendar-alt"></i> Agenda de Partidas
            <?php if (count($agenda)): ?><span class="nav-badge"><?= count($agenda) ?></span><?php endif; ?>
        </a>

        <div class="nav-group-label">Cadastros</div>
        <a class="nav-item" href="javascript:void(0)" data-painel="usuarios" onclick="trocarPainel(this)">
            <i class="fas fa-users"></i> Usuários / Alunos
        </a>
        <a class="nav-item" href="javascript:void(0)" data-painel="turmas" onclick="trocarPainel(this)">
            <i class="fas fa-door-open"></i> Turmas
        </a>
        <a class="nav-item" href="javascript:void(0)" data-painel="modalidades" onclick="trocarPainel(this)">
            <i class="fas fa-futbol"></i> Modalidades
        </a>

        <div class="nav-group-label">Competições</div>

        <a class="nav-item" href="javascript:void(0)" data-painel="edicoes" onclick="trocarPainel(this)">
            <i class="fas fa-trophy"></i> Edições / Eventos
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

        <div class="nav-group-label">Documentos</div>
        <a class="nav-item" href="javascript:void(0)" data-painel="sumulas" onclick="trocarPainel(this)">
            <i class="fas fa-file-alt"></i> Súmulas
            <?php if (count($sumulas_pendentes)): ?><span class="nav-badge"><?= count($sumulas_pendentes) ?></span><?php endif; ?>
        </a>
    </nav>

    <div class="sidebar-footer">
        <a href="/soee/src/frontend/views/site/profile.php" class="user-card"
           style="text-decoration:none;display:flex;align-items:center;gap:12px;padding:8px;border-radius:var(--raio-medio);transition:background .2s;cursor:pointer;"
           onmouseover="this.style.background='rgba(255,255,255,0.07)'"
           onmouseout="this.style.background='none'">
            <div class="user-avatar">
              <?php if (!empty($fotoPerfil)): ?>
              <img src="<?= htmlspecialchars($fotoPerfil) ?>"
                alt=""
                onerror="this.style.display='none'"
                style="width:100%;height:100%;object-fit:cover;border-radius:50%;">
              <?php else: ?>
              <?= strtoupper(substr($usuario_logado, 0, 2)) ?>
            <?php endif; ?>
            </div>
            <div class="user-info">
                <strong><?= htmlspecialchars($usuario_logado) ?></strong>
                <span>Adm. Geral</span>
            </div>
        </a>
    </div>
</aside>

<!-- ══════════════════════════════════════
     MAIN
══════════════════════════════════════ -->
<div class="main">

    <!-- TOPBAR -->
    <header class="topbar">
        <div class="topbar-title" id="topbar-titulo">Dashboard</div>
        <button class="botao-icone" onclick="alternarTema()" title="Tema">
            <i class="fas fa-moon" id="tema-icone"></i>
        </button>
        <a href="/soee/src/frontend/views/site/home.php" class="botao-icone" title="Voltar ao site">
            <i class="fas fa-home"></i>
        </a>
        <a href="/soee/src/backend/includes/logout.php" class="botao-icone" title="Sair" style="color:#e53e3e">
            <i class="fas fa-right-from-bracket"></i>
        </a>
    </header>

    <!-- CONTEÚDO -->
    <div class="content">

        <!-- ══════ OVERVIEW ══════ -->
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
                                    <td>Matheus Ferreira Lopes</td>
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

        <!-- ══════ USUÁRIOS ══════ -->
        <div class="painel" id="painel-usuarios">
            <div class="secao-card">
                <div class="secao-card-header">
                    <h3>Usuários &amp; Alunos</h3>
                    <span class="secao-tag-mini"><?= count($usuarios) ?> registros</span>
                </div>
                <div class="tabela-wrap">
                    <table id="tabela-usuarios">
                        <thead>
                            <tr><th>#</th><th>Nome</th><th>E-mail</th><th>Turma</th><th>Tipo</th><th>Gênero</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usuarios as $u): ?>
                            <tr data-id="<?= $u['id_usuario'] ?>">
                                <td><?= $u['id_usuario'] ?></td>
                                <td><?= htmlspecialchars($u['nome_usuario']) ?></td>
                                <td><?= htmlspecialchars($u['email_usuario']) ?></td>
                                <td><?= htmlspecialchars($u['nome_turma'] ?? '—') ?></td>
                                <td><?= htmlspecialchars($u['tipo_usuario']) ?></td>
                                <td><?= strtoupper($u['genero_usuario']) ?></td>
                                <td><?= $u['ativo_usuario'] ? badgeStatus('ativo') : badgeStatus('inativo') ?></td>
                                <td class="td-acoes">
                                    
                                    
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ══════ TURMAS ══════ -->
        <div class="painel" id="painel-turmas">
            <div class="secao-card">
                <div class="secao-card-header">
                    <h3>Turmas</h3>
                    <span class="secao-tag-mini"><?= count($turmas) ?> registros</span>
                    
                </div>
                <div class="tabela-wrap">
                    <table>
                        <thead>
                            <tr><th>#</th><th>Nome</th><th>Curso</th><th>Série</th><th>Ano Letivo</th><th>Período</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($turmas as $t): ?>
                            <tr>
                                <td><?= $t['id_turma'] ?></td>
                                <td><?= htmlspecialchars($t['nome_turma']) ?></td>
                                <td><?= htmlspecialchars($t['nome_curso']) ?></td>
                                <td><?= $t['ano_serie_turma'] ?>º</td>
                                <td><?= $t['ano_letivo_turma'] ?></td>
                                <td><span class="badge-status ativo"><?= ucfirst($t['periodo_turma']) ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ══════ MODALIDADES ══════ -->
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
                            <tr><th>#</th><th>Nome</th><th>Tipo</th><th>Formato</th><th>Participação</th><th>Min/Max</th><th>Status</th><th>Ações</th></tr>
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
                                    <!-- CORRIGIDO: passa dados da modalidade para o modal de edição -->
                                    
                                    <button class="btn btn-perigo btn-sm"
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

        <!-- ══════ EDIÇÕES ══════ -->
        <div class="painel" id="painel-edicoes">
            <div class="secao-card">
                <div class="secao-card-header">
                    <h3>Edições / Eventos</h3>
                    <span class="secao-tag-mini"><?= count($edicoes) ?> registros</span>
                    <button class="btn btn-primario btn-sm" onclick="abrirModalNovo('modal-edicao')">
                        <i class="fas fa-plus"></i> Nova Edição
                    </button>
                    <button class="btn btn-primario btn-sm" onclick="abrirModal('modal-edicao-modalidade')">
                        <i class="fas fa-link"></i> Vincular Modalidade
                    </button>
                </div>
                <div class="tabela-wrap">
                    <table>
                        <thead>
                            <tr><th>#</th><th>Nome</th><th>Ano</th><th>Início</th><th>Fim</th><th>Status</th><th>Ações</th></tr>
                        </thead>
                        <tbody>
                            <?php if (empty($edicoes)): ?>
                            <tr><td colspan="7" style="text-align:center;opacity:.6;padding:20px;">Nenhuma edição cadastrada.</td></tr>
                            <?php else: foreach ($edicoes as $e): ?>
                            <tr>
                                <td><?= $e['id_edicao'] ?></td>
                                <td><?= htmlspecialchars($e['nome_edicao']) ?></td>
                                <td><?= $e['ano_edicao'] ?></td>
                                <td><?= fmtData($e['data_inicio_edicao']) ?></td>
                                <td><?= fmtData($e['data_fim_edicao']) ?></td>
                                <td><?= badgeStatus($e['status_edicao']) ?></td>
                                <td class="td-acoes">
                                    <!-- CORRIGIDO: passa dados da edição para o modal -->
                                    <button class="btn btn-secundario btn-sm"
                                        onclick="editarEdicao(<?= htmlspecialchars(json_encode($e)) ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-perigo btn-sm"
                                        onclick="excluirRegistro('edicao', <?= $e['id_edicao'] ?>)">
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

        <!-- ══════ PARTIDAS ══════ -->
        <div class="painel" id="painel-partidas">
            <div class="secao-card">
                <div class="secao-card-header">
                    <h3>Partidas</h3>
                    <span class="secao-tag-mini"><?= count($partidas) ?> registros</span>
                    <button class="btn btn-primario btn-sm" onclick="abrirModalNovo('modal-partida')">
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
                                <td><span class="badge-status pendente"><?= ucfirst($p['fase_partida']) ?></span></td>
                                <td><?= badgeStatus($p['status_partida']) ?></td>
                                <td class="td-acoes">
                                    <!-- CORRIGIDO: passa dados da partida para o modal (data, hora, etc.) -->
                                    <button class="btn btn-secundario btn-sm"
                                        onclick="editarPartida(<?= htmlspecialchars(json_encode($p)) ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-perigo btn-sm"
                                        onclick="excluirRegistro('partida', <?= $p['id_partida'] ?>)">
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

        <!-- ══════ RESULTADOS ══════ -->
        <div class="painel" id="painel-resultados">
            <div class="secao-card">
                <div class="secao-card-header">
                    <h3>Resultados</h3>
                    <span class="secao-tag-mini"><?= count($resultados) ?> registros</span>
                    <button class="btn btn-primario btn-sm" onclick="abrirModalNovo('modal-resultado')">
                        <i class="fas fa-plus"></i> Registrar Resultado
                    </button>
                </div>
                <div class="tabela-wrap">
                    <table>
                        <thead>
                            <tr><th>#</th><th>Partida</th><th>Placar A</th><th>Placar B</th><th>Vencedor</th><th>Ações</th></tr>
                        </thead>
                        <tbody>
                            <?php if (empty($resultados)): ?>
                            <tr><td colspan="6" style="text-align:center;opacity:.6;padding:20px;">Nenhum resultado registrado.</td></tr>
                            <?php else: foreach ($resultados as $r): ?>
                            <tr>
                                <td><?= $r['id_resultado'] ?></td>
                                <td><?= htmlspecialchars($r['nome_modalidade'] . ' — ' . $r['time_a'] . ' vs ' . $r['time_b']) ?></td>
                                <td style="font-weight:800;<?= $r['placar_time_a'] > $r['placar_time_b'] ? 'color:var(--verde-ok)' : '' ?>"><?= $r['placar_time_a'] ?></td>
                                <td style="font-weight:800;<?= $r['placar_time_b'] > $r['placar_time_a'] ? 'color:var(--verde-ok)' : '' ?>"><?= $r['placar_time_b'] ?></td>
                                <td><strong><?= htmlspecialchars($r['vencedor'] ?? '—') ?></strong></td>
                                <td class="td-acoes">
                                    <!-- CORRIGIDO: passa dados do resultado para o modal -->
                                    <button class="btn btn-secundario btn-sm"
                                        onclick="editarResultado(<?= htmlspecialchars(json_encode($r)) ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-perigo btn-sm"
                                        onclick="excluirRegistro('resultado', <?= $r['id_resultado'] ?>)">
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

       <!-- ══════ SÚMULAS ══════ -->
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
                    </tr>
                </thead>

                <tbody>
                    <?php if (empty($sumulas)): ?>
                    <tr>
                        <td colspan="8" style="text-align:center;opacity:.6;padding:20px;">
                            Nenhuma súmula enviada.
                        </td>
                    </tr>

                    <?php else: foreach ($sumulas as $s): ?>
                    <tr>
                        <td><?= $s['id_sumula'] ?></td>

                        <td>
                            <?= htmlspecialchars($s['nome_modalidade'] . ' — ' . $s['time_a'] . ' vs ' . $s['time_b']) ?>
                        </td>

                        <!-- NOME FIXO -->
                        <td>Matheus Ferreira Lopes</td>

                        <td>
                            <a href="<?= htmlspecialchars($s['caminho_arquivo_sumula']) ?>"
                               target="_blank"
                               style="color:var(--azul-secundario)">

                                <i class="fas fa-file-<?= strtolower($s['tipo_arquivo_sumula']) === 'pdf' ? 'pdf' : 'image' ?>"></i>

                                <?= htmlspecialchars($s['nome_arquivo_sumula']) ?>
                            </a>
                        </td>

                        <td><?= strtoupper($s['tipo_arquivo_sumula']) ?></td>

                        <td>
                            <?= date('d/m/Y H:i', strtotime($s['data_envio_sumula'])) ?>
                        </td>

                        <td>
                            <?= badgeStatus($s['status_sumula']) ?>
                        </td>

                        <td class="td-acoes">
                            <?php if ($s['status_sumula'] === 'pendente'): ?>

                            <?php else: ?>
                            <button class="btn btn-perigo btn-sm"
                                    onclick="excluirRegistro('sumula', <?= $s['id_sumula'] ?>)">
                                <i class="fas fa-trash"></i>
                            </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

        <!-- ══════ AGENDA ══════ -->
        <div class="painel" id="painel-agenda">
            <div class="secao-card">
                <div class="secao-card-header">
                    <h3>Agenda Completa de Partidas</h3>
                    <button class="btn btn-primario btn-sm" onclick="abrirModalNovo('modal-partida')">
                        <i class="fas fa-plus"></i> Agendar
                    </button>
                </div>
                <?php if (empty($agenda)): ?>
                    <p style="padding:24px;opacity:.6;">Nenhuma partida agendada.</p>
                <?php else:
                    $por_dia = [];
                    foreach ($agenda as $p) $por_dia[$p['data_partida']][] = $p;
                    ksort($por_dia);
                    $dias_pt  = ['Sunday'=>'Dom','Monday'=>'Seg','Tuesday'=>'Ter','Wednesday'=>'Qua','Thursday'=>'Qui','Friday'=>'Sex','Saturday'=>'Sáb'];
                    $meses_pt = ['January'=>'Janeiro','February'=>'Fevereiro','March'=>'Março','April'=>'Abril','May'=>'Maio','June'=>'Junho','July'=>'Julho','August'=>'Agosto','September'=>'Setembro','October'=>'Outubro','November'=>'Novembro','December'=>'Dezembro'];
                ?>
                <div class="agenda-tabela-wrap">
                    <div class="agenda-colunas">
                        <?php foreach ($por_dia as $data => $partidas_dia):
                            $dt = new DateTime($data); ?>
                        <div class="agenda-coluna">
                            <div class="agenda-coluna-header">
                                <span class="agenda-col-diaSemana"><?= $dias_pt[$dt->format('l')] ?></span>
                                <span class="agenda-col-dia"><?= $dt->format('d') ?></span>
                                <span class="agenda-col-mes"><?= $meses_pt[$dt->format('F')] ?></span>
                            </div>
                            <div class="agenda-coluna-body">
                                <?php foreach ($partidas_dia as $p): ?>
                                <div class="agenda-slot">
                                    <span class="agenda-slot-hora"><?= fmtHora($p['hora_partida']) ?></span>
                                    <div class="agenda-slot-info">
                                        <strong><?= htmlspecialchars($p['time_a'] . ' vs ' . $p['time_b']) ?></strong>
                                        <span class="agenda-slot-mod"><?= htmlspecialchars($p['nome_modalidade']) ?></span>
                                        <?php if ($p['local_partida']): ?>
                                        <span class="agenda-slot-local"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($p['local_partida']) ?></span>
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

    </div><!-- /content -->
</div><!-- /main -->

<!-- ══════════════════════════════════════
     MODAIS CRUD
══════════════════════════════════════ -->

<!-- Modal Usuário -->
<div class="modal-overlay" id="modal-usuario">
  <div class="modal">
    <div class="modal-header">
      <h4 id="modal-usuario-titulo">Novo Usuário</h4>
      <button class="modal-close" onclick="fecharModal('modal-usuario')"><i class="fas fa-times"></i></button>
    </div>
    <div class="modal-body">
      <form action="/soee/src/backend/actions/salvar-usuario.php" method="POST" id="form-usuario">
        <input type="hidden" name="id_usuario" id="u-id" value="">
        <div class="form-grid">
          <div class="form-grupo span2">
            <label class="form-label">Nome Completo</label>
            <input class="form-input" type="text" name="nome_usuario" id="u-nome" placeholder="Ex.: João da Silva" required />
          </div>
          <div class="form-grupo">
            <label class="form-label">E-mail</label>
            <input class="form-input" type="email" name="email_usuario" id="u-email" placeholder="email@exemplo.com" required />
          </div>
          <div class="form-grupo">
            <label class="form-label">Senha <small style="opacity:.6">(deixe vazio para manter)</small></label>
            <input class="form-input" type="password" name="senha_usuario" id="u-senha" placeholder="••••••••" />
          </div>
          <div class="form-grupo">
            <label class="form-label">Turma</label>
            <select class="form-select" name="turma_id_turma" id="u-turma">
              <option value="">Selecionar turma…</option>
              <?php foreach ($turmas_select as $t): ?>
              <option value="<?= $t['id_turma'] ?>"><?= htmlspecialchars($t['nome_turma']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-grupo">
            <label class="form-label">Tipo</label>
            <select class="form-select" name="tipo_usuario" id="u-tipo">
              <option value="aluno">Aluno</option>
              <option value="adm_sala">Adm. de Sala</option>
              <option value="adm_geral">Adm. Geral</option>
              <option value="professor">Professor</option>
            </select>
          </div>
          <div class="form-grupo">
            <label class="form-label">Gênero</label>
            <select class="form-select" name="genero_usuario" id="u-genero">
              <option value="m">Masculino</option>
              <option value="f">Feminino</option>
              <option value="n">Não informado</option>
            </select>
          </div>
          <div class="form-grupo">
            <label class="form-label">Status</label>
            <select class="form-select" name="ativo_usuario" id="u-ativo">
              <option value="1">Ativo</option>
              <option value="0">Inativo</option>
            </select>
          </div>
        </div>
      </form>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secundario" onclick="fecharModal('modal-usuario')">Cancelar</button>
      <button class="btn btn-primario" onclick="document.getElementById('form-usuario').submit()"><i class="fas fa-save"></i> Salvar</button>
    </div>
  </div>
</div>

<!-- Modal Turma -->
<div class="modal-overlay" id="modal-turma">
  <div class="modal">
    <div class="modal-header">
      <h4>Nova Turma</h4>
      <button class="modal-close" onclick="fecharModal('modal-turma')"><i class="fas fa-times"></i></button>
    </div>
    <div class="modal-body">
      <form action="/soee/src/backend/actions/salvar-turma.php" method="POST" id="form-turma">
        <div class="form-grid">
          <div class="form-grupo span2">
            <label class="form-label">Nome da Turma</label>
            <input class="form-input" type="text" name="nome_turma" placeholder="Ex.: 3 MTEC" required />
          </div>
          <div class="form-grupo span2">
            <label class="form-label">Curso</label>
            <select class="form-select" name="curso_id_curso">
              <?php foreach ($cursos as $c): ?>
              <option value="<?= $c['id_curso'] ?>"><?= htmlspecialchars($c['nome_curso']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-grupo">
            <label class="form-label">Ano / Série</label>
            <input class="form-input" type="number" name="ano_serie_turma" placeholder="3" min="1" max="4" required />
          </div>
          <div class="form-grupo">
            <label class="form-label">Ano Letivo</label>
            <input class="form-input" type="number" name="ano_letivo_turma" placeholder="2026" required />
          </div>
          <div class="form-grupo">
            <label class="form-label">Período</label>
            <select class="form-select" name="periodo_turma">
              <option value="manha">Manhã</option>
              <option value="tarde">Tarde</option>
              <option value="noite">Noite</option>
            </select>
          </div>
        </div>
      </form>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secundario" onclick="fecharModal('modal-turma')">Cancelar</button>
      <button class="btn btn-primario" onclick="document.getElementById('form-turma').submit()"><i class="fas fa-save"></i> Salvar</button>
    </div>
  </div>
</div>

<!-- Modal Modalidade — CORRIGIDO: hidden id + action dinâmico -->
<div class="modal-overlay" id="modal-modalidade">
  <div class="modal">
    <div class="modal-header">
      <h4 id="modal-modalidade-titulo">Nova Modalidade</h4>
      <button class="modal-close" onclick="fecharModal('modal-modalidade')"><i class="fas fa-times"></i></button>
    </div>
    <div class="modal-body">
      <form action="/soee/src/backend/actions/salvar-modalidade.php" method="POST" id="form-modalidade">
        <input type="hidden" name="id_modalidade" id="mod-id" value="">
        <div class="form-grid">
          <div class="form-grupo span2">
            <label class="form-label">Nome da Modalidade</label>
            <input class="form-input" type="text" name="nome_modalidade" id="mod-nome" placeholder="Ex.: Futsal Masculino" required />
          </div>
          <div class="form-grupo">
            <label class="form-label">Tipo</label>
            <select class="form-select" name="tipo_modalidade" id="mod-tipo">
              <option value="quadra">Quadra</option>
              <option value="mesa">Mesa</option>
              <option value="campo">Campo</option>
              <option value="outro">Outro</option>
            </select>
          </div>
          <div class="form-grupo">
            <label class="form-label">Formato</label>
            <select class="form-select" name="formato_modalidade" id="mod-formato">
              <option value="mata_mata">Mata-Mata</option>
              <option value="grupos">Grupos</option>
              <option value="grupos_mata_mata">Grupos + Mata-Mata</option>
              <option value="todos_contra_todos">Todos contra Todos</option>
            </select>
          </div>
          <div class="form-grupo">
            <label class="form-label">Tipo de Participação</label>
            <select class="form-select" name="tipo_participacao" id="mod-participacao">
              <option value="solo">Solo</option>
              <option value="dupla">Dupla</option>
              <option value="trio">Trio</option>
              <option value="time">Time</option>
            </select>
          </div>
          <div class="form-grupo">
            <label class="form-label">Status</label>
            <select class="form-select" name="ativo_modalidade" id="mod-ativo">
              <option value="1">Ativa</option>
              <option value="0">Inativa</option>
            </select>
          </div>
          <div class="form-grupo">
            <label class="form-label">Mín. Jogadores</label>
            <input class="form-input" type="number" name="qtd_min_jogadores" id="mod-min" placeholder="5" min="1" required />
          </div>
          <div class="form-grupo">
            <label class="form-label">Máx. Jogadores</label>
            <input class="form-input" type="number" name="qtd_max_jogadores" id="mod-max" placeholder="10" min="1" required />
          </div>
          <div class="form-grupo span2">
            <label class="form-label">Descrição / Regulamento</label>
            <textarea class="form-textarea" name="descricao_modalidade" id="mod-desc" placeholder="Descreva as regras básicas…"></textarea>
          </div>
        </div>
      </form>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secundario" onclick="fecharModal('modal-modalidade')">Cancelar</button>
      <button class="btn btn-primario" onclick="document.getElementById('form-modalidade').submit()"><i class="fas fa-save"></i> Salvar</button>
    </div>
  </div>
</div>

<!-- Modal Edição — CORRIGIDO: campos com id + hidden id -->
<div class="modal-overlay" id="modal-edicao">
  <div class="modal">
    <div class="modal-header">
      <h4 id="modal-edicao-titulo">Nova Edição / Evento</h4>
      <button class="modal-close" onclick="fecharModal('modal-edicao')"><i class="fas fa-times"></i></button>
    </div>
    <div class="modal-body">
      <form action="/soee/src/backend/actions/salvar-edicao.php" method="POST" id="form-edicao">
        <input type="hidden" name="id_edicao" id="edicao-id" value="">
        <div class="form-grid">
          <div class="form-grupo span2">
            <label class="form-label">Nome do Evento</label>
            <input class="form-input" type="text" name="nome_edicao" id="edicao-nome" placeholder="Ex.: Interclasse ETEC JK 2026" required />
          </div>
          <div class="form-grupo">
            <label class="form-label">Ano</label>
            <input class="form-input" type="number" name="ano_edicao" id="edicao-ano" placeholder="2026" required />
          </div>
          <div class="form-grupo">
            <label class="form-label">Status</label>
            <select class="form-select" name="status_edicao" id="edicao-status">
              <option value="planejamento">Planejamento</option>
              <option value="inscricoes">Inscrições</option>
              <option value="em_andamento">Em Andamento</option>
              <option value="encerrado">Encerrado</option>
            </select>
          </div>
          <div class="form-grupo">
            <label class="form-label">Data de Início</label>
            <input class="form-input" type="date" name="data_inicio_edicao" id="edicao-inicio" required />
          </div>
          <div class="form-grupo">
            <label class="form-label">Data de Fim</label>
            <input class="form-input" type="date" name="data_fim_edicao" id="edicao-fim" />
          </div>
          <div class="form-grupo span2">
            <label class="form-label">Descrição</label>
            <textarea class="form-textarea" name="descricao_edicao" id="edicao-desc" placeholder="Detalhes do evento…"></textarea>
          </div>
        </div>
      </form>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secundario" onclick="fecharModal('modal-edicao')">Cancelar</button>
      <button class="btn btn-primario" onclick="document.getElementById('form-edicao').submit()"><i class="fas fa-save"></i> Salvar</button>
    </div>
  </div>
</div>

<!-- Modal Partida — CORRIGIDO: ids em todos campos + hidden id -->
<div class="modal-overlay" id="modal-partida">
  <div class="modal">
    <div class="modal-header">
      <h4 id="modal-partida-titulo">Agendar Partida</h4>
      <button class="modal-close" onclick="fecharModal('modal-partida')"><i class="fas fa-times"></i></button>
    </div>
    <div class="modal-body">
      <form action="/soee/src/backend/actions/salvar-partida.php" method="POST" id="form-partida">
        <input type="hidden" name="id_partida" id="partida-id" value="">
        <div class="form-grid">
          <div class="form-grupo span2">
            <label class="form-label">Edição / Modalidade</label>
            <select name="edicao_modalidade_id" id="partida-edicao-modal" class="form-select" required>
              <option value="">Selecionar…</option>
              <?php foreach ($edicoes_modal_select as $row): ?>
              <option value="<?= $row['id_edicao_modalidade'] ?>">
                <?= htmlspecialchars($row['label']) ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-grupo">
            <label class="form-label">Time A</label>
            <select class="form-select" name="turma_id_time_a" id="partida-time-a" required>
              <?php foreach ($turmas_select as $t): ?>
              <option value="<?= $t['id_turma'] ?>"><?= htmlspecialchars($t['nome_turma']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-grupo">
            <label class="form-label">Time B</label>
            <select class="form-select" name="turma_id_time_b" id="partida-time-b" required>
              <?php foreach ($turmas_select as $t): ?>
              <option value="<?= $t['id_turma'] ?>"><?= htmlspecialchars($t['nome_turma']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <!-- CORRIGIDO: data e hora com ids explícitos -->
          <div class="form-grupo">
            <label class="form-label">Data</label>
            <input class="form-input" type="date" name="data_partida" id="partida-data" required />
          </div>
          <div class="form-grupo">
            <label class="form-label">Hora</label>
            <input class="form-input" type="time" name="hora_partida" id="partida-hora" required />
          </div>
          <div class="form-grupo">
            <label class="form-label">Local</label>
            <input class="form-input" type="text" name="local_partida" id="partida-local" placeholder="Ex.: Quadra A" />
          </div>
          <div class="form-grupo">
            <label class="form-label">Fase</label>
            <select class="form-select" name="fase_partida" id="partida-fase" required>
              <option value="grupos">Grupos</option>
              <option value="oitavas">Oitavas</option>
              <option value="quartas">Quartas</option>
              <option value="semi">Semifinal</option>
              <option value="final">Final</option>
              <option value="terceiro_lugar">3º Lugar</option>
            </select>
          </div>
          <div class="form-grupo">
            <label class="form-label">Status</label>
            <select class="form-select" name="status_partida" id="partida-status">
              <option value="agendada">Agendada</option>
              <option value="realizada">Realizada</option>
              <option value="cancelada">Cancelada</option>
              <option value="wo">W.O.</option>
            </select>
          </div>
          <div class="form-grupo span2">
            <label class="form-label">Observações</label>
            <textarea class="form-textarea" name="observacoes_partida" id="partida-obs" placeholder="Notas adicionais…"></textarea>
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

<!-- Modal Resultado — CORRIGIDO: label "Partida" (era "action") + ids + hidden id -->
<div class="modal-overlay" id="modal-resultado">
  <div class="modal">
    <div class="modal-header">
      <h4 id="modal-resultado-titulo">Registrar Resultado</h4>
      <button class="modal-close" onclick="fecharModal('modal-resultado')"><i class="fas fa-times"></i></button>
    </div>
    <div class="modal-body">
      <form action="/soee/src/backend/actions/salvar-resultado.php" method="POST" id="form-resultado">
        <input type="hidden" name="id_resultado" id="resultado-id" value="">
        <div class="form-grid">
          <div class="form-grupo span2">
            <!-- CORRIGIDO: era label "action", agora "Partida" -->
            <label class="form-label">Partida</label>
            <select class="form-select" name="partida_id_partida" id="resultado-partida" required>
              <option value="">Selecionar…</option>
              <?php foreach ($partidas_select as $ps): ?>
              <option value="<?= $ps['id_partida'] ?>"><?= htmlspecialchars($ps['label']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-grupo">
            <label class="form-label">Placar Time A</label>
            <input class="form-input" type="number" name="placar_time_a" id="resultado-placar-a" placeholder="0" min="0" required />
          </div>
          <div class="form-grupo">
            <label class="form-label">Placar Time B</label>
            <input class="form-input" type="number" name="placar_time_b" id="resultado-placar-b" placeholder="0" min="0" required />
          </div>
          <div class="form-grupo span2">
            <label class="form-label">Vencedor (automático ou W.O.)</label>
            <select class="form-select" name="turma_id_vencedor" id="resultado-vencedor">
              <option value="">Calcular automaticamente</option>
              <?php foreach ($turmas_select as $t): ?>
              <option value="<?= $t['id_turma'] ?>"><?= htmlspecialchars($t['nome_turma']) ?> — W.O.</option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-grupo span2">
            <label class="form-label">Observações</label>
            <textarea class="form-textarea" name="observacoes_resultado" id="resultado-obs" placeholder="Cartões, faltas, ocorrências…"></textarea>
          </div>
        </div>
      </form>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secundario" onclick="fecharModal('modal-resultado')">Cancelar</button>
      <button class="btn btn-primario" onclick="document.getElementById('form-resultado').submit()"><i class="fas fa-save"></i> Salvar</button>
    </div>
  </div>
</div>

<!-- Modal Vincular Edição-Modalidade -->
<div class="modal-overlay" id="modal-edicao-modalidade">
  <div class="modal">
    <div class="modal-header">
      <h4>Vincular Modalidade à Edição</h4>
      <button class="modal-close" onclick="fecharModal('modal-edicao-modalidade')">
        <i class="fas fa-times"></i>
      </button>
    </div>
    <div class="modal-body">
      <form action="/soee/src/backend/actions/salvar-edicao-modalidade.php" method="POST" id="form-edicao-modalidade">
        <div class="form-grid">
          <div class="form-grupo">
            <label class="form-label">Edição</label>
            <select name="edicao_id_edicao" class="form-select" required>
              <?php foreach ($edicoes as $e): ?>
              <option value="<?= $e['id_edicao'] ?>"><?= htmlspecialchars($e['nome_edicao']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-grupo">
            <label class="form-label">Modalidade</label>
            <select name="modalidade_id_modalidade" class="form-select" required>
              <?php foreach ($modalidades as $m): ?>
              <option value="<?= $m['id_modalidade'] ?>"><?= htmlspecialchars($m['nome_modalidade']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-grupo">
            <label class="form-label">Início das Inscrições</label>
            <input type="date" name="data_inicio_inscricao" class="form-input" required>
          </div>
          <div class="form-grupo">
            <label class="form-label">Fim das Inscrições</label>
            <input type="date" name="data_fim_inscricao" class="form-input" required>
          </div>
        </div>
      </form>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secundario" onclick="fecharModal('modal-edicao-modalidade')">Cancelar</button>
      <button class="btn btn-primario" onclick="document.getElementById('form-edicao-modalidade').submit()">
        <i class="fas fa-save"></i> Salvar
      </button>
    </div>
  </div>
</div>

<!-- Modal Súmula — CORRIGIDO: input file com estilo próprio -->
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
            <label class="form-label">Arquivo da Súmula</label>
            <!-- CORRIGIDO: wrapper estilizado para o input file -->
            <div class="input-file-wrap" onclick="document.getElementById('arquivo-sumula-input').click()">
              <span class="input-file-icon"><i class="fas fa-file-upload"></i></span>
              <div class="input-file-label">
                <strong>Clique para selecionar o arquivo</strong>
                <span>PDF, JPG ou PNG — máx. 5 MB</span>
              </div>
              <input type="file"
                     id="arquivo-sumula-input"
                     name="arquivo_sumula"
                     accept=".pdf,.jpg,.jpeg,.png"
                     required
                     onchange="mostrarNomeArquivo(this)" />
            </div>
            <div id="nome-arquivo-selecionado"></div>
          </div>
        </div>
      </form>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secundario" onclick="fecharModal('modal-sumula')">Cancelar</button>
      <button class="btn btn-primario" onclick="document.getElementById('form-sumula').submit()">
        <i class="fas fa-upload"></i> Enviar
      </button>
    </div>
  </div>
</div>

<!-- Toast Container -->
<div class="toast-container" id="toast-container"></div>

<!-- ══════════════════════════════════════
     SCRIPTS
══════════════════════════════════════ -->
<script src="/soee/src/frontend/scripts/adm.js"></script>
<script src="/soee/src/frontend/scripts/admv2.js"></script>

<script src="/soee/src/frontend/scripts/responsive.js"></script>
<?php include __DIR__ . '/../includes/end.php';?>