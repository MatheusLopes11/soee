<?php
// dash-adm.php
// Simulação de sessão para protótipo — substituir pela autenticação real
session_start();
// $_SESSION['usuario'] = ['nome' => 'Henrique Batista', 'tipo' => 'adm_geral'];
$usuario_logado = $_SESSION['usuario']['nome'] ?? 'Administrador';
$tipo_usuario   = $_SESSION['usuario']['tipo'] ?? 'adm_geral';
?>
<!DOCTYPE html>
<html lang="pt-BR" data-theme="dark">
<head>
    <title>SOEE | Dashboard Administrativo</title>

    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <link rel="stylesheet" href="/soee/src/frontend/css/dash-adm.css">
    <link rel="icon" type="image/png" href="/soee/src/images/logo-soee.png">
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;800&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

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
    <a class="nav-item active" data-painel="overview" onclick="trocarPainel(this)">
      <i class="fas fa-chart-line"></i> Dashboard
    </a>
    <a class="nav-item" data-painel="agenda" onclick="trocarPainel(this)">
      <i class="fas fa-calendar-alt"></i> Agenda de Partidas
      <span class="nav-badge">5</span>
    </a>

    <div class="nav-group-label">Cadastros</div>
    <a class="nav-item" data-painel="usuarios" onclick="trocarPainel(this)">
      <i class="fas fa-users"></i> Usuários / Alunos
    </a>
    <a class="nav-item" data-painel="turmas" onclick="trocarPainel(this)">
      <i class="fas fa-door-open"></i> Turmas
    </a>
    <a class="nav-item" data-painel="modalidades" onclick="trocarPainel(this)">
      <i class="fas fa-futbol"></i> Modalidades
    </a>

    <div class="nav-group-label">Competições</div>
    <a class="nav-item" data-painel="edicoes" onclick="trocarPainel(this)">
      <i class="fas fa-trophy"></i> Edições / Eventos
    </a>
    <a class="nav-item" data-painel="partidas" onclick="trocarPainel(this)">
      <i class="fas fa-whistle"></i> Partidas
    </a>
    <a class="nav-item" data-painel="resultados" onclick="trocarPainel(this)">
      <i class="fas fa-flag-checkered"></i> Resultados
    </a>
    <a class="nav-item" data-painel="classificacao" onclick="trocarPainel(this)">
      <i class="fas fa-list-ol"></i> Classificação
    </a>

    <div class="nav-group-label">Documentos</div>
    <a class="nav-item" data-painel="sumulas" onclick="trocarPainel(this)">
      <i class="fas fa-file-alt"></i> Súmulas
      <span class="nav-badge">3</span>
    </a>
  </nav>

  <div class="sidebar-footer">
    <div class="user-card">
      <div class="user-avatar"><?= strtoupper(substr($usuario_logado, 0, 2)) ?></div>
      <div class="user-info">
        <strong><?= htmlspecialchars($usuario_logado) ?></strong>
        <span>Adm. Geral</span>
      </div>
    </div>
  </div>
</aside>

<!-- ══════════════════════════════════════
     MAIN
══════════════════════════════════════ -->
<div class="main">

  <!-- TOPBAR -->
  <header class="topbar">
    <button class="botao-icone" onclick="document.getElementById('sidebar').classList.toggle('open')" title="Menu">
      <i class="fas fa-bars"></i>
    </button>
    <div class="topbar-title" id="topbar-titulo">Dashboard</div>

    <div class="topbar-search">
      <i class="fas fa-search"></i>
      <input type="text" placeholder="Buscar aluno, turma, partida…" />
    </div>

    <button class="botao-icone notif-dot" title="Notificações"><i class="fas fa-bell"></i></button>
    <button class="botao-icone" onclick="alternarTema()" title="Tema"><i class="fas fa-moon" id="tema-icone"></i></button>
    <a href="/soee/src/backend/php/pages/inicio.php" class="botao-icone" title="Voltar ao site"><i class="fas fa-home"></i></a>
  </header>

  <!-- CONTEÚDO -->
  <div class="content">

    <!-- ══════ OVERVIEW ══════ -->
    <div class="painel active" id="painel-overview">
      <div class="kpi-grid">
        <div class="kpi-card azul">
          <div class="kpi-icon"><i class="fas fa-users"></i></div>
          <div class="kpi-num" id="kpi-alunos">247</div>
          <div class="kpi-label">Alunos Cadastrados</div>
          <div class="kpi-trend up"><i class="fas fa-arrow-up"></i> 12%</div>
        </div>
        <div class="kpi-card laranja">
          <div class="kpi-icon"><i class="fas fa-futbol"></i></div>
          <div class="kpi-num" id="kpi-partidas">38</div>
          <div class="kpi-label">Partidas Agendadas</div>
          <div class="kpi-trend up"><i class="fas fa-arrow-up"></i> 5%</div>
        </div>
        <div class="kpi-card verde">
          <div class="kpi-icon"><i class="fas fa-check-circle"></i></div>
          <div class="kpi-num" id="kpi-realizadas">21</div>
          <div class="kpi-label">Partidas Realizadas</div>
          <div class="kpi-trend up"><i class="fas fa-arrow-up"></i> 8%</div>
        </div>
        <div class="kpi-card roxo">
          <div class="kpi-icon"><i class="fas fa-layer-group"></i></div>
          <div class="kpi-num" id="kpi-modalidades">6</div>
          <div class="kpi-label">Modalidades Ativas</div>
          <div class="kpi-trend down"><i class="fas fa-minus"></i> 0%</div>
        </div>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">
        <!-- Gráfico por Modalidade -->
        <div class="secao-card">
          <div class="secao-card-header">
            <h3>Inscrições por Modalidade</h3>
            <span class="secao-tag-mini">2025</span>
          </div>
          <div class="chart-bar-wrap">
            <div class="chart-bar-row">
              <span class="chart-bar-label">Futsal</span>
              <div class="chart-bar-track"><div class="chart-bar-fill laranja" style="width:88%"></div></div>
              <span class="chart-bar-num">88</span>
            </div>
            <div class="chart-bar-row">
              <span class="chart-bar-label">Vôlei</span>
              <div class="chart-bar-track"><div class="chart-bar-fill" style="width:72%"></div></div>
              <span class="chart-bar-num">72</span>
            </div>
            <div class="chart-bar-row">
              <span class="chart-bar-label">Basquete</span>
              <div class="chart-bar-track"><div class="chart-bar-fill verde" style="width:54%"></div></div>
              <span class="chart-bar-num">54</span>
            </div>
            <div class="chart-bar-row">
              <span class="chart-bar-label">Tênis de Mesa</span>
              <div class="chart-bar-track"><div class="chart-bar-fill" style="width:40%"></div></div>
              <span class="chart-bar-num">40</span>
            </div>
            <div class="chart-bar-row">
              <span class="chart-bar-label">Xadrez</span>
              <div class="chart-bar-track"><div class="chart-bar-fill laranja" style="width:28%"></div></div>
              <span class="chart-bar-num">28</span>
            </div>
            <div class="chart-bar-row">
              <span class="chart-bar-label">Atletismo</span>
              <div class="chart-bar-track"><div class="chart-bar-fill verde" style="width:18%"></div></div>
              <span class="chart-bar-num">18</span>
            </div>
          </div>
        </div>

        <!-- Próximas Partidas -->
        <div class="secao-card">
          <div class="secao-card-header">
            <h3>Próximas Partidas</h3>
            <button class="btn btn-primario btn-sm" onclick="trocarPainelById('agenda')"><i class="fas fa-arrow-right"></i> Ver todas</button>
          </div>
          <div class="agenda-lista">
            <div class="agenda-item">
              <div class="agenda-data"><strong>18</strong><span>Ago</span></div>
              <div class="agenda-info">
                <strong>3 MTEC vs 2 BINF — Futsal ♂</strong>
                <span><i class="fas fa-map-marker-alt"></i> Quadra A</span>
              </div>
              <span class="agenda-hora">08:00</span>
            </div>
            <div class="agenda-item">
              <div class="agenda-data"><strong>18</strong><span>Ago</span></div>
              <div class="agenda-info">
                <strong>1 MLOG vs 1 MADM — Vôlei ♀</strong>
                <span><i class="fas fa-map-marker-alt"></i> Quadra B</span>
              </div>
              <span class="agenda-hora">10:00</span>
            </div>
            <div class="agenda-item">
              <div class="agenda-data"><strong>19</strong><span>Ago</span></div>
              <div class="agenda-info">
                <strong>2 MTEC vs 3 BINF — Basquete ♂</strong>
                <span><i class="fas fa-map-marker-alt"></i> Quadra A</span>
              </div>
              <span class="agenda-hora">14:00</span>
            </div>
            <div class="agenda-item">
              <div class="agenda-data"><strong>20</strong><span>Ago</span></div>
              <div class="agenda-info">
                <strong>Final — Tênis de Mesa</strong>
                <span><i class="fas fa-map-marker-alt"></i> Sala de Jogos</span>
              </div>
              <span class="agenda-hora">16:00</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Súmulas Pendentes -->
      <div class="secao-card" style="margin-top:24px;">
        <div class="secao-card-header">
          <h3>Súmulas Pendentes de Validação</h3>
          <span class="secao-tag-mini">3 pendentes</span>
        </div>
        <div class="tabela-wrap">
          <table>
            <thead>
              <tr>
                <th>Partida</th><th>Enviado por</th><th>Data Envio</th><th>Arquivo</th><th>Status</th><th>Ações</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>3 MTEC vs 2 BINF — Futsal</td>
                <td>Carlos Valentim</td>
                <td>14/08/2025</td>
                <td><a href="#" style="color:var(--azul-secundario)"><i class="fas fa-file-pdf"></i> sumula_01.pdf</a></td>
                <td><span class="badge-status pendente">Pendente</span></td>
                <td class="td-acoes">
                  <button class="btn btn-ok btn-sm" onclick="toast('Súmula validada!','sucesso')"><i class="fas fa-check"></i></button>
                  <button class="btn btn-perigo btn-sm" onclick="toast('Súmula rejeitada.','erro')"><i class="fas fa-times"></i></button>
                </td>
              </tr>
              <tr>
                <td>1 MLOG vs 1 MADM — Vôlei</td>
                <td>Isabelly Barbosa</td>
                <td>15/08/2025</td>
                <td><a href="#" style="color:var(--azul-secundario)"><i class="fas fa-file-pdf"></i> sumula_02.pdf</a></td>
                <td><span class="badge-status pendente">Pendente</span></td>
                <td class="td-acoes">
                  <button class="btn btn-ok btn-sm" onclick="toast('Súmula validada!','sucesso')"><i class="fas fa-check"></i></button>
                  <button class="btn btn-perigo btn-sm" onclick="toast('Súmula rejeitada.','erro')"><i class="fas fa-times"></i></button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ══════ USUÁRIOS ══════ -->
    <div class="painel" id="painel-usuarios">
      <div class="secao-card">
        <div class="secao-card-header">
          <h3>Usuários &amp; Alunos</h3>
          <span class="secao-tag-mini">CRUD</span>
          <button class="btn btn-primario btn-sm" onclick="abrirModal('modal-usuario')">
            <i class="fas fa-plus"></i> Novo Usuário
          </button>
        </div>
        <div class="tabela-wrap">
          <table id="tabela-usuarios">
            <thead>
              <tr><th>#</th><th>Nome</th><th>E-mail</th><th>Turma</th><th>Tipo</th><th>Gênero</th><th>Status</th><th>Ações</th></tr>
            </thead>
            <tbody>
              <tr data-id="1">
                <td>1</td>
                <td>Henrique Batista Orlovas</td>
                <td>batista.henriqui@gmail.com</td>
                <td>3 MTEC</td>
                <td>Adm. Geral</td>
                <td>M</td>
                <td><span class="badge-status ativo">Ativo</span></td>
                <td class="td-acoes">
                  <button class="btn btn-secundario btn-sm" onclick="editarUsuario(1)"><i class="fas fa-edit"></i></button>
                  <button class="btn btn-perigo btn-sm" onclick="excluir('usuário', 1)"><i class="fas fa-trash"></i></button>
                </td>
              </tr>
              <tr data-id="2">
                <td>2</td>
                <td>Carlos Henrique Valentim</td>
                <td>rikcar22@gmail.com</td>
                <td>3 MTEC</td>
                <td>Adm. Geral</td>
                <td>M</td>
                <td><span class="badge-status ativo">Ativo</span></td>
                <td class="td-acoes">
                  <button class="btn btn-secundario btn-sm" onclick="editarUsuario(2)"><i class="fas fa-edit"></i></button>
                  <button class="btn btn-perigo btn-sm" onclick="excluir('usuário', 2)"><i class="fas fa-trash"></i></button>
                </td>
              </tr>
              <tr data-id="3">
                <td>3</td>
                <td>Miguel Lopes Aquinez da Silva</td>
                <td>miguelaquinez17@gmail.com</td>
                <td>3 MTEC</td>
                <td>Adm. Geral</td>
                <td>M</td>
                <td><span class="badge-status ativo">Ativo</span></td>
                <td class="td-acoes">
                  <button class="btn btn-secundario btn-sm" onclick="editarUsuario(3)"><i class="fas fa-edit"></i></button>
                  <button class="btn btn-perigo btn-sm" onclick="excluir('usuário', 3)"><i class="fas fa-trash"></i></button>
                </td>
              </tr>
              <tr data-id="4">
                <td>4</td>
                <td>Matheus Ferreira Lopes</td>
                <td>matheusflopes167@gmail.com</td>
                <td>3 MTEC</td>
                <td>Adm. Geral</td>
                <td>M</td>
                <td><span class="badge-status ativo">Ativo</span></td>
                <td class="td-acoes">
                  <button class="btn btn-secundario btn-sm" onclick="editarUsuario(4)"><i class="fas fa-edit"></i></button>
                  <button class="btn btn-perigo btn-sm" onclick="excluir('usuário', 4)"><i class="fas fa-trash"></i></button>
                </td>
              </tr>
              <tr data-id="5">
                <td>5</td>
                <td>Isabelly Barbosa Santos</td>
                <td>isabellybarbosantos1357@gmail.com</td>
                <td>3 MTEC</td>
                <td>Adm. Geral</td>
                <td>F</td>
                <td><span class="badge-status ativo">Ativo</span></td>
                <td class="td-acoes">
                  <button class="btn btn-secundario btn-sm" onclick="editarUsuario(5)"><i class="fas fa-edit"></i></button>
                  <button class="btn btn-perigo btn-sm" onclick="excluir('usuário', 5)"><i class="fas fa-trash"></i></button>
                </td>
              </tr>
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
          <span class="secao-tag-mini">CRUD</span>
          <button class="btn btn-primario btn-sm" onclick="abrirModal('modal-turma')">
            <i class="fas fa-plus"></i> Nova Turma
          </button>
        </div>
        <div class="tabela-wrap">
          <table>
            <thead>
              <tr><th>#</th><th>Nome</th><th>Curso</th><th>Série</th><th>Ano Letivo</th><th>Período</th><th>Ações</th></tr>
            </thead>
            <tbody>
              <tr>
                <td>1</td><td>3 MTEC</td><td>Técnico em Informática</td><td>3º</td><td>2026</td>
                <td><span class="badge-status ativo">Manhã</span></td>
                <td class="td-acoes">
                  <button class="btn btn-secundario btn-sm" onclick="abrirModal('modal-turma')"><i class="fas fa-edit"></i></button>
                  <button class="btn btn-perigo btn-sm" onclick="excluir('turma', 1)"><i class="fas fa-trash"></i></button>
                </td>
              </tr>
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
          <span class="secao-tag-mini">CRUD</span>
          <button class="btn btn-primario btn-sm" onclick="abrirModal('modal-modalidade')">
            <i class="fas fa-plus"></i> Nova Modalidade
          </button>
        </div>
        <div class="tabela-wrap">
          <table>
            <thead>
              <tr><th>#</th><th>Nome</th><th>Tipo</th><th>Formato</th><th>Participação</th><th>Min/Max Jogadores</th><th>Status</th><th>Ações</th></tr>
            </thead>
            <tbody>
              <tr><td>1</td><td>Futsal Masculino</td><td>Quadra</td><td>Grupos + Mata-Mata</td><td>Time</td><td>5 / 10</td><td><span class="badge-status ativo">Ativo</span></td><td class="td-acoes"><button class="btn btn-secundario btn-sm" onclick="abrirModal('modal-modalidade')"><i class="fas fa-edit"></i></button><button class="btn btn-perigo btn-sm" onclick="excluir('modalidade',1)"><i class="fas fa-trash"></i></button></td></tr>
              <tr><td>2</td><td>Vôlei Feminino</td><td>Quadra</td><td>Grupos + Mata-Mata</td><td>Time</td><td>6 / 12</td><td><span class="badge-status ativo">Ativo</span></td><td class="td-acoes"><button class="btn btn-secundario btn-sm"><i class="fas fa-edit"></i></button><button class="btn btn-perigo btn-sm"><i class="fas fa-trash"></i></button></td></tr>
              <tr><td>3</td><td>Basquete Misto</td><td>Quadra</td><td>Todos contra Todos</td><td>Time</td><td>5 / 10</td><td><span class="badge-status ativo">Ativo</span></td><td class="td-acoes"><button class="btn btn-secundario btn-sm"><i class="fas fa-edit"></i></button><button class="btn btn-perigo btn-sm"><i class="fas fa-trash"></i></button></td></tr>
              <tr><td>4</td><td>Tênis de Mesa</td><td>Mesa</td><td>Mata-Mata</td><td>Solo</td><td>1 / 1</td><td><span class="badge-status ativo">Ativo</span></td><td class="td-acoes"><button class="btn btn-secundario btn-sm"><i class="fas fa-edit"></i></button><button class="btn btn-perigo btn-sm"><i class="fas fa-trash"></i></button></td></tr>
              <tr><td>5</td><td>Xadrez</td><td>Mesa</td><td>Todos contra Todos</td><td>Solo</td><td>1 / 1</td><td><span class="badge-status ativo">Ativo</span></td><td class="td-acoes"><button class="btn btn-secundario btn-sm"><i class="fas fa-edit"></i></button><button class="btn btn-perigo btn-sm"><i class="fas fa-trash"></i></button></td></tr>
              <tr><td>6</td><td>Atletismo</td><td>Campo</td><td>Todos contra Todos</td><td>Solo</td><td>1 / 1</td><td><span class="badge-status inativo">Inativo</span></td><td class="td-acoes"><button class="btn btn-secundario btn-sm"><i class="fas fa-edit"></i></button><button class="btn btn-perigo btn-sm"><i class="fas fa-trash"></i></button></td></tr>
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
          <span class="secao-tag-mini">CRUD</span>
          <button class="btn btn-primario btn-sm" onclick="abrirModal('modal-edicao')">
            <i class="fas fa-plus"></i> Nova Edição
          </button>
        </div>
        <div class="tabela-wrap">
          <table>
            <thead>
              <tr><th>#</th><th>Nome do Evento</th><th>Ano</th><th>Início</th><th>Fim</th><th>Status</th><th>Ações</th></tr>
            </thead>
            <tbody>
              <tr>
                <td>1</td>
                <td>Interclasse ETEC JK 2025</td>
                <td>2025</td>
                <td>10/08/2025</td>
                <td>30/08/2025</td>
                <td><span class="badge-status ativo">Em Andamento</span></td>
                <td class="td-acoes">
                  <button class="btn btn-secundario btn-sm" onclick="abrirModal('modal-edicao')"><i class="fas fa-edit"></i></button>
                  <button class="btn btn-perigo btn-sm" onclick="excluir('edição',1)"><i class="fas fa-trash"></i></button>
                </td>
              </tr>
              <tr>
                <td>2</td>
                <td>Interclasse ETEC JK 2024</td>
                <td>2024</td>
                <td>05/08/2024</td>
                <td>25/08/2024</td>
                <td><span class="badge-status encerrado">Encerrado</span></td>
                <td class="td-acoes">
                  <button class="btn btn-secundario btn-sm"><i class="fas fa-edit"></i></button>
                  <button class="btn btn-perigo btn-sm"><i class="fas fa-trash"></i></button>
                </td>
              </tr>
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
          <span class="secao-tag-mini">CRUD</span>
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
              <tr>
                <td>1</td><td>Futsal ♂</td><td>3 MTEC</td><td>2 BINF</td><td>18/08</td><td>08:00</td><td>Quadra A</td>
                <td><span class="badge-status pendente">Grupos</span></td>
                <td><span class="badge-status ativo">Agendada</span></td>
                <td class="td-acoes">
                  <button class="btn btn-secundario btn-sm" onclick="abrirModal('modal-partida')"><i class="fas fa-edit"></i></button>
                  <button class="btn btn-perigo btn-sm" onclick="excluir('partida',1)"><i class="fas fa-trash"></i></button>
                </td>
              </tr>
              <tr>
                <td>2</td><td>Vôlei ♀</td><td>1 MLOG</td><td>1 MADM</td><td>18/08</td><td>10:00</td><td>Quadra B</td>
                <td><span class="badge-status pendente">Grupos</span></td>
                <td><span class="badge-status ativo">Agendada</span></td>
                <td class="td-acoes">
                  <button class="btn btn-secundario btn-sm"><i class="fas fa-edit"></i></button>
                  <button class="btn btn-perigo btn-sm"><i class="fas fa-trash"></i></button>
                </td>
              </tr>
              <tr>
                <td>3</td><td>Futsal ♂</td><td>1 MTEC</td><td>3 BINF</td><td>14/08</td><td>08:00</td><td>Quadra A</td>
                <td><span class="badge-status pendente">Grupos</span></td>
                <td><span class="badge-status verde">Realizada</span></td>
                <td class="td-acoes">
                  <button class="btn btn-secundario btn-sm"><i class="fas fa-edit"></i></button>
                  <button class="btn btn-perigo btn-sm"><i class="fas fa-trash"></i></button>
                </td>
              </tr>
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
          <span class="secao-tag-mini">CRUD</span>
          <button class="btn btn-primario btn-sm" onclick="abrirModal('modal-resultado')">
            <i class="fas fa-plus"></i> Registrar Resultado
          </button>
        </div>
        <div class="tabela-wrap">
          <table>
            <thead>
              <tr><th>#</th><th>Partida</th><th>Placar Time A</th><th>Placar Time B</th><th>Vencedor</th><th>Ações</th></tr>
            </thead>
            <tbody>
              <tr>
                <td>1</td>
                <td>Futsal — 1 MTEC vs 3 BINF</td>
                <td style="font-weight:800;color:var(--verde-ok);">3</td>
                <td>1</td>
                <td><strong>1 MTEC</strong></td>
                <td class="td-acoes">
                  <button class="btn btn-secundario btn-sm" onclick="abrirModal('modal-resultado')"><i class="fas fa-edit"></i></button>
                  <button class="btn btn-perigo btn-sm" onclick="excluir('resultado',1)"><i class="fas fa-trash"></i></button>
                </td>
              </tr>
              <tr>
                <td>2</td>
                <td>Vôlei — 2 MTEC vs 1 MLOG</td>
                <td>1</td>
                <td style="font-weight:800;color:var(--verde-ok);">2</td>
                <td><strong>1 MLOG</strong></td>
                <td class="td-acoes">
                  <button class="btn btn-secundario btn-sm"><i class="fas fa-edit"></i></button>
                  <button class="btn btn-perigo btn-sm"><i class="fas fa-trash"></i></button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ══════ CLASSIFICAÇÃO ══════ -->
    <div class="painel" id="painel-classificacao">
      <div class="secao-card">
        <div class="secao-card-header">
          <h3>Tabela de Classificação — Futsal Masculino 2025</h3>
          <span class="secao-tag-mini">Grupos</span>
        </div>
        <div class="tabela-wrap">
          <table>
            <thead>
              <tr><th>Pos.</th><th>Turma</th><th>J</th><th>V</th><th>E</th><th>D</th><th>GP</th><th>GC</th><th>SG</th><th>Pts</th></tr>
            </thead>
            <tbody>
              <tr style="background:rgba(255,77,18,0.06);">
                <td><strong style="color:var(--laranja-destaque)">1º</strong></td>
                <td><strong>3 MTEC</strong></td>
                <td>3</td><td>3</td><td>0</td><td>0</td><td>10</td><td>3</td><td>+7</td>
                <td><strong style="color:var(--laranja-destaque)">9</strong></td>
              </tr>
              <tr>
                <td><strong>2º</strong></td>
                <td>1 MLOG</td>
                <td>3</td><td>2</td><td>0</td><td>1</td><td>7</td><td>5</td><td>+2</td>
                <td><strong>6</strong></td>
              </tr>
              <tr>
                <td><strong>3º</strong></td>
                <td>2 BINF</td>
                <td>3</td><td>1</td><td>0</td><td>2</td><td>4</td><td>8</td><td>-4</td>
                <td><strong>3</strong></td>
              </tr>
              <tr>
                <td><strong>4º</strong></td>
                <td>1 MADM</td>
                <td>3</td><td>0</td><td>0</td><td>3</td><td>2</td><td>7</td><td>-5</td>
                <td><strong>0</strong></td>
              </tr>
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
          <span class="secao-tag-mini">CRUD</span>
          <button class="btn btn-primario btn-sm" onclick="abrirModal('modal-sumula')">
            <i class="fas fa-upload"></i> Enviar Súmula
          </button>
        </div>
        <div class="tabela-wrap">
          <table>
            <thead>
              <tr><th>#</th><th>Partida</th><th>Enviado por</th><th>Arquivo</th><th>Tipo</th><th>Data Envio</th><th>Status</th><th>Ações</th></tr>
            </thead>
            <tbody>
              <tr>
                <td>1</td>
                <td>Futsal — 3 MTEC vs 2 BINF</td>
                <td>Carlos Valentim</td>
                <td><a href="#" style="color:var(--azul-secundario)"><i class="fas fa-file-pdf"></i> sumula_ft01.pdf</a></td>
                <td>PDF</td>
                <td>14/08/2025 09:32</td>
                <td><span class="badge-status pendente">Pendente</span></td>
                <td class="td-acoes">
                  <button class="btn btn-ok btn-sm" onclick="toast('Súmula validada com sucesso!','sucesso')"><i class="fas fa-check"></i> Validar</button>
                  <button class="btn btn-perigo btn-sm" onclick="toast('Súmula rejeitada.','erro')"><i class="fas fa-times"></i></button>
                </td>
              </tr>
              <tr>
                <td>2</td>
                <td>Vôlei — 1 MLOG vs 1 MADM</td>
                <td>Isabelly Barbosa</td>
                <td><a href="#" style="color:var(--azul-secundario)"><i class="fas fa-file-image"></i> sumula_vl01.jpg</a></td>
                <td>JPEG</td>
                <td>15/08/2025 11:10</td>
                <td><span class="badge-status pendente">Pendente</span></td>
                <td class="td-acoes">
                  <button class="btn btn-ok btn-sm" onclick="toast('Súmula validada com sucesso!','sucesso')"><i class="fas fa-check"></i> Validar</button>
                  <button class="btn btn-perigo btn-sm" onclick="toast('Súmula rejeitada.','erro')"><i class="fas fa-times"></i></button>
                </td>
              </tr>
              <tr>
                <td>3</td>
                <td>Futsal — 1 MTEC vs 3 BINF</td>
                <td>Matheus Lopes</td>
                <td><a href="#" style="color:var(--azul-secundario)"><i class="fas fa-file-pdf"></i> sumula_ft02.pdf</a></td>
                <td>PDF</td>
                <td>14/08/2025 08:55</td>
                <td><span class="badge-status ativo">Validada</span></td>
                <td class="td-acoes">
                  <button class="btn btn-perigo btn-sm" onclick="excluir('súmula',3)"><i class="fas fa-trash"></i></button>
                </td>
              </tr>
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
          <span class="secao-tag-mini">Interclasse 2025</span>
          <button class="btn btn-primario btn-sm" onclick="abrirModal('modal-partida')">
            <i class="fas fa-plus"></i> Agendar
          </button>
        </div>
        <div class="agenda-lista">
          <div class="agenda-item">
            <div class="agenda-data"><strong>18</strong><span>Ago</span></div>
            <div class="agenda-info">
              <strong>3 MTEC vs 2 BINF — Futsal Masculino (Grupos)</strong>
              <span><i class="fas fa-map-marker-alt"></i> Quadra A &nbsp;|&nbsp; <i class="fas fa-user"></i> Árbitro: Prof. Silva</span>
            </div>
            <span class="agenda-hora">08:00</span>
          </div>
          <div class="agenda-item">
            <div class="agenda-data"><strong>18</strong><span>Ago</span></div>
            <div class="agenda-info">
              <strong>1 MLOG vs 1 MADM — Vôlei Feminino (Grupos)</strong>
              <span><i class="fas fa-map-marker-alt"></i> Quadra B</span>
            </div>
            <span class="agenda-hora">10:00</span>
          </div>
          <div class="agenda-item">
            <div class="agenda-data"><strong>19</strong><span>Ago</span></div>
            <div class="agenda-info">
              <strong>2 MTEC vs 3 BINF — Basquete Misto (Grupos)</strong>
              <span><i class="fas fa-map-marker-alt"></i> Quadra A</span>
            </div>
            <span class="agenda-hora">14:00</span>
          </div>
          <div class="agenda-item">
            <div class="agenda-data"><strong>20</strong><span>Ago</span></div>
            <div class="agenda-info">
              <strong>Semifinal — Futsal Masculino (Jogo 1)</strong>
              <span><i class="fas fa-map-marker-alt"></i> Quadra A</span>
            </div>
            <span class="agenda-hora">08:00</span>
          </div>
          <div class="agenda-item">
            <div class="agenda-data"><strong>20</strong><span>Ago</span></div>
            <div class="agenda-info">
              <strong>Final — Tênis de Mesa</strong>
              <span><i class="fas fa-map-marker-alt"></i> Sala de Jogos</span>
            </div>
            <span class="agenda-hora">16:00</span>
          </div>
        </div>
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
      <div class="form-grid">
        <div class="form-grupo span2">
          <label class="form-label">Nome Completo</label>
          <input class="form-input" type="text" id="u-nome" placeholder="Ex.: João da Silva" />
        </div>
        <div class="form-grupo">
          <label class="form-label">E-mail</label>
          <input class="form-input" type="email" id="u-email" placeholder="email@exemplo.com" />
        </div>
        <div class="form-grupo">
          <label class="form-label">Senha</label>
          <input class="form-input" type="password" id="u-senha" placeholder="••••••••" />
        </div>
        <div class="form-grupo">
          <label class="form-label">Turma</label>
          <select class="form-select" id="u-turma">
            <option value="">Selecionar turma…</option>
            <option value="1">3 MTEC — Manhã</option>
          </select>
        </div>
        <div class="form-grupo">
          <label class="form-label">Tipo</label>
          <select class="form-select" id="u-tipo">
            <option value="aluno">Aluno</option>
            <option value="adm_sala">Adm. de Sala</option>
            <option value="adm_geral">Adm. Geral</option>
            <option value="professor">Professor</option>
          </select>
        </div>
        <div class="form-grupo">
          <label class="form-label">Gênero</label>
          <select class="form-select" id="u-genero">
            <option value="m">Masculino</option>
            <option value="f">Feminino</option>
          </select>
        </div>
        <div class="form-grupo">
          <label class="form-label">Status</label>
          <select class="form-select" id="u-ativo">
            <option value="1">Ativo</option>
            <option value="0">Inativo</option>
          </select>
        </div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secundario" onclick="fecharModal('modal-usuario')">Cancelar</button>
      <button class="btn btn-primario" onclick="salvarUsuario()"><i class="fas fa-save"></i> Salvar</button>
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
      <div class="form-grid">
        <div class="form-grupo span2">
          <label class="form-label">Nome da Turma</label>
          <input class="form-input" type="text" placeholder="Ex.: 3 MTEC" />
        </div>
        <div class="form-grupo span2">
          <label class="form-label">Curso</label>
          <select class="form-select">
            <option>Técnico em Informática — MTEC</option>
          </select>
        </div>
        <div class="form-grupo">
          <label class="form-label">Ano / Série</label>
          <input class="form-input" type="number" placeholder="3" min="1" max="4" />
        </div>
        <div class="form-grupo">
          <label class="form-label">Ano Letivo</label>
          <input class="form-input" type="number" placeholder="2026" />
        </div>
        <div class="form-grupo">
          <label class="form-label">Período</label>
          <select class="form-select">
            <option value="manha">Manhã</option>
            <option value="tarde">Tarde</option>
            <option value="noite">Noite</option>
          </select>
        </div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secundario" onclick="fecharModal('modal-turma')">Cancelar</button>
      <button class="btn btn-primario" onclick="fecharModal('modal-turma');toast('Turma salva!','sucesso')"><i class="fas fa-save"></i> Salvar</button>
    </div>
  </div>
</div>

<!-- Modal Modalidade -->
<div class="modal-overlay" id="modal-modalidade">
  <div class="modal">
    <div class="modal-header">
      <h4>Nova Modalidade</h4>
      <button class="modal-close" onclick="fecharModal('modal-modalidade')"><i class="fas fa-times"></i></button>
    </div>
    <div class="modal-body">
      <div class="form-grid">
        <div class="form-grupo span2">
          <label class="form-label">Nome da Modalidade</label>
          <input class="form-input" type="text" placeholder="Ex.: Futsal Masculino" />
        </div>
        <div class="form-grupo">
          <label class="form-label">Tipo</label>
          <select class="form-select">
            <option>quadra</option><option>mesa</option><option>campo</option><option>outro</option>
          </select>
        </div>
        <div class="form-grupo">
          <label class="form-label">Formato</label>
          <select class="form-select">
            <option>mata_mata</option><option>grupos</option><option>grupos_mata_mata</option><option>todos_contra_todos</option>
          </select>
        </div>
        <div class="form-grupo">
          <label class="form-label">Tipo de Participação</label>
          <select class="form-select">
            <option>solo</option><option>dupla</option><option>trio</option><option>time</option>
          </select>
        </div>
        <div class="form-grupo">
          <label class="form-label">Mín. Jogadores</label>
          <input class="form-input" type="number" placeholder="5" />
        </div>
        <div class="form-grupo">
          <label class="form-label">Máx. Jogadores</label>
          <input class="form-input" type="number" placeholder="10" />
        </div>
        <div class="form-grupo span2">
          <label class="form-label">Descrição / Regulamento</label>
          <textarea class="form-textarea" placeholder="Descreva as regras básicas…"></textarea>
        </div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secundario" onclick="fecharModal('modal-modalidade')">Cancelar</button>
      <button class="btn btn-primario" onclick="fecharModal('modal-modalidade');toast('Modalidade salva!','sucesso')"><i class="fas fa-save"></i> Salvar</button>
    </div>
  </div>
</div>

<!-- Modal Edição -->
<div class="modal-overlay" id="modal-edicao">
  <div class="modal">
    <div class="modal-header">
      <h4>Nova Edição / Evento</h4>
      <button class="modal-close" onclick="fecharModal('modal-edicao')"><i class="fas fa-times"></i></button>
    </div>
    <div class="modal-body">
      <div class="form-grid">
        <div class="form-grupo span2">
          <label class="form-label">Nome do Evento</label>
          <input class="form-input" type="text" placeholder="Ex.: Interclasse ETEC JK 2026" />
        </div>
        <div class="form-grupo">
          <label class="form-label">Ano</label>
          <input class="form-input" type="number" placeholder="2026" />
        </div>
        <div class="form-grupo">
          <label class="form-label">Status</label>
          <select class="form-select">
            <option>planejamento</option><option>inscricoes</option><option>em_andamento</option><option>encerrado</option>
          </select>
        </div>
        <div class="form-grupo">
          <label class="form-label">Data de Início</label>
          <input class="form-input" type="date" />
        </div>
        <div class="form-grupo">
          <label class="form-label">Data de Fim</label>
          <input class="form-input" type="date" />
        </div>
        <div class="form-grupo span2">
          <label class="form-label">Descrição</label>
          <textarea class="form-textarea" placeholder="Detalhes do evento…"></textarea>
        </div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secundario" onclick="fecharModal('modal-edicao')">Cancelar</button>
      <button class="btn btn-primario" onclick="fecharModal('modal-edicao');toast('Edição salva!','sucesso')"><i class="fas fa-save"></i> Salvar</button>
    </div>
  </div>
</div>

<!-- Modal Partida -->
<div class="modal-overlay" id="modal-partida">
  <div class="modal">
    <div class="modal-header">
      <h4>Agendar Partida</h4>
      <button class="modal-close" onclick="fecharModal('modal-partida')"><i class="fas fa-times"></i></button>
    </div>
    <div class="modal-body">
      <div class="form-grid">
        <div class="form-grupo span2">
          <label class="form-label">Edição / Modalidade</label>
          <select class="form-select">
            <option>Interclasse 2025 — Futsal Masculino</option>
            <option>Interclasse 2025 — Vôlei Feminino</option>
            <option>Interclasse 2025 — Basquete Misto</option>
          </select>
        </div>
        <div class="form-grupo">
          <label class="form-label">Time A</label>
          <select class="form-select"><option>3 MTEC</option><option>2 BINF</option><option>1 MLOG</option><option>1 MADM</option></select>
        </div>
        <div class="form-grupo">
          <label class="form-label">Time B</label>
          <select class="form-select"><option>2 BINF</option><option>3 MTEC</option><option>1 MADM</option><option>1 MLOG</option></select>
        </div>
        <div class="form-grupo">
          <label class="form-label">Data</label>
          <input class="form-input" type="date" />
        </div>
        <div class="form-grupo">
          <label class="form-label">Hora</label>
          <input class="form-input" type="time" />
        </div>
        <div class="form-grupo">
          <label class="form-label">Local</label>
          <input class="form-input" type="text" placeholder="Ex.: Quadra A" />
        </div>
        <div class="form-grupo">
          <label class="form-label">Fase</label>
          <select class="form-select">
            <option>grupos</option><option>oitavas</option><option>quartas</option><option>semi</option><option>final</option><option>terceiro_lugar</option>
          </select>
        </div>
        <div class="form-grupo span2">
          <label class="form-label">Observações</label>
          <textarea class="form-textarea" placeholder="Notas adicionais…"></textarea>
        </div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secundario" onclick="fecharModal('modal-partida')">Cancelar</button>
      <button class="btn btn-primario" onclick="fecharModal('modal-partida');toast('Partida agendada!','sucesso')"><i class="fas fa-save"></i> Salvar</button>
    </div>
  </div>
</div>

<!-- Modal Resultado -->
<div class="modal-overlay" id="modal-resultado">
  <div class="modal">
    <div class="modal-header">
      <h4>Registrar Resultado</h4>
      <button class="modal-close" onclick="fecharModal('modal-resultado')"><i class="fas fa-times"></i></button>
    </div>
    <div class="modal-body">
      <div class="form-grid">
        <div class="form-grupo span2">
          <label class="form-label">Partida</label>
          <select class="form-select">
            <option>Futsal — 3 MTEC vs 2 BINF (18/08)</option>
            <option>Vôlei — 1 MLOG vs 1 MADM (18/08)</option>
          </select>
        </div>
        <div class="form-grupo">
          <label class="form-label">Placar Time A</label>
          <input class="form-input" type="number" placeholder="0" min="0" />
        </div>
        <div class="form-grupo">
          <label class="form-label">Placar Time B</label>
          <input class="form-input" type="number" placeholder="0" min="0" />
        </div>
        <div class="form-grupo span2">
          <label class="form-label">Vencedor (automático ou W.O.)</label>
          <select class="form-select">
            <option value="">Calcular automaticamente</option>
            <option>Time A — W.O.</option>
            <option>Time B — W.O.</option>
          </select>
        </div>
        <div class="form-grupo span2">
          <label class="form-label">Observações</label>
          <textarea class="form-textarea" placeholder="Cartões, faltas, ocorrências…"></textarea>
        </div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secundario" onclick="fecharModal('modal-resultado')">Cancelar</button>
      <button class="btn btn-primario" onclick="fecharModal('modal-resultado');toast('Resultado registrado!','sucesso')"><i class="fas fa-save"></i> Salvar</button>
    </div>
  </div>
</div>

<!-- Modal Súmula -->
<div class="modal-overlay" id="modal-sumula">
  <div class="modal">
    <div class="modal-header">
      <h4>Enviar Súmula</h4>
      <button class="modal-close" onclick="fecharModal('modal-sumula')"><i class="fas fa-times"></i></button>
    </div>
    <div class="modal-body">
      <div class="form-grid">
        <div class="form-grupo span2">
          <label class="form-label">Partida</label>
          <select class="form-select">
            <option>Futsal — 3 MTEC vs 2 BINF (18/08)</option>
            <option>Vôlei — 1 MLOG vs 1 MADM (18/08)</option>
          </select>
        </div>
        <div class="form-grupo span2">
          <label class="form-label">Arquivo da Súmula (PDF / JPG / PNG)</label>
          <input class="form-input" type="file" accept=".pdf,.jpg,.jpeg,.png" />
        </div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secundario" onclick="fecharModal('modal-sumula')">Cancelar</button>
      <button class="btn btn-primario" onclick="fecharModal('modal-sumula');toast('Súmula enviada para validação!','aviso')"><i class="fas fa-upload"></i> Enviar</button>
    </div>
  </div>
</div>

<!-- Toast Container -->
<div class="toast-container" id="toast-container"></div>

<script src="/soee/src/frontend/js/dash-adm.js"></script>
</body>
</html>