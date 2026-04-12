/* ─────────────────────────────────────────
   dash-prof.js — SOEE
───────────────────────────────────────── */

document.addEventListener('DOMContentLoaded', function () {

    /* ── Data atual ── */
    var elData = document.getElementById('dataAtual');
    if (elData) {
        elData.textContent = new Date().toLocaleDateString('pt-BR', {
            weekday: 'short', day: '2-digit', month: 'short', year: 'numeric'
        });
    }

    /* ── Sidebar toggle ── */
    var sidebar       = document.getElementById('sidebar');
    var btnSidebar    = document.getElementById('toggleSidebar');
    if (btnSidebar && sidebar) {
        btnSidebar.addEventListener('click', function () {
            sidebar.classList.toggle('aberta');
        });
    }

    /* ── Tema ── */
    var html        = document.documentElement;
    var btnTema     = document.getElementById('toggleTema');
    var iconeTema   = document.getElementById('iconeTema');
    var temaSalvo   = localStorage.getItem('theme') || 'light';
    html.setAttribute('data-theme', temaSalvo);
    if (iconeTema) {
        iconeTema.className = temaSalvo === 'dark' ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
    }
    if (btnTema) {
        btnTema.addEventListener('click', function () {
            var atual = html.getAttribute('data-theme');
            var novo  = atual === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-theme', novo);
            localStorage.setItem('theme', novo);
            if (iconeTema) {
                iconeTema.className = novo === 'dark' ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
            }
        });
    }

    /* ── Navegação entre painéis ── */
    document.querySelectorAll('.nav-item[data-painel]').forEach(function (el) {
        el.addEventListener('click', function (e) {
            e.preventDefault();
            trocarPainel(el);
        });
    });

    /* ── Fechar modal clicando fora ── */
    document.querySelectorAll('.modal-overlay').forEach(function (overlay) {
        overlay.addEventListener('click', function (e) {
            if (e.target === overlay) overlay.classList.remove('open');
        });
    });

    /* ── Busca rápida ── */
    var inputBusca = document.querySelector('.topbar-search input');
    if (inputBusca) {
        inputBusca.addEventListener('input', function () {
            var q = this.value.toLowerCase();
            document.querySelectorAll('.painel.active tbody tr').forEach(function (tr) {
                tr.style.display = tr.textContent.toLowerCase().includes(q) ? '' : 'none';
            });
        });
    }

    /* ── Toast de redirecionamento ── */
    var params = new URLSearchParams(window.location.search);
    if (params.get('ok') === '1') {
        toast('Operação realizada com sucesso!', 'sucesso');
        history.replaceState({}, '', window.location.pathname);
    }

});

/* ── Navegação entre painéis ── */
function trocarPainel(el) {
    document.querySelectorAll('.nav-item').forEach(function (i) {
        i.classList.remove('ativo');
    });
    el.classList.add('ativo');
    var id = el.dataset.painel;
    trocarPainelById(id);
}

function trocarPainelById(id) {
    document.querySelectorAll('.painel').forEach(function (p) {
        p.classList.remove('active');
    });
    var alvo = document.getElementById('painel-' + id);
    if (alvo) alvo.classList.add('active');

    var titulos = {
        overview:     'Dashboard',
        agenda:       'Agenda de Partidas',
        alunos:       'Alunos & Turmas',
        modalidades:  'Modalidades Esportivas',
        edicoes:      'Edições / Eventos',
        partidas:     'Partidas',
        resultados:   'Resultados',
        sumulas:      'Súmulas',
    };
    var tituloEl = document.getElementById('topbar-titulo');
    if (tituloEl) tituloEl.textContent = titulos[id] || 'Dashboard';

    document.querySelectorAll('.nav-item[data-painel]').forEach(function (i) {
        if (i.dataset.painel === id) i.classList.add('ativo');
        else i.classList.remove('ativo');
    });
}

/* ── Modais ── */
function abrirModal(id) {
    var el = document.getElementById(id);
    if (el) el.classList.add('open');
}

function fecharModal(id) {
    var el = document.getElementById(id);
    if (el) el.classList.remove('open');
}

/* ── Toast ── */
function toast(msg, tipo) {
    tipo = tipo || 'sucesso';
    var icons = { sucesso: 'fa-check-circle', erro: 'fa-times-circle', aviso: 'fa-exclamation-triangle' };
    var el = document.createElement('div');
    el.className = 'toast ' + tipo;
    el.innerHTML = '<i class="fas ' + (icons[tipo] || icons.sucesso) + '"></i><span>' + msg + '</span>';
    var container = document.getElementById('toast-container');
    if (container) container.appendChild(el);
    setTimeout(function () {
        el.style.opacity   = '0';
        el.style.transform = 'translateX(40px)';
        el.style.transition = '0.4s';
        setTimeout(function () { el.remove(); }, 400);
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
    .then(function (r) { return r.json(); })
    .then(function (d) {
        if (d.ok) {
            toast(msg, tipo);
            setTimeout(function () { location.reload(); }, 1200);
        } else {
            toast('Erro: ' + (d.erro || 'desconhecido'), 'erro');
        }
    })
    .catch(function () { toast('Erro de conexão.', 'erro'); });
}

/* ── Excluir registro via fetch ── */
function excluirRegistro(entidade, id) {
    if (!confirm('Excluir este(a) ' + entidade + '? Esta ação não pode ser desfeita.')) return;
    fetch('/soee/src/backend/php/actions/excluir-registro.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'entidade=' + entidade + '&id=' + id
    })
    .then(function (r) { return r.json(); })
    .then(function (d) {
        if (d.ok) {
            toast('Registro excluído.', 'sucesso');
            setTimeout(function () { location.reload(); }, 1200);
        } else {
            toast('Erro: ' + (d.erro || 'desconhecido'), 'erro');
        }
    })
    .catch(function () { toast('Erro de conexão.', 'erro'); });
}

/* ── Eleger adm-sala via fetch ── */
function elegerAdmSala(idAluno, nomeAluno) {
    if (!confirm('Eleger "' + nomeAluno + '" como Adm. de Sala?')) return;
    fetch('/soee/src/backend/php/actions/eleger-adm-sala.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'id_usuario=' + idAluno
    })
    .then(function (r) { return r.json(); })
    .then(function (d) {
        if (d.ok) {
            toast(nomeAluno + ' agora é Adm. de Sala!', 'sucesso');
            setTimeout(function () { location.reload(); }, 1400);
        } else {
            toast('Erro: ' + (d.erro || 'desconhecido'), 'erro');
        }
    })
    .catch(function () { toast('Erro de conexão.', 'erro'); });
}

/* ── Agendar partida (modal) ── */
function abrirAgendarPartida() {
    abrirModal('modal-partida');
}

/* ── Registrar resultado (modal) ── */
function abrirRegistrarResultado() {
    abrirModal('modal-resultado');
}

