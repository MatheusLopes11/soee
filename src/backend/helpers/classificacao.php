<?php
// ── HELPERS ──────────────────────────────────────────────
$tipoIcons = [
    'quadra' => 'fa-basketball',
    'mesa'   => 'fa-table-tennis-paddle-ball',
    'campo'  => 'fa-futbol',
    'outro'  => 'fa-medal',
];

$faseLabel = [
    'grupos'         => 'Fase de Grupos',
    'oitavas'        => 'Oitavas de Final',
    'quartas'        => 'Quartas de Final',
    'semi'           => 'Semifinais',
    'terceiro_lugar' => '3º Lugar',
    'final'          => 'Grande Final',
];

$formatoLabel = [
    'grupos'             => 'Somente Grupos',
    'mata_mata'          => 'Somente Mata-Mata',
    'grupos_mata_mata'   => 'Grupos + Mata-Mata',
    'todos_contra_todos' => 'Todos Contra Todos',
];

$participacaoLabel = [
    'solo'  => 'Individual',
    'dupla' => 'Dupla',
    'trio'  => 'Trio',
    'time'  => 'Times por Turma',
];

$faseOrdemMata    = ['oitavas', 'quartas', 'semi', 'terceiro_lugar', 'final'];
$temMataMata      = $formato && in_array($formato, ['mata_mata', 'grupos_mata_mata']);
$icone            = $tipoIcons[$esporte['tipo_modalidade'] ?? 'outro'] ?? 'fa-medal';
$participacao     = $esporte['tipo_participacao'] ?? 'time';
$ehIndividual     = in_array($participacao, ['solo', 'dupla', 'trio']);

// Quantos classificam por grupo para mata-mata
$classificamPorGrupo = 2;

function fmtData($d)     { return $d ? date('d/m', strtotime($d)) : '—'; }
function fmtDataLong($d) { return $d ? date('d/m/Y', strtotime($d)) : '—'; }
function fmtHora($h)     { return $h ? substr($h, 0, 5) : ''; }
function avatar($nome)   { return mb_strtoupper(mb_substr(trim($nome ?? '?'), 0, 2)); }

// ── AUTH ─────────────────────────────────────────────────
$logado = !empty($_SESSION['usuario_id']);
$tipo   = $_SESSION['usuario_tipo'] ?? '';
$destDash = match($tipo) {
    'professor' => '/soee/src/frontend/views/dashboards/professor.php',
    'adm_sala'  => '/soee/src/frontend/views/dashboards/adm-sala.php',
    'adm_geral' => '/soee/src/frontend/views/dashboards/adm.php',
    default     => '/soee/src/frontend/views/dashboards/aluno.php',
};
?>