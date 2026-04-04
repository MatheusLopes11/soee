/* ─────────────────────────────────────────
   dash-adm.js — SOEE
───────────────────────────────────────── */

document.addEventListener('DOMContentLoaded', function() {

  /* ── Tema ── */
  var temaAtual = localStorage.getItem('theme') || 'dark';
  document.documentElement.setAttribute('data-theme', temaAtual);
  atualizarIconeTema(temaAtual);

  /* ── Busca rápida ── */
  var inputBusca = document.querySelector('.topbar-search input');
  if (inputBusca) {
    inputBusca.addEventListener('input', function() {
      var q = this.value.toLowerCase();
      document.querySelectorAll('.painel.active tbody tr').forEach(function(tr) {
        tr.style.display = tr.textContent.toLowerCase().includes(q) ? '' : 'none';
      });
    });
  }

  /* ── Fechar modal clicando fora ── */
  document.querySelectorAll('.modal-overlay').forEach(function(overlay) {
    overlay.addEventListener('click', function(e) {
      if (e.target === overlay) overlay.classList.remove('open');
    });
  });

  /* ── Verificar toast de redirecionamento ── */
  var params = new URLSearchParams(window.location.search);
  if (params.get('cadastro') === 'ok') {
    toast('Modalidade cadastrada com sucesso!', 'sucesso');
    history.replaceState({}, '', window.location.pathname);
  }

});

/* ── Navegação entre painéis ── */
function trocarPainel(el) {
  document.querySelectorAll('.nav-item').forEach(function(i) {
    i.classList.remove('active');
  });
  el.classList.add('active');
  var id = el.dataset.painel;
  trocarPainelById(id);
}

function trocarPainelById(id) {
  document.querySelectorAll('.painel').forEach(function(p) {
    p.classList.remove('active');
  });
  var alvo = document.getElementById('painel-' + id);
  if (alvo) alvo.classList.add('active');

  var titulos = {
    overview:    'Dashboard',
    agenda:      'Agenda de Partidas',
    usuarios:    'Usuários & Alunos',
    turmas:      'Turmas',
    modalidades: 'Modalidades Esportivas',
    edicoes:     'Edições / Eventos',
    partidas:    'Partidas',
    resultados:  'Resultados',
    sumulas:     'Súmulas',
  };
  var tituloEl = document.getElementById('topbar-titulo');
  if (tituloEl) tituloEl.textContent = titulos[id] || 'Dashboard';

  document.querySelectorAll('.nav-item').forEach(function(i) {
    if (i.dataset.painel === id) i.classList.add('active');
    else i.classList.remove('active');
  });
}

/* ── Modais ── */
function abrirModal(id) {
  document.getElementById(id).classList.add('open');
}

function fecharModal(id) {
  document.getElementById(id).classList.remove('open');
}

/* ── Tema ── */
function alternarTema() {
  var atual = document.documentElement.getAttribute('data-theme');
  var novo  = atual === 'dark' ? 'light' : 'dark';
  document.documentElement.setAttribute('data-theme', novo);
  localStorage.setItem('theme', novo);
  atualizarIconeTema(novo);
}

function atualizarIconeTema(tema) {
  var icone = document.getElementById('tema-icone');
  if (!icone) return;
  icone.className = tema === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
}

/* ── Toast ── */
function toast(msg, tipo) {
  tipo = tipo || 'sucesso';
  var icons = {
    sucesso: 'fa-check-circle',
    erro:    'fa-times-circle',
    aviso:   'fa-exclamation-triangle'
  };
  var el = document.createElement('div');
  el.className = 'toast ' + tipo;
  el.innerHTML = '<i class="fas ' + icons[tipo] + '"></i><span>' + msg + '</span>';
  var container = document.getElementById('toast-container');
  if (container) container.appendChild(el);
  setTimeout(function() {
    el.style.opacity   = '0';
    el.style.transform = 'translateX(40px)';
    el.style.transition = '0.4s';
    setTimeout(function() { el.remove(); }, 400);
  }, 3200);
}

/* ── Validar súmula via fetch ── */
function validarSumula(id, status) {
  var msg  = status === 'validada' ? 'Súmula validada!' : 'Súmula rejeitada.';
  var tipo = status === 'validada' ? 'sucesso' : 'erro';
  fetch('/soee/src/backend/php/actions/validar-sumula.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'id_sumula=' + id + '&status_sumula=' + status
  })
  .then(function(r) { return r.json(); })
  .then(function(d) {
    if (d.ok) {
      toast(msg, tipo);
      setTimeout(function() { location.reload(); }, 1200);
    } else {
      toast('Erro: ' + (d.erro || 'desconhecido'), 'erro');
    }
  })
  .catch(function() { toast('Erro de conexão.', 'erro'); });
}

/* ── Excluir registro via fetch ── */
function excluirRegistro(entidade, id) {
  if (!confirm('Excluir este(a) ' + entidade + '? Esta ação não pode ser desfeita.')) return;
  fetch('/soee/src/backend/php/actions/excluir-registro.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'entidade=' + entidade + '&id=' + id
  })
  .then(function(r) { return r.json(); })
  .then(function(d) {
    if (d.ok) {
      toast('Registro excluído.', 'sucesso');
      setTimeout(function() { location.reload(); }, 1200);
    } else {
      toast('Erro: ' + (d.erro || 'desconhecido'), 'erro');
    }
  })
  .catch(function() { toast('Erro de conexão.', 'erro'); });
}

/* ── Editar usuário — preenche modal com dados da linha ── */
function editarUsuario(id) {
  var row = document.querySelector('#tabela-usuarios tr[data-id="' + id + '"]');
  if (!row) return;
  var cells = row.querySelectorAll('td');
  document.getElementById('u-id').value    = id;
  document.getElementById('u-nome').value  = cells[1].textContent.trim();
  document.getElementById('u-email').value = cells[2].textContent.trim();
  document.getElementById('modal-usuario-titulo').textContent = 'Editar Usuário';
  abrirModal('modal-usuario');
}