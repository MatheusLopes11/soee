# SOEE - Sistema de Organização Esportiva Escolar

O **SOEE** é um sistema projetado para organizar e gerenciar eventos esportivos escolares de forma eficiente. Ele suporta a criação de modalidades individuais e por times, gerenciamento de inscrições, chaveamentos, súmulas e muito mais.

##  Instituição
<p>
Etec Juscelino Kubitschek de Oliveira<br>
https://etecjk.cps.sp.gov.br/
</p>

##  Curso
<p>Técnico em Desenvolvimento de Sistemas</p>

##  Tecnologias Aplicadas

O projeto utiliza um conjunto robusto de tecnologias para oferecer segurança, velocidade e compatibilidade:

<p align="center">
  <table>
    <tr>
      <td align="center">
        <img src="https://raw.githubusercontent.com/devicons/devicon/master/icons/html5/html5-original.svg" height="40"/><br>
        <sub>HTML5</sub>
      </td>
      <td align="center">
        <img src="https://raw.githubusercontent.com/devicons/devicon/master/icons/css3/css3-original.svg" height="40"/><br>
        <sub>CSS3</sub>
      </td>
      <td align="center">
        <img src="https://raw.githubusercontent.com/devicons/devicon/master/icons/javascript/javascript-original.svg" height="40"/><br>
        <sub>JavaScript</sub>
      </td>
      <td align="center">
        <img src="https://raw.githubusercontent.com/devicons/devicon/master/icons/php/php-original.svg" height="40"/><br>
        <sub>PHP</sub>
      </td>
      <td align="center">
        <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/postgresql/postgresql-original.svg" height="40"/><br>
        <sub>PostgreSQL / Supabase</sub>
      </td>
      <td align="center">
        <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/apache/apache-original.svg" height="40"/><br>
        <sub>Apache</sub>
      </td>
    </tr>
    <tr>
      <td align="center">
        <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/git/git-original.svg" height="40"/><br>
        <sub>Git</sub>
      </td>
      <td align="center">
        <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/trello/trello-plain.svg" height="40"/><br>
        <sub>Trello</sub>
      </td>
      <td align="center">
        <img src="https://upload.wikimedia.org/wikipedia/commons/9/9a/Visual_Studio_Code_1.35_icon.svg" height="40"/><br>
        <sub>VsCode</sub>
      </td>
      <td align="center">
        <img src="https://upload.wikimedia.org/wikipedia/commons/d/dc/XAMPP_Logo.png" height="40"/><br>
        <sub>XAMPP</sub>
      </td>
      </td>
    </tr>
  </table>
</p>

##  Orientador
- Prof Helton de Andrade Silva

##  Desenvolvedores
- CARLOS HENRIQUE VALENTIM SILVA
- MATHEUS FERREIRA LOPES
- MIGUEL LOPES AQUINEZ DA SILVA
- HENRIQUE BATISTA ORLOVAS
- ISABELLY BARBOSA SANTOS

##  Wiki do Projeto

Para entender o funcionamento interno do projeto e saber como contribuir, preparamos uma documentação completa na nossa Wiki (encontrada na pasta `wiki/` deste repositório):

1. [Página Inicial](wiki/Home.md)
2. [Autenticação e Segurança](wiki/Autenticacao.md)
3. [Banco de Dados (Supabase)](wiki/BancoDeDados.md)
4. [Módulos do Sistema](wiki/Modulos.md)

##  Como executar localmente

1. Clone o repositório em sua pasta `htdocs` (se usando XAMPP).
2. Configure o banco de dados Supabase e insira os dados usando o arquivo `sql/soee.sql`.
3. Renomeie o arquivo `.env.example` (ou crie um arquivo `.env`) na raiz do projeto contendo as variáveis:
   ```env
   DB_HOST=seu_host_supabase
   DB_PORT=6543
   DB_NAME=postgres
   DB_USER=seu_usuario
   DB_PASS=sua_senha
   ```
4. Inicie o XAMPP (Apache).
5. Acesse `http://localhost/soee` no navegador.
