<?php include __DIR__ . '/src/frontend/views/includes/html-above.php';?>

<head>

    <title>Entrar</title>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

    <link rel="stylesheet" href="/soee/src/frontend/styles/forms/index.css">
    <link rel="icon" type="image/png" href="/soee/src/frontend/assets/icons/logo-soee.png">

</head>

<body>
        <form action="/soee/src/backend/controllers/index.php" method="POST">
            <section class="todo-form">
                <div class="todo-form">

                <section class="section-informacoes-breve">
                    <div class="section-informacoes-breve"> <!-- (TÍTULO BREVE DIZENDO OQUE FAZER PARA CONTINUAR) -->

                        <div class="h1-conteudo">
                            <h1>Bem vindo!</h1>
                        </div>

                        <div class="p-conteudo">
                            <p>Digite dados para conhecer nosso sistema</p>
                        </div>

                    </div><br>
                </section>

                <div class="campo-label-input"> <!-- (PARTE DO E-MAIL) -->

                    <div class="label-conteudo">
                        <label>E-mail</label>
                    </div>

                    <div class="input-conteudo">
                        <input type="text" name="email" placeholder="Digite seu Email" required>
                    </div>

                </div>


                <div class="campo-label-input"> <!-- (PARTE DA SENHA) -->

                    <div class="label-conteudo">
                        <label>Senha</label>
                    </div>
                    <div class="input-conteudo">
                        <input type="password" name="senha" placeholder="Digite sua Senha" required>
                    </div>

                </div><br>


                <div class="input-submit"> <!-- (PARTE BOTÃO CADASTRAR) -->
                    <input type="submit" name="botao" value="CADASTRAR"> 
                </div>


                <div class="a-entrar-sem-conta"> <!-- (CAMINHO PARA ENTRAR NA HOME SEM CADASTRO) -->
                    <a href="/soee/src/frontend/views/pages/home.php">Entrar sem Conta</a> 
                </div>


                <div class="a-cadastrar"> <!-- (CAMINHO PARA CADASTRAR) -->
                    <a href="/soee/src/frontend/views/forms/cadastrar.php">Cadastrar-se</a>
                </div>

                
                </div>
            </section>
        </form>

    <script src="/soee/src/frontend/scripts/index.js"></script> <!-- (Link JavaScript SE A GENTE USAR!) -->

</body>

<?php include __DIR__ . '/src/frontend/views/includes/html-down.php';?>