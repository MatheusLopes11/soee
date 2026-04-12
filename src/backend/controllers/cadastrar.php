<?php
ob_start();
require __DIR__ . '/../includes/conexao.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /soee/src/frontend/views/forms/cadastrar.php');
    exit();
}

/* ── Coleta ── */
$nome      = trim($_POST['nome'] ?? '');
$email     = trim($_POST['email'] ?? '');
$senha     = $_POST['senha'] ?? '';
$confirma  = $_POST['confirma_senha'] ?? '';
$generoRaw = $_POST['genero'] ?? '';
$ano       = (int) ($_POST['ano_serie'] ?? 0);
$curso     = trim($_POST['curso'] ?? '');

/* ── Validações ── */
if ($nome === '' || $email === '' || $senha === '' || $confirma === '' || $generoRaw === '' || $ano === 0 || $curso === '') {
    header('Location: /soee/src/frontend/views/forms/cadastrar.php?erro=campos');
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: /soee/src/frontend/views/forms/cadastrar.php?erro=email');
    exit();
}

if ($senha !== $confirma) {
    header('Location: /soee/src/frontend/views/forms/cadastrar.php?erro=senha');
    exit();
}

if (strlen($senha) < 8) {
    header('Location: /soee/src/frontend/views/forms/cadastrar.php?erro=senha_curta');
    exit();
}

/* ── Gênero ── */
$genero = match ($generoRaw) {
    'm' => 'm',
    'f' => 'f',
    default => 'n'
};

/* ── Hash senha ── */
$senhaHash = password_hash($senha, PASSWORD_DEFAULT);

try {

    /* ── Verifica email duplicado ── */
    $stmt = $conn->prepare("
        SELECT id_usuario 
        FROM usuario 
        WHERE email_usuario = :email 
        LIMIT 1
    ");
    $stmt->execute([':email' => $email]);

    if ($stmt->fetch()) {
        header('Location: /soee/src/frontend/views/forms/cadastrar.php?erro=email_existe');
        exit();
    }

    /* ── BUSCA CURSO ── */
    $stmtCurso = $conn->prepare("
        SELECT id_curso 
        FROM curso 
        WHERE sigla_curso = :sigla 
        LIMIT 1
    ");
    $stmtCurso->execute([':sigla' => $curso]);
    $cursoData = $stmtCurso->fetch(PDO::FETCH_ASSOC);

    if (!$cursoData) {
        header('Location: /soee/src/frontend/views/forms/cadastrar.php?erro=curso');
        exit();
    }

    $idCurso = $cursoData['id_curso'];

    /* ── BUSCA TURMA ── */
    $stmtTurma = $conn->prepare("
        SELECT id_turma
        FROM turma
        WHERE curso_id_curso = :curso
          AND ano_serie_turma = :ano
        ORDER BY ano_letivo_turma DESC
        LIMIT 1
    ");

    $stmtTurma->execute([
        ':curso' => $idCurso,
        ':ano'   => $ano
    ]);

    $turma = $stmtTurma->fetch(PDO::FETCH_ASSOC);

    if (!$turma) {
        header('Location: /soee/src/frontend/views/forms/cadastrar.php?erro=turma_nao_encontrada');
        exit();
    }

    /* ── INSERÇÃO USUÁRIO ── */
    $insert = $conn->prepare("
        INSERT INTO usuario
        (turma_id_turma, nome_usuario, email_usuario, senha_usuario, genero_usuario, tipo_usuario, ativo_usuario)
        VALUES
        (:turma, :nome, :email, :senha, :genero, 'aluno', 1)
    ");

    $insert->execute([
        ':turma'  => $turma['id_turma'],
        ':nome'   => $nome,
        ':email'  => $email,
        ':senha'  => $senhaHash,
        ':genero' => $genero
    ]);

    header('Location: /soee/index.php?cadastro=sucesso');
    exit();

} catch (PDOException $e) {
    echo "ERRO NO CADASTRO: " . $e->getMessage();
    exit();
}