/* ── Navegação ── */
function trocarPainel(el) {
  document.querySelectorAll('.nav-item').forEach(i => i.classList.remove('active'));
  el.classList.add('active');
  const id = el.dataset.painel;
  trocarPainelById(id);
}
function trocarPainelById(id) {
  document.querySelectorAll('.painel').forEach(p => p.classList.remove('active'));
  const alvo = document.getElementById('painel-' + id);
  if (alvo) alvo.classList.add('active');
  const titulos = {
    overview:       'Dashboard',
    agenda:         'Agenda de Partidas',
    usuarios:       'Usuários & Alunos',
    turmas:         'Turmas',
    modalidades:    'Modalidades Esportivas',
    edicoes:        'Edições / Eventos',
    partidas:       'Partidas',
    resultados:     'Resultados',
    classificacao:  'Classificação',
    sumulas:        'Súmulas',
  };
  document.getElementById('topbar-titulo').textContent = titulos[id] || 'Dashboard';
  // sincronizar nav
  document.querySelectorAll('.nav-item').forEach(i => {
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
document.querySelectorAll('.modal-overlay').forEach(overlay => {
  overlay.addEventListener('click', e => {
    if (e.target === overlay) overlay.classList.remove('open');
  });
});

/* ── CRUD Usuário (demo) ── */
let modoEdicaoUsuario = false;
let idEdicaoUsuario = null;

function salvarUsuario() {
  const nome  = document.getElementById('u-nome').value.trim();
  const email = document.getElementById('u-email').value.trim();
  const tipo  = document.getElementById('u-tipo').value;
  const genero= document.getElementById('u-genero').value;
  const ativo = document.getElementById('u-ativo').value;

  if (!nome || !email) { toast('Preencha nome e e-mail!', 'aviso'); return; }

  const tbody = document.querySelector('#tabela-usuarios tbody');

  if (modoEdicaoUsuario) {
    // UPDATE
    const tr = tbody.querySelector(`tr[data-id="${idEdicaoUsuario}"]`);
    if (tr) {
      tr.cells[1].textContent = nome;
      tr.cells[2].textContent = email;
      tr.cells[4].textContent = mapTipo(tipo);
      tr.cells[5].textContent = genero.toUpperCase();
      tr.cells[6].innerHTML = `<span class="badge-status ${ativo==='1'?'ativo':'inativo'}">${ativo==='1'?'Ativo':'Inativo'}</span>`;
    }
    toast('Usuário atualizado!', 'sucesso');
  } else {
    // INSERT
    const newId = tbody.querySelectorAll('tr').length + 1;
    const tr = document.createElement('tr');
    tr.dataset.id = newId;
    tr.innerHTML = `
      <td>${newId}</td>
      <td>${nome}</td>
      <td>${email}</td>
      <td>—</td>
      <td>${mapTipo(tipo)}</td>
      <td>${genero.toUpperCase()}</td>
      <td><span class="badge-status ${ativo==='1'?'ativo':'inativo'}">${ativo==='1'?'Ativo':'Inativo'}</span></td>
      <td class="td-acoes">
        <button class="btn btn-secundario btn-sm" onclick="editarUsuario(${newId})"><i class="fas fa-edit"></i></button>
        <button class="btn btn-perigo btn-sm" onclick="excluir('usuário',${newId})"><i class="fas fa-trash"></i></button>
      </td>`;
    tbody.appendChild(tr);
    document.getElementById('kpi-alunos').textContent = parseInt(document.getElementById('kpi-alunos').textContent) + 1;
    toast('Usuário criado com sucesso!', 'sucesso');
  }

  fecharModal('modal-usuario');
  limparFormUsuario();
}

function mapTipo(v) {
  return {aluno:'Aluno',adm_sala:'Adm. Sala',adm_geral:'Adm. Geral',professor:'Professor'}[v] || v;
}

function editarUsuario(id) {
  modoEdicaoUsuario = true;
  idEdicaoUsuario   = id;
  document.getElementById('modal-usuario-titulo').textContent = 'Editar Usuário';
  // preencher com dados da linha (demo)
  const tr = document.querySelector(`#tabela-usuarios tr[data-id="${id}"]`);
  if (tr) {
    document.getElementById('u-nome').value  = tr.cells[1].textContent;
    document.getElementById('u-email').value = tr.cells[2].textContent;
  }
  abrirModal('modal-usuario');
}

function limparFormUsuario() {
  ['u-nome','u-email','u-senha'].forEach(id => document.getElementById(id).value = '');
  modoEdicaoUsuario = false;
  idEdicaoUsuario   = null;
  document.getElementById('modal-usuario-titulo').textContent = 'Novo Usuário';
}

/* ── Excluir (demo) ── */
function excluir(tipo, id) {
  if (!confirm(`Deseja realmente excluir este(a) ${tipo}?`)) return;
  // Tenta remover linha da tabela atual
  const tr = document.querySelector(`tr[data-id="${id}"]`);
  if (tr) { tr.remove(); }
  toast(`${tipo.charAt(0).toUpperCase()+tipo.slice(1)} excluído(a)!`, 'erro');
}

/* ── Toast ── */
function toast(msg, tipo = 'sucesso') {
  const icons = { sucesso:'fa-check-circle', erro:'fa-times-circle', aviso:'fa-exclamation-triangle' };
  const el = document.createElement('div');
  el.className = `toast ${tipo}`;
  el.innerHTML = `<i class="fas ${icons[tipo]}"></i><span>${msg}</span>`;
  const container = document.getElementById('toast-container');
  container.appendChild(el);
  setTimeout(() => { el.style.opacity='0'; el.style.transform='translateX(40px)'; el.style.transition='0.4s'; setTimeout(()=>el.remove(),400); }, 3200);
}

/* ── Tema ── */
function alternarTema() {
  const html = document.documentElement;
  const isDark = html.dataset.theme === 'dark';
  html.dataset.theme = isDark ? 'light' : 'dark';
  document.getElementById('tema-icone').className = isDark ? 'fas fa-moon' : 'fas fa-sun';
}

/* ── Busca rápida ── */
document.querySelector('.topbar-search input').addEventListener('input', function() {
  const q = this.value.toLowerCase();
  document.querySelectorAll('.painel.active tbody tr').forEach(tr => {
    tr.style.display = tr.textContent.toLowerCase().includes(q) ? '' : 'none';
  });
});