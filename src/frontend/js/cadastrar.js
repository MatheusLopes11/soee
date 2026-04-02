/* ═══════════════════════════════════════════════════════════
   cadastrar.js  —  SOEE · Lógica completa do cadastro
═══════════════════════════════════════════════════════════ */

(function () {
  'use strict';

  /* ── Cursor personalizado (reutiliza início.js se carregado) ── */
  const dot  = document.getElementById('cursorDot');
  const ring = document.getElementById('cursorRing');
  if (dot && ring) {
    let rx = 0, ry = 0;
    document.addEventListener('mousemove', e => {
      dot.style.left  = e.clientX + 'px';
      dot.style.top   = e.clientY + 'px';
      rx += (e.clientX - rx) * 0.12;
      ry += (e.clientY - ry) * 0.12;
      ring.style.left = rx + 'px';
      ring.style.top  = ry + 'px';
    });
  }

  /* ── Loader ── */
  window.addEventListener('load', () => {
    const loader = document.getElementById('loader');
    if (loader) setTimeout(() => loader.classList.add('hide'), 900);
  });

  /* ── Tema claro/escuro (herda o botão do header se existir) ── */
  const btnTema = document.getElementById('toggle-theme');
  if (btnTema) {
    btnTema.addEventListener('click', () => {
      const t = document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
      document.documentElement.setAttribute('data-theme', t);
      btnTema.querySelector('i').className = t === 'dark' ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
      localStorage.setItem('soee-theme', t);
    });
    const saved = localStorage.getItem('soee-theme');
    if (saved) {
      document.documentElement.setAttribute('data-theme', saved);
      btnTema.querySelector('i').className = saved === 'dark' ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
    }
  }

  /* ─────────────────────────────────────────
     CAMPO "GÊNERO OUTRO"
  ───────────────────────────────────────── */
  const selectGenero = document.getElementById('opcoes');
  const campoExtra   = document.getElementById('campoExtra');
  if (selectGenero && campoExtra) {
    selectGenero.addEventListener('change', () => {
      const mostrar = selectGenero.value === 'outro';
      campoExtra.style.display = mostrar ? 'flex' : 'none';
    });
  }

  /* ─────────────────────────────────────────
     TOGGLE VISIBILIDADE DE SENHA
  ───────────────────────────────────────── */
  document.querySelectorAll('.toggle-senha').forEach(btn => {
    btn.addEventListener('click', () => {
      const input = btn.closest('.input-wrapper').querySelector('input');
      const icon  = btn.querySelector('i');
      if (!input) return;
      const mostrar = input.type === 'password';
      input.type = mostrar ? 'text' : 'password';
      icon.className = mostrar ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye';
    });
  });

  /* ─────────────────────────────────────────
     MEDIDOR DE FORÇA DA SENHA
  ───────────────────────────────────────── */
  const senhaInput   = document.getElementById('senha');
  const forcaFill    = document.getElementById('forcaFill');
  const forcaTxt     = document.getElementById('forcaTxt');
  const forcaDica    = document.getElementById('forcaDica');
  const reqLen       = document.getElementById('req-len');
  const reqUpper     = document.getElementById('req-upper');
  const reqNum       = document.getElementById('req-num');
  const reqSpecial   = document.getElementById('req-special');

  const niveis = [
    { txt: 'Muito fraca', cor: 'f1', dica: 'Adicione mais caracteres' },
    { txt: 'Fraca',       cor: 'f2', dica: 'Inclua letras maiúsculas' },
    { txt: 'Razoável',    cor: 'f3', dica: 'Quase lá! Adicione símbolos' },
    { txt: 'Forte',       cor: 'f4', dica: 'Senha segura ✓' },
  ];

  function avaliarSenha(v) {
    const checks = {
      len:     v.length >= 8,
      upper:   /[A-Z]/.test(v),
      num:     /[0-9]/.test(v),
      special: /[^A-Za-z0-9]/.test(v),
    };
    const score = Object.values(checks).filter(Boolean).length;
    return { checks, score };
  }

  if (senhaInput) {
    senhaInput.addEventListener('input', () => {
      const v = senhaInput.value;
      if (!v) {
        forcaFill.className = 'forca-fill';
        forcaTxt.textContent = '—';
        forcaDica.textContent = '';
        [reqLen, reqUpper, reqNum, reqSpecial].forEach(r => r && r.classList.remove('ok'));
        return;
      }
      const { checks, score } = avaliarSenha(v);
      const nivel = niveis[score - 1] || niveis[0];
      forcaFill.className = 'forca-fill ' + nivel.cor;
      forcaTxt.textContent = nivel.txt;
      forcaTxt.style.color = score === 4 ? '#16a34a' : score === 3 ? '#3b82f6' : score === 2 ? '#f59e0b' : '#e53e3e';
      forcaDica.textContent = nivel.dica;
      reqLen     && reqLen.classList.toggle('ok',     checks.len);
      reqUpper   && reqUpper.classList.toggle('ok',   checks.upper);
      reqNum     && reqNum.classList.toggle('ok',     checks.num);
      reqSpecial && reqSpecial.classList.toggle('ok', checks.special);
    });
  }

  /* ─────────────────────────────────────────
     VALIDAÇÃO POR CAMPO
  ───────────────────────────────────────── */
  function setEstado(grupoId, estado, msg) {
    const el = document.getElementById(grupoId);
    if (!el) return;
    el.classList.remove('ok', 'erro');
    if (estado) el.classList.add(estado);
    const msgEl = el.querySelector('.campo-msg');
    if (msgEl) msgEl.textContent = msg || '';
  }

  function validarNome() {
    const val = document.getElementById('nome')?.value.trim();
    if (!val) { setEstado('grupo-nome', 'erro', 'Nome é obrigatório'); return false; }
    if (val.length < 3) { setEstado('grupo-nome', 'erro', 'Mínimo 3 caracteres'); return false; }
    if (!/\s/.test(val)) { setEstado('grupo-nome', 'erro', 'Informe nome e sobrenome'); return false; }
    setEstado('grupo-nome', 'ok', 'Ótimo!'); return true;
  }

  function validarEmail() {
    const val = document.getElementById('email')?.value.trim();
    if (!val) { setEstado('grupo-email', 'erro', 'E-mail é obrigatório'); return false; }
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) { setEstado('grupo-email', 'erro', 'E-mail inválido'); return false; }
    setEstado('grupo-email', 'ok', 'E-mail válido!'); return true;
  }

  function validarSenha() {
    const val = senhaInput?.value;
    if (!val) { setEstado('grupo-senha', 'erro', 'Senha é obrigatória'); return false; }
    const { score } = avaliarSenha(val);
    if (score < 2) { setEstado('grupo-senha', 'erro', 'Senha muito fraca'); return false; }
    setEstado('grupo-senha', 'ok', ''); return true;
  }

  function validarConfirma() {
    const s = senhaInput?.value;
    const c = document.getElementById('confirma_senha')?.value;
    if (!c) { setEstado('grupo-confirma', 'erro', 'Confirme sua senha'); return false; }
    if (s !== c) { setEstado('grupo-confirma', 'erro', 'Senhas não conferem'); return false; }
    setEstado('grupo-confirma', 'ok', 'Senhas conferem!'); return true;
  }

  /* Validação em tempo real */
  document.getElementById('nome')?.addEventListener('blur', validarNome);
  document.getElementById('email')?.addEventListener('blur', validarEmail);
  document.getElementById('confirma_senha')?.addEventListener('input', validarConfirma);

  /* ─────────────────────────────────────────
     MULTI-STEP
  ───────────────────────────────────────── */
  let passoAtual = 1;

  function irParaPasso(novo) {
    const atual = document.getElementById('passo' + passoAtual);
    const prox  = document.getElementById('passo' + novo);
    const linhas = document.querySelectorAll('.cad-step-line');
    const passos = document.querySelectorAll('.cad-step');
    if (!prox) return;

    atual.classList.add('hidden');
    prox.classList.remove('hidden');
    passoAtual = novo;

    /* Atualiza step tracker */
    passos.forEach((s, i) => {
      s.classList.remove('active', 'completo');
      const n = i + 1;
      if (n < novo)      s.classList.add('completo');
      else if (n === novo) s.classList.add('active');
    });
    linhas.forEach((l, i) => {
      l.classList.toggle('preenchido', i < novo - 1);
    });

    /* Preenche resumo no passo 3 */
    if (novo === 3) preencherResumo();

    /* Scroll suave ao topo do card */
    document.querySelector('.cad-card')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }

  function preencherResumo() {
    const generoMap = { m: 'Masculino', f: 'Feminino', outro: 'Prefiro não informar / Outro' };
    document.getElementById('r-nome').textContent   = document.getElementById('nome')?.value.trim() || '—';
    document.getElementById('r-email').textContent  = document.getElementById('email')?.value.trim() || '—';
    const gv = document.getElementById('opcoes')?.value;
    const generoOutro = document.getElementById('genero_outro')?.value.trim();
    document.getElementById('r-genero').textContent = gv === 'outro' && generoOutro
      ? generoOutro : (generoMap[gv] || '—');
  }

  /* Botão passo 1 → 2 */
  document.getElementById('btnP1')?.addEventListener('click', () => {
    const ok = validarNome() & validarEmail(); // bitwise: avalia ambos
    if (ok) irParaPasso(2);
  });

  /* Botão passo 2 → 3 */
  document.getElementById('btnP2')?.addEventListener('click', () => {
    const ok = validarSenha() & validarConfirma();
    if (ok) irParaPasso(3);
  });

  /* Voltar */
  document.getElementById('btnVoltarP2')?.addEventListener('click', () => irParaPasso(1));
  document.getElementById('btnVoltarP3')?.addEventListener('click', () => irParaPasso(2));

  /* ─────────────────────────────────────────
     SUBMIT COM LOADING
  ───────────────────────────────────────── */
  document.getElementById('formCadastro')?.addEventListener('submit', function (e) {
    const aceite = document.getElementById('aceite_termos');
    if (!aceite?.checked) {
      e.preventDefault();
      aceite.closest('.cad-termo').style.borderColor = '#e53e3e';
      aceite.closest('.cad-termo').style.boxShadow = '0 0 0 4px rgba(229,62,62,0.1)';
      setTimeout(() => {
        aceite.closest('.cad-termo').style.borderColor = '';
        aceite.closest('.cad-termo').style.boxShadow = '';
      }, 2000);
      return;
    }

    const btn = document.getElementById('btnSubmit');
    if (btn) {
      btn.disabled = true;
      btn.querySelector('.btn-txt').style.display = 'none';
      btn.querySelector('.btn-loading').style.display = 'flex';
    }
  });

})();