/* ═══════════════════════════════════════════════════════════
   classificacao.js — SOEE · Campeonato
   Responsável por: tabs, loader, cursor, tema, reveal,
   animação de pontos, bracket hover, sidebar mobile,
   eliminação animada, confete campeão
═══════════════════════════════════════════════════════════ */

(function () {
    'use strict';

    /* ═══════════════════════════════════════
       1. LOADER
    ═══════════════════════════════════════ */
    window.addEventListener('load', function () {
        var loader = document.getElementById('loader');
        if (!loader) return;
        setTimeout(function () {
            loader.classList.add('hide');
        }, 1200);
    });

    /* ═══════════════════════════════════════
       2. CURSOR PERSONALIZADO
    ═══════════════════════════════════════ */
    var dot  = document.getElementById('cursorDot');
    var ring = document.getElementById('cursorRing');

    if (dot && ring) {
        var mouseX = 0, mouseY = 0;
        var ringX  = 0, ringY  = 0;
        var raf;

        document.addEventListener('mousemove', function (e) {
            mouseX = e.clientX;
            mouseY = e.clientY;
            dot.style.left = mouseX + 'px';
            dot.style.top  = mouseY + 'px';
        });

        function animateRing() {
            ringX += (mouseX - ringX) * 0.14;
            ringY += (mouseY - ringY) * 0.14;
            ring.style.left = ringX + 'px';
            ring.style.top  = ringY + 'px';
            raf = requestAnimationFrame(animateRing);
        }
        animateRing();

        // Expande ring sobre elementos clicáveis
        document.addEventListener('mouseover', function (e) {
            if (e.target.closest('a, button, .sidebar-item, .tab, .bracket-jogo, .grupo-card')) {
                ring.classList.add('expanded');
            }
        });
        document.addEventListener('mouseout', function (e) {
            if (e.target.closest('a, button, .sidebar-item, .tab, .bracket-jogo, .grupo-card')) {
                ring.classList.remove('expanded');
            }
        });

        // Oculta cursor custom ao sair da janela
        document.addEventListener('mouseleave', function () {
            dot.style.opacity  = '0';
            ring.style.opacity = '0';
        });
        document.addEventListener('mouseenter', function () {
            dot.style.opacity  = '1';
            ring.style.opacity = '1';
        });
    }

    /* ═══════════════════════════════════════
       3. TEMA ESCURO / CLARO
    ═══════════════════════════════════════ */
    var btnTema   = document.getElementById('toggleTema');
    var icTema    = document.getElementById('iconeTema');
    var htmlEl    = document.documentElement;

    function aplicarTema(tema) {
        htmlEl.setAttribute('data-theme', tema);
        localStorage.setItem('theme', tema);
        if (icTema) {
            icTema.className = tema === 'dark'
                ? 'fa-solid fa-sun'
                : 'fa-solid fa-moon';
        }
    }

    // Inicializa ícone conforme tema já ativo
    (function () {
        var atual = htmlEl.getAttribute('data-theme') || 'light';
        aplicarTema(atual);
    })();

    if (btnTema) {
        btnTema.addEventListener('click', function () {
            var atual = htmlEl.getAttribute('data-theme') || 'light';
            aplicarTema(atual === 'dark' ? 'light' : 'dark');
        });
    }

    /* ═══════════════════════════════════════
       4. TABS
    ═══════════════════════════════════════ */
    window.trocarTab = function (btnEl, tabId) {
        // Desativa todos
        document.querySelectorAll('.tab').forEach(function (b) {
            b.classList.remove('ativo');
        });
        document.querySelectorAll('.tab-conteudo').forEach(function (t) {
            t.classList.remove('ativo');
        });

        // Ativa o alvo
        btnEl.classList.add('ativo');
        var alvo = document.getElementById('tab-' + tabId);
        if (alvo) {
            alvo.classList.add('ativo');
            // Reanima reveals da aba recém-aberta
            alvo.querySelectorAll('.reveal').forEach(function (el) {
                el.classList.remove('visible');
            });
            setTimeout(function () { observarReveal(); }, 50);
        }

        // Na aba chaveamento: desenha conectores SVG após render
        if (tabId === 'chaveamento') {
            setTimeout(desenharConectores, 120);
        }
    };

    /* ═══════════════════════════════════════
       5. ANIMAÇÃO REVEAL (scroll)
    ═══════════════════════════════════════ */
    function observarReveal() {
        var elementos = document.querySelectorAll('.reveal:not(.visible)');
        if (!('IntersectionObserver' in window)) {
            elementos.forEach(function (el) { el.classList.add('visible'); });
            return;
        }
        var obs = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    obs.unobserve(entry.target);
                }
            });
        }, { threshold: 0.08, rootMargin: '0px 0px -32px 0px' });

        elementos.forEach(function (el) { obs.observe(el); });
    }

    /* ═══════════════════════════════════════
       6. CONTADOR ANIMADO DE PONTOS
    ═══════════════════════════════════════ */
    function animarPontos() {
        document.querySelectorAll('.pts[data-target]').forEach(function (el) {
            var alvo    = parseInt(el.getAttribute('data-target'), 10) || 0;
            var atual   = 0;
            var duracao = 900;
            var inicio  = null;

            function passo(ts) {
                if (!inicio) inicio = ts;
                var progresso = Math.min((ts - inicio) / duracao, 1);
                var easing    = 1 - Math.pow(1 - progresso, 3); // easeOutCubic
                atual = Math.round(easing * alvo);
                el.textContent = atual;
                if (progresso < 1) requestAnimationFrame(passo);
                else el.textContent = alvo;
            }

            // Só anima quando o elemento fica visível
            if ('IntersectionObserver' in window) {
                var obs = new IntersectionObserver(function (entries) {
                    if (entries[0].isIntersecting) {
                        requestAnimationFrame(passo);
                        obs.disconnect();
                    }
                }, { threshold: 0.2 });
                obs.observe(el);
            } else {
                requestAnimationFrame(passo);
            }
        });
    }

    /* ═══════════════════════════════════════
       7. BRACKET — CONECTORES SVG
       (linhas de "pipes" entre rodadas)
    ═══════════════════════════════════════ */
    function desenharConectores() {
        var root = document.getElementById('bracket-root');
        if (!root) return;

        // Remove SVGs anteriores
        root.querySelectorAll('.bracket-connector-svg').forEach(function (s) {
            s.remove();
        });

        var colunas = root.querySelectorAll('.bracket-coluna');
        colunas.forEach(function (colAtual, idx) {
            var colProx = colunas[idx + 1];
            if (!colProx) return;

            var jogosAtual = colAtual.querySelectorAll('.bracket-jogo');
            var jogosProx  = colProx.querySelectorAll('.bracket-jogo');

            // Para cada par de jogos atuais → 1 jogo na próxima
            for (var i = 0; i < jogosProx.length; i++) {
                var jaA = jogosAtual[i * 2];
                var jaB = jogosAtual[i * 2 + 1];
                var jb  = jogosProx[i];
                if (!jaA || !jaB || !jb) continue;

                var rectA    = jaA.getBoundingClientRect();
                var rectB    = jaB.getBoundingClientRect();
                var rectDest = jb.getBoundingClientRect();
                var rootR    = root.getBoundingClientRect();

                var x1  = rectA.right  - rootR.left;
                var y1a = (rectA.top + rectA.bottom) / 2 - rootR.top;
                var y1b = (rectB.top + rectB.bottom) / 2 - rootR.top;
                var x2  = rectDest.left - rootR.left;
                var y2  = (rectDest.top + rectDest.bottom) / 2 - rootR.top;
                var xm  = (x1 + x2) / 2;

                var svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
                svg.classList.add('bracket-connector-svg');
                svg.style.cssText = [
                    'position:absolute',
                    'left:0', 'top:0',
                    'width:' + root.scrollWidth + 'px',
                    'height:' + root.scrollHeight + 'px',
                    'pointer-events:none',
                    'overflow:visible',
                    'z-index:0',
                ].join(';');

                var path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
                var d = [
                    'M', x1, y1a,
                    'H', xm,
                    'V', y2,
                    'H', x2,
                    'M', x1, y1b,
                    'H', xm,
                ].join(' ');

                path.setAttribute('d', d);
                path.setAttribute('fill', 'none');
                path.setAttribute('stroke', 'var(--borda)');
                path.setAttribute('stroke-width', '1.5');
                path.setAttribute('stroke-dasharray', '200');
                path.setAttribute('stroke-dashoffset', '200');
                path.style.transition = 'stroke-dashoffset .6s ease ' + (idx * 0.12) + 's';

                svg.appendChild(path);
                root.style.position = 'relative';
                root.insertBefore(svg, root.firstChild);

                // Anima (dispara após inserção no DOM)
                requestAnimationFrame(function (p) {
                    return function () {
                        requestAnimationFrame(function () {
                            p.setAttribute('stroke-dashoffset', '0');
                        });
                    };
                }(path));
            }
        });
    }

    /* ═══════════════════════════════════════
       8. BRACKET — HOVER + HIGHLIGHT TIMES
    ═══════════════════════════════════════ */
    function iniciarBracketHover() {
        document.querySelectorAll('.bracket-jogo').forEach(function (card) {
            card.addEventListener('mouseenter', function () {
                var nomes = [];
                card.querySelectorAll('.bracket-time span').forEach(function (s) {
                    var txt = s.textContent.trim();
                    if (txt && txt !== 'A definir') nomes.push(txt);
                });
                if (!nomes.length) return;

                // Destaca no bracket inteiro
                document.querySelectorAll('.bracket-time').forEach(function (row) {
                    var n = row.querySelector('span');
                    if (!n) return;
                    var txt = n.textContent.trim();
                    row.style.transition = 'opacity .2s';
                    row.style.opacity = nomes.includes(txt) ? '1' : '0.38';
                });
            });
            card.addEventListener('mouseleave', function () {
                document.querySelectorAll('.bracket-time').forEach(function (row) {
                    row.style.opacity = '';
                });
            });
        });
    }

    /* ═══════════════════════════════════════
       9. ANIMAÇÃO DE ELIMINAÇÃO / VITÓRIA
       (pode ser chamado externamente se
        resultado for registrado via AJAX)
    ═══════════════════════════════════════ */
    window.animarResultadoPartida = function (idPartida, placarA, placarB) {
        var card = document.querySelector('.bracket-jogo[data-partida="' + idPartida + '"]');
        if (!card) return;

        var times = card.querySelectorAll('.bracket-time');
        if (times.length < 2) return;

        var winIdx  = placarA > placarB ? 0 : 1;
        var loseIdx = winIdx === 0 ? 1 : 0;

        // Flash verde no vencedor
        times[winIdx].classList.add('animar-vitoria');
        // Efeito saída no perdedor
        times[loseIdx].classList.add('animar-eliminacao');

        setTimeout(function () {
            times[winIdx].classList.remove('animar-vitoria');
            times[loseIdx].classList.remove('animar-eliminacao');
        }, 900);
    };

    /* ═══════════════════════════════════════
       10. CAMPEÃO — CONFETE
    ═══════════════════════════════════════ */
    window.dispararConfete = function (nomeVencedor) {
        var banner = document.getElementById('banner-campeao');
        if (banner) {
            banner.style.display = 'flex';
            var nomEl = document.getElementById('campeao-nome');
            if (nomEl) nomEl.textContent = nomeVencedor;
        }
        _gerarConfete();
    };

    function _gerarConfete() {
        var container = document.getElementById('confete-container');
        if (!container) return;
        var cores = ['#ff4d12', '#1e5671', '#22c55e', '#f59e0b', '#2c7da3', '#fff'];
        for (var i = 0; i < 60; i++) {
            (function (i) {
                var peca = document.createElement('div');
                peca.className = 'confete-peca';
                peca.style.cssText = [
                    'left:' + (Math.random() * 100) + '%',
                    'top:-12px',
                    'background:' + cores[Math.floor(Math.random() * cores.length)],
                    'animation-delay:' + (Math.random() * 1.2) + 's',
                    'animation-duration:' + (1.2 + Math.random() * 1.2) + 's',
                    'width:' + (6 + Math.random() * 6) + 'px',
                    'height:' + (6 + Math.random() * 6) + 'px',
                    'border-radius:' + (Math.random() > .5 ? '50%' : '2px'),
                    'transform:rotate(' + (Math.random() * 360) + 'deg)',
                ].join(';');
                container.appendChild(peca);
                setTimeout(function () { peca.remove(); }, 2600);
            }(i));
        }
    }

    /* ═══════════════════════════════════════
       11. SIDEBAR MOBILE
    ═══════════════════════════════════════ */
    var sidebar       = document.getElementById('sidebar');
    var btnSidebar    = document.getElementById('sidebarToggle');
    var overlay;

    function criarOverlay() {
        overlay = document.createElement('div');
        overlay.id = 'sidebar-overlay';
        overlay.style.cssText = [
            'position:fixed', 'inset:0',
            'background:rgba(0,0,0,.38)',
            'z-index:199',
            'opacity:0',
            'transition:opacity .3s',
            'display:none',
        ].join(';');
        document.body.appendChild(overlay);
        overlay.addEventListener('click', fecharSidebar);
    }
    criarOverlay();

    function abrirSidebar() {
        if (!sidebar) return;
        sidebar.classList.add('aberta');
        overlay.style.display = 'block';
        requestAnimationFrame(function () {
            overlay.style.opacity = '1';
        });
        if (btnSidebar) btnSidebar.querySelector('i').className = 'fa-solid fa-xmark';
    }

    function fecharSidebar() {
        if (!sidebar) return;
        sidebar.classList.remove('aberta');
        overlay.style.opacity = '0';
        setTimeout(function () { overlay.style.display = 'none'; }, 300);
        if (btnSidebar) btnSidebar.querySelector('i').className = 'fa-solid fa-bars';
    }

    if (btnSidebar) {
        btnSidebar.addEventListener('click', function () {
            sidebar && sidebar.classList.contains('aberta')
                ? fecharSidebar()
                : abrirSidebar();
        });
    }

    // Fecha sidebar ao redimensionar para desktop
    window.addEventListener('resize', function () {
        if (window.innerWidth > 960) fecharSidebar();
    });

    /* ═══════════════════════════════════════
       12. SCROLL SUAVE PARA TABS STICKY
    ═══════════════════════════════════════ */
    document.querySelectorAll('.tab').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var topbarH = parseInt(
                getComputedStyle(document.documentElement).getPropertyValue('--topbar-h') || '62',
                10
            );
            var tabsBar = document.querySelector('.tabs-bar');
            if (!tabsBar) return;
            var rect = tabsBar.getBoundingClientRect();
            if (rect.top < topbarH + 2) {
                window.scrollTo({
                    top: window.scrollY + rect.top - topbarH,
                    behavior: 'smooth',
                });
            }
        });
    });

    /* ═══════════════════════════════════════
       13. SORT DA TABELA DE GRUPOS
    ═══════════════════════════════════════ */
    document.querySelectorAll('.grupo-tabela thead th').forEach(function (th, idx) {
        if (idx === 0 || idx === 1) return; // pos e nome não são sortáveis
        th.style.cursor = 'pointer';
        th.title = (th.title || '') + ' (clique para ordenar)';
        th._sortAsc = false;

        th.addEventListener('click', function () {
            var tabela = th.closest('.grupo-tabela');
            var tbody  = tabela.querySelector('tbody');
            var linhas = Array.from(tbody.querySelectorAll('tr'));
            var colIdx = Array.from(th.parentElement.children).indexOf(th);
            th._sortAsc = !th._sortAsc;

            // Remove indicadores dos outros headers
            tabela.querySelectorAll('thead th').forEach(function (h) {
                if (h !== th) h.removeAttribute('data-sort');
            });
            th.setAttribute('data-sort', th._sortAsc ? 'asc' : 'desc');

            linhas.sort(function (a, b) {
                var va = parseInt(a.cells[colIdx]?.textContent?.trim() || '0', 10);
                var vb = parseInt(b.cells[colIdx]?.textContent?.trim() || '0', 10);
                return th._sortAsc ? va - vb : vb - va;
            });
            linhas.forEach(function (l) { tbody.appendChild(l); });

            // Reatualiza medalhas de posição
            linhas.forEach(function (tr, i) {
                var badge = tr.querySelector('.pos-badge');
                if (!badge) return;
                badge.className = 'pos-badge' +
                    (i === 0 ? ' ouro' : i === 1 ? ' prata' : i === 2 ? ' bronze' : '');
                badge.textContent = i + 1;
            });
        });
    });

    /* ═══════════════════════════════════════
       14. TOOLTIP MÁGICO (TH com title)
    ═══════════════════════════════════════ */
    var tooltip = document.createElement('div');
    tooltip.id  = 'soee-tooltip';
    tooltip.style.cssText = [
        'position:fixed',
        'background:var(--texto)',
        'color:var(--fundo)',
        'font-size:.71rem',
        'font-weight:600',
        'padding:4px 10px',
        'border-radius:6px',
        'pointer-events:none',
        'z-index:9000',
        'opacity:0',
        'transition:opacity .15s',
        'white-space:nowrap',
        'box-shadow:0 4px 16px rgba(0,0,0,.15)',
    ].join(';');
    document.body.appendChild(tooltip);

    document.querySelectorAll('[title]').forEach(function (el) {
        var txt = el.getAttribute('title');
        if (!txt || txt.length < 2) return;
        el.removeAttribute('title');
        el.setAttribute('data-tip', txt);

        el.addEventListener('mouseenter', function (e) {
            tooltip.textContent = txt;
            tooltip.style.opacity = '1';
        });
        el.addEventListener('mousemove', function (e) {
            tooltip.style.left = (e.clientX + 12) + 'px';
            tooltip.style.top  = (e.clientY - 28) + 'px';
        });
        el.addEventListener('mouseleave', function () {
            tooltip.style.opacity = '0';
        });
    });

    /* ═══════════════════════════════════════
       15. PARTIDA ROW — EXPAND DETALHES
       (apenas quando há local/hora)
    ═══════════════════════════════════════ */
    document.querySelectorAll('.partida-row').forEach(function (row) {
        row.style.cursor = 'pointer';
        var meta = row.querySelector('.pr-meta');
        if (!meta) return;

        row.addEventListener('click', function () {
            var aberto = row.classList.toggle('expandido');
            meta.style.maxHeight = aberto ? meta.scrollHeight + 'px' : '';
        });
    });

    /* ═══════════════════════════════════════
       16. INICIALIZAÇÃO COMPLETA
    ═══════════════════════════════════════ */
    document.addEventListener('DOMContentLoaded', function () {
        // Reveal
        observarReveal();

        // Contagem de pontos
        animarPontos();

        // Bracket hover
        iniciarBracketHover();

        // Conectores do bracket se já estiver visível
        if (document.getElementById('tab-chaveamento')?.classList.contains('ativo')) {
            setTimeout(desenharConectores, 200);
        }

        // Atualiza ícone tema (caso já esteja dark)
        var temaAtual = document.documentElement.getAttribute('data-theme') || 'light';
        if (icTema) {
            icTema.className = temaAtual === 'dark'
                ? 'fa-solid fa-sun'
                : 'fa-solid fa-moon';
        }
    });

})();