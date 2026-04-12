/* ─────────────────────────────────────────
   portal.js — SOEE
   JS exclusivo do portal.
   O inicio.js já cuida de: loader, cursor,
   reveal, tema. Aqui ficam apenas as
   funcionalidades novas desta página.
───────────────────────────────────────── */

/* ── Contador animado das stats do hero ── */
function animarContador(el, alvo, sufixo = '') {
  let atual = 0;
  const passo = alvo / 40;
  const timer = setInterval(() => {
    atual += passo;
    if (atual >= alvo) {
      atual = alvo;
      clearInterval(timer);
    }
    el.textContent = Math.floor(atual) + sufixo;
  }, 30);
}

document.addEventListener('DOMContentLoaded', () => {
  setTimeout(() => {
    const elModalidades = document.getElementById('stat-modalidades');
    const elTimes       = document.getElementById('stat-times');
    const elDigital     = document.getElementById('stat-digital');

    if (elModalidades) animarContador(elModalidades, 4,   '+');
    if (elTimes)       animarContador(elTimes,       12,  '');
    if (elDigital)     animarContador(elDigital,     100, '%');
  }, 1700);
});

/* ── Parallax suave no hero ── */
window.addEventListener('scroll', () => {
  const scrolled = window.scrollY;
  const heroBg   = document.querySelector('.hero-portal .bg');
  const heroGrid = document.querySelector('.hero-portal .grid');
  if (heroBg)   heroBg.style.transform   = `translateY(${scrolled * 0.28}px)`;
  if (heroGrid) heroGrid.style.transform = `translateY(${scrolled * 0.13}px)`;
});
