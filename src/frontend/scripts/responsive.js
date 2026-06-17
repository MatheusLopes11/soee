/* ══════════════════════════════════════════════════════════════
   responsive.js — SOEE · Hambúrguer universal para dashboards
   Inclua este script em todos os dashboards que tenham .sidebar
══════════════════════════════════════════════════════════════ */

(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {
    var sidebar = document.querySelector('.sidebar');
    if (!sidebar) return; // só age se houver sidebar

    /* ── Cria overlay ── */
    var overlay = document.createElement('div');
    overlay.className = 'sidebar-overlay';
    document.body.appendChild(overlay);

    /* ── Cria botão hambúrguer ── */
    var btn = document.createElement('button');
    btn.className = 'sidebar-toggle-mobile';
    btn.setAttribute('aria-label', 'Abrir menu lateral');
    btn.innerHTML = '<i class="fas fa-bars"></i>';
    document.body.appendChild(btn);

    /* ── Abre/fecha sidebar ── */
    function abrirSidebar() {
      sidebar.classList.add('open', 'aberta');
      overlay.classList.add('ativa');
      btn.innerHTML = '<i class="fas fa-times"></i>';
      btn.setAttribute('aria-label', 'Fechar menu lateral');
      document.body.style.overflow = 'hidden';
    }

    function fecharSidebar() {
      sidebar.classList.remove('open', 'aberta');
      overlay.classList.remove('ativa');
      btn.innerHTML = '<i class="fas fa-bars"></i>';
      btn.setAttribute('aria-label', 'Abrir menu lateral');
      document.body.style.overflow = '';
    }

    btn.addEventListener('click', function () {
      var estaAberta = sidebar.classList.contains('open') || sidebar.classList.contains('aberta');
      estaAberta ? fecharSidebar() : abrirSidebar();
    });

    overlay.addEventListener('click', fecharSidebar);

    /* Fecha ao clicar em link da sidebar no mobile */
    sidebar.querySelectorAll('a, .sidebar-item').forEach(function (el) {
      el.addEventListener('click', function () {
        if (window.innerWidth <= 960) fecharSidebar();
      });
    });

    /* Fecha ao redimensionar para desktop */
    window.addEventListener('resize', function () {
      if (window.innerWidth > 960) fecharSidebar();
    });

    /* ── Swipe para fechar (touch) ── */
    var touchStartX = 0;
    sidebar.addEventListener('touchstart', function (e) {
      touchStartX = e.touches[0].clientX;
    }, { passive: true });
    sidebar.addEventListener('touchend', function (e) {
      var dx = e.changedTouches[0].clientX - touchStartX;
      if (dx < -60) fecharSidebar(); // swipe para esquerda fecha
    }, { passive: true });

    /* ── Swipe da borda esquerda para abrir ── */
    document.addEventListener('touchstart', function (e) {
      touchStartX = e.touches[0].clientX;
    }, { passive: true });
    document.addEventListener('touchend', function (e) {
      var dx = e.changedTouches[0].clientX - touchStartX;
      var estaAberta = sidebar.classList.contains('open') || sidebar.classList.contains('aberta');
      if (touchStartX < 24 && dx > 60 && !estaAberta) abrirSidebar(); // swipe borda esq
    }, { passive: true });
  });
})();