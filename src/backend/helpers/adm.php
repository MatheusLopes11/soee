<?php
// ── Helpers ────────────────────────────────────────────────
function badgeStatus($s) {
    $map = [
        'agendada'=>'ativo','realizada'=>'verde','ativo'=>'ativo',
        'em_andamento'=>'ativo','inativo'=>'inativo','encerrado'=>'encerrado',
        'pendente'=>'pendente','validada'=>'ativo','rejeitada'=>'inativo',
        'inscricoes'=>'pendente','planejamento'=>'pendente',
        'cancelada'=>'inativo','wo'=>'inativo',
    ];
    $labels = [
        'agendada'=>'Agendada','realizada'=>'Realizada','ativo'=>'Ativo',
        'inativo'=>'Inativo','em_andamento'=>'Em Andamento','encerrado'=>'Encerrado',
        'pendente'=>'Pendente','validada'=>'Validada','rejeitada'=>'Rejeitada',
        'inscricoes'=>'Inscrições','planejamento'=>'Planejamento',
        'cancelada'=>'Cancelada','wo'=>'W.O.',
    ];
    $cls   = $map[$s]    ?? 'pendente';
    $label = $labels[$s] ?? ucfirst($s);
    return "<span class=\"badge-status $cls\">$label</span>";
}
function fmtData($d) { return $d ? date('d/m/Y', strtotime($d)) : '—'; }
function fmtHora($h) { return $h ? substr($h, 0, 5) : '—'; }

// ── JSON para JS (dados de edição nos modais) ──────────────
$modalidades_json = json_encode($modalidades, JSON_UNESCAPED_UNICODE);
$partidas_json    = json_encode($partidas,    JSON_UNESCAPED_UNICODE);
$resultados_json  = json_encode($resultados,  JSON_UNESCAPED_UNICODE);
$edicoes_json     = json_encode($edicoes,     JSON_UNESCAPED_UNICODE);
?>