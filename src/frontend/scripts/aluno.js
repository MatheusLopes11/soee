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

// ═══════════════════════════════════════════════════════════
//  aluno.js  —  SOEE  (versão com suporte a Duplas e Trios)
//  Substitui / complementa o aluno.js existente.
// ═══════════════════════════════════════════════════════════

/* ── Cancelar inscrição (mantido do original) ─────────── */
async function cancelarInscricao(inscricaoId, nomeModalidade) {
    if (!confirm(`Cancelar inscrição em "${nomeModalidade}"?`)) return;
    try {
        const fd = new FormData();
        fd.append('id_inscricao', inscricaoId);
        const r = await fetch('/soee/src/backend/actions/cancelar_inscricao.php', {
            method: 'POST', body: fd
        });
        const d = await r.json();
        if (d.ok) {
            alert('Inscrição cancelada com sucesso.');
            location.reload();
        } else {
            alert(d.msg || 'Erro ao cancelar.');
        }
    } catch { alert('Erro de conexão.'); }
}

/* ── Inscrição simples (solo / time) ─────────────────── */
async function enviarInscricao(e, emId) {
    e.preventDefault();
    const form = e.target;
    const fd   = new FormData(form);
    fd.append('edicao_modalidade_id', emId);

    try {
        const r = await fetch('/soee/src/backend/actions/inscrever.php', {
            method: 'POST', body: fd
        });
        const d = await r.json();
        if (d.ok) {
            alert(d.msg || 'Inscrito com sucesso!');
            location.reload();
        } else {
            alert(d.msg || 'Erro ao inscrever.');
        }
    } catch { alert('Erro de conexão.'); }
}

/* ── Inscrição de Dupla / Trio ───────────────────────── */
async function enviarInscricaoDupla(e, emId, tipo) {
    e.preventDefault();
    const form = e.target;

    // Valida que parceiros foram selecionados
    const p1Id = form.querySelector('[name="parceiro1_id"]')?.value;
    if (!p1Id) {
        alert('Selecione o(a) parceiro(a) antes de confirmar.');
        return;
    }
    if (tipo === 'trio') {
        const p2Id = form.querySelector('[name="parceiro2_id"]')?.value;
        if (!p2Id) {
            alert('Selecione o(a) segundo(a) parceiro(a) antes de confirmar.');
            return;
        }
    }

    const fd = new FormData(form);
    fd.append('edicao_modalidade_id', emId);

    try {
        const r = await fetch('/soee/src/backend/actions/inscrever_dupla.php', {
            method: 'POST', body: fd
        });
        const d = await r.json();
        if (d.ok) {
            alert(d.msg || 'Dupla inscrita com sucesso!');
            location.reload();
        } else {
            alert(d.msg || 'Erro ao inscrever.');
        }
    } catch { alert('Erro de conexão.'); }
}

/* ── Autocomplete de parceiros ──────────────────────── */
function initAutocompleteParceiro(inputEl, hiddenEl, emId) {
    if (!inputEl || !hiddenEl) return;

    // Cria container de sugestões
    const container = document.createElement('div');
    container.className = 'parceiro-sugestoes';
    inputEl.parentNode.style.position = 'relative';
    inputEl.parentNode.appendChild(container);

    let debounce;

    inputEl.addEventListener('input', () => {
        clearTimeout(debounce);
        hiddenEl.value = '';
        inputEl.classList.remove('parceiro-selecionado');
        const q = inputEl.value.trim();

        if (q.length < 2) {
            container.innerHTML = '';
            container.style.display = 'none';
            return;
        }

        debounce = setTimeout(async () => {
            try {
                const url = `/soee/src/backend/actions/buscar_parceiros.php?em=${emId}&q=${encodeURIComponent(q)}`;
                const r   = await fetch(url);
                const lista = await r.json();

                container.innerHTML = '';
                if (!lista.length) {
                    container.innerHTML = '<div class="parceiro-item vazio">Nenhum aluno disponível encontrado.</div>';
                } else {
                    lista.forEach(aluno => {
                        const item = document.createElement('div');
                        item.className = 'parceiro-item';
                        item.innerHTML = `<strong>${escHtml(aluno.nome_usuario)}</strong>
                                          <span class="parceiro-turma">${escHtml(aluno.nome_turma || '')}</span>`;
                        item.addEventListener('click', () => {
                            inputEl.value   = aluno.nome_usuario;
                            hiddenEl.value  = aluno.id_usuario;
                            inputEl.classList.add('parceiro-selecionado');
                            container.innerHTML  = '';
                            container.style.display = 'none';
                        });
                        container.appendChild(item);
                    });
                }
                container.style.display = 'block';
            } catch { /* silencioso */ }
        }, 280);
    });

    // Fecha ao clicar fora
    document.addEventListener('click', (ev) => {
        if (!inputEl.parentNode.contains(ev.target)) {
            container.style.display = 'none';
        }
    });
}

function escHtml(str) {
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

/* ── Inicializa todos os autocompletes da página ─────── */
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.parceiro-busca-input').forEach(inputEl => {
        const emId    = inputEl.dataset.emid;
        const target  = inputEl.dataset.target;
        const hiddenEl = document.querySelector(`[name="${target}"]`);
        if (emId && hiddenEl) {
            initAutocompleteParceiro(inputEl, hiddenEl, emId);
        }
    });
});
