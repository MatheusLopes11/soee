/* ═══════════════════════════════════════════════════════════
   dash-user.js — SOEE · Dashboard do Aluno
   Depende de PHP_DATA injetado pelo dash-user.php
═══════════════════════════════════════════════════════════ */
'use strict';

const {
  userId, userNome, userGenero, turmaId, nomeTurma, siglaCurso,
  modalidades, inscricoes, classificacao, proximaPartida
} = PHP_DATA;

const API = '/soee/src/backend/php/dashboard/api-dashboard.php';

let modalidadeAtual = null;
let emIdAtual       = null;
let paginaAtual     = 'overview';

const ICONES_MODAL = {
  quadra: 'fa-solid fa-volleyball',
  mesa:   'fa-solid fa-table-tennis-paddle-ball',
  campo:  'fa-solid fa-futbol',
  outro:  'fa-solid fa-medal',
};

const CORES = ['#1e5671','#2c7da3','#ff4d12','#16a34a','#7c3aed','#ca8a04','#dc2626','#0891b2'];

/* ══════════════════════════════════════════
   LOADER
══════════════════════════════════════════ */
function esconderLoader() {
  const l = document.getElementById('loader');
  if (l) l.classList.add('hide');
}
window.addEventListener('load', () => setTimeout(esconderLoader, 1200));
setTimeout(esconderLoader, 3000);

/* ══════════════════════════════════════════
   CURSOR
══════════════════════════════════════════ */
const dot  = document.getElementById('cursorDot');
const ring = document.getElementById('cursorRing');
let mx = 0, my = 0, rx = 0, ry = 0;
document.addEventListener('mousemove', e => {
  mx = e.clientX; my = e.clientY;
  if (dot) { dot.style.left = mx + 'px'; dot.style.top = my + 'px'; }
});
(function animRing() {
  rx += (mx - rx) * 0.12; ry += (my - ry) * 0.12;
  if (ring) { ring.style.left = rx + 'px'; ring.style.top = ry + 'px'; }
  requestAnimationFrame(animRing);
})();

/* ══════════════════════════════════════════
   TEMA
══════════════════════════════════════════ */
const btnTema = document.getElementById('toggle-theme');
function setTheme(t) {
  document.documentElement.setAttribute('data-theme', t);
  localStorage.setItem('theme', t);
  if (btnTema) btnTema.querySelector('i').className = t === 'dark' ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
}
setTheme(localStorage.getItem('theme') || 'light');
btnTema?.addEventListener('click', () => {
  setTheme(document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark');
});

/* ══════════════════════════════════════════
   REVEAL
══════════════════════════════════════════ */
function setupReveal() {
  const obs = new IntersectionObserver(entries => {
    entries.forEach(e => {
      if (e.isIntersecting) { e.target.classList.add('visible'); obs.unobserve(e.target); }
    });
  }, { threshold: 0.08 });
  document.querySelectorAll('.reveal:not(.visible)').forEach(el => obs.observe(el));
}

/* ══════════════════════════════════════════
   NAVEGAÇÃO
══════════════════════════════════════════ */
function navigate(page, el) {
  paginaAtual = page;

  document.querySelectorAll('.page-view').forEach(p => p.classList.remove('active'));
  const pageEl = document.getElementById('page-' + page);
  if (pageEl) pageEl.classList.add('active');

  document.querySelectorAll('.nav-item[data-page]').forEach(n => n.classList.remove('active'));
  if (el) el.classList.add('active');
  else document.querySelectorAll(`[data-page="${page}"]`).forEach(n => n.classList.add('active'));

  const titulos = {
    overview:      'Visão <span>Geral</span>',
    times:         'Times',
    classificacao: 'Classificação',
    partidas:      'Partidas',
    meutime:       'Meu <span>Time</span>',
    esportes:      'Esportes',
  };
  const pt = document.getElementById('pageTitle');
  if (pt) pt.innerHTML = titulos[page] || page;

  if (emIdAtual) {
    if (page === 'classificacao') carregarClassificacao();
    if (page === 'times')         carregarTimes();
    if (page === 'partidas')      carregarPartidas();
    if (page === 'meutime')       carregarMeuTime();
  }
  if (page === 'esportes') carregarEsportes();

  setTimeout(setupReveal, 80);
}

/* ══════════════════════════════════════════
   FETCH HELPER
══════════════════════════════════════════ */
async function apiFetch(acao, extra = {}) {
  const params = new URLSearchParams({ acao, em_id: emIdAtual ?? 0, ...extra });
  const res = await fetch(`${API}?${params}`);
  if (!res.ok) throw new Error('HTTP ' + res.status);
  return res.json();
}

/* ══════════════════════════════════════════
   SELECIONAR MODALIDADE
══════════════════════════════════════════ */
function selecionarModalidade(mod) {
  modalidadeAtual = mod;
  emIdAtual = mod.id_edicao_modalidade;

  const icone = ICONES_MODAL[mod.tipo_modalidade] || 'fa-solid fa-medal';
  const si = document.getElementById('sportIcon');
  const sn = document.getElementById('sportName');
  if (si) si.innerHTML = `<i class="${icone}"></i>`;
  if (sn) sn.textContent = mod.nome_modalidade;

  ['sportTagOverview','sportTagTimes','sportTagClass','sportTagPartidas','sportTagMeuTime'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.textContent = mod.nome_modalidade;
  });

  const sub = document.getElementById('heroSub');
  if (sub) sub.textContent = `${nomeTurma} — ${mod.nome_modalidade}`;

  // Stats da turma do usuário (vindos do PHP)
  const clTurma = classificacao[emIdAtual];
  if (clTurma) {
    setText('sc1', clTurma.vitorias);
    setText('sc2', clTurma.empates);
    setText('sc3', clTurma.jogos);
    setText('sc4', clTurma.pontos);
    setText('heroPoints', clTurma.pontos);
    setText('heroGames',  clTurma.jogos);
  } else {
    ['sc1','sc2','sc3','sc4','heroPoints','heroGames'].forEach(id => setText(id, '—'));
    setText('heroRank', '—');
  }

  carregarTimes();
  if (paginaAtual === 'classificacao') carregarClassificacao();
  if (paginaAtual === 'partidas')      carregarPartidas();
  if (paginaAtual === 'meutime')       carregarMeuTime();
}

/* ══════════════════════════════════════════
   CARREGAR ESPORTES (página dedicada)
══════════════════════════════════════════ */
async function carregarEsportes() {
  const container = document.getElementById('esportesGrid');
  if (!container) return;
  container.innerHTML = '<p style="color:var(--texto-secundario);padding:16px">Carregando esportes…</p>';
  try {
    const { dados } = await fetch(`${API}?acao=esportes`).then(r => r.json());
    if (!dados || !dados.length) {
      container.innerHTML = '<p style="color:var(--texto-secundario);padding:16px">Nenhum campeonato ativo no momento.</p>';
      return;
    }
    container.innerHTML = dados.map(m => {
      const icone = ICONES_MODAL[m.tipo_modalidade] || 'fa-solid fa-medal';
      const ativo = m.id_edicao_modalidade == emIdAtual;
      return `<div class="team-card reveal ${ativo ? 'my-team' : ''}"
                   onclick="selectSport(${m.id_modalidade})"
                   style="cursor:pointer;text-align:center;">
        ${ativo ? '<div class="my-team-badge">Ativo</div>' : ''}
        <div class="team-logo" style="font-size:2rem;">
          <i class="${icone}" style="color:white"></i>
        </div>
        <div class="team-name">${escHtml(m.nome_modalidade)}</div>
        <div class="team-meta" style="margin-top:4px">
          <span class="team-points-badge">${traduzirStatus(m.status_edicao_modalidade)}</span>
        </div>
      </div>`;
    }).join('');
    setupReveal();
  } catch(e) {
    container.innerHTML = '<p style="color:var(--texto-secundario);padding:16px">Erro ao carregar esportes.</p>';
  }
}

function alternarTema() {
  const atual = document.documentElement.getAttribute('data-theme');
  const novo = atual === 'dark' ? 'light' : 'dark';
  setTheme(novo);
}

/* ══════════════════════════════════════════
   CARREGAR CLASSIFICAÇÃO
══════════════════════════════════════════ */
async function carregarClassificacao() {
  const tbody = document.getElementById('rankingBody');
  if (!tbody) return;
  tbody.innerHTML = '<tr><td colspan="10" style="text-align:center;padding:20px;color:var(--texto-secundario)">Carregando…</td></tr>';
  try {
    const { dados } = await apiFetch('classificacao');
    if (!dados || !dados.length) {
      tbody.innerHTML = '<tr><td colspan="10" style="text-align:center;padding:24px;color:var(--texto-secundario)">Nenhuma classificação registrada ainda.</td></tr>';
      return;
    }
    const posUsuario = dados.findIndex(r => String(r.turma_id_turma) === String(turmaId)) + 1;
    if (posUsuario > 0) setText('heroRank', posUsuario + 'º');

    const medals = ['gold','silver','bronze'];
    tbody.innerHTML = dados.map((r, i) => {
      const isMe = String(r.turma_id_turma) === String(turmaId);
      const cls  = medals[i] || (isMe ? 'my' : 'normal');
      return `<tr class="${isMe ? 'highlight-row' : ''}">
        <td><span class="rank-pos ${cls}">${i + 1}</span></td>
        <td>
          <span class="team-row-logo">${r.nome_turma.charAt(0)}</span>
          ${escHtml(r.nome_turma)}
          ${isMe ? '<span style="font-size:0.7rem;color:var(--laranja-destaque);font-weight:700"> (Você)</span>' : ''}
        </td>
        <td>${r.jogos}</td>
        <td>${r.vitorias}</td>
        <td>${r.empates}</td>
        <td>${r.derrotas}</td>
        <td>${r.pontos_pro}</td>
        <td>${r.pontos_contra}</td>
        <td>${r.saldo > 0 ? '+' : ''}${r.saldo}</td>
        <td><span class="rank-pts">${r.pontos}</span></td>
      </tr>`;
    }).join('');
  } catch(e) {
    tbody.innerHTML = '<tr><td colspan="10" style="text-align:center;color:var(--texto-secundario)">Erro ao carregar.</td></tr>';
  }
}

/* ══════════════════════════════════════════
   CARREGAR TIMES
══════════════════════════════════════════ */
async function carregarTimes() {
  try {
    const [{ dados: times }, { dados: classif }] = await Promise.all([
      apiFetch('times'),
      apiFetch('classificacao'),
    ]);
    if (!times) return;

    const ptsMap = {};
    (classif || []).forEach(r => { ptsMap[r.turma_id_turma] = r; });

    const sorted = [...times].sort((a, b) => {
      const pa = ptsMap[a.id_turma]?.pontos ?? 0;
      const pb = ptsMap[b.id_turma]?.pontos ?? 0;
      return pb - pa;
    });

    renderTeamsGrid(document.getElementById('teamsGridOverview'), sorted.slice(0, 4), ptsMap);
    renderTeamsGrid(document.getElementById('teamsGridFull'), sorted, ptsMap);
  } catch(e) {
    console.error('Erro times:', e);
  }
}

function renderTeamsGrid(container, times, ptsMap) {
  if (!container) return;
  if (!times || !times.length) {
    container.innerHTML = '<p style="color:var(--texto-secundario);padding:16px">Nenhum time inscrito nesta modalidade ainda.</p>';
    return;
  }
  container.innerHTML = times.map(t => {
    const isMe = String(t.id_turma) === String(turmaId);
    const cl   = ptsMap[t.id_turma];
    const pts  = cl ? cl.pontos : '—';
    return `<div class="team-card ${isMe ? 'my-team' : ''} reveal" onclick="navigate('meutime',null)">
      ${isMe ? '<div class="my-team-badge">Meu Time</div>' : ''}
      <div class="team-logo">${escHtml(t.nome_turma.charAt(0).toUpperCase())}</div>
      <div class="team-name">${escHtml(t.nome_turma)}</div>
      <div class="team-meta">${t.total_inscritos} inscrito(s)</div>
      <div class="team-points-badge">${pts} pts</div>
    </div>`;
  }).join('');
  setupReveal();
}

/* ══════════════════════════════════════════
   CARREGAR PARTIDAS
══════════════════════════════════════════ */
async function carregarPartidas() {
  const lista = document.getElementById('partidasLista');
  if (!lista) return;
  lista.innerHTML = '<p style="color:var(--texto-secundario);text-align:center;padding:32px">Carregando…</p>';
  try {
    const { dados } = await apiFetch('partidas');
    if (!dados || !dados.length) {
      lista.innerHTML = '<p style="color:var(--texto-secundario);text-align:center;padding:32px">Nenhuma partida cadastrada.</p>';
      return;
    }
    lista.innerHTML = dados.map(p => {
      const envolveMeu = String(p.turma_id_time_a) === String(turmaId) || String(p.turma_id_time_b) === String(turmaId);
      const statusCls = { agendada:'status-agendada', realizada:'status-realizada', cancelada:'status-cancelada', wo:'status-wo' };
      const placar = p.status_partida === 'realizada'
        ? `<div class="partida-placar">${p.placar_time_a ?? 0} × ${p.placar_time_b ?? 0}</div>`
        : `<div class="partida-hora">${p.hora_partida ? p.hora_partida.slice(0,5) : '—'}</div>`;
      return `<div class="partida-card ${envolveMeu ? 'partida-card-mine' : ''} reveal">
        <div class="partida-header">
          <span class="partida-data"><i class="fa-solid fa-calendar"></i> ${formatarData(p.data_partida)}</span>
          <span class="partida-status ${statusCls[p.status_partida] || ''}">${traduzirStatus(p.status_partida)}</span>
          <span class="partida-fase">${traduzirFase(p.fase_partida)}${p.grupo_partida ? ' · Grupo ' + p.grupo_partida : ''}</span>
        </div>
        <div class="partida-times">
          <span class="partida-time ${String(p.turma_id_time_a) === String(turmaId) ? 'partida-time-mine' : ''}">${escHtml(p.nome_time_a)}</span>
          ${placar}
          <span class="partida-time ${String(p.turma_id_time_b) === String(turmaId) ? 'partida-time-mine' : ''}">${escHtml(p.nome_time_b)}</span>
        </div>
        ${p.local_partida ? `<div class="partida-local"><i class="fa-solid fa-location-dot"></i> ${escHtml(p.local_partida)}</div>` : ''}
      </div>`;
    }).join('');
    setupReveal();
  } catch(e) {
    lista.innerHTML = '<p style="color:var(--texto-secundario);text-align:center;padding:32px">Erro ao carregar partidas.</p>';
  }
}

/* ══════════════════════════════════════════
   CARREGAR MEU TIME
══════════════════════════════════════════ */
async function carregarMeuTime() {
  if (!turmaId) return;
  try {
    const [{ dados: jogadores }, { dados: classif }] = await Promise.all([
      apiFetch('jogadores', { turma_id: turmaId }),
      apiFetch('classificacao'),
    ]);

    if (classif) {
      const idx = classif.findIndex(r => String(r.turma_id_turma) === String(turmaId));
      const cl  = classif[idx];
      setText('mtPos', idx >= 0 ? (idx + 1) + 'º' : '—');
      setText('mtPts', cl ? cl.pontos : '—');
    }
    setText('mtPlayers', jogadores ? jogadores.length : '—');
    setText('myTeamSport', modalidadeAtual ? modalidadeAtual.nome_modalidade + ' • ' + nomeTurma : nomeTurma);

    const grid = document.getElementById('playersGrid');
    if (!grid) return;
    if (!jogadores || !jogadores.length) {
      grid.innerHTML = '<p style="color:var(--texto-secundario)">Nenhum jogador inscrito nesta modalidade.</p>';
      return;
    }
    grid.innerHTML = jogadores.map((j, i) => {
      const iniciais = j.nome_usuario.split(' ').map(n => n[0]).join('').slice(0, 2).toUpperCase();
      const cor      = CORES[i % CORES.length];
      return `<div class="player-card reveal">
        <div class="player-avatar" style="background:${cor}">${iniciais}</div>
        <div class="player-name">${escHtml(j.nome_usuario)}</div>
        <div class="player-pos">${escHtml(j.posicao_inscricao || '—')}</div>
        ${j.numero_camisa_inscricao ? `<div class="player-num">#${j.numero_camisa_inscricao}</div>` : ''}
        ${j.capitao_inscricao == 1 ? '<div class="captain-badge"><i class="fa-solid fa-star"></i> Capitão</div>' : ''}
      </div>`;
    }).join('');
    setupReveal();
  } catch(e) {
    console.error('Erro meutime:', e);
  }
}

/* ══════════════════════════════════════════
   SPORT PICKER MODAL
══════════════════════════════════════════ */
function openSportPicker() {
  const modal = document.getElementById('sportModal');
  if (!modal) return;
  modal.style.display = 'flex';
  const grid = document.getElementById('sportPickerGrid');
  if (!grid) return;
  grid.innerHTML = modalidades.map(m => `
    <button onclick="selectSport(${m.id_modalidade})" class="sport-picker-btn ${m.id_edicao_modalidade == emIdAtual ? 'ativo' : ''}">
      <i class="${ICONES_MODAL[m.tipo_modalidade] || 'fa-solid fa-medal'}" style="font-size:1.6rem;display:block;margin-bottom:8px"></i>
      <span>${escHtml(m.nome_modalidade)}</span>
    </button>`).join('');
}
function closeSportPicker() {
  const modal = document.getElementById('sportModal');
  if (modal) modal.style.display = 'none';
}
function selectSport(modId) {
  const mod = modalidades.find(m => m.id_modalidade == modId);
  if (!mod) return;
  closeSportPicker();
  selecionarModalidade(mod);
  navigate('overview', document.querySelector('[data-page="overview"]'));
}

/* ══════════════════════════════════════════
   HELPERS
══════════════════════════════════════════ */
function setText(id, val) {
  const el = document.getElementById(id);
  if (el) el.textContent = val ?? '—';
}
function escHtml(str) {
  return String(str ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}
function formatarData(str) {
  if (!str) return '—';
  const [y,m,d] = str.split('-');
  return `${d}/${m}/${y}`;
}
function traduzirStatus(s) {
  return {
    agendada:'Agendada', realizada:'Realizada', cancelada:'Cancelada', wo:'W.O.',
    inscricoes:'Inscrições abertas', em_andamento:'Em andamento', encerrado:'Encerrado'
  }[s] || s;
}
function traduzirFase(f) {
  return { grupos:'Fase de Grupos', oitavas:'Oitavas', quartas:'Quartas', semi:'Semifinal', final:'Final', terceiro_lugar:'3º Lugar' }[f] || f;
}

/* ══════════════════════════════════════════
   INIT
══════════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', () => {
  if (inscricoes && inscricoes.length > 0) {
    const primeiraInsc = inscricoes[0];
    const mod = modalidades.find(m => m.id_edicao_modalidade == primeiraInsc.edicao_modalidade_id);
    if (mod) selecionarModalidade(mod);
    else if (modalidades.length > 0) selecionarModalidade(modalidades[0]);
  } else if (modalidades.length > 0) {
    selecionarModalidade(modalidades[0]);
  } else {
    setText('sportName', 'Sem campeonato');
  }

  setupReveal();
});