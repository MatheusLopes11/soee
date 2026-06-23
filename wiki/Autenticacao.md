# Autenticação e Segurança

O SOEE conta com um sistema de autenticação próprio para proteger as rotas de acordo com o nível de acesso do usuário. 

## Tipos de Usuários
1. **Aluno:** Possui acesso para se inscrever em modalidades, formar duplas/trios e acompanhar classificações.
2. **Professor:** Permissões estendidas para gerenciar turmas e eventos sob sua jurisdição.
3. **Admin de Sala:** Tem permissão para ações administrativas focadas em uma sala específica.
4. **Admin Geral:** Permissão total ao sistema, possibilitando o cadastro de campeonatos, sorteios e manipulação de notas.

## Hashes de Senhas (Bcrypt)
Para garantir a segurança dos dados dos alunos, todas as senhas são armazenadas no banco de dados Supabase em formato de hash, utilizando o algoritmo `bcrypt` nativo do PHP (através da função `password_hash()`). Isso impede que qualquer pessoa com acesso ao banco consiga ler as senhas originais. 
Na hora do login (`AuthHome::processarLogin`), o sistema compara a senha usando `password_verify()`.

## Proteção de Credenciais
Os dados de conexão ao banco de dados não ficam no código-fonte. O sistema lê as informações de acesso (host, banco, usuário, senha) através de um arquivo `.env` localizado na raiz do projeto. Isso impede vazamento de dados caso o código seja público no GitHub.
