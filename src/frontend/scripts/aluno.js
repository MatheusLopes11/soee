// ── Tema ──
(function() {
    const t = localStorage.getItem('theme') || 'light';
    document.documentElement.setAttribute('data-theme', t);
    const ic = document.getElementById('temaIcone');
    if (ic) ic.className = t === 'dark' ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
})();

document.getElementById('toggle-theme')?.addEventListener('click', () => {
    const atual = document.documentElement.getAttribute('data-theme');
    const novo  = atual === 'dark' ? 'light' : 'dark';
    document.documentElement.setAttribute('data-theme', novo);
    localStorage.setItem('theme', novo);
    const ic = document.getElementById('temaIcone');
    if (ic) ic.className = novo === 'dark' ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
});

// ── Navegação entre painéis ──
function trocarPainel(el) {
    document.querySelectorAll('.nav-item').forEach(i => i.classList.remove('active'));
    el.classList.add('active');
    trocarPainelById(el.dataset.painel);
}

function trocarPainelById(id) {
    document.querySelectorAll('.painel').forEach(p => p.classList.remove('active'));
    const alvo = document.getElementById('painel-' + id);
    if (alvo) alvo.classList.add('active');

    const titulos = {
        overview:      'Visão Geral',
        partidas:      'Partidas',
        times:         'Times',
        classificacao: 'Classificação',
        meutime:       'Meu Time',
        inscricoes:    'Inscrições',
    };
    const el = document.getElementById('topbar-titulo');
    if (el) el.textContent = titulos[id] || 'Dashboard';

    document.querySelectorAll('.nav-item[data-painel]').forEach(i => {
        i.classList.toggle('active', i.dataset.painel === id);
    });
}

// ── Inscrição ──
function enviarInscricao(e, edicaoModalidadeId) {
    e.preventDefault();
    const form     = e.target;
    const nomeCamisa = (form.nome_camisa?.value ?? '').trim();
    const camisa   = (form.camisa?.value ?? '').trim();

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

// ── Cancelar inscrição ──
function cancelarInscricao(id, nome) {
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