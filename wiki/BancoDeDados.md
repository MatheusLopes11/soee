# Banco de Dados (Supabase/PostgreSQL)

O banco de dados utilizado é o PostgreSQL, hospedado na nuvem pelo **Supabase**. Isso garante alta disponibilidade e performance.

## Estrutura e Relacionamentos
O sistema é vasto e relacional. Principais tabelas:
- `usuario`: Guarda alunos, professores e admins (com senhas criptografadas).
- `curso` e `turma`: Classifica de onde são os alunos.
- `modalidade`: Contém as regras dos esportes (ex: quadra, mesa, número de jogadores, mata-mata, etc).
- `edicao` e `edicao_modalidade`: Representa o campeonato anual (ex: "Interclasses 2026").
- `inscricao`: Liga usuários às modalidades abertas em uma edição. Conta com suporte a inscrições individuais, duplas ou times (`grupo_dupla_id`).
- `partida` e `resultado`: Gerencia as chaves e os placares.
- `classificacao`: Mantém os pontos e saldos de gols/vitórias atualizados para fases de grupos.

## Enums
Muitas tabelas fazem uso de `ENUMs` no PostgreSQL para limitar valores em um conjunto pré-determinado, prevenindo dados inconsistentes no banco. Exemplos de enums são: `tipo_usuario_enum`, `fase_partida_enum` e `status_inscricao_enum`.
