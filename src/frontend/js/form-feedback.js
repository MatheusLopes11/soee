/* ─── LOADER ─── */
  window.addEventListener('load', () => {
    setTimeout(() => document.getElementById('loader').classList.add('hide'), 1500);
  });

  /* ─── CURSOR ─── */
  const dot  = document.getElementById('cursorDot');
  const ring = document.getElementById('cursorRing');
  document.addEventListener('mousemove', e => {
    dot.style.left  = ring.style.left  = e.clientX + 'px';
    dot.style.top   = ring.style.top   = e.clientY + 'px';
  });

  /* ─── TEMA ─── */
  const toggleTema = document.getElementById('toggleTema');
  const html       = document.documentElement;
  const iconTema   = toggleTema.querySelector('i');
  const temaSalvo  = localStorage.getItem('soee-tema') || 'light';
  html.setAttribute('data-theme', temaSalvo);
  iconTema.className = temaSalvo === 'dark' ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
  toggleTema.addEventListener('click', () => {
    const atual  = html.getAttribute('data-theme');
    const novo   = atual === 'dark' ? 'light' : 'dark';
    html.setAttribute('data-theme', novo);
    localStorage.setItem('soee-tema', novo);
    iconTema.className = novo === 'dark' ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
  });

  /* ─── TOAST ─── */
  function mostrarToast(mensagem, tipo = 'sucesso') {
    const t    = document.getElementById('toast');
    const msg  = document.getElementById('toastMsg');
    t.className = `toast ${tipo}`;
    t.querySelector('i').className = tipo === 'sucesso'
      ? 'fa-solid fa-circle-check'
      : 'fa-solid fa-circle-xmark';
    msg.textContent = mensagem;
    t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), 4000);
  }