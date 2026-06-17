<?php
require_once __DIR__ . '/../includes/conexao.php';

if (isset($_GET['tipo']) && isset($_GET['id'])) {

    $tipo = $_GET['tipo'];
    $id   = (int) $_GET['id'];

    // ── Modalidade: apaga dependentes antes de apagar a modalidade ──
    // A ordem importa: inscricao/partida/classificacao/sorteio_gerado
    // dependem de edicao_modalidade, que depende de modalidade.
    if ($tipo === 'modalidade') {

        // IDs das edicao_modalidade vinculadas
        $stmtEm = $conn->prepare("
            SELECT id_edicao_modalidade
            FROM edicao_modalidade
            WHERE modalidade_id_modalidade = ?
        ");
        $stmtEm->execute([$id]);
        $emIds = $stmtEm->fetchAll(PDO::FETCH_COLUMN);

        if (!empty($emIds)) {
            $in = implode(',', array_map('intval', $emIds));

            $conn->exec("DELETE FROM inscricao      WHERE edicao_modalidade_id IN ($in)");
            $conn->exec("DELETE FROM classificacao  WHERE edicao_modalidade_id IN ($in)");
            $conn->exec("DELETE FROM sorteio_gerado WHERE edicao_modalidade_id IN ($in)");

            // resultado depende de partida — apaga resultado antes
            $conn->exec("
                DELETE FROM resultado
                WHERE partida_id_partida IN (
                    SELECT id_partida FROM partida
                    WHERE edicao_modalidade_id IN ($in)
                )
            ");
            $conn->exec("DELETE FROM partida          WHERE edicao_modalidade_id IN ($in)");
            $conn->exec("DELETE FROM edicao_modalidade WHERE id_edicao_modalidade IN ($in)");
        }

        $stmt = $conn->prepare("DELETE FROM modalidade WHERE id_modalidade = ?");
        $stmt->execute([$id]);

    // ── Edição: apaga edicao_modalidade e tudo que depende dela ──
    } elseif ($tipo === 'edicao') {

        $stmtEm = $conn->prepare("
            SELECT id_edicao_modalidade
            FROM edicao_modalidade
            WHERE edicao_id_edicao = ?
        ");
        $stmtEm->execute([$id]);
        $emIds = $stmtEm->fetchAll(PDO::FETCH_COLUMN);

        if (!empty($emIds)) {
            $in = implode(',', array_map('intval', $emIds));

            $conn->exec("DELETE FROM inscricao      WHERE edicao_modalidade_id IN ($in)");
            $conn->exec("DELETE FROM classificacao  WHERE edicao_modalidade_id IN ($in)");
            $conn->exec("DELETE FROM sorteio_gerado WHERE edicao_modalidade_id IN ($in)");
            $conn->exec("
                DELETE FROM resultado
                WHERE partida_id_partida IN (
                    SELECT id_partida FROM partida
                    WHERE edicao_modalidade_id IN ($in)
                )
            ");
            $conn->exec("DELETE FROM partida           WHERE edicao_modalidade_id IN ($in)");
            $conn->exec("DELETE FROM edicao_modalidade WHERE id_edicao_modalidade IN ($in)");
        }

        $stmt = $conn->prepare("DELETE FROM edicao WHERE id_edicao = ?");
        $stmt->execute([$id]);

    // ── Partida: apaga resultado e sumula antes ──
    } elseif ($tipo === 'partida') {

        $conn->prepare("DELETE FROM resultado WHERE partida_id_partida = ?")->execute([$id]);
        $conn->prepare("DELETE FROM sumula    WHERE partida_id_partida = ?")->execute([$id]);
        $conn->prepare("DELETE FROM partida   WHERE id_partida = ?")        ->execute([$id]);

    // ── Tipos simples sem dependentes complexos ──
    } elseif ($tipo === 'resultado') {

        $conn->prepare("DELETE FROM resultado WHERE id_resultado = ?")->execute([$id]);

    } elseif ($tipo === 'sumula') {

        $conn->prepare("DELETE FROM sumula WHERE id_sumula = ?")->execute([$id]);
    }
}

header("Location: /soee/src/frontend/views/dashboards/adm.php");
exit;