<?php
try{
    include __DIR__ . '/../models/db.php';

    if(!isset($_POST['nome'], $_POST['email'], $_POST['senha'], $_POST['genero'] , $_POST['foto']) || 
        empty($_POST['nome']) || empty($_POST['email']) || empty($_POST['senha']) || empty($_POST['genero']) || empty($_POST['foto'])) {

        header('Location: /soee/src/frontend/views/forms/cadastrar.php');
        die("Preencha os campos obrigátórios.");

    } else {

    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $senha = $_POST['senha'];
    $genero = $_POST['genero'];
    $foto = $_POST['foto'];

        $sql = "insert into usuario(nome_usuario, email_usuario, senha_usuario, genero_usuario, foto_usuario) values(?,?,?,?,?);";
        $stmt = $pdo->prepare($sql);

        $stmt->binParam(1, $nome);
        $stmt->binParam(2, $email);
        $stmt->binParam(3, $senha);
        $stmt->binParam(4, $genero);
        $stmt->binParam(5, $foto);

        $stmt->execute();

        $return = $stmt->fecth(PDO::FETCH_ASSOC);

            if($return){

                header('Location: /soee/index');
                die("Cadastro concluido, Bem vindo {$nome}!.");

            } else {

                header('Location: /soee/src/frotend/views/forms/cadastrar.php');
                die("Você já cadastrou {$nome}, coloque seu email e senha para entrar.");
            }
    }

} catch(PDOException $erro) {
    echo 'Erro ao processar o Cadastro: '. $erro->getMessage(); 
}
?>