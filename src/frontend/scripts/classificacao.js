/* ═══════════════════════════════════════════════════════════
   esporte.js — SOEE · Página de Esporte / Campeonato
═══════════════════════════════════════════════════════════ */
'use strict';

/* ── LOADER ─────────────────────────────────────────────── */
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
const html       = document.documentElement;
const btnTema    = document.getElementById('toggleTema');
const iconeTema  = document.getElementById('iconeTema');

function setTheme(t) {
    html.setAttribute('data-theme', t);
    localStorage.setItem('theme', t);
    if (iconeTema) {
        iconeTema.className = t === 'dark' ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
    }
}

// Aplica tema salvo imediatamente
setTheme(localStorage.getItem('theme') || 'light');

if (btnTema) {
    btnTema.addEventListener('click', () => {
        const atual = html.getAttribute('data-theme');
        setTheme(atual === 'dark' ? 'light' : 'dark');
    });
}

/* ── TABS ────────────────────────────────────────────────── */
function trocarTab(btnEl, tabId) {
    // Remove ativo de todos os botões e conteúdos
    document.querySelectorAll('.tab').forEach(b => b.classList.remove('ativo'));
    document.querySelectorAll('.tab-conteudo').forEach(c => c.classList.remove('ativo'));

    // Ativa o clicado
    btnEl.classList.add('ativo');
    const alvo = document.getElementById('tab-' + tabId);
    if (alvo) {
        alvo.classList.add('ativo');
        // Dispara reveal para elementos dentro da tab recém aberta
        setTimeout(setupReveal, 80);
    }
}

/* ── REVEAL POR SCROLL ───────────────────────────────────── */
function setupReveal() {
    const obs = new IntersectionObserver(entries => {
        entries.forEach(e => {
            if (e.isIntersecting) {
                e.target.classList.add('visible');
                obs.unobserve(e.target);
            }
        });
    }, { threshold: 0.08 });

    document.querySelectorAll('.reveal:not(.visible)').forEach(el => obs.observe(el));
}
document.addEventListener('DOMContentLoaded', setupReveal);

/* ── SIDEBAR MOBILE ─────────────────────────────────────── */
const sidebar       = document.getElementById('sidebar');
const sidebarToggle = document.getElementById('sidebarToggle');

// Fecha sidebar ao clicar fora (mobile)
document.addEventListener('click', e => {
    if (!sidebar) return;
    if (window.innerWidth > 900) return;
    if (sidebar.classList.contains('aberta') &&
        !sidebar.contains(e.target) &&
        e.target !== sidebarToggle &&
        !sidebarToggle?.contains(e.target)) {
        sidebar.classList.remove('aberta');
    }
});

// Fecha sidebar ao clicar em um item (mobile)
document.querySelectorAll('.sidebar-item').forEach(item => {
    item.addEventListener('click', () => {
        if (window.innerWidth <= 900) {
            sidebar?.classList.remove('aberta');
        }
    });
});

/* ── HIGHLIGHT ESPORTE ATIVO NA SIDEBAR ─────────────────── */
(function() {
    const params  = new URLSearchParams(window.location.search);
    const idAtual = params.get('id');
    if (!idAtual) return;

    // Garante scroll da sidebar até o item ativo
    const itemAtivo = document.querySelector('.sidebar-item.ativo');
    if (itemAtivo && sidebar) {
        const itemTop    = itemAtivo.offsetTop;
        const sidebarMid = sidebar.clientHeight / 2;
        sidebar.scrollTop = itemTop - sidebarMid;
    }
})();

/* ── EFEITO HOVER NOS JOGOS DO BRACKET ──────────────────── */
document.querySelectorAll('.bracket-jogo').forEach(jogo => {
    jogo.addEventListener('mouseenter', () => {
        const times = jogo.querySelectorAll('.bracket-time-avatar');
        times.forEach(a => {
            a.style.transform = 'scale(1.08)';
        });
    });
    jogo.addEventListener('mouseleave', () => {
        const times = jogo.querySelectorAll('.bracket-time-avatar');
        times.forEach(a => {
            a.style.transform = '';
        });
    });
});

/* ── ANIMAÇÃO DOS PONTOS NA TABELA ───────────────────────── */
function animarContadores() {
    document.querySelectorAll('.pts').forEach(el => {
        const alvo = parseInt(el.textContent, 10);
        if (isNaN(alvo) || alvo === 0) return;
        let atual = 0;
        const passo = Math.max(1, Math.ceil(alvo / 20));
        el.textContent = '0';
        const timer = setInterval(() => {
            atual = Math.min(atual + passo, alvo);
            el.textContent = atual;
            if (atual >= alvo) clearInterval(timer);
        }, 40);
    });
}

// Dispara animação dos contadores quando tab grupos está ativa
const tabGrupos = document.querySelector('[data-tab="grupos"]');
if (tabGrupos && tabGrupos.classList.contains('ativo')) {
    setTimeout(animarContadores, 1600);
}

// Também dispara ao trocar para a tab de grupos
document.querySelectorAll('.tab[data-tab="grupos"]').forEach(btn => {
    btn.addEventListener('click', () => setTimeout(animarContadores, 200));
});

/* ── TOPBAR: ESCONDER AO ROLAR PARA BAIXO ───────────────── */
(function() {
    let lastY = 0;
    const topbar = document.querySelector('.topbar');
    if (!topbar) return;

    window.addEventListener('scroll', () => {
        const currentY = window.scrollY;
        if (currentY > lastY && currentY > 80) {
            // Rolando para baixo — mantém topbar visível (fixed)
            // poderia esconder: topbar.style.transform = 'translateY(-100%)';
        } else {
            topbar.style.transform = 'translateY(0)';
        }
        lastY = currentY;
    }, { passive: true });
})();

/* ── TOOLTIP NAS COLUNAS DA TABELA ──────────────────────── */
document.querySelectorAll('.grupo-tabela th[title]').forEach(th => {
    th.style.cursor = 'help';
});