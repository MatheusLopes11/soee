/* ══════════════════════════════════════
   FUNÇÕES DE EDIÇÃO DOS MODAIS
   Carregam os dados do registro clicado
   nos campos do modal antes de abrir.
══════════════════════════════════════ */

// ── Helpers genéricos ──────────────────
function setVal(id, val) {
    const el = document.getElementById(id);
    if (!el) return;
    el.value = val ?? '';
}
function setSelect(id, val) {
    const el = document.getElementById(id);
    if (!el) return;
    for (let i = 0; i < el.options.length; i++) {
        if (String(el.options[i].value) === String(val)) {
            el.selectedIndex = i;
            break;
        }
    }
}

// ── abrirModalNovo: limpa o form antes de abrir ──────────
function abrirModalNovo(id) {
    const overlay = document.getElementById(id);
    if (!overlay) return;
    const form = overlay.querySelector('form');
    if (form) form.reset();
    // Limpa hidden ids
    const hidden = overlay.querySelector('input[type=hidden]');
    if (hidden) hidden.value = '';
    // Restaura títulos padrão
    const tituloMap = {
        'modal-modalidade': 'Nova Modalidade',
        'modal-partida':    'Agendar Partida',
        'modal-resultado':  'Registrar Resultado',
        'modal-edicao':     'Nova Edição / Evento',
        'modal-usuario':    'Novo Usuário',
    };
    const titulo = overlay.querySelector('[id$="-titulo"]');
    if (titulo && tituloMap[id]) titulo.textContent = tituloMap[id];
    abrirModal(id);
}

// ── EDITAR MODALIDADE ─────────────────────────────────────
function editarModalidade(m) {
    document.getElementById('modal-modalidade-titulo').textContent = 'Editar Modalidade';
    setVal('mod-id',           m.id_modalidade);
    setVal('mod-nome',         m.nome_modalidade);
    setSelect('mod-tipo',      m.tipo_modalidade);
    setSelect('mod-formato',   m.formato_modalidade);
    setSelect('mod-participacao', m.tipo_participacao);
    setSelect('mod-ativo',     m.ativo_modalidade);
    setVal('mod-min',          m.qtd_min_jogadores);
    setVal('mod-max',          m.qtd_max_jogadores);
    setVal('mod-desc',         m.descricao_modalidade);
    abrirModal('modal-modalidade');
}

// ── EDITAR EDIÇÃO ─────────────────────────────────────────
function editarEdicao(e) {
    document.getElementById('modal-edicao-titulo').textContent = 'Editar Edição / Evento';
    setVal('edicao-id',        e.id_edicao);
    setVal('edicao-nome',      e.nome_edicao);
    setVal('edicao-ano',       e.ano_edicao);
    setSelect('edicao-status', e.status_edicao);
    setVal('edicao-inicio',    e.data_inicio_edicao); // formato YYYY-MM-DD vindo do BD
    setVal('edicao-fim',       e.data_fim_edicao);
    setVal('edicao-desc',      e.descricao_edicao);
    abrirModal('modal-edicao');
}

// ── EDITAR PARTIDA ────────────────────────────────────────
// CORRIGIDO: popula data, hora, local, fase, status, times
function editarPartida(p) {
    document.getElementById('modal-partida-titulo').textContent = 'Editar Partida';
    setVal('partida-id',              p.id_partida);
    setSelect('partida-edicao-modal', p.edicao_modalidade_id);
    setSelect('partida-time-a',       p.turma_id_time_a);
    setSelect('partida-time-b',       p.turma_id_time_b);
    // data_partida vem como "YYYY-MM-DD" do BD — compatível com input[type=date]
    setVal('partida-data',            p.data_partida);
    // hora_partida vem como "HH:MM:SS" — input[type=time] aceita "HH:MM"
    setVal('partida-hora',            p.hora_partida ? p.hora_partida.substring(0, 5) : '');
    setVal('partida-local',           p.local_partida);
    setSelect('partida-fase',         p.fase_partida);
    setSelect('partida-status',       p.status_partida);
    setVal('partida-obs',             p.observacoes_partida);
    abrirModal('modal-partida');
}

// ── EDITAR RESULTADO ──────────────────────────────────────
// CORRIGIDO: popula partida, placares, vencedor, obs
function editarResultado(r) {
    document.getElementById('modal-resultado-titulo').textContent = 'Editar Resultado';
    setVal('resultado-id',              r.id_resultado);
    setSelect('resultado-partida',      r.partida_id_partida);
    setVal('resultado-placar-a',        r.placar_time_a);
    setVal('resultado-placar-b',        r.placar_time_b);
    setSelect('resultado-vencedor',     r.turma_id_vencedor ?? '');
    setVal('resultado-obs',             r.observacoes_resultado);
    abrirModal('modal-resultado');
}

// ── EDITAR USUÁRIO ────────────────────────────────────────
function editarUsuario(u) {
    document.getElementById('modal-usuario-titulo').textContent = 'Editar Usuário';
    setVal('u-id',        u.id_usuario);
    setVal('u-nome',      u.nome_usuario);
    setVal('u-email',     u.email_usuario);
    setVal('u-senha',     ''); // nunca preenche senha por segurança
    setSelect('u-turma',  u.turma_id_turma ?? '');
    setSelect('u-tipo',   u.tipo_usuario);
    setSelect('u-genero', u.genero_usuario);
    setSelect('u-ativo',  u.ativo_usuario);
    abrirModal('modal-usuario');
}

// ── Input file: mostra nome do arquivo selecionado ────────
function mostrarNomeArquivo(input) {
    const el = document.getElementById('nome-arquivo-selecionado');
    if (!el) return;
    if (input.files && input.files.length > 0) {
        el.innerHTML = '<i class="fas fa-check-circle"></i> ' + input.files[0].name;
    } else {
        el.textContent = '';
    }
}