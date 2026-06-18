/**
 * acessibilidade.js — Barra de acessibilidade SOEE
 * A barra é injetada automaticamente via JS — sem precisar de include PHP em cada página.
 * Recursos: tamanho de fonte, alto contraste, fonte dislexia, espaçamento.
 * Todos os estados persistem via localStorage.
 */

(function () {
    const html = document.documentElement;

    // ── Injeção automática da barra no topo do <body> ─────────────────────────

    function injetarBarra() {
        const barra = document.createElement('div');
        barra.className = 'barra-acessibilidade';
        barra.setAttribute('role', 'region');
        barra.setAttribute('aria-label', 'Barra de acessibilidade');

        barra.innerHTML = `
            <div class="barra-acessibilidade-container">
                <span class="barra-acessibilidade-label">Acessibilidade</span>

                <button id="btnFonteMenos"  class="btn-acesso" aria-label="Diminuir tamanho da fonte">A-</button>
                <button id="btnFonteReset"  class="btn-acesso" aria-label="Redefinir tamanho da fonte">A</button>
                <button id="btnFonteMais"   class="btn-acesso" aria-label="Aumentar tamanho da fonte">A+</button>

                <span class="barra-sep" aria-hidden="true"></span>

                <button id="btnContraste"   class="btn-acesso" aria-label="Alternar alto contraste" aria-pressed="false">
                    <i class="fa-solid fa-circle-half-stroke" aria-hidden="true"></i> Contraste
                </button>

                <span class="barra-sep" aria-hidden="true"></span>

                <button id="btnDislexia"    class="btn-acesso" aria-label="Alternar fonte para dislexia" aria-pressed="false">
                    <i class="fa-solid fa-font" aria-hidden="true"></i> Dislexia
                </button>

                <span class="barra-sep" aria-hidden="true"></span>

                <button id="btnEspacamento" class="btn-acesso" aria-label="Alternar espaçamento aumentado" aria-pressed="false">
                    <i class="fa-solid fa-text-height" aria-hidden="true"></i> Espaçamento
                </button>
            </div>
        `;

        // Insere como primeiro filho do <body>
        document.body.insertBefore(barra, document.body.firstChild);
    }

    // ── Tamanho de fonte ──────────────────────────────────────────────────────

    const FONT_BASE = 16;
    const FONT_STEP = 2;
    const FONT_MIN  = 12;
    const FONT_MAX  = 24;

    let fonteAtual = parseInt(localStorage.getItem('acesso-fonte')) || FONT_BASE;

    function aplicarFonte(tamanho) {
        fonteAtual = Math.min(FONT_MAX, Math.max(FONT_MIN, tamanho));
        html.style.fontSize = fonteAtual + 'px';
        localStorage.setItem('acesso-fonte', fonteAtual);
        atualizarBotoesFonte();
    }

    function atualizarBotoesFonte() {
        const btnMenos = document.getElementById('btnFonteMenos');
        const btnReset = document.getElementById('btnFonteReset');
        const btnMais  = document.getElementById('btnFonteMais');

        if (btnMenos) btnMenos.disabled = (fonteAtual <= FONT_MIN);
        if (btnMais)  btnMais.disabled  = (fonteAtual >= FONT_MAX);
        if (btnReset) btnReset.classList.toggle('ativo', fonteAtual !== FONT_BASE);
    }

    // ── Alto contraste ────────────────────────────────────────────────────────

    let contraste = localStorage.getItem('acesso-contraste') || 'normal';

    function aplicarContraste(valor) {
        contraste = valor;
        html.setAttribute('data-contraste', contraste);
        localStorage.setItem('acesso-contraste', contraste);
        atualizarBotaoContraste();
    }

    function atualizarBotaoContraste() {
        const btn = document.getElementById('btnContraste');
        if (!btn) return;
        const ativo = contraste === 'alto';
        btn.classList.toggle('ativo', ativo);
        btn.setAttribute('aria-pressed', ativo ? 'true' : 'false');
        btn.title = ativo ? 'Desativar alto contraste' : 'Ativar alto contraste';
    }

    // ── Fonte dislexia ────────────────────────────────────────────────────────

    let fonteDislexia = localStorage.getItem('acesso-dislexia') || 'padrao';

    function aplicarDislexia(valor) {
        fonteDislexia = valor;
        html.setAttribute('data-fonte', fonteDislexia);
        localStorage.setItem('acesso-dislexia', fonteDislexia);
        atualizarBotaoDislexia();
    }

    function atualizarBotaoDislexia() {
        const btn = document.getElementById('btnDislexia');
        if (!btn) return;
        const ativo = fonteDislexia === 'dislexia';
        btn.classList.toggle('ativo', ativo);
        btn.setAttribute('aria-pressed', ativo ? 'true' : 'false');
        btn.title = ativo ? 'Desativar fonte para dislexia' : 'Ativar fonte para dislexia (OpenDyslexic)';
    }

    // ── Espaçamento ───────────────────────────────────────────────────────────

    let espacamento = localStorage.getItem('acesso-espacamento') || 'normal';

    function aplicarEspacamento(valor) {
        espacamento = valor;
        html.setAttribute('data-espacamento', espacamento);
        localStorage.setItem('acesso-espacamento', espacamento);
        atualizarBotaoEspacamento();
    }

    function atualizarBotaoEspacamento() {
        const btn = document.getElementById('btnEspacamento');
        if (!btn) return;
        const ativo = espacamento === 'aumentado';
        btn.classList.toggle('ativo', ativo);
        btn.setAttribute('aria-pressed', ativo ? 'true' : 'false');
        btn.title = ativo ? 'Desativar espaçamento aumentado' : 'Ativar espaçamento aumentado';
    }

    // ── Inicialização ─────────────────────────────────────────────────────────

    function init() {
        injetarBarra();

        // Aplica estados salvos no localStorage
        aplicarFonte(fonteAtual);
        aplicarContraste(contraste);
        aplicarDislexia(fonteDislexia);
        aplicarEspacamento(espacamento);

        // Eventos — tamanho de fonte
        document.getElementById('btnFonteMenos') ?.addEventListener('click', () => aplicarFonte(fonteAtual - FONT_STEP));
        document.getElementById('btnFonteMais')  ?.addEventListener('click', () => aplicarFonte(fonteAtual + FONT_STEP));
        document.getElementById('btnFonteReset') ?.addEventListener('click', () => aplicarFonte(FONT_BASE));

        // Evento — alto contraste
        document.getElementById('btnContraste')   ?.addEventListener('click', () => aplicarContraste(contraste === 'alto' ? 'normal' : 'alto'));

        // Evento — fonte dislexia
        document.getElementById('btnDislexia')    ?.addEventListener('click', () => aplicarDislexia(fonteDislexia === 'dislexia' ? 'padrao' : 'dislexia'));

        // Evento — espaçamento
        document.getElementById('btnEspacamento') ?.addEventListener('click', () => aplicarEspacamento(espacamento === 'aumentado' ? 'normal' : 'aumentado'));
    }

    // Garante que o DOM já existe antes de rodar
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();