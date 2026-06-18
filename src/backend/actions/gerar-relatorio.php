<?php
session_start();

require_once __DIR__ . '/../includes/conexao.php';
require_once __DIR__ . '/../controllers/home.php';
require_once __DIR__ . '/../includes/fpdf.php';

AuthHome::exigirTipo(['adm_geral', 'adm_sala', 'professor']);

$tipo = $_GET['tipo'] ?? 'inscricoes'; // 'inscricoes' ou 'jogos'

// â”€â”€â”€ CLASSE PDF CUSTOMIZADA â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

class RelatorioPDF extends FPDF {

    public $titulo_relatorio = '';

    function Header() {
        // Faixa de cabeÃ§alho azul
        $this->SetFillColor(10, 61, 98);
        $this->Rect(0, 0, 210, 28, 'F');

        // TÃ­tulo principal
        $this->SetFont('Arial', 'B', 16);
        $this->SetTextColor(255, 255, 255);
        $this->SetXY(10, 8);
        $this->Cell(0, 8, 'SOEE - Sistema de Organizacao de Eventos Esportivos', 0, 1, 'C');

        // SubtÃ­tulo
        $this->SetFont('Arial', '', 10);
        $this->SetTextColor(180, 210, 230);
        $this->SetX(10);
        $this->Cell(0, 6, utf8_decode($this->titulo_relatorio), 0, 1, 'C');

        // Linha laranja decorativa
        $this->SetFillColor(230, 100, 20);
        $this->Rect(0, 28, 210, 2, 'F');

        $this->SetTextColor(0, 0, 0);
        $this->Ln(6);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(120, 120, 120);
        $data = date('d/m/Y H:i');
        $this->Cell(0, 5, utf8_decode("Gerado em $data  |  ETEC Juscelino Kubitschek de Oliveira  |  Pagina " . $this->PageNo()), 0, 0, 'C');
    }

    // CabeÃ§alho de seÃ§Ã£o laranja
    function SecaoTitulo($texto) {
        $this->SetFillColor(230, 100, 20);
        $this->SetTextColor(255, 255, 255);
        $this->SetFont('Arial', 'B', 11);
        $this->Cell(0, 8, utf8_decode("  $texto"), 0, 1, 'L', true);
        $this->SetTextColor(0, 0, 0);
        $this->Ln(2);
    }

    // Linha de dado com label + valor
    function LinhaDado($label, $valor, $destaque = false) {
        if ($destaque) {
            $this->SetFillColor(240, 246, 252);
        } else {
            $this->SetFillColor(255, 255, 255);
        }
        $this->SetFont('Arial', 'B', 9);
        $this->Cell(65, 6, utf8_decode($label), 0, 0, 'L', true);
        $this->SetFont('Arial', '', 9);
        $this->Cell(0, 6, utf8_decode($valor), 0, 1, 'L', true);
    }

    // CabeÃ§alho de tabela
    function TabelaCabecalho($colunas, $larguras) {
        $this->SetFillColor(10, 61, 98);
        $this->SetTextColor(255, 255, 255);
        $this->SetFont('Arial', 'B', 9);
        foreach ($colunas as $i => $col) {
            $this->Cell($larguras[$i], 7, utf8_decode($col), 0, 0, 'C', true);
        }
        $this->Ln();
        $this->SetTextColor(0, 0, 0);
    }

    // Linha de tabela zebrada
    function TabelaLinha($dados, $larguras, $par) {
        if ($par) {
            $this->SetFillColor(245, 249, 253);
        } else {
            $this->SetFillColor(255, 255, 255);
        }
        $this->SetFont('Arial', '', 8);
        foreach ($dados as $i => $val) {
            $this->Cell($larguras[$i], 6, utf8_decode($val), 0, 0, 'L', true);
        }
        $this->Ln();
    }

    // Card de KPI
    function KpiBox($label, $valor, $x, $y, $w = 42, $h = 18) {
        $this->SetXY($x, $y);
        $this->SetFillColor(10, 61, 98);
        $this->Rect($x, $y, $w, $h, 'F');
        $this->SetXY($x, $y + 2);
        $this->SetFont('Arial', 'B', 14);
        $this->SetTextColor(255, 200, 50);
        $this->Cell($w, 7, utf8_decode($valor), 0, 1, 'C');
        $this->SetXY($x, $y + 10);
        $this->SetFont('Arial', '', 7);
        $this->SetTextColor(180, 210, 230);
        $this->Cell($w, 5, utf8_decode($label), 0, 1, 'C');
        $this->SetTextColor(0, 0, 0);
    }
}

// â”€â”€â”€ RELATÃ“RIO DE INSCRIÃ‡Ã•ES â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

function relatorio_inscricoes($conn) {
    $pdf = new RelatorioPDF('P', 'mm', 'A4');
    $pdf->titulo_relatorio = 'Relatorio de Inscricoes e Participantes';
    $pdf->SetAutoPageBreak(true, 20);
    $pdf->AddPage();

    // â”€â”€ KPIs gerais â”€â”€
    $total_alunos    = $conn->query("SELECT COUNT(*) FROM usuario WHERE tipo_usuario = 'aluno' AND ativo_usuario = TRUE")->fetchColumn();
    $total_inscricoes = $conn->query("SELECT COUNT(*) FROM inscricao WHERE status_inscricao = 'ativa'")->fetchColumn();
    $total_modalidades = $conn->query("SELECT COUNT(*) FROM modalidade WHERE ativo_modalidade = TRUE")->fetchColumn();
    $total_turmas    = $conn->query("SELECT COUNT(*) FROM turma")->fetchColumn();

    $pdf->SecaoTitulo('Resumo Geral');

    $y = $pdf->GetY();
    $pdf->KpiBox('Alunos cadastrados', $total_alunos,       10,  $y);
    $pdf->KpiBox('Inscricoes ativas',  $total_inscricoes,   56,  $y);
    $pdf->KpiBox('Modalidades ativas', $total_modalidades,  102, $y);
    $pdf->KpiBox('Turmas',             $total_turmas,       148, $y);
    $pdf->SetY($y + 24);
    $pdf->Ln(4);

    // â”€â”€ InscriÃ§Ãµes por modalidade â”€â”€
    $pdf->SecaoTitulo('Inscricoes por Modalidade');

    $por_modalidade = $conn->query("
        SELECT m.nome_modalidade,
               m.tipo_participacao,
               COUNT(i.id_inscricao) AS total_inscritos,
               COUNT(DISTINCT u.turma_id_turma) AS turmas_participando
        FROM modalidade m
        JOIN edicao_modalidade em ON em.modalidade_id_modalidade = m.id_modalidade
        JOIN inscricao i ON i.edicao_modalidade_id = em.id_edicao_modalidade AND i.status_inscricao = 'ativa'
        JOIN usuario u ON u.id_usuario = i.usuario_id_usuario
        GROUP BY m.id_modalidade, m.nome_modalidade, m.tipo_participacao
        ORDER BY total_inscritos DESC
    ")->fetchAll(PDO::FETCH_ASSOC);

    $pdf->TabelaCabecalho(
        ['Modalidade', 'Participacao', 'Total Inscritos', 'Turmas'],
        [80, 40, 40, 30]
    );
    foreach ($por_modalidade as $i => $row) {
        $pdf->TabelaLinha([
            $row['nome_modalidade'],
            $row['tipo_participacao'],
            $row['total_inscritos'],
            $row['turmas_participando'],
        ], [80, 40, 40, 30], $i % 2 === 0);
    }
    $pdf->Ln(6);

    // â”€â”€ InscriÃ§Ãµes por turma â”€â”€
    $pdf->SecaoTitulo('Inscricoes por Turma');

    $por_turma = $conn->query("
        SELECT t.nome_turma,
               c.sigla_curso,
               COUNT(DISTINCT u.id_usuario) AS alunos_inscritos,
               COUNT(i.id_inscricao) AS total_inscricoes
        FROM turma t
        JOIN curso c ON c.id_curso = t.curso_id_curso
        JOIN usuario u ON u.turma_id_turma = t.id_turma AND u.tipo_usuario = 'aluno'
        LEFT JOIN inscricao i ON i.usuario_id_usuario = u.id_usuario AND i.status_inscricao = 'ativa'
        GROUP BY t.id_turma, t.nome_turma, c.sigla_curso
        ORDER BY t.nome_turma
    ")->fetchAll(PDO::FETCH_ASSOC);

    $pdf->TabelaCabecalho(
        ['Turma', 'Curso', 'Alunos Inscritos', 'Total de Inscricoes'],
        [60, 40, 50, 40]
    );
    foreach ($por_turma as $i => $row) {
        $pdf->TabelaLinha([
            $row['nome_turma'],
            $row['sigla_curso'],
            $row['alunos_inscritos'],
            $row['total_inscricoes'],
        ], [60, 40, 50, 40], $i % 2 === 0);
    }
    $pdf->Ln(6);

    // â”€â”€ LÃ­deres de sala (adm_sala) â”€â”€
    $pdf->SecaoTitulo('Lideres de Sala (ADM Sala)');

    $lideres = $conn->query("
        SELECT u.nome_usuario, t.nome_turma, c.sigla_curso, t.periodo_turma
        FROM usuario u
        JOIN turma t ON t.id_turma = u.turma_id_turma
        JOIN curso c ON c.id_curso = t.curso_id_curso
        WHERE u.tipo_usuario = 'adm_sala' AND u.ativo_usuario = TRUE
        ORDER BY t.nome_turma
    ")->fetchAll(PDO::FETCH_ASSOC);

    if (count($lideres) > 0) {
        $pdf->TabelaCabecalho(
            ['Nome do Lider', 'Turma', 'Curso', 'Periodo'],
            [80, 35, 50, 25]
        );
        foreach ($lideres as $i => $row) {
            $pdf->TabelaLinha([
                $row['nome_usuario'],
                $row['nome_turma'],
                $row['sigla_curso'],
                $row['periodo_turma'],
            ], [80, 35, 50, 25], $i % 2 === 0);
        }
    } else {
        $pdf->SetFont('Arial', 'I', 9);
        $pdf->SetTextColor(120, 120, 120);
        $pdf->Cell(0, 6, 'Nenhum lider de sala cadastrado.', 0, 1);
        $pdf->SetTextColor(0, 0, 0);
    }
    $pdf->Ln(6);

    // â”€â”€ Lista detalhada por modalidade â”€â”€
    $modalidades_ativas = $conn->query("
        SELECT DISTINCT m.id_modalidade, m.nome_modalidade, m.tipo_participacao, em.id_edicao_modalidade
        FROM modalidade m
        JOIN edicao_modalidade em ON em.modalidade_id_modalidade = m.id_modalidade
        JOIN inscricao i ON i.edicao_modalidade_id = em.id_edicao_modalidade AND i.status_inscricao = 'ativa'
        ORDER BY m.nome_modalidade
    ")->fetchAll(PDO::FETCH_ASSOC);

    foreach ($modalidades_ativas as $mod) {
        $pdf->AddPage();
        $pdf->SecaoTitulo('Inscritos em: ' . $mod['nome_modalidade'] . ' (' . $mod['tipo_participacao'] . ')');

        $inscritos = $conn->prepare("
            SELECT u.nome_usuario,
                   t.nome_turma,
                   c.sigla_curso,
                   i.numero_camisa_inscricao,
                   i.posicao_inscricao,
                   i.capitao_inscricao,
                   i.data_inscricao
            FROM inscricao i
            JOIN usuario u ON u.id_usuario = i.usuario_id_usuario
            LEFT JOIN turma t ON t.id_turma = u.turma_id_turma
            LEFT JOIN curso c ON c.id_curso = t.curso_id_curso
            WHERE i.edicao_modalidade_id = :emid AND i.status_inscricao = 'ativa'
            ORDER BY t.nome_turma, u.nome_usuario
        ");
        $inscritos->execute([':emid' => $mod['id_edicao_modalidade']]);
        $lista = $inscritos->fetchAll(PDO::FETCH_ASSOC);

        $total = count($lista);
        $pdf->SetFont('Arial', 'I', 9);
        $pdf->SetTextColor(80, 80, 80);
        $pdf->Cell(0, 5, "Total de inscritos: $total", 0, 1);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Ln(2);

        $pdf->TabelaCabecalho(
            ['Nome', 'Turma', 'Curso', 'Camisa', 'Posicao', 'Capitao'],
            [60, 28, 25, 17, 35, 25]
        );
        foreach ($lista as $i => $aluno) {
            $pdf->TabelaLinha([
                $aluno['nome_usuario'],
                $aluno['nome_turma'] ?? '-',
                $aluno['sigla_curso'] ?? '-',
                $aluno['numero_camisa_inscricao'] ?? '-',
                $aluno['posicao_inscricao'] ?? '-',
                $aluno['capitao_inscricao'] ? 'Sim' : 'Nao',
            ], [60, 28, 25, 17, 35, 25], $i % 2 === 0);
        }
    }

    return $pdf;
}

// â”€â”€â”€ RELATÃ“RIO DE JOGOS â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

function relatorio_jogos($conn) {
    $pdf = new RelatorioPDF('P', 'mm', 'A4');
    $pdf->titulo_relatorio = 'Relatorio de Partidas, Resultados e Classificacao';
    $pdf->SetAutoPageBreak(true, 20);
    $pdf->AddPage();

    // â”€â”€ KPIs â”€â”€
    $total_partidas   = $conn->query("SELECT COUNT(*) FROM partida")->fetchColumn();
    $realizadas       = $conn->query("SELECT COUNT(*) FROM partida WHERE status_partida = 'realizada'")->fetchColumn();
    $agendadas        = $conn->query("SELECT COUNT(*) FROM partida WHERE status_partida = 'agendada'")->fetchColumn();
    $total_modalidades = $conn->query("SELECT COUNT(DISTINCT modalidade_id_modalidade) FROM edicao_modalidade")->fetchColumn();

    $pdf->SecaoTitulo('Resumo Geral');
    $y = $pdf->GetY();
    $pdf->KpiBox('Total de Partidas', $total_partidas,  10,  $y);
    $pdf->KpiBox('Realizadas',        $realizadas,      56,  $y);
    $pdf->KpiBox('Agendadas',         $agendadas,       102, $y);
    $pdf->KpiBox('Modalidades',       $total_modalidades, 148, $y);
    $pdf->SetY($y + 24);
    $pdf->Ln(4);

    // â”€â”€ Resultados por modalidade â”€â”€
    $modalidades = $conn->query("
        SELECT DISTINCT m.id_modalidade, m.nome_modalidade, em.id_edicao_modalidade, em.status_edicao_modalidade
        FROM modalidade m
        JOIN edicao_modalidade em ON em.modalidade_id_modalidade = m.id_modalidade
        ORDER BY m.nome_modalidade
    ")->fetchAll(PDO::FETCH_ASSOC);

    foreach ($modalidades as $mod) {

        // Partidas desta modalidade
        $partidas = $conn->prepare("
            SELECT p.id_partida,
                   ta.nome_turma AS time_a,
                   tb.nome_turma AS time_b,
                   p.data_partida,
                   p.hora_partida,
                   p.local_partida,
                   p.fase_partida,
                   p.grupo_partida,
                   p.status_partida,
                   r.placar_time_a,
                   r.placar_time_b,
                   tv.nome_turma AS vencedor
            FROM partida p
            JOIN turma ta ON ta.id_turma = p.turma_id_time_a
            JOIN turma tb ON tb.id_turma = p.turma_id_time_b
            LEFT JOIN resultado r ON r.partida_id_partida = p.id_partida
            LEFT JOIN turma tv ON tv.id_turma = r.turma_id_vencedor
            WHERE p.edicao_modalidade_id = :emid
            ORDER BY p.data_partida, p.hora_partida
        ");
        $partidas->execute([':emid' => $mod['id_edicao_modalidade']]);
        $lista_partidas = $partidas->fetchAll(PDO::FETCH_ASSOC);

        if (empty($lista_partidas)) continue;

        $pdf->SecaoTitulo('Partidas: ' . $mod['nome_modalidade'] . '  [' . $mod['status_edicao_modalidade'] . ']');

        $pdf->TabelaCabecalho(
            ['Time A', 'Time B', 'Data', 'Hora', 'Local', 'Fase', 'Placar', 'Status'],
            [28, 28, 18, 14, 28, 20, 20, 22]
        );
        foreach ($lista_partidas as $i => $p) {
            $placar = ($p['placar_time_a'] !== null)
                ? $p['placar_time_a'] . ' x ' . $p['placar_time_b']
                : '-';
            $data = $p['data_partida']
                ? date('d/m/y', strtotime($p['data_partida']))
                : '-';
            $hora = $p['hora_partida']
                ? substr($p['hora_partida'], 0, 5)
                : '-';
            $pdf->TabelaLinha([
                $p['time_a'],
                $p['time_b'],
                $data,
                $hora,
                $p['local_partida'] ?? '-',
                $p['fase_partida'],
                $placar,
                $p['status_partida'],
            ], [28, 28, 18, 14, 28, 20, 20, 22], $i % 2 === 0);
        }
        $pdf->Ln(4);

        // ClassificaÃ§Ã£o desta modalidade
        $classificacao = $conn->prepare("
            SELECT t.nome_turma,
                   cl.grupo_classificacao,
                   cl.pontos, cl.jogos, cl.vitorias, cl.empates, cl.derrotas,
                   cl.pontos_pro, cl.pontos_contra, cl.saldo
            FROM classificacao cl
            JOIN turma t ON t.id_turma = cl.turma_id_turma
            WHERE cl.edicao_modalidade_id = :emid
            ORDER BY cl.grupo_classificacao, cl.pontos DESC, cl.saldo DESC
        ");
        $classificacao->execute([':emid' => $mod['id_edicao_modalidade']]);
        $tabela_class = $classificacao->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($tabela_class)) {
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->SetTextColor(50, 50, 50);
            $pdf->Cell(0, 5, 'Classificacao:', 0, 1);
            $pdf->SetTextColor(0, 0, 0);

            $pdf->TabelaCabecalho(
                ['Turma', 'Grupo', 'Pts', 'J', 'V', 'E', 'D', 'GP', 'GC', 'Saldo'],
                [46, 16, 12, 10, 10, 10, 10, 14, 14, 16]
            );
            foreach ($tabela_class as $i => $cl) {
                $pdf->TabelaLinha([
                    $cl['nome_turma'],
                    $cl['grupo_classificacao'] ?? '-',
                    $cl['pontos'],
                    $cl['jogos'],
                    $cl['vitorias'],
                    $cl['empates'],
                    $cl['derrotas'],
                    $cl['pontos_pro'],
                    $cl['pontos_contra'],
                    $cl['saldo'],
                ], [46, 16, 12, 10, 10, 10, 10, 14, 14, 16], $i % 2 === 0);
            }
            $pdf->Ln(6);
        }
    }

    return $pdf;
}

// â”€â”€â”€ GERAÃ‡ÃƒO â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

try {
    if ($tipo === 'inscricoes') {
        $pdf = relatorio_inscricoes($conn);
        $nome_arquivo = 'SOEE_Relatorio_Inscricoes_' . date('Ymd_His') . '.pdf';
    } else {
        $pdf = relatorio_jogos($conn);
        $nome_arquivo = 'SOEE_Relatorio_Jogos_' . date('Ymd_His') . '.pdf';
    }

    $pdf->Output('I', $nome_arquivo); // 'I' = abre no browser, 'D' = forÃ§a download

} catch (Exception $e) {
    http_response_code(500);
    echo 'Erro ao gerar relatorio: ' . $e->getMessage();
}
?>
