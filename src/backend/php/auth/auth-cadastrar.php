<?php
// Incluindo o banco
require_once $_SERVER['DOCUMENT_ROOT'] . "/soee/src/backend/php/include/conexao.php";

// Verifica se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: form-cadastro.php");
    die();
}

// Verifica campos obrigatórios
if (
    empty($_POST['nome']) ||
    empty($_POST['email']) ||
    empty($_POST['senha']) ||
    empty($_POST['genero'])
) {
    die("Preencha todos os campos obrigatórios.");
}

// Recebe os dados
$nome = trim($_POST['nome']);
$email = trim($_POST['email']);
$senha = $_POST['senha'];
$genero = $_POST['genero'];

// Criptografa senha
$senhaHash = password_hash($senha, PASSWORD_DEFAULT);

try {
    // Verifica se email já existe
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);

    if ($verifica->rowCount() > 0) {
        die("Email já cadastrado.");
    }

    // Inserção no banco
    $stmt = $pdo->prepare("
        INSERT INTO usuarios 
        (nome,email,senha,genero)
        VALUES
        (:nome,:email,:senha,genero)
    ");

    $stmt->execute([
        ':nome' => $nome,
        ':email' => $email,
        ':senha' => $senhaHash,
        ':genero' => $genero,
    ]);

    // Redireciona para login
    header("Location:/soee/index.php?cadastro=sucesso");
    die();

}catch(PDOException $e) {
    echo "Erro no cadastro: " . $e->getMessage();
}
?>