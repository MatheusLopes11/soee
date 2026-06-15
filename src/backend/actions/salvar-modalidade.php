<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/soee/src/backend/includes/conexao.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/soee/src/backend/controllers/home.php';

AuthHome::exigirTipo(['adm_geral', 'professor', 'adm_sala']);

$idModalidade = (int) ($_POST['id_modalidade'] ?? 0);
$acao         = trim($_POST['acao'] ?? '');
$tipo_user    = AuthHome::getTipo();

$dashboard_url = '/soee/src/frontend/views/dashboards/adm.php';
if ($tipo_user === 'professor') {
    $dashboard_url = '/soee/src/frontend/views/dashboards/professor.php';
} elseif ($tipo_user === 'adm_sala') {
    $dashboard_url = '/soee/src/frontend/views/dashboards/adm-sala.php';
}

if ($acao === 'excluir' && $idModalidade > 0) {
    try {
        $stmt = $conn->prepare("DELETE FROM modalidade WHERE id_modalidade = :id");
        $stmt->execute([':id' => $idModalidade]);

        $_SESSION['flash_msg']  = 'Modalidade excluída com sucesso!';
        $_SESSION['flash_tipo'] = 'sucesso';
        header("Location: $dashboard_url");
        exit;
    } catch (PDOException $e) {
        error_log("Erro ao excluir modalidade: " . $e->getMessage());
        $_SESSION['flash_msg']  = 'Não é possível excluir! Existem inscrições ou partidas vinculadas a esta modalidade.';
        $_SESSION['flash_tipo'] = 'erro';
        header("Location: $dashboard_url?erro=dependencia_db");
        exit;
    }
}

$redir = '/soee/src/frontend/views/forms/esporte.php';
$obrigatorios = ['nome_modalidade', 'tipo_modalidade', 'formato_modalidade',
                 'tipo_participacao', 'qtd_min_jogadores', 'qtd_max_jogadores',
                 'tipo_duracao', 'regulamento_modalidade', 'genero_modalidade'];

foreach ($obrigatorios as $campo) {
    if (empty($_POST[$campo])) {
        header("Location: $redir?erro=dados_incompletos");
        exit;
    }
}

$nome           = trim($_POST['nome_modalidade']);
$descricao      = trim($_POST['descricao_modalidade'] ?? '');
$tipo           = $_POST['tipo_modalidade'];
$formato        = $_POST['formato_modalidade'];
$participacao   = $_POST['tipo_participacao'];
$qtdMin         = (int) $_POST['qtd_min_jogadores'];
$qtdMax         = (int) $_POST['qtd_max_jogadores'];
$tipoDuracao    = $_POST['tipo_duracao'];
$regulamento    = trim($_POST['regulamento_modalidade']);
$genero         = $_POST['genero_modalidade'];

$duracaoMinutos = null;
$duracaoPontos  = null;
if ($tipoDuracao === 'minutos') {
    $duracaoMinutos = $_POST['duracao_minutos'] ?? null;
    if ($duracaoMinutos === 'outro') $duracaoMinutos = trim($_POST['outro_minutos'] ?? '');
} elseif ($tipoDuracao === 'pontos') {
    $duracaoPontos = $_POST['duracao_pontos'] ?? null;
    if ($duracaoPontos === 'outro') $duracaoPontos = (int) ($_POST['outro_pontos'] ?? 0) ?: null;
    else $duracaoPontos = (int) $duracaoPontos ?: null;
}

$fotoFinal = null;
$origemFoto = $_POST['origem_foto'] ?? 'nenhuma';

if ($origemFoto === 'upload' && !empty($_FILES['foto_arquivo']['tmp_name'])) {
    $file    = $_FILES['foto_arquivo'];
    $allowed = ['image/jpeg','image/png','image/gif','image/webp'];
    if (!in_array($file['type'], $allowed)) {
        header("Location: $redir?erro=foto_invalida"); exit;
    }
    if ($file['size'] > 5 * 1024 * 1024) {
        header("Location: $redir?erro=foto_grande"); exit;
    }
    $ext     = pathinfo($file['name'], PATHINFO_EXTENSION);
    $nome_arquivo = uniqid('modal_') . '.' . $ext;
    $destino = $_SERVER['DOCUMENT_ROOT'] . '/soee/src/frontend/assets/modalidades/' . $nome_arquivo;
    if (!is_dir(dirname($destino))) mkdir(dirname($destino), 0775, true);
    if (move_uploaded_file($file['tmp_name'], $destino)) {
        $fotoFinal = '/soee/src/frontend/assets/modalidades/' . $nome_arquivo;
    }
} elseif ($origemFoto === 'url' && !empty($_POST['foto_url'])) {
    $fotoFinal = trim($_POST['foto_url']);
}

if ($idModalidade === 0) {
    $check = $conn->prepare("SELECT id_modalidade FROM modalidade WHERE nome_modalidade = :nome LIMIT 1");
    $check->execute([':nome' => $nome]);
    if ($check->fetchColumn()) {
        header("Location: $redir?erro=modalidade_duplicada"); exit;
    }
}

try {
    if ($idModalidade > 0) {
        $sql = "UPDATE modalidade SET
                    nome_modalidade        = :nome,
                    descricao_modalidade   = :desc,
                    tipo_modalidade        = :tipo,
                    formato_modalidade     = :formato,
                    tipo_participacao      = :participacao,
                    qtd_min_jogadores      = :min,
                    qtd_max_jogadores      = :max,
                    ativo_modalidade       = :ativo,
                    genero_modalidade      = :genero,
                    tipo_duracao           = :tipoDur,
                    duracao_minutos        = :durMin,
                    duracao_pontos         = :durPts,
                    regulamento_modalidade = :regul
                    " . ($fotoFinal ? ', foto_modalidade = :foto' : '') . "
                WHERE id_modalidade = :id";

        $params = [
            ':nome'         => $nome,
            ':desc'         => $descricao,
            ':tipo'         => $tipo,
            ':formato'      => $formato,
            ':participacao' => $participacao,
            ':min'          => $qtdMin,
            ':max'          => $qtdMax,
            ':ativo'        => (isset($_POST['ativo_modalidade']) && ($_POST['ativo_modalidade'] == '1' || $_POST['ativo_modalidade'] == 'true')) ? true : false,
            ':genero'       => $genero,
            ':tipoDur'      => $tipoDuracao,
            ':durMin'       => $duracaoMinutos,
            ':durPts'       => $duracaoPontos,
            ':regul'        => $regulamento,
            ':id'           => $idModalidade,
        ];
        if ($fotoFinal) $params[':foto'] = $fotoFinal;

        $stmt = $conn->prepare($sql);
        $stmt->execute($params);

        $_SESSION['flash_msg']  = 'Modalidade atualizada com sucesso!';
        $_SESSION['flash_tipo'] = 'sucesso';
        header("Location: $dashboard_url");
        exit;

    } else {
        $stmt = $conn->prepare("
            INSERT INTO modalidade
                (nome_modalidade, descricao_modalidade, tipo_modalidade,
                 formato_modalidade, tipo_participacao, qtd_min_jogadores,
                 qtd_max_jogadores, ativo_modalidade, genero_modalidade,
                 foto_modalidade, tipo_duracao, duracao_minutos,
                 duracao_pontos, regulamento_modalidade)
            VALUES
                (:nome, :desc, :tipo, :formato, :participacao, :min, :max,
                 TRUE, :genero, :foto, :tipoDur, :durMin, :durPts, :regul)
        ");
        $stmt->execute([
            ':nome'         => $nome,
            ':desc'         => $descricao,
            ':tipo'         => $tipo,
            ':formato'      => $formato,
            ':participacao' => $participacao,
            ':min'          => $qtdMin,
            ':max'          => $qtdMax,
            ':genero'       => $genero,
            ':foto'         => $fotoFinal,
            ':tipoDur'      => $tipoDuracao,
            ':durMin'       => $duracaoMinutos,
            ':durPts'       => $duracaoPontos,
            ':regul'        => $regulamento,
        ]);

        $_SESSION['flash_msg']  = 'Modalidade cadastrada com sucesso!';
        $_SESSION['flash_tipo'] = 'sucesso';
        header("Location: $dashboard_url?ok=1");
        exit;
    }

} catch (PDOException $e) {
    error_log("Erro de banco de dados no salvar-modalidade: " . $e->getMessage());
    header("Location: $redir?erro=erro_db");
    exit;
}