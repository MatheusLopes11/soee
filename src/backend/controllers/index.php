<?php
try{
    include __DIR__ . '/../models/db.php';

    if (!isset($_POST['email'], $_POST['senha']) || empty($_POST['email']) || empty($_POST['senha'])) {

        header('location: /soee/index.php');
        die("Preencha os campos obrigátorios.");

    } else {

    $email = $_POST['email'];
    $senha = $_POST['senha'];

    $sql = "select id_usuario from usuario where nome_usuario=? and senha_usuario=?;";
    $stmt = $pdo->prepare($sql);

    $stmt->bindParam(1, $email);
    $stmt->bindParam(2, $senha);
    
    $stmt->execute();

    $return = $stmt->fetch(PDO::FETCH_ASSOC);

        if($return) {

            header('Location: /soee/src/frontend/views/pages/home.php');
            die("Bem-Vindo");

        } else {

            header('Location: /soee/index.php');
            die("Você não cadastrou, cadastre-se para usufluir o sistema.");

        }
    }
    
} catch(PDOException $erro) {
    echo 'Erro ao processar o Login: ' . $erro->getMessage();
}
?>