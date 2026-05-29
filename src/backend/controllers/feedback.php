<?php
session_start();

// CAMINHO CORRIGIDO: feedback.php fica em /soee/src/backend/actions/
// '/../includes/conexao.php' → conexao está em /soee/src/backend/includes/
// O __DIR__ de actions/ sobe um nível para backend/ e entra em includes/
try {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/soee/src/backend/includes/conexao.php';

    $usuario_id = $_SESSION['user_id'] ?? null;

    if (
        empty($_POST['nome_feedback'])  ||
        empty($_POST['turma_feedback']) ||
        empty($_POST['email_feedback']) ||
        empty($_POST['categorias'])     ||
        empty($_POST['tipo_feedback'])  ||
        empty($_POST['mensagem_feedback'])
    ) {
        die("Preencha todos os campos.");
    }

    $nome     = $_POST['nome_feedback'];
    $turma    = $_POST['turma_feedback'];
    $email    = $_POST['email_feedback'];
    $tipo     = $_POST['tipo_feedback'];
    $mensagem = $_POST['mensagem_feedback'];

    $categorias = is_array($_POST['categorias'])
        ? implode(',', $_POST['categorias'])
        : '';

    $sql = "INSERT INTO feedback (
        usuario_id_usuario,
        nome_feedback,
        email_feedback,
        turma_feedback,
        tipo_feedback,
        categorias_feedback,
        mensagem_feedback
    ) VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);

    $stmt->execute([
        $usuario_id,
        $nome,
        $email,
        $turma,
        $tipo,
        $categorias,
        $mensagem
    ]);

    header("Location: /soee/src/frontend/views/forms/feedback.php");
    exit;

} catch (PDOException $erro) {
    error_log("Erro feedback: " . $erro->getMessage());
    echo "Erro ao enviar feedback. Tente novamente.";
}