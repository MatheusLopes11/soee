/* ─────────────────────────────────────────
   home.js — SOEE
───────────────────────────────────────── */


function esconderLoader() {
  document.getElementById('loader').classList.add('hide');
}

// Garante que o loader some mesmo se algum recurso falhar
window.addEventListener('load', () => setTimeout(esconderLoader, 1500));
document.addEventListener('DOMContentLoaded', () => setTimeout(esconderLoader, 1600));
setTimeout(esconderLoader, 3000); // failsafe: some em até 3s de qualquer forma


const dot  = document.getElementById('cursorDot');
const ring = document.getElementById('cursorRing');
let mouseX = 0, mouseY = 0;
let ringX  = 0, ringY  = 0;

document.addEventListener('mousemove', e => {
  mouseX = e.clientX;
  mouseY = e.clientY;
  dot.style.left = mouseX + 'px';
  dot.style.top  = mouseY + 'px';
});

function animateRing() {
  ringX += (mouseX - ringX) * 0.12;
  ringY += (mouseY - ringY) * 0.12;
  ring.style.left = ringX + 'px';
  ring.style.top  = ringY + 'px';
  requestAnimationFrame(animateRing);
}
animateRing();

const reveals  = document.querySelectorAll('.reveal');
const observer = new IntersectionObserver(entries => {
  entries.forEach(e => {
    if (e.isIntersecting) {
      e.target.classList.add('visible');
      observer.unobserve(e.target);
    }
  });
}, { threshold: 0.12 });

reveals.forEach(el => observer.observe(el));

const toggleTheme = document.getElementById('toggle-theme');
const icon        = toggleTheme.querySelector('i');

function setTheme(theme) {
  document.documentElement.setAttribute('data-theme', theme);
  localStorage.setItem('theme', theme);
  icon.className = theme === 'dark' ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
}

setTheme(localStorage.getItem('theme') || 'light');

toggleTheme.addEventListener('click', () => {
  const atual = document.documentElement.getAttribute('data-theme');
  setTheme(atual === 'dark' ? 'light' : 'dark');
});

function animateCount(el, target, suffix = '') {
  let current = 0;
  const step  = target / 40;
  const timer = setInterval(() => {
    current += step;
    if (current >= target) {
      current = target;
      clearInterval(timer);
    }
    el.textContent = Math.floor(current) + suffix;
  }, 30);
}

setTimeout(() => {
  const stats = document.querySelectorAll('.hero-stat strong');
  if (stats[0]) animateCount(stats[0], 4,   '+');
  if (stats[1]) animateCount(stats[1], 100, '%');
}, 1600);

window.addEventListener('scroll', () => {
  const scrolled = window.scrollY;
  const heroBg   = document.querySelector('.hero-bg');
  const heroGrid = document.querySelector('.hero-grid');
  if (heroBg)   heroBg.style.transform   = `translateY(${scrolled * 0.3}px)`;
  if (heroGrid) heroGrid.style.transform = `translateY(${scrolled * 0.15}px)`;
});