<?php
// ── HELPERS ───────────────────────────────────────────────────────
$faseLabel = [
    'grupos'         => 'Grupos',
    'oitavas'        => 'Oitavas',
    'quartas'        => 'Quartas',
    'semi'           => 'Semi',
    'final'          => 'Final',
    'terceiro_lugar' => '3º Lugar',
];
$statusPartidaLabel = [
    'agendada'  => 'Agendada',
    'realizada' => 'Realizada',
    'cancelada' => 'Cancelada',
    'wo'        => 'W.O.',
];
$tipoIcons = [
    'quadra' => 'fa-basketball',
    'mesa'   => 'fa-table-tennis-paddle-ball',
    'campo'  => 'fa-futbol',
    'outro'  => 'fa-star',
];
$formatoLabel = [
    'mata_mata'          => 'Mata-mata',
    'grupos'             => 'Grupos',
    'grupos_mata_mata'   => 'Grupos + Mata-mata',
    'todos_contra_todos' => 'Todos contra todos',
];
$participacaoLabel = [
    'solo'  => 'Individual',
    'dupla' => 'Dupla',
    'trio'  => 'Trio',
    'time'  => 'Time',
];
$statusEdicaoLabel = [
    'planejamento'  => 'Planejamento',
    'inscricoes'    => 'Inscrições',
    'em_andamento'  => 'Em Andamento',
    'encerrado'     => 'Encerrado',
];

// ── FLASH MESSAGE ─────────────────────────────────────────────────
$flashMsg  = $_SESSION['flash_msg']  ?? '';
$flashTipo = $_SESSION['flash_tipo'] ?? '';
unset($_SESSION['flash_msg'], $_SESSION['flash_tipo']);
?>