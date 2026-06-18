document.addEventListener('DOMContentLoaded', function () {

    const sidebar    = document.getElementById('sidebar');
    const html       = document.documentElement;
    const btnTema    = document.getElementById('toggleTema');
    const iconeTema  = document.getElementById('iconeTema');
    const btnSidebar = document.getElementById('toggleSidebar');

    // ── Tema ──────────────────────────────────────────────────────
    const temaSalvo = localStorage.getItem('theme') || 'light';
    html.setAttribute('data-theme', temaSalvo);
    if (iconeTema) {
        iconeTema.className = temaSalvo === 'dark' ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
    }
    if (btnTema && iconeTema) {
        btnTema.addEventListener('click', () => {
            const atual = html.getAttribute('data-theme');
            const novo  = atual === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-theme', novo);
            localStorage.setItem('theme', novo);
            iconeTema.className = novo === 'dark' ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
        });
    }

    // ── Toggle sidebar mobile ──────────────────────────────────────
    if (btnSidebar && sidebar) {
        btnSidebar.addEventListener('click', () => {
            if (window.innerWidth > 768) return;

            sidebar.classList.toggle('aberta');
            const icone = btnSidebar.querySelector('i');
            if (icone) {
                icone.className = sidebar.classList.contains('aberta')
                    ? 'fa-solid fa-xmark'
                    : 'fa-solid fa-bars';
            }
        });
    }
    document.addEventListener('click', (e) => {
        if (!sidebar) return;
        if (window.innerWidth <= 768 &&
            sidebar.classList.contains('aberta') &&
            !sidebar.contains(e.target) &&
            e.target !== btnSidebar) {
            sidebar.classList.remove('aberta');
            const icone = btnSidebar?.querySelector('i');
            if (icone) icone.className = 'fa-solid fa-bars';
        }
    });

    window.addEventListener('resize', () => {
        if (!sidebar || !btnSidebar || window.innerWidth <= 768) return;

        sidebar.classList.remove('aberta');
        const icone = btnSidebar.querySelector('i');
        if (icone) icone.className = 'fa-solid fa-bars';
    });

    // ── Busca alunos (painel overview) ────────────────────────────
    const buscaOverview = document.getElementById('buscaAlunoOverview');
    if (buscaOverview) {
        buscaOverview.addEventListener('input', function () {
            const q = this.value.toLowerCase().trim();
            document.querySelectorAll('#listaAlunosOverview .aluno-item').forEach(item => {
                item.style.display = (item.getAttribute('data-nome') || '').includes(q) ? '' : 'none';
            });
        });
    }

    // ── Busca alunos (painel alunos) ──────────────────────────────
    const buscaAlunos = document.getElementById('buscaAluno');
    if (buscaAlunos) {
        buscaAlunos.addEventListener('input', function () {
            const q = this.value.toLowerCase().trim();
            document.querySelectorAll('#listaAlunos .aluno-item').forEach(item => {
                item.style.display = (item.getAttribute('data-nome') || '').includes(q) ? '' : 'none';
            });
        });
    }

});

// ── Navegação entre painéis ───────────────────────────────────────
function trocarPainel(el) {
    document.querySelectorAll('.nav-item').forEach(i => i.classList.remove('ativo'));
    el.classList.add('ativo');
    trocarPainelById(el.dataset.painel);
}

function trocarPainelById(id) {
    document.querySelectorAll('.painel').forEach(p => p.classList.remove('active'));
    const alvo = document.getElementById('painel-' + id);
    if (alvo) alvo.classList.add('active');

    const titulos = {
        overview:      'Dashboard',
        alunos:        'Alunos da Turma',
        inscricoes:    'Inscrições',
        partidas:      'Partidas',
        classificacao: 'Classificação',
        modalidades:   'Modalidades',
    };
    const tituloEl = document.getElementById('topbar-titulo');
    if (tituloEl) tituloEl.textContent = titulos[id] || 'Dashboard';

    // Sincroniza o item ativo na sidebar
    document.querySelectorAll('.nav-item[data-painel]').forEach(i => {
        i.classList.toggle('ativo', i.dataset.painel === id);
    });
}

// ── Modais — abrir ────────────────────────────────────────────────

/**
 * Redireciona para a página de cadastro de modalidade (esporte.php)
 * em vez de abrir o modal inline.
 */
function abrirModalNovaModalidade() {
    window.location.href = '/soee/src/frontend/views/forms/esporte.php';
}

function abrirModalEditarModalidade(md) {
    document.getElementById('modal-modalidade-titulo').innerHTML =
        '<i class="fa-solid fa-pen"></i> Editar Modalidade';
    document.getElementById('inp-id-modalidade').value    = md.id_modalidade            || '';
    document.getElementById('inp-nome').value             = md.nome_modalidade           || '';
    document.getElementById('inp-tipo').value             = md.tipo_modalidade           || '';
    document.getElementById('inp-formato').value          = md.formato_modalidade        || '';
    document.getElementById('inp-participacao').value     = md.tipo_participacao         || '';
    document.getElementById('inp-min').value              = md.qtd_min_jogadores         || '';
    document.getElementById('inp-max').value              = md.qtd_max_jogadores         || '';
    document.getElementById('inp-desc').value             = md.descricao_modalidade      || '';
    document.getElementById('inp-regul').value            = md.regulamento_modalidade    || '';
    document.getElementById('inp-ativo').checked          = md.ativo_modalidade == 1;

    const td = md.tipo_duracao || '';
    document.getElementById('inp-tipo-duracao').value = td;
    toggleDuracao(td);
    if (td === 'minutos') document.getElementById('inp-dur-minutos').value = md.duracao_minutos || '';
    if (td === 'pontos')  document.getElementById('inp-dur-pontos').value  = md.duracao_pontos  || '';

    document.getElementById('modal-modalidade').classList.add('open');
}

function abrirModalVincularEdicao(id, nome) {
    document.getElementById('vinc-modal-id').value   = id;
    document.getElementById('vinc-modal-nome').value = nome;
    document.getElementById('modal-vincular').classList.add('open');
}

// ── Modais — fechar ───────────────────────────────────────────────
function fecharModal(id) {
    document.getElementById(id).classList.remove('open');
}
function fecharSeOverlay(event, id) {
    if (event.target === document.getElementById(id)) fecharModal(id);
}

// ── Toggle duração ────────────────────────────────────────────────
function toggleDuracao(val) {
    document.getElementById('grupo-dur-minutos').style.display = val === 'minutos' ? '' : 'none';
    document.getElementById('grupo-dur-pontos').style.display  = val === 'pontos'  ? '' : 'none';
}

// ── Filtro de modalidades ─────────────────────────────────────────
function filtrarModalidades(q) {
    q = q.toLowerCase();
    document.querySelectorAll('#gridModalidades .modalidade-card').forEach(function (el) {
        const nome = el.dataset.nome || '';
        const tipo = el.dataset.tipo || '';
        el.style.display = (nome.includes(q) || tipo.includes(q)) ? '' : 'none';
    });
}
// ── Inscrição do ADM ──────────────────────────────────────────────
function enviarInscricaoAdm(e, edicaoModalidadeId) {
    e.preventDefault();
    const form       = e.target;
    const nomeCamisa = (form.nome_camisa?.value ?? '').trim();
    const camisa     = (form.camisa?.value ?? '').trim();
 
    fetch('/soee/src/backend/actions/inscrever-aluno.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `edicao_modalidade_id=${edicaoModalidadeId}`
            + `&nome_camisa=${encodeURIComponent(nomeCamisa)}`
            + `&camisa=${encodeURIComponent(camisa)}`
    })
    .then(r => r.json())
    .then(d => {
        if (d.ok) {
            alert('Inscrição realizada com sucesso!');
            location.reload();
        } else {
            alert('Erro: ' + (d.erro || 'desconhecido'));
        }
    })
    .catch(() => alert('Erro de conexão.'));
}
 
// ── Cancelar inscrição do ADM ─────────────────────────────────────
function cancelarInscricaoAdm(id, nome) {
    if (!confirm('Cancelar inscrição em "' + nome + '"?')) return;
    fetch('/soee/src/backend/actions/cancelar-inscricao.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `id_inscricao=${id}`
    })
    .then(r => r.json())
    .then(d => {
        if (d.ok) { alert('Inscrição cancelada.'); location.reload(); }
        else alert('Erro: ' + (d.erro || 'desconhecido'));
    })
    .catch(() => alert('Erro de conexão.'));
}
