document.addEventListener('DOMContentLoaded', function () {

    const sidebar    = document.getElementById('sidebar');
    const html       = document.documentElement;
    const btnTema    = document.getElementById('toggleTema');
    const iconeTema  = document.getElementById('iconeTema');
    const btnSidebar = document.getElementById('toggleSidebar');

    // ── Tema ──
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

    // ── Toggle sidebar mobile ──
    if (btnSidebar && sidebar) {
        btnSidebar.addEventListener('click', () => sidebar.classList.toggle('aberta'));
    }
    document.addEventListener('click', (e) => {
        if (!sidebar) return;
        if (window.innerWidth <= 768 &&
            sidebar.classList.contains('aberta') &&
            !sidebar.contains(e.target) &&
            e.target !== btnSidebar) {
            sidebar.classList.remove('aberta');
        }
    });

    // ── Busca alunos (painel overview) ──
    const buscaOverview = document.getElementById('buscaAlunoOverview');
    if (buscaOverview) {
        buscaOverview.addEventListener('input', function () {
            const q = this.value.toLowerCase().trim();
            document.querySelectorAll('#listaAlunosOverview .aluno-item').forEach(item => {
                item.style.display = (item.getAttribute('data-nome') || '').includes(q) ? '' : 'none';
            });
        });
    }

    // ── Busca alunos (painel alunos) ──
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

// ── Navegação entre painéis ──
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

    // Sincroniza o item ativo na sidebar quando chamado via trocarPainelById direto
    document.querySelectorAll('.nav-item[data-painel]').forEach(i => {
        i.classList.toggle('ativo', i.dataset.painel === id);
    });
}