<?php
session_start();
require_once __DIR__ . '/../../includes/conexao.php'; 
require_once __DIR__ . '/../../controllers/home.php';

AuthHome::exigirTipo(['professor']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome  = filter_input(INPUT_POST, 'nome_usuario', FILTER_SANITIZE_SPECIAL_CHARS);
    $email = filter_input(INPUT_POST, 'email_usuario', FILTER_VALIDATE_EMAIL);
    $senha = $_POST['senha_usuario'] ?? '';
    $genero = filter_input(INPUT_POST, 'genero_usuario', FILTER_SANITIZE_SPECIAL_CHARS);
    $ativo  = filter_input(INPUT_POST, 'ativo_usuario', FILTER_VALIDATE_INT);

    if (!$nome || !$email || strlen($senha) < 6 || !in_array($genero, ['m', 'f', 'n'])) {
        $_SESSION['flash_msg'] = "Preencha todos os campos obrigatórios corretamente e com senha válida.";
        $_SESSION['flash_tipo'] = "erro";
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    }

    try {
        $stmtCheck = $conn->prepare("SELECT id_usuario FROM usuarios WHERE email_usuario = ?");
        $stmtCheck->execute([$email]);
        if ($stmtCheck->fetch()) {
            $_SESSION['flash_msg'] = "O e-mail digitado já está em uso.";
            $_SESSION['flash_tipo'] = "erro";
            header("Location: " . $_SERVER['HTTP_REFERER']);
            exit;
        }

        $fotoCaminho = null;
        if (isset($_FILES['foto_usuario']) && $_FILES['foto_usuario']['error'] === UPLOAD_ERR_OK) {
            $extensoesValidas = ['jpg', 'jpeg', 'png'];
            $extensao = strtolower(pathinfo($_FILES['foto_usuario']['name'], PATHINFO_EXTENSION));

            if (in_array($extensao, $extensoesValidas)) {
                $novoNome = uniqid('prof_', true) . '.' . $extensao;
                $diretorioDestino = __DIR__ . '/../../../frontend/assets/uploads/perfis/';
                
                if (!is_dir($diretorioDestino)) {
                    mkdir($diretorioDestino, 0755, true);
                }

                if (move_uploaded_file($_FILES['foto_usuario']['tmp_name'], $diretorioDestino . $novoNome)) {
                    
                    $fotoCaminho = '/soee/src/frontend/assets/uploads/perfis/' . $novoNome;
                }
            }
        }
        $senhaHash = password_hash($senha, PASSWORD_BCRYPT);


        $sql = "INSERT INTO usuarios (nome_usuario, email_usuario, senha_usuario, genero_usuario, tipo_usuario, ativo_usuario, foto_usuario) 
                VALUES (?, ?, ?, ?, 'professor', ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$nome, $email, $senhaHash, $genero, $ativo, $fotoCaminho]);

        $_SESSION['flash_msg'] = "Professor cadastrado com sucesso!";
        $_SESSION['flash_tipo'] = "sucesso";

    } catch (PDOException $e) {
        $_SESSION['flash_msg'] = "Erro no banco de dados ao salvar o professor: " . $e->getMessage();
        $_SESSION['flash_tipo'] = "erro";
    }

    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
}