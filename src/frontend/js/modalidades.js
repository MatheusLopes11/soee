/* ─────────────────────────────────────────
   modalidades.js — SOEE
───────────────────────────────────────── */

/* ─── FILTRO DE CATEGORIAS ─── */
const filtros = document.querySelectorAll('.filtro-btn');
const cards   = document.querySelectorAll('.modalidade-card');

filtros.forEach(btn => {
  btn.addEventListener('click', () => {
    // Atualiza botão ativo
    filtros.forEach(b => b.classList.remove('ativo'));
    btn.classList.add('ativo');

    const filtro = btn.dataset.filtro;

    cards.forEach((card, i) => {
      const categoria = card.dataset.categoria;
      const mostrar   = filtro === 'todos' || categoria === filtro;

      if (mostrar) {
        card.classList.remove('oculto');
        // Reaplica animação de entrada escalonada
        card.style.animationDelay = `${i * 0.08}s`;
        card.classList.remove('visible');
        // Pequeno timeout para a transição funcionar
        setTimeout(() => card.classList.add('visible'), 20);
      } else {
        card.classList.add('oculto');
      }
    });
  });
});