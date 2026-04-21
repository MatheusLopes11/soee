/* ═══════════════════════════════════════════════════════════
   classificacao.js — SOEE · Página de Campeonato
═══════════════════════════════════════════════════════════ */
'use strict';

/* ── LOADER ── */
function esconderLoader() {
    const l = document.getElementById('loader');
    if (l) l.classList.add('hide');
}
window.addEventListener('load', () => setTimeout(esconderLoader, 1500));
setTimeout(esconderLoader, 3000);

/* ── CURSOR ── */
const dot  = document.getElementById('cursorDot');
const ring = document.getElementById('cursorRing');
let mx = 0, my = 0, rx = 0, ry = 0;
if (dot && ring) {
    document.addEventListener('mousemove', e => {
        mx = e.clientX; my = e.clientY;
        dot.style.left = mx + 'px'; dot.style.top = my + 'px';
    });
    (function animRing() {
        rx += (mx - rx) * 0.12; ry += (my - ry) * 0.12;
        ring.style.left = rx + 'px'; ring.style.top = ry + 'px';
        requestAnimationFrame(animRing);
    })();
}

/* ── TEMA ── */
const html     = document.documentElement;
const btnTema  = document.getElementById('toggleTema');
const icoTema  = document.getElementById('iconeTema');

function setTheme(t) {
    html.setAttribute('data-theme', t);
    localStorage.setItem('theme', t);
    if (icoTema) icoTema.className = t === 'dark' ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
}
setTheme(localStorage.getItem('theme') || 'light');
btnTema?.addEventListener('click', () => setTheme(html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark'));

/* ── TABS ── */
function trocarTab(btn, tabId) {
    document.querySelectorAll('.tab').forEach(b => b.classList.remove('ativo'));
    document.querySelectorAll('.tab-conteudo').forEach(c => c.classList.remove('ativo'));
    btn.classList.add('ativo');
    const alvo = document.getElementById('tab-' + tabId);
    if (alvo) {
        alvo.classList.add('ativo');
        setTimeout(setupReveal, 60);
        if (tabId === 'grupos') setTimeout(animarPontos, 120);
    }
}

/* ── REVEAL ── */
function setupReveal() {
    const obs = new IntersectionObserver(entries => {
        entries.forEach(e => {
            if (e.isIntersecting) { e.target.classList.add('visible'); obs.unobserve(e.target); }
        });
    }, { threshold: 0.07 });
    document.querySelectorAll('.reveal:not(.visible)').forEach(el => obs.observe(el));
}

/* ── ANIMAÇÃO DE PONTOS ── */
function animarPontos() {
    document.querySelectorAll('.tab-conteudo.ativo .pts[data-target]').forEach(el => {
        const alvo = parseInt(el.getAttribute('data-target'), 10);
        if (isNaN(alvo)) return;
        let atual = 0;
        const passo = Math.max(1, Math.ceil(alvo / 16));
        el.textContent = '0';
        const timer = setInterval(() => {
            atual = Math.min(atual + passo, alvo);
            el.textContent = atual;
            if (atual >= alvo) clearInterval(timer);
        }, 40);
    });
}

/* ── TOOLTIPS NAS COLUNAS ── */
function setupTooltips() {
    document.querySelectorAll('.grupo-tabela th[title]').forEach(th => {
        th.style.cursor = 'help';
        th.addEventListener('mouseenter', function () {
            const tip = document.createElement('div');
            tip.className = '_soee_tip';
            tip.textContent = this.title;
            Object.assign(tip.style, {
                position: 'fixed', background: '#1e293b', color: '#fff',
                fontSize: '.72rem', padding: '4px 10px', borderRadius: '6px',
                pointerEvents: 'none', zIndex: '9999', whiteSpace: 'nowrap',
                boxShadow: '0 4px 12px rgba(0,0,0,.2)',
            });
            document.body.appendChild(tip);
            const rect = this.getBoundingClientRect();
            tip.style.left = rect.left + rect.width / 2 - tip.offsetWidth / 2 + 'px';
            tip.style.top  = rect.top - tip.offsetHeight - 6 + 'px';
        });
        th.addEventListener('mouseleave', () => {
            document.querySelectorAll('._soee_tip').forEach(t => t.remove());
        });
    });
}

/* ── SIDEBAR MOBILE ── */
const sidebar = document.getElementById('sidebar');
const btnSide = document.getElementById('sidebarToggle');
if (btnSide) btnSide.addEventListener('click', () => sidebar?.classList.toggle('aberta'));
document.addEventListener('click', e => {
    if (!sidebar || window.innerWidth > 960) return;
    if (sidebar.classList.contains('aberta') && !sidebar.contains(e.target) && e.target !== btnSide)
        sidebar.classList.remove('aberta');
});
document.querySelectorAll('.sidebar-item').forEach(item =>
    item.addEventListener('click', () => { if (window.innerWidth <= 960) sidebar?.classList.remove('aberta'); })
);

/* ── SCROLL SIDEBAR AO ITEM ATIVO ── */
(function() {
    const item = document.querySelector('.sidebar-item.ativo');
    if (item && sidebar) sidebar.scrollTop = item.offsetTop - sidebar.clientHeight / 2;
})();

/* ── INIT ── */
document.addEventListener('DOMContentLoaded', () => {
    setupReveal();
    setupTooltips();
    setTimeout(animarPontos, 1700);
});