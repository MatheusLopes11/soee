/* ================================================================
   login.js — SOEE Sistema de Organização Esportiva Escolar
   Responsável por:
     1. Toggle de visibilidade da senha
     2. Avatar reativo ao foco dos inputs
     3. Loading no botão ao submeter o formulário
     4. Partículas animadas no fundo (canvas)
   ================================================================ */

document.addEventListener('DOMContentLoaded', () => {

    /* ── 1. Toggle de senha ─────────────────────────────────── */
    const togglePw = document.getElementById('togglePw');
    const pwInput  = document.getElementById('password');

    if (togglePw && pwInput) {
        togglePw.addEventListener('click', () => {
            const mostrar  = pwInput.type === 'password';
            pwInput.type   = mostrar ? 'text' : 'password';
            togglePw.className = `fa-solid ${mostrar ? 'fa-eye-slash' : 'fa-eye'} toggle-pw`;
        });
    }

    /* ── 2. Avatar reativo ao foco ──────────────────────────── */
    const avatarEl   = document.getElementById('avatarIcon');
    const avatarIcon = avatarEl ? avatarEl.querySelector('i') : null;

    const inputUsername = document.getElementById('username');
    const inputPassword = document.getElementById('password');

    if (avatarIcon) {
        inputUsername?.addEventListener('focus', () => {
            avatarIcon.className = 'fa-solid fa-user-pen';
        });

        inputPassword?.addEventListener('focus', () => {
            avatarIcon.className = 'fa-solid fa-user-shield';
        });

        document.querySelectorAll('.form-group input').forEach(el => {
            el.addEventListener('blur', () => {
                avatarIcon.className = 'fa-solid fa-user';
            });
        });
    }

    /* ── 3. Loading no botão ao submeter ────────────────────── */
    const form   = document.getElementById('loginForm');
    const btnLogin = document.getElementById('btnLogin');

    form?.addEventListener('submit', () => {
        const u = inputUsername?.value.trim();
        const p = inputPassword?.value.trim();

        // Só ativa o loading se os campos estiverem preenchidos
        // (caso contrário o PHP vai tratar o erro vazio)
        if (u && p && btnLogin) {
            btnLogin.innerHTML  = '<i class="fa-solid fa-spinner fa-spin"></i> ENTRANDO...';
            btnLogin.disabled   = true;
        }
    });

    /* ── 4. Partículas no canvas ────────────────────────────── */
    const canvas = document.getElementById('particles');
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    let W, H, particles;

    /* Redimensiona o canvas para ocupar a tela inteira */
    function resize() {
        W = canvas.width  = window.innerWidth;
        H = canvas.height = window.innerHeight;
    }

    /* Cria as partículas com posição e velocidade aleatórias */
    function initParticles() {
        particles = Array.from({ length: 55 }, () => ({
            x:  Math.random() * W,
            y:  Math.random() * H,
            r:  Math.random() * 1.8 + 0.4,
            vx: (Math.random() - 0.5) * 0.35,
            vy: (Math.random() - 0.5) * 0.35,
            o:  Math.random() * 0.35 + 0.05
        }));
    }

    /* Loop de animação — desenha partículas e linhas de conexão */
    function drawParticles() {
        ctx.clearRect(0, 0, W, H);

        /* Desenha cada partícula */
        particles.forEach(p => {
            ctx.beginPath();
            ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
            ctx.fillStyle = `rgba(46,138,184,${p.o})`;
            ctx.fill();

            /* Move a partícula */
            p.x += p.vx;
            p.y += p.vy;

            /* Rebote nas bordas (teletransporte para o lado oposto) */
            if (p.x < 0) p.x = W;
            if (p.x > W) p.x = 0;
            if (p.y < 0) p.y = H;
            if (p.y > H) p.y = 0;
        });

        /* Desenha linhas entre partículas próximas */
        for (let i = 0; i < particles.length; i++) {
            for (let j = i + 1; j < particles.length; j++) {
                const dx   = particles[i].x - particles[j].x;
                const dy   = particles[i].y - particles[j].y;
                const dist = Math.sqrt(dx * dx + dy * dy);

                if (dist < 100) {
                    ctx.beginPath();
                    ctx.moveTo(particles[i].x, particles[i].y);
                    ctx.lineTo(particles[j].x, particles[j].y);
                    ctx.strokeStyle = `rgba(46,138,184,${0.12 * (1 - dist / 100)})`;
                    ctx.lineWidth   = 0.6;
                    ctx.stroke();
                }
            }
        }

        requestAnimationFrame(drawParticles);
    }

    /* Inicializa e escuta redimensionamento */
    resize();
    initParticles();
    drawParticles();

    window.addEventListener('resize', () => {
        resize();
        initParticles();
    });

});