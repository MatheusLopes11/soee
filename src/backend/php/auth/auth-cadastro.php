<?php

if (!isset($_POST['nome'], $_POST['email'], $_POST['senha'], $_POST['rm'], $_POST['ra'], $_POST['cpf'], $_POST['genero'], $_POST['data_nascimento'], $_POST['genero'], $_POST['tipo_usuario'], $_POST['foto'])) {
    header('Location: form-cadastro.php');
    die();
}

?>
