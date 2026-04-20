/* ═══════════════════════════════════════════════════════════
   esporte.js — SOEE · Página de Esporte / Campeonato
═══════════════════════════════════════════════════════════ */
'use strict';

/* ── LOADER ──────────────────────────────────────────────── */
function esconderLoader() {
    const l = document.getElementById('loader');
    if (l) l.classList.add('hide');
}
window.addEventListener('load', () => setTimeout(esconderLoader, 1500));
setTimeout(esconderLoader, 3000);

/* ── CURSOR ──────────────────────────────────────────────── */
const dot  = document.getElementById('cursorDot');
const ring = document.getElementById('cursorRing');
let mx = 0, my = 0, rx = 0, ry = 0;

if (dot && ring) {
    document.addEventListener('mousemove', e => {
        mx = e.clientX; my = e.clientY;
        dot.style.left = mx + 'px';
        dot.style.top  = my + 'px';
    });
    (function animRing() {
        rx += (mx - rx) * 0.12;
        ry += (my - ry) * 0.12;
        ring.style.left = rx + 'px';
        ring.style.top  = ry + 'px';
        requestAnimationFrame(animRing);
    })();
}

/* ── TEMA ────────────────────────────────────────────────── */
const html      = document.documentElement;
const btnTema   = document.getElementById('toggleTema');
const iconeTema = document.getElementById('iconeTema');

function setTheme(t) {
    html.setAttribute('data-theme', t);
    localStorage.setItem('theme', t);
    if (iconeTema) iconeTema.className = t === 'dark' ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
}
// tema já foi aplicado inline no <head> para evitar flash
// só sincroniza o ícone:
setTheme(localStorage.getItem('theme') || 'light');

if (btnTema) {
    btnTema.addEventListener('click', () => {
        setTheme(html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark');
    });
}

/* ── TABS ────────────────────────────────────────────────── */
function trocarTab(btnEl, tabId) {
    document.querySelectorAll('.tab').forEach(b => b.classList.remove('ativo'));
    document.querySelectorAll('.tab-conteudo').forEach(c => c.classList.remove('ativo'));
    btnEl.classList.add('ativo');
    const alvo = document.getElementById('tab-' + tabId);
    if (alvo) {
        alvo.classList.add('ativo');
        setTimeout(setupReveal, 60);
        // Anima pontos se for tab de grupos ou tabela-geral
        if (tabId === 'grupos' || tabId === 'tabela-geral') {
            setTimeout(animarPontos, 100);
        }
    }
}

/* ── REVEAL ──────────────────────────────────────────────── */
function setupReveal() {
    const obs = new IntersectionObserver(entries => {
        entries.forEach(e => {
            if (e.isIntersecting) { e.target.classList.add('visible'); obs.unobserve(e.target); }
        });
    }, { threshold: 0.07 });
    document.querySelectorAll('.reveal:not(.visible)').forEach(el => obs.observe(el));
}

document.addEventListener('DOMContentLoaded', () => {
    setupReveal();
    // Scroll da sidebar até o item ativo
    const sidebar  = document.getElementById('sidebar');
    const itemAtivo = document.querySelector('.sidebar-item.ativo');
    if (itemAtivo && sidebar) {
        sidebar.scrollTop = itemAtivo.offsetTop - sidebar.clientHeight / 2;
    }
    // Anima pontos na tab inicial
    setTimeout(animarPontos, 1700);
});

/* ── ANIMAÇÃO DOS PONTOS ─────────────────────────────────── */
function animarPontos() {
    document.querySelectorAll('.tab-conteudo.ativo .pts[data-target]').forEach(el => {
        const alvo = parseInt(el.getAttribute('data-target'), 10);
        if (isNaN(alvo)) return;
        let atual = 0;
        const passo = Math.max(1, Math.ceil(alvo / 18));
        el.textContent = '0';
        const timer = setInterval(() => {
            atual = Math.min(atual + passo, alvo);
            el.textContent = atual;
            if (atual >= alvo) clearInterval(timer);
        }, 40);
    });
}

/* ── SIDEBAR MOBILE ──────────────────────────────────────── */
const sidebar       = document.getElementById('sidebar');
const sidebarToggle = document.getElementById('sidebarToggle');

if (sidebarToggle && sidebar) {
    sidebarToggle.addEventListener('click', () => sidebar.classList.toggle('aberta'));
}
document.addEventListener('click', e => {
    if (!sidebar || window.innerWidth > 960) return;
    if (sidebar.classList.contains('aberta') &&
        !sidebar.contains(e.target) &&
        e.target !== sidebarToggle) {
        sidebar.classList.remove('aberta');
    }
});
// Fecha ao navegar (mobile)
document.querySelectorAll('.sidebar-item').forEach(item => {
    item.addEventListener('click', () => {
        if (window.innerWidth <= 960) sidebar?.classList.remove('aberta');
    });
});

/* ── HIGHLIGHT ATIVO NA TABELA QUANDO SCROLL ─────────────── */
document.querySelectorAll('.grupo-tabela tbody tr').forEach(tr => {
    tr.addEventListener('mouseenter', () => tr.style.background = 'rgba(255,77,18,.04)');
    tr.addEventListener('mouseleave', () => tr.style.background = '');
});

/* ── TOOLTIP SIMPLES NAS COLUNAS ─────────────────────────── */
document.querySelectorAll('.grupo-tabela th[title]').forEach(th => {
    th.style.cursor = 'help';
    th.addEventListener('mouseenter', function(e) {
        const tip = document.createElement('div');
        tip.className = '_tip';
        tip.textContent = this.title;
        tip.style.cssText = 'position:fixed;background:#1e293b;color:#fff;font-size:.72rem;padding:4px 10px;border-radius:6px;pointer-events:none;z-index:9999;white-space:nowrap;box-shadow:0 4px 12px rgba(0,0,0,.2);';
        document.body.appendChild(tip);
        const rect = this.getBoundingClientRect();
        tip.style.left = rect.left + rect.width / 2 - tip.offsetWidth / 2 + 'px';
        tip.style.top  = rect.top - tip.offsetHeight - 6 + 'px';
    });
    th.addEventListener('mouseleave', () => {
        document.querySelectorAll('._tip').forEach(t => t.remove());
    });
});