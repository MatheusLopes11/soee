/* ═══════════════════════════════════════════════════════════
   cadastrar.js  —  SOEE · Lógica completa do cadastro (4 passos)
═══════════════════════════════════════════════════════════ */

(function () {
  'use strict';

  /* ── Cursor ── */
  const dot  = document.getElementById('cursorDot');
  const ring = document.getElementById('cursorRing');
  if (dot && ring) {
    document.addEventListener('mousemove', e => {
      dot.style.left  = e.clientX + 'px';
      dot.style.top   = e.clientY + 'px';
      ring.style.left = e.clientX + 'px';
      ring.style.top  = e.clientY + 'px';
    });
  }

  /* ── Loader ── */
  window.addEventListener('load', () => {
    const loader = document.getElementById('loader');
    if (loader) setTimeout(() => loader.classList.add('hide'), 900);
  });

  /* ── Tema ── */
  const btnTema = document.getElementById('toggle-theme');
  if (btnTema) {
    const saved = localStorage.getItem('soee-theme');
    if (saved) {
      document.documentElement.setAttribute('data-theme', saved);
      const ic = btnTema.querySelector('i');
      if (ic) ic.className = saved === 'dark' ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
    }
    btnTema.addEventListener('click', () => {
      const t = document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
      document.documentElement.setAttribute('data-theme', t);
      localStorage.setItem('soee-theme', t);
      const ic = btnTema.querySelector('i');
      if (ic) ic.className = t === 'dark' ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
    });
  }

  /* ── Toggle visibilidade de senha ── */
  document.querySelectorAll('.toggle-senha').forEach(btn => {
    btn.addEventListener('click', () => {
      const input = btn.closest('.input-wrapper').querySelector('input');
      const icon  = btn.querySelector('i');
      if (!input) return;
      const mostrar  = input.type === 'password';
      input.type     = mostrar ? 'text' : 'password';
      icon.className = mostrar ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye';
    });
  });

  /* ─────────────────────────────────────────
     PREVIEW DA TURMA
  ───────────────────────────────────────── */
  const selectAno   = document.getElementById('ano_serie');
  const selectCurso = document.getElementById('curso');
  const preview     = document.getElementById('turmaPreview');
  const previewTxt  = document.getElementById('turmaPreviewTxt');

  const nomeCurso = {
    MTEC:   'MTEC',
    EMIF:   'EMIF',
    MTECPI: 'PI',
  };

  function atualizarPreview() {
    const ano   = selectAno?.value;
    const curso = selectCurso?.value;
    if (ano && curso && preview && previewTxt) {
      const sigla = nomeCurso[curso] || curso;
      previewTxt.textContent = ano + 'º ' + sigla;
      preview.classList.remove('hidden');
    } else if (preview) {
      preview.classList.add('hidden');
    }
  }

  selectAno?.addEventListener('change',   atualizarPreview);
  selectCurso?.addEventListener('change', atualizarPreview);

  /* ─────────────────────────────────────────
     MEDIDOR DE FORÇA DA SENHA
  ───────────────────────────────────────── */
  const senhaInput = document.getElementById('senha');
  const forcaFill  = document.getElementById('forcaFill');
  const forcaTxt   = document.getElementById('forcaTxt');
  const forcaDica  = document.getElementById('forcaDica');
  const reqLen     = document.getElementById('req-len');
  const reqUpper   = document.getElementById('req-upper');
  const reqNum     = document.getElementById('req-num');
  const reqSpecial = document.getElementById('req-special');

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
    return { checks, score: Object.values(checks).filter(Boolean).length };
  }

  if (senhaInput) {
    senhaInput.addEventListener('input', () => {
      const v = senhaInput.value;
      if (!v) {
        if (forcaFill) forcaFill.className = 'forca-fill';
        if (forcaTxt)  forcaTxt.textContent = '—';
        if (forcaDica) forcaDica.textContent = '';
        [reqLen, reqUpper, reqNum, reqSpecial].forEach(r => r?.classList.remove('ok'));
        return;
      }
      const { checks, score } = avaliarSenha(v);
      const nivel = niveis[score - 1] || niveis[0];
      if (forcaFill) forcaFill.className = 'forca-fill ' + nivel.cor;
      if (forcaTxt) {
        forcaTxt.textContent = nivel.txt;
        forcaTxt.style.color = score === 4 ? '#16a34a' : score === 3 ? '#3b82f6' : score === 2 ? '#f59e0b' : '#e53e3e';
      }
      if (forcaDica) forcaDica.textContent = nivel.dica;
      reqLen?.classList.toggle('ok',     checks.len);
      reqUpper?.classList.toggle('ok',   checks.upper);
      reqNum?.classList.toggle('ok',     checks.num);
      reqSpecial?.classList.toggle('ok', checks.special);
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
    if (!val)          { setEstado('grupo-nome', 'erro', 'Nome é obrigatório'); return false; }
    if (val.length < 3){ setEstado('grupo-nome', 'erro', 'Mínimo 3 caracteres'); return false; }
    if (!/\s/.test(val)){ setEstado('grupo-nome', 'erro', 'Informe nome e sobrenome'); return false; }
    setEstado('grupo-nome', 'ok', 'Ótimo!'); return true;
  }

  function validarEmail() {
    const val = document.getElementById('email')?.value.trim();
    if (!val) { setEstado('grupo-email', 'erro', 'E-mail é obrigatório'); return false; }
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) { setEstado('grupo-email', 'erro', 'E-mail inválido'); return false; }
    setEstado('grupo-email', 'ok', 'E-mail válido!'); return true;
  }

  function validarAno() {
    const val = document.getElementById('ano_serie')?.value;
    if (!val) { setEstado('grupo-ano', 'erro', 'Selecione o ano'); return false; }
    setEstado('grupo-ano', 'ok', ''); return true;
  }

  function validarCurso() {
    const val = document.getElementById('curso')?.value;
    if (!val) { setEstado('grupo-curso', 'erro', 'Selecione o curso'); return false; }
    setEstado('grupo-curso', 'ok', ''); return true;
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
     MULTI-STEP (4 passos)
  ───────────────────────────────────────── */
  let passoAtual = 1;
  const TOTAL_PASSOS = 4;

  function irParaPasso(novo) {
    const atual = document.getElementById('passo' + passoAtual);
    const prox  = document.getElementById('passo' + novo);
    if (!prox) return;

    atual?.classList.add('hidden');
    prox.classList.remove('hidden');
    passoAtual = novo;

    const passos = document.querySelectorAll('.cad-step');
    const linhas = document.querySelectorAll('.cad-step-line');

    passos.forEach((s, i) => {
      s.classList.remove('active', 'completo');
      const n = i + 1;
      if (n < novo)       s.classList.add('completo');
      else if (n === novo) s.classList.add('active');
    });
    linhas.forEach((l, i) => {
      l.classList.toggle('preenchido', i < novo - 1);
    });

    if (novo === TOTAL_PASSOS) preencherResumo();

    document.querySelector('.cad-card')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }

  function preencherResumo() {
    const generoMap = { m: 'Masculino', f: 'Feminino', outro: 'Prefiro não informar / Outro' };
    const nomeCursoMap = { MTEC: 'MTEC', EMIF: 'EMIF', MTECPI: 'PI' };

    document.getElementById('r-nome').textContent  = document.getElementById('nome')?.value.trim() || '—';
    document.getElementById('r-email').textContent = document.getElementById('email')?.value.trim() || '—';

    const gv = document.getElementById('opcoes')?.value;
    document.getElementById('r-genero').textContent = generoMap[gv] || '—';

    const ano   = document.getElementById('ano_serie')?.value;
    const curso = document.getElementById('curso')?.value;
    const sigla = nomeCursoMap[curso] || curso;
    document.getElementById('r-turma').textContent = (ano && curso) ? (ano + 'º ' + sigla) : '—';
  }

  /* Botão passo 1 → 2 */
  document.getElementById('btnP1')?.addEventListener('click', () => {
    const ok = validarNome() & validarEmail();
    if (ok) irParaPasso(2);
  });

  /* Botão passo 2 → 3 */
  document.getElementById('btnP2')?.addEventListener('click', () => {
    const ok = validarAno() & validarCurso();
    if (ok) irParaPasso(3);
  });

  /* Botão passo 3 → 4 */
  document.getElementById('btnP3')?.addEventListener('click', () => {
    const ok = validarSenha() & validarConfirma();
    if (ok) irParaPasso(4);
  });

  /* Voltar */
  document.getElementById('btnVoltarP2')?.addEventListener('click', () => irParaPasso(1));
  document.getElementById('btnVoltarP3')?.addEventListener('click', () => irParaPasso(2));
  document.getElementById('btnVoltarP4')?.addEventListener('click', () => irParaPasso(3));

  /* ─────────────────────────────────────────
     SUBMIT COM LOADING
  ───────────────────────────────────────── */
  document.getElementById('formCadastro')?.addEventListener('submit', function (e) {
    const aceite = document.getElementById('aceite_termos');
    if (!aceite?.checked) {
      e.preventDefault();
      const termo = aceite.closest('.cad-termo');
      termo.style.borderColor = '#e53e3e';
      termo.style.boxShadow = '0 0 0 4px rgba(229,62,62,0.1)';
      setTimeout(() => {
        termo.style.borderColor = '';
        termo.style.boxShadow = '';
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