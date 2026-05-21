<?php
/* ── FLASH ───────────────────────────────────────── */
$flashMsg  = $_SESSION['flash_msg']  ?? '';
$flashTipo = $_SESSION['flash_tipo'] ?? '';
unset($_SESSION['flash_msg'], $_SESSION['flash_tipo']);

/* ── HELPERS ─────────────────────────────────────── */
function badgeStatus(string $s): string {
    $map = [
        'agendada'    => 'ativo',    'realizada'  => 'verde',
        'ativo'       => 'ativo',    'inativo'    => 'inativo',
        'em_andamento'=> 'ativo',    'encerrado'  => 'encerrado',
        'pendente'    => 'pendente', 'validada'   => 'ativo',
        'rejeitada'   => 'inativo',  'inscricoes' => 'pendente',
        'planejamento'=> 'pendente', 'cancelada'  => 'inativo',
        'wo'          => 'inativo',
    ];
    $labels = [
        'agendada'    => 'Agendada',    'realizada'   => 'Realizada',
        'ativo'       => 'Ativo',       'inativo'     => 'Inativo',
        'em_andamento'=> 'Em Andamento','encerrado'   => 'Encerrado',
        'pendente'    => 'Pendente',    'validada'    => 'Validada',
        'rejeitada'   => 'Rejeitada',   'inscricoes'  => 'Inscrições',
        'planejamento'=> 'Planejamento','cancelada'   => 'Cancelada',
        'wo'          => 'W.O.',
    ];
    return '<span class="badge-status '.($map[$s] ?? 'pendente').'">'.($labels[$s] ?? ucfirst($s)).'</span>';
}
function fmtData(?string $d): string { return $d ? date('d/m/Y', strtotime($d)) : '—'; }
function fmtHora(?string $h): string { return $h ? substr($h, 0, 5) : '—'; }

$dias_pt  = ['Sunday'=>'Dom','Monday'=>'Seg','Tuesday'=>'Ter','Wednesday'=>'Qua',
             'Thursday'=>'Qui','Friday'=>'Sex','Saturday'=>'Sáb'];
$meses_pt = ['January'=>'Janeiro','February'=>'Fevereiro','March'=>'Março',
             'April'=>'Abril','May'=>'Maio','June'=>'Junho','July'=>'Julho',
             'August'=>'Agosto','September'=>'Setembro','October'=>'Outubro',
             'November'=>'Novembro','December'=>'Dezembro'];
$faseLabel = ['grupos'=>'Grupos','oitavas'=>'Oitavas','quartas'=>'Quartas',
              'semi'=>'Semifinal','final'=>'Final','terceiro_lugar'=>'3º Lugar'];
?>