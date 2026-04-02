<?php include __DIR__ . '/../includes/html-above.php'; ?>

<head>

    <title>Criar Conta</title>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="/soee/src/frontend/styles/forms/cadastrar.css">
    <link rel="icon" type="image/png" href="/soee/src/frontend/assets/icons/logo-soee.png">
  
  </head>
<body>

  <form action="/soee/src/backend/controllers/cadastrar.php" method="POST">
    <section>
      <div>

      <section class="section-título-breve">
        <div class="div-título-breve">

            <div class="h1-conteudo">
              <h1>Bem-Vindo!</h1>
            </div>

            <div class="p-conteudo">
              <p>Cadastre-se no nosso sistema de acordo com as informaçôes abaixo</p>
            </div>

        </div>
      </section>


      <div>

        <div>
          <label>Nome</label>
        </div>

        <div>
          <input type="text" name="nome" placeholder="Digite seu Nome" required>
        </div>

      </div>


      <div>

        <div>
          <label>E-mail</label>
        </div>

        <div>
          <input type="text" name="email" placeholder="Digite seu Email" required>
        </div>

      </div>


      <div>

        <div>
          <label>Senha</label>
        </div>

        <div>
          <input type="password" name="senha" placeholder="Digite sua Senha" required>
        </div>

      </div><br>


      <div>

        <div>
          <label>Escolha seu Gênero:</label>
        </div>


        <div>
          <label>Masculino</label>
        </div>

          <div>
            <input type="radio" name="genero" value="m" required> 
          </div>


        <div>
          <label>Feminino</label>
        </div>

          <div>
            <input type="radio" name="genero" value="f">
          </div>
          
      </div><br>

      <div>
        <label for="input-foto">Selecionar foto de perfil</label>
      </div>

      <div class="input-foto">
        <input type="file" name="foto" id="input-foto">
      </div><br>

      <div class="botao-submit">
        <input type="submit" name="botao" value="Cadastrar">
      </div>


      </div>
    </section>
  </form>

  <script src="/soee/src/frontend/scripts/cadastrar.js"></script>

</body>
<?php include __DIR__ . '/../includes/html-down.php'; ?>