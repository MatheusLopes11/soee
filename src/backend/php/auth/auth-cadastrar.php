<?php
ob_start();
include __DIR__ . '/../include/conexao.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /soee/src/backend/php/form/form-cadastrar.php');
    exit();
}

/* ── Coleta ── */
$nome      = trim($_POST['nome']          ?? '');
$email     = trim($_POST['email']         ?? '');
$senha     = $_POST['senha']              ?? '';
$confirma  = $_POST['confirma_senha']     ?? '';
$generoRaw = $_POST['genero']             ?? '';
$ano       = (int) ($_POST['ano_serie']   ?? 0);
$curso     = trim($_POST['curso']         ?? '');

/* ── Validações ── */
if (!$nome || !$email || !$senha || !$confirma || !$generoRaw || !$ano || !$curso) {
    header('Location: /soee/src/backend/php/form/form-cadastrar.php?erro=campos');
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: /soee/src/backend/php/form/form-cadastrar.php?erro=email');
    exit();
}

if ($senha !== $confirma) {
    header('Location: /soee/src/backend/php/form/form-cadastrar.php?erro=senha');
    exit();
}

if (strlen($senha) < 8) {
    header('Location: /soee/src/backend/php/form/form-cadastrar.php?erro=senha_curta');
    exit();
}

$cursosValidos = ['MTEC', 'EMIF', 'MTECPI'];
if (!in_array($curso, $cursosValidos)) {
    header('Location: /soee/src/backend/php/form/form-cadastrar.php?erro=curso');
    exit();
}

if ($ano < 1 || $ano > 3) {
    header('Location: /soee/src/backend/php/form/form-cadastrar.php?erro=ano');
    exit();
}

/* ── Gênero ── */
$genero = in_array($generoRaw, ['m', 'f']) ? $generoRaw : 'n';

/* ── Hash seguro da senha ── */
$senhaHash = password_hash($senha, PASSWORD_DEFAULT);

try {
    /* ── Verifica e-mail duplicado ── */
    $stmt = $conn->prepare("SELECT id_usuario FROM usuario WHERE email_usuario = :email LIMIT 1");
    $stmt->execute([':email' => $email]);
    if ($stmt->fetch()) {
        header('Location: /soee/src/backend/php/form/form-cadastrar.php?erro=email_existe');
        exit();
    }

    /* ── Busca a turma pelo ano + sigla do curso ── */
    $stmtTurma = $conn->prepare("
        SELECT t.id_turma
        FROM turma t
        INNER JOIN curso c ON c.id_curso = t.curso_id_curso
        WHERE c.sigla_curso     = :sigla
          AND t.ano_serie_turma = :ano
        ORDER BY t.ano_letivo_turma DESC
        LIMIT 1
    ");
    $stmtTurma->execute([':sigla' => $curso, ':ano' => $ano]);
    $turma = $stmtTurma->fetch(PDO::FETCH_ASSOC);

    if (!$turma) {
        header('Location: /soee/src/backend/php/form/form-cadastrar.php?erro=turma_nao_encontrada');
        exit();
    }

    /* ── Insere o novo usuário como aluno ── */
    $insert = $conn->prepare("
        INSERT INTO usuario
            (turma_id_turma, nome_usuario, email_usuario, senha_usuario,
             genero_usuario, tipo_usuario, ativo_usuario)
        VALUES
            (:turma, :nome, :email, :senha,
             :genero, 'aluno', 1)
    ");
    $insert->execute([
        ':turma'  => $turma['id_turma'],
        ':nome'   => $nome,
        ':email'  => $email,
        ':senha'  => $senhaHash,
        ':genero' => $genero,
    ]);

    header('Location: /soee/index.php?cadastro=sucesso');
    exit();

} catch (PDOException $e) {
    error_log('Erro no cadastro: ' . $e->getMessage());
    header('Location: /soee/src/backend/php/form/form-cadastrar.php?erro=servidor');
    exit();
}