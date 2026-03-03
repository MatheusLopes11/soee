<!DOCTYPE html>
<html lang="pt-br">
<head>
    <!-- Meta Dados -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Links -->
    <link rel="icon" type="image/png" href="/soee/src/images/logo-soee.png">
    <title>Cadastrar</title>
</head>
    <body>
        <!-- Formulario Cadastro -->
        <form action="index.php" method="POST">
            <label>Nome:</label><br>
                <input type="text" name="nome"><br>

            <label>Email:</label><br>
                <input type="email" name="email"><br>

            <label>Senha:</label><br>
                <input type="password" name="senha"><br>

            <label>RM:</label><br>
                <input type="number" name="rm"><br>

            <label>RA:</label><br>
                <input type="number" name="ra"><br>

            <label>CPF:</label><br>
                <input type="number" name="cpf"><br>

            <label>Genero:</label><br>
                <input type="text" name="genero"><br>

            <label>Data de Nascimento:</label><br>
                <input type="text" name="data_nascimento"><br>

            <label>Tipo:</label><br>
                <input type="text" name="tipo_usuario"><br>

            <label>Foto:</label><br>
                <input type="text" name="foto"><br><br>
            
            <button type="submit" name="cadastrar">Cadastrar</button><br>
            <a href="/soee/index.php">Entrar</a><br>
        </form>
    </body>
</html>