# Módulos e Funcionalidades

O projeto está dividido logicamente da seguinte forma:

- **frontend/views:** Contém todas as telas HTML e PHP renderizadas para os usuários. Está dividido entre `dashboards` (as páginas iniciais após login dependendo do tipo do usuário), `forms` (telas de cadastro, edição de modalidades), e `modals` (caixas de diálogo flutuantes para edição rápida).
- **backend/controllers:** Recebe as requisições principais do frontend e encaminha para as ações de negócio.
- **backend/actions:** Scripts focados em uma única ação (ex: `salvar-usuario.php`, `gerar-sorteio.php`, `avancar-fase.php`). Realizam validação, query de inserção/update e redirecionamento.
- **backend/helpers:** Funções de suporte, como os cálculos de chaveamento para campeonatos.
- **backend/includes:** Scripts que são repetidamente incluídos, como a conexão segura com o banco (`conexao.php`) e a geração de relatórios com o `fpdf`.
