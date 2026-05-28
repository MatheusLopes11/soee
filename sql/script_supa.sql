-- =========================================
-- SOEE - PostgreSQL / Supabase
-- =========================================

-- =========================================
-- REMOÇÃO
-- =========================================

DROP TABLE IF EXISTS feedback CASCADE;
DROP TABLE IF EXISTS foto_perfil CASCADE;
DROP TABLE IF EXISTS sorteio_gerado CASCADE;
DROP TABLE IF EXISTS classificacao CASCADE;
DROP TABLE IF EXISTS sumula CASCADE;
DROP TABLE IF EXISTS resultado CASCADE;
DROP TABLE IF EXISTS partida CASCADE;
DROP TABLE IF EXISTS inscricao CASCADE;
DROP TABLE IF EXISTS edicao_modalidade CASCADE;
DROP TABLE IF EXISTS edicao CASCADE;
DROP TABLE IF EXISTS modalidade CASCADE;
DROP TABLE IF EXISTS usuario CASCADE;
DROP TABLE IF EXISTS turma CASCADE;
DROP TABLE IF EXISTS curso CASCADE;

-- =========================================
-- ENUMS
-- =========================================

CREATE TYPE periodo_enum AS ENUM (
    'manha',
    'tarde',
    'noite'
);

CREATE TYPE tipo_usuario_enum AS ENUM (
    'aluno',
    'adm_sala',
    'adm_geral',
    'professor'
);

CREATE TYPE tipo_modalidade_enum AS ENUM (
    'quadra',
    'mesa',
    'outro'
);

CREATE TYPE formato_modalidade_enum AS ENUM (
    'mata_mata',
    'grupos',
    'grupos_mata_mata',
    'todos_contra_todos'
);

CREATE TYPE tipo_participacao_enum AS ENUM (
    'solo',
    'dupla',
    'trio',
    'time'
);

CREATE TYPE genero_modalidade_enum AS ENUM (
    'masculino',
    'feminino',
    'misto'
);

CREATE TYPE tipo_duracao_enum AS ENUM (
    'minutos',
    'pontos'
);

CREATE TYPE status_edicao_enum AS ENUM (
    'planejamento',
    'inscricoes',
    'em_andamento',
    'encerrado'
);

CREATE TYPE status_edicao_modalidade_enum AS ENUM (
    'inscricoes',
    'em_andamento',
    'encerrado'
);

CREATE TYPE status_inscricao_enum AS ENUM (
    'ativa',
    'cancelada'
);

CREATE TYPE fase_partida_enum AS ENUM (
    'grupos',
    'oitavas',
    'quartas',
    'semi',
    'final',
    'terceiro_lugar'
);

CREATE TYPE status_partida_enum AS ENUM (
    'agendada',
    'realizada',
    'cancelada',
    'wo'
);

CREATE TYPE status_sumula_enum AS ENUM (
    'pendente',
    'validada',
    'rejeitada'
);

CREATE TYPE tipo_feedback_enum AS ENUM (
    'elogio',
    'sugestao',
    'critica',
    'problema'
);

CREATE TYPE status_feedback_enum AS ENUM (
    'pendente',
    'lido',
    'respondido'
);

-- =========================================
-- CURSO
-- =========================================

CREATE TABLE curso (
    id_curso INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    nome_curso VARCHAR(60) NOT NULL,
    sigla_curso VARCHAR(15) NOT NULL UNIQUE
);

-- =========================================
-- TURMA
-- =========================================

CREATE TABLE turma (
    id_turma INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    curso_id_curso INT NOT NULL,
    nome_turma VARCHAR(20) NOT NULL,
    ano_serie_turma INT NOT NULL,
    ano_letivo_turma SMALLINT NOT NULL,
    periodo_turma periodo_enum NOT NULL,

    CONSTRAINT fk_turma_curso
        FOREIGN KEY (curso_id_curso)
        REFERENCES curso(id_curso)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
);

-- =========================================
-- USUARIO
-- =========================================

CREATE TABLE usuario (
    id_usuario INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,

    turma_id_turma INT NULL,

    nome_usuario VARCHAR(100) NOT NULL,

    email_usuario VARCHAR(120) NOT NULL UNIQUE,

    senha_usuario VARCHAR(255),

    genero_usuario CHAR(1) NOT NULL,

    tipo_usuario tipo_usuario_enum
        NOT NULL
        DEFAULT 'aluno',

    foto_perfil_usuario VARCHAR(255),

    remember_token VARCHAR(255),

    ativo_usuario BOOLEAN DEFAULT TRUE,

    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_usuario_turma
        FOREIGN KEY (turma_id_turma)
        REFERENCES turma(id_turma)
        ON DELETE SET NULL
        ON UPDATE CASCADE
);

-- =========================================
-- MODALIDADE
-- =========================================

CREATE TABLE modalidade (
    id_modalidade INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,

    nome_modalidade VARCHAR(60) NOT NULL UNIQUE,

    descricao_modalidade TEXT,

    tipo_modalidade tipo_modalidade_enum NOT NULL,

    formato_modalidade formato_modalidade_enum NOT NULL,

    tipo_participacao tipo_participacao_enum NOT NULL,

    qtd_min_jogadores INT NOT NULL,

    qtd_max_jogadores INT NOT NULL,

    ativo_modalidade BOOLEAN DEFAULT TRUE,

    genero_modalidade genero_modalidade_enum
        NOT NULL
        DEFAULT 'misto',

    foto_modalidade VARCHAR(255),

    regulamento_modalidade TEXT,

    tipo_duracao tipo_duracao_enum,

    duracao_minutos VARCHAR(10),

    duracao_pontos SMALLINT
);

-- =========================================
-- EDICAO
-- =========================================

CREATE TABLE edicao (
    id_edicao INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,

    nome_edicao VARCHAR(80) NOT NULL,

    ano_edicao SMALLINT NOT NULL,

    data_inicio_edicao DATE NOT NULL,

    data_fim_edicao DATE,

    status_edicao status_edicao_enum
        DEFAULT 'planejamento',

    descricao_edicao TEXT
);

-- =========================================
-- EDICAO_MODALIDADE
-- =========================================

CREATE TABLE edicao_modalidade (
    id_edicao_modalidade INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,

    edicao_id_edicao INT NOT NULL,

    modalidade_id_modalidade INT NOT NULL,

    data_inicio_inscricao DATE NOT NULL,

    data_fim_inscricao DATE NOT NULL,

    status_edicao_modalidade
        status_edicao_modalidade_enum
        DEFAULT 'inscricoes',

    CONSTRAINT fk_em_edicao
        FOREIGN KEY (edicao_id_edicao)
        REFERENCES edicao(id_edicao)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_em_modalidade
        FOREIGN KEY (modalidade_id_modalidade)
        REFERENCES modalidade(id_modalidade)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
);

-- =========================================
-- INSCRICAO
-- =========================================

CREATE TABLE inscricao (
    id_inscricao INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,

    usuario_id_usuario INT NOT NULL,

    edicao_modalidade_id INT NOT NULL,

    numero_camisa_inscricao INT,

    nome_camisa_inscricao VARCHAR(20),

    posicao_inscricao VARCHAR(40),

    capitao_inscricao BOOLEAN DEFAULT FALSE,

    data_inscricao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    status_inscricao status_inscricao_enum
        DEFAULT 'ativa',

    CONSTRAINT fk_inscricao_usuario
        FOREIGN KEY (usuario_id_usuario)
        REFERENCES usuario(id_usuario)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_inscricao_em
        FOREIGN KEY (edicao_modalidade_id)
        REFERENCES edicao_modalidade(id_edicao_modalidade)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

-- =========================================
-- PARTIDA
-- =========================================

CREATE TABLE partida (
    id_partida INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,

    edicao_modalidade_id INT NOT NULL,

    turma_id_time_a INT NOT NULL,

    turma_id_time_b INT NOT NULL,

    data_partida DATE NOT NULL,

    hora_partida TIME NOT NULL,

    local_partida VARCHAR(100),

    fase_partida fase_partida_enum NOT NULL,

    grupo_partida CHAR(1),

    status_partida status_partida_enum
        DEFAULT 'agendada',

    observacoes_partida TEXT,

    CONSTRAINT fk_partida_em
        FOREIGN KEY (edicao_modalidade_id)
        REFERENCES edicao_modalidade(id_edicao_modalidade)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_partida_time_a
        FOREIGN KEY (turma_id_time_a)
        REFERENCES turma(id_turma),

    CONSTRAINT fk_partida_time_b
        FOREIGN KEY (turma_id_time_b)
        REFERENCES turma(id_turma)
);

-- =========================================
-- RESULTADO
-- =========================================

CREATE TABLE resultado (
    id_resultado INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,

    partida_id_partida INT NOT NULL UNIQUE,

    placar_time_a INT DEFAULT 0,

    placar_time_b INT DEFAULT 0,

    turma_id_vencedor INT,

    observacoes_resultado TEXT,

    CONSTRAINT fk_resultado_partida
        FOREIGN KEY (partida_id_partida)
        REFERENCES partida(id_partida)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_resultado_vencedor
        FOREIGN KEY (turma_id_vencedor)
        REFERENCES turma(id_turma)
        ON DELETE SET NULL
        ON UPDATE CASCADE
);

-- =========================================
-- SUMULA
-- =========================================

CREATE TABLE sumula (
    id_sumula INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,

    partida_id_partida INT,

    usuario_id_enviou INT NOT NULL,

    nome_arquivo_sumula VARCHAR(200) NOT NULL,

    caminho_arquivo_sumula VARCHAR(255) NOT NULL,

    tipo_arquivo_sumula VARCHAR(20) NOT NULL,

    data_envio_sumula TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    status_sumula status_sumula_enum
        DEFAULT 'pendente',

    CONSTRAINT fk_sumula_partida
        FOREIGN KEY (partida_id_partida)
        REFERENCES partida(id_partida)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_sumula_usuario
        FOREIGN KEY (usuario_id_enviou)
        REFERENCES usuario(id_usuario)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

-- =========================================
-- CLASSIFICACAO
-- =========================================

CREATE TABLE classificacao (
    id_classificacao INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,

    edicao_modalidade_id INT NOT NULL,

    turma_id_turma INT NOT NULL,

    grupo_classificacao CHAR(1) DEFAULT 'A',

    pontos INT DEFAULT 0,

    vitorias INT DEFAULT 0,

    derrotas INT DEFAULT 0,

    empates INT DEFAULT 0,

    pontos_pro INT DEFAULT 0,

    pontos_contra INT DEFAULT 0,

    saldo INT DEFAULT 0,

    jogos INT DEFAULT 0,

    UNIQUE (edicao_modalidade_id, turma_id_turma),

    CONSTRAINT fk_classificacao_em
        FOREIGN KEY (edicao_modalidade_id)
        REFERENCES edicao_modalidade(id_edicao_modalidade)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_classificacao_turma
        FOREIGN KEY (turma_id_turma)
        REFERENCES turma(id_turma)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

-- =========================================
-- SORTEIO_GERADO
-- =========================================

CREATE TABLE sorteio_gerado (
    id INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,

    edicao_modalidade_id INT NOT NULL UNIQUE,

    gerado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    gerado_por INT,

    CONSTRAINT fk_sorteio_em
        FOREIGN KEY (edicao_modalidade_id)
        REFERENCES edicao_modalidade(id_edicao_modalidade)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_sorteio_usuario
        FOREIGN KEY (gerado_por)
        REFERENCES usuario(id_usuario)
        ON DELETE SET NULL
        ON UPDATE CASCADE
);

-- =========================================
-- FOTO PERFIL
-- =========================================

CREATE TABLE foto_perfil (
    id_foto INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,

    usuario_id_usuario INT NOT NULL,

    caminho_foto VARCHAR(255) NOT NULL,

    nome_arquivo_foto VARCHAR(200) NOT NULL,

    tipo_arquivo_foto VARCHAR(10) NOT NULL,

    data_upload_foto TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    atual_foto BOOLEAN DEFAULT TRUE,

    CONSTRAINT fk_foto_usuario
        FOREIGN KEY (usuario_id_usuario)
        REFERENCES usuario(id_usuario)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

-- =========================================
-- FEEDBACK
-- =========================================

CREATE TABLE feedback (
    id_feedback INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,

    usuario_id_usuario INT,

    nome_feedback VARCHAR(120) NOT NULL,

    email_feedback VARCHAR(120) NOT NULL,

    turma_feedback VARCHAR(20) NOT NULL,

    tipo_feedback tipo_feedback_enum NOT NULL,

    categorias_feedback VARCHAR(120) NOT NULL,

    mensagem_feedback TEXT NOT NULL,

    data_feedback TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    status_feedback status_feedback_enum
        DEFAULT 'pendente',

    CONSTRAINT fk_feedback_usuario
        FOREIGN KEY (usuario_id_usuario)
        REFERENCES usuario(id_usuario)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

-- =========================================
-- ÍNDICES
-- =========================================

CREATE INDEX idx_partida_emid_fase
ON partida(edicao_modalidade_id, fase_partida);

CREATE INDEX idx_classificacao_emid
ON classificacao(
    edicao_modalidade_id,
    grupo_classificacao,
    pontos DESC
);

-- =========================================
-- INSERTS
-- =========================================

INSERT INTO curso (nome_curso, sigla_curso)
VALUES
('Ensino medio com administração', 'MTEC'),
('Ensino medio com itinerario formativo', 'EMIF'),
('Ensino medio com administração em periodo integral','MTECPI'),
('Professores','PROF');

INSERT INTO turma
(curso_id_curso, nome_turma, ano_serie_turma, ano_letivo_turma, periodo_turma)
VALUES

-- MTEC
(1, '1 MTEC', 1, 2026, 'manha'),
(1, '2 MTEC', 2, 2026, 'manha'),
(1, '3 MTEC', 3, 2026, 'manha'),

-- EMIF
(2, '1 EMIF', 1, 2026, 'manha'),
(2, '2 EMIF', 2, 2026, 'manha'),
(2, '3 EMIF', 3, 2026, 'manha'),

-- PI
(3, '1 PI', 1, 2026, 'manha'),
(3, '2 PI', 2, 2026, 'manha'),
(3, '3 PI', 3, 2026, 'manha'),

-- Professores
(4, 'Professores', 10, 2026, 'manha');

SELECT u.nome_usuario, t.nome_turma
FROM usuario u
JOIN turma t
ON u.turma_id_turma = t.id_turma;

insert into usuario (turma_id_turma, nome_usuario, email_usuario, senha_usuario, genero_usuario, tipo_usuario, ativo_usuario) 
values 
-- ADM --
(NULL, 'Henrique Batista Orlovas', 'batista.henriqui@gmail.com', '12345hbo', 'm', 'adm_geral', TRUE),
(NULL, 'Carlos Henrique Valentim', 'rikcar22@gmail.com', 'Carlosrik@22', 'm', 'adm_geral', TRUE),
(NULL, 'Miguel Lopes Aquinez da Silva', 'miguelaquinez17@gmail.com', '12345mlas', 'm', 'adm_geral', TRUE),
(NULL, 'Matheus Ferreira Lopes', 'matheusflopes167@gmail.com', '12345mfl', 'm', 'adm_geral', TRUE),
(NULL, 'Isabelly Barbosa Santos', 'isabellybarbosantos1357@gmail.com', '12345ibs', 'f', 'adm_geral', TRUE),


-- Professores --
(10, 'Silmara Beltrame', 'silmara.beltrame@gmail.com', '123456', 'f', 'professor', TRUE),

-- EMIF 1 (ID 4)
(4, 'Guilherme Luiz', 'guilherme.luiz@gmail.com', '123456', 'n', 'aluno', TRUE),
(4, 'Gabriel de Carvalho', 'gabriel.carvalho@gmail.com', '123456', 'n', 'aluno', TRUE),
(4, 'Laura', 'laura.emif1@gmail.com', '123456', 'n', 'aluno', TRUE),
(4, 'Lara', 'lara.emif1@gmail.com', '123456', 'n', 'aluno', TRUE),
(4, 'Luisa Souza de Jesus', 'luisa.jesus@gmail.com', '123456', 'n', 'aluno', TRUE),
(4, 'Carlos Miguel', 'carlos.miguel@gmail.com', '123456', 'n', 'aluno', TRUE),
(4, 'Pedro', 'pedro.emif1@gmail.com', '123456', 'n', 'aluno', TRUE),
(4, 'Melissa', 'melissa.emif1@gmail.com', '123456', 'n', 'aluno', TRUE),
(4, 'Amanda Batista', 'amanda.batista@gmail.com', '123456', 'n', 'aluno', TRUE),
(4, 'Allana Assis', 'allana.assis@gmail.com', '123456', 'n', 'aluno', TRUE),
(4, 'Eloise Tavares', 'eloise.tavares@gmail.com', '123456', 'n', 'aluno', TRUE),
(4, 'Leonardo Sousa', 'leonardo.sousa@gmail.com', '123456', 'n', 'aluno', TRUE),
(4, 'João Augusto', 'joao.augusto@gmail.com', '123456', 'n', 'aluno', TRUE),
(4, 'Alexsander Lima', 'alexsander.lima@gmail.com', '123456', 'n', 'aluno', TRUE),
(4, 'Pietro', 'pietro.emif1@gmail.com', '123456', 'n', 'aluno', TRUE),
(4, 'Julia Campos de Sousa', 'julia.sousa@gmail.com', '123456', 'n', 'aluno', TRUE),
(4, 'João Hélio', 'joao.helio@gmail.com', '123456', 'n', 'aluno', TRUE),
(4, 'Sophia Carvalho', 'sophia.carvalho@gmail.com', '123456', 'n', 'aluno', TRUE),
(4, 'Miguel Eduardo', 'miguel.eduardo@gmail.com', '123456', 'n', 'aluno', TRUE),
(4, 'Yuki', 'yuki@gmail.com', '123456', 'n', 'aluno', TRUE),
(4, 'Enzo Dias Siqueira', 'enzo.siqueira@gmail.com', '123456', 'n', 'aluno', TRUE),
(4, 'Leonardo Gomes Costa Silva', 'leonardo.silva@gmail.com', '123456', 'n', 'aluno', TRUE),
(4, 'Rafael Oliveira Sa', 'rafael.sa@gmail.com', '123456', 'n', 'aluno', TRUE),
(4, 'Iasmin Lucas Silva', 'iasmin.silva@gmail.com', '123456', 'n', 'aluno', TRUE),
(4, 'Lucas Azevedo', 'lucas.azevedo@gmail.com', '123456', 'n', 'aluno', TRUE),
(4, 'Belly', 'belly@gmail.com', '123456', 'n', 'aluno', TRUE),
(4, 'Rebeca', 'rebeca@gmail.com', '123456', 'n', 'aluno', TRUE),
(4, 'Geovanna Lima Lopes', 'geovanna.lopes@gmail.com', '123456', 'n', 'aluno', TRUE),
(4, 'Isabela Vasconcelos', 'isabela.vasconcelos@gmail.com', '123456', 'n', 'aluno', TRUE),
(4, 'Mateus Vieira', 'mateus.vieira@gmail.com', '123456', 'n', 'aluno', TRUE),

-- EMIF 2 (ID 5)
(5, 'Rafaella Barbosa', 'rafaella.barbosa@gmail.com', '123456', 'n', 'aluno', TRUE),
(5, 'Davi', 'davi.emif2@gmail.com', '123456', 'n', 'aluno', TRUE),
(5, 'Giovanna Silva de Oliveira', 'giovanna.oliveira@gmail.com', '123456', 'n', 'aluno', TRUE),
(5, 'Luiz Henrique Apolinário Silva', 'luiz.henrique@gmail.com', '123456', 'n', 'aluno', TRUE),
(5, 'Julia Gonçalves Zambotti', 'julia.zambotti@gmail.com', '123456', 'n', 'aluno', TRUE),
(5, 'Clara Cenni', 'clara.cenni@gmail.com', '123456', 'n', 'aluno', TRUE),
(5, 'Thales Oliveira Rieger', 'thales.rieger@gmail.com', '123456', 'n', 'aluno', TRUE),
(5, 'Willian Geraldo Alves Martins', 'willian.martins@gmail.com', '123456', 'n', 'aluno', TRUE),
(5, 'Flavia Nery', 'flavia.nery@gmail.com', '123456', 'n', 'aluno', TRUE),
(5, 'Giulia', 'giulia.emif2@gmail.com', '123456', 'n', 'aluno', TRUE),
(5, 'Arthur Mentros', 'arthur.mentros@gmail.com', '123456', 'n', 'aluno', TRUE),
(5, 'Giovanna Figueira', 'giovanna.figueira@gmail.com', '123456', 'n', 'aluno', TRUE),
(5, 'Brenda Rosa', 'brenda.rosa@gmail.com', '123456', 'n', 'aluno', TRUE),
(5, 'Duda', 'duda.emif2@gmail.com', '123456', 'n', 'aluno', TRUE),
(5, 'Gabriella Arantes', 'gabriella.arantes@gmail.com', '123456', 'n', 'aluno', TRUE),
(5, 'Petherson Gabriel Macedo Santos', 'petherson.santos@gmail.com', '123456', 'n', 'aluno', TRUE),
(5, 'Samuel José Correa de Andrade', 'samuel.andrade@gmail.com', '123456', 'n', 'aluno', TRUE),
(5, 'Arthur', 'arthur.emif2@gmail.com', '123456', 'n', 'aluno', TRUE),
(5, 'Gisele Leles de Lima', 'gisele.lima@gmail.com', '123456', 'n', 'aluno', TRUE),
(5, 'Henrique Soares de Matos Junior', 'henrique.matos@gmail.com', '123456', 'n', 'aluno', TRUE),
(5, 'Duduq', 'duduq@gmail.com', '123456', 'n', 'aluno', TRUE),
(5, 'Beatriz Bezerra', 'beatriz.bezerra@gmail.com', '123456', 'n', 'aluno', TRUE),
(5, 'Arthur Rosa Silva', 'arthur.rosa@gmail.com', '123456', 'n', 'aluno', TRUE),
(5, 'Eduardo Henrique', 'eduardo.henrique@gmail.com', '123456', 'n', 'aluno', TRUE),
(5, 'Ana Sardi', 'ana.sardi@gmail.com', '123456', 'n', 'aluno', TRUE),
(5, 'Gabriel Bitencourt', 'gabriel.bitencourt@gmail.com', '123456', 'n', 'aluno', TRUE),
(5, 'Sophia Freitas Santos', 'sophia.santos@gmail.com', '123456', 'n', 'aluno', TRUE),
(5, 'Herick Cilindro', 'herick@gmail.com', '123456', 'n', 'aluno', TRUE),
(5, 'Arthur Olintras Teixeira', 'arthur.teixeira@gmail.com', '123456', 'n', 'aluno', TRUE),
(5, 'Pablo Gomes Gonçalves', 'pablo.gomes@gmail.com', '123456', 'n', 'aluno', TRUE),
(5, 'Triana Macabi', 'triana.macabi@gmail.com', '123456', 'n', 'aluno', TRUE),
(5, 'Laura de Medeiros', 'laura.medeiros@gmail.com', '123456', 'n', 'aluno', TRUE),

-- EMIF 3 (ID 6)
(6, 'André', 'andre@gmail.com', '123456', 'n', 'aluno', TRUE),
(6, 'Julia Santos Costa', 'julia.costa@gmail.com', '123456', 'n', 'aluno', TRUE),
(6, 'Julia', 'julia.emif3@gmail.com', '123456', 'n', 'aluno', TRUE),
(6, 'Seiji Seguchi dos Santos', 'seiji.santos@gmail.com', '123456', 'n', 'aluno', TRUE),
(6, 'Karolyne', 'karolyne@gmail.com', '123456', 'n', 'aluno', TRUE),
(6, 'Alvaro', 'alvaro@gmail.com', '123456', 'n', 'aluno', TRUE),
(6, 'Nicolle Cavalcante', 'nicolle.cavalcante@gmail.com', '123456', 'n', 'aluno', TRUE),
(6, 'Maria Eduarda da Costa', 'maria.costa@gmail.com', '123456', 'n', 'aluno', TRUE),
(6, 'Rodrigo Vilas Boas', 'rodrigo.boas@gmail.com', '123456', 'n', 'aluno', TRUE),
(6, 'Nicolas Alves Bezerra', 'nicolas.bezerra@gmail.com', '123456', 'n', 'aluno', TRUE),
(6, 'Lucas Alves Almeida', 'lucas.almeida@gmail.com', '123456', 'n', 'aluno', TRUE),
(6, 'Grazielly', 'grazielly@gmail.com', '123456', 'n', 'aluno', TRUE),
(6, 'Guilherme Bispo', 'guilherme.bispo@gmail.com', '123456', 'n', 'aluno', TRUE),
(6, 'Mayara Veloso', 'mayara.veloso@gmail.com', '123456', 'n', 'aluno', TRUE),
(6, 'Maria', 'maria.emif3@gmail.com', '123456', 'n', 'aluno', TRUE),
(6, 'Laryssa', 'laryssa@gmail.com', '123456', 'n', 'aluno', TRUE),
(6, 'Clarissa', 'clarissa@gmail.com', '123456', 'n', 'aluno', TRUE),
(6, 'Alícia Carvalho', 'alicia.carvalho@gmail.com', '123456', 'n', 'aluno', TRUE),
(6, 'Mariana Oliveira', 'mariana.oliveira@gmail.com', '123456', 'n', 'aluno', TRUE),
(6, 'Pietra', 'pietra@gmail.com', '123456', 'n', 'aluno', TRUE),
(6, 'Leandro Carneiro Reis', 'leandro.reis@gmail.com', '123456', 'n', 'aluno', TRUE),
(6, 'Davi Bispo Bomfim', 'davi.bomfim@gmail.com', '123456', 'n', 'aluno', TRUE),
(6, 'Kelly', 'kelly@gmail.com', '123456', 'n', 'aluno', TRUE),
(6, 'Marcos Araújo', 'marcos.araujo@gmail.com', '123456', 'n', 'aluno', TRUE),
(6, 'Mariana Gonçalves de Lima Santos', 'mariana.lima@gmail.com', '123456', 'n', 'aluno', TRUE),
(6, 'Kauã Dias', 'kaua.dias@gmail.com', '123456', 'n', 'aluno', TRUE),
(6, 'Kauã Moreira', 'kaua.moreira@gmail.com', '123456', 'n', 'aluno', TRUE),
(6, 'Gabs', 'gabs@gmail.com', '123456', 'n', 'aluno', TRUE),
(6, 'Arthur Mafra', 'arthur.mafra@gmail.com', '123456', 'n', 'aluno', TRUE),
(6, 'Heloísa de Freitas', 'heloisa.freitas@gmail.com', '123456', 'n', 'aluno', TRUE),

-- MTEC 1 (ID 1)
(1, 'Ruan', 'ruan@email.com', '123456', 'n', 'aluno', TRUE),
(1, 'Phietro', 'phietro@email.com', '123456', 'n', 'aluno', TRUE),
(1, 'Luan Borges Santos', 'luanborges@email.com', '123456', 'n', 'aluno', TRUE),
(1, 'Pedro Maia de Carvalho Neto', 'pedromaia@email.com', '123456', 'n', 'aluno', TRUE),
(1, 'Conrado H', 'conrado@email.com', '123456', 'n', 'aluno', TRUE),
(1, 'Sophia Oliveira Santos', 'sophiaoliveira@email.com', '123456', 'n', 'aluno', TRUE),
(1, 'Dayane Aparecida', 'dayane@email.com', '123456', 'n', 'aluno', TRUE),
(1, 'Valeria', 'valeria@email.com', '123456', 'n', 'aluno', TRUE),
(1, 'Laís Borgeus Nascimento', 'laisborgeus@email.com', '123456', 'n', 'aluno', TRUE),
(1, 'Izabelle Crispim Faustino da Paixão', 'izabelle@email.com', '123456', 'n', 'aluno', TRUE),
(1, 'Beatriz Silva Lapazini', 'beatrizlapazini@email.com', '123456', 'n', 'aluno', TRUE),
(1, 'TIFFANY', 'tiffany@email.com', '123456', 'n', 'aluno', TRUE),
(1, 'Sofia Costa Andrade', 'sofiacosta@email.com', '123456', 'n', 'aluno', TRUE),
(1, 'Lorena Tavares Braga de Oliveira', 'lorena@email.com', '123456', 'n', 'aluno', TRUE),
(1, 'Maria Luiza de Souza Santos', 'marialuiza@email.com', '123456', 'n', 'aluno', TRUE),
(1, 'Heloiza', 'heloiza@email.com', '123456', 'n', 'aluno', TRUE),
(1, 'Luana Sabino', 'luana@email.com', '123456', 'n', 'aluno', TRUE),
(1, 'Aline de Souza Medina', 'aline@email.com', '123456', 'n', 'aluno', TRUE),
(1, 'João Vitor Pestana da Silva', 'joaovitor@email.com', '123456', 'n', 'aluno', TRUE),
(1, 'Gabriel de Almeida', 'gabriel@email.com', '123456', 'n', 'aluno', TRUE),
(1, 'Davi Braga', 'davibraga@email.com', '123456', 'n', 'aluno', TRUE),
(1, 'Laura Mariah', 'laura.mtec1@email.com', '123456', 'n', 'aluno', TRUE),
(1, 'Maria Luiza Silva', 'mluiza.mtec1@email.com', '123456', 'n', 'aluno', TRUE),
(1, 'Julia Oliveira Santos', 'juliaoliveira@email.com', '123456', 'n', 'aluno', TRUE),
(1, 'Isabelly Pimenta', 'isabelly@email.com', '123456', 'n', 'aluno', TRUE),

-- MTEC 2 (ID 2)
(2, 'Fernanda Fava', 'fernanda@email.com', '123456', 'n', 'aluno', TRUE),
(2, 'Mateus Soares', 'mateus.mtec2@email.com', '123456', 'n', 'aluno', TRUE),
(2, 'Daniel de Araujo Nogueira', 'daniel.mtec2@email.com', '123456', 'n', 'aluno', TRUE),
(2, 'Fernanda Destra', 'destra@email.com', '123456', 'n', 'aluno', TRUE),
(2, 'Eloisa Mendes Ferreira', 'eloisa@email.com', '123456', 'n', 'aluno', TRUE),
(2, 'Raquel Santos', 'raquel.mtec2@email.com', '123456', 'n', 'aluno', TRUE),
(2, 'Eduardo Romão', 'eduardo@email.com', '123456', 'n', 'aluno', TRUE),
(2, 'Jonathan Teo Motoki França', 'jonathan@email.com', '123456', 'n', 'aluno', TRUE),
(2, 'Miguel Oliveira Gama Chaves', 'miguel.mtec2@email.com', '123456', 'n', 'aluno', TRUE),
(2, 'Gabriell Pereira de Lana', 'gabriell@email.com', '123456', 'n', 'aluno', TRUE),
(2, 'Luigi Sanches Pereira Monteiro', 'luigi.mtec2@email.com', '123456', 'n', 'aluno', TRUE),
(2, 'Pedro Nolasco Moraes', 'pedronolasco@email.com', '123456', 'n', 'aluno', TRUE),
(2, 'Yasmin Azevedo', 'yasmin.mtec2@email.com', '123456', 'n', 'aluno', TRUE),
(2, 'Gabriela Aparecida', 'gabriela@email.com', '123456', 'n', 'aluno', TRUE),
(2, 'Kaíke Eduardo da Silva Vigeta dos Santos', 'kaike@email.com', '123456', 'n', 'aluno', TRUE),
(2, 'Ana Luísa de Sousa Osório', 'analuisa@email.com', '123456', 'n', 'aluno', TRUE),
(2, 'Luís Fernando Ferreira Barbosa', 'luis.mtec2@email.com', '123456', 'n', 'aluno', TRUE),
(2, 'Eduardo Pereira de Sousa', 'eduardop@email.com', '123456', 'n', 'aluno', TRUE),
(2, 'Isabela Reis', 'isabela.mtec2@email.com', '123456', 'n', 'aluno', TRUE),
(2, 'Nicoly Carvalho de Souza', 'nicoly.mtec2@email.com', '123456', 'n', 'aluno', TRUE),
(2, 'Ana Giulia Ribeiro dos Santos', 'anagiulia@email.com', '123456', 'n', 'aluno', TRUE),
(2, 'Kemelly Luara Delmira Guimarães', 'kemelly@email.com', '123456', 'n', 'aluno', TRUE),
(2, 'Fernanda de Souza', 'fernandas@email.com', '123456', 'n', 'aluno', TRUE),
(2, 'Davi Silva Alves', 'davis.mtec2@email.com', '123456', 'n', 'aluno', TRUE),
(2, 'Pedro Tavares', 'pedrot@email.com', '123456', 'n', 'aluno', TRUE),
(2, 'Guilherme Lopes Dourado', 'guilherme.mtec2@email.com', '123456', 'n', 'aluno', TRUE),
(2, 'Beatriz da Conceição Constantino', 'beatrizc.mtec2@email.com', '123456', 'n', 'aluno', TRUE),
(2, 'Rafaella Siervi Cabral', 'rafaella.mtec2@email.com', '123456', 'n', 'aluno', TRUE),
(2, 'Leonardo Lima de Oliveira', 'leonardo.mtec2@email.com', '123456', 'n', 'aluno', TRUE),
(2, 'Rafaella Rodrigues de Oliveira', 'rafaellar.mtec2@email.com', '123456', 'n', 'aluno', TRUE),

-- MTEC 3 (ID 3)
(3, 'Ana Julia Oliveira', 'anajulia@email.com', '123456', 'n', 'aluno', TRUE),
(3, 'Isabelly Pereira Neves', 'isabellyn.mtec3@email.com', '123456', 'n', 'aluno', TRUE),
(3, 'Sarah', 'sarah@email.com', '123456', 'n', 'aluno', TRUE),
(3, 'Igor', 'igor@email.com', '123456', 'n', 'aluno', TRUE),
(3, 'Maria Isabella Rodrigues Duarte', 'mariaisabella@email.com', '123456', 'n', 'aluno', TRUE),
(3, 'Isabella Nascimento', 'isabellan.mtec3@email.com', '123456', 'n', 'aluno', TRUE),
(3, 'Pedro Henrique Vieira Leal', 'pedrohenrique.mtec3@email.com', '123456', 'n', 'aluno', TRUE),
(3, 'Gustavo Alves da Silva', 'gustavo@email.com', '123456', 'n', 'aluno', TRUE),
(3, 'Matheus Nascimento Dias', 'matheus.mtec3@email.com', '123456', 'n', 'aluno', TRUE),
(3, 'Beatriz Teodosio', 'beatrizt@email.com', '123456', 'n', 'aluno', TRUE),
(3, 'Gabriel dos Santos Gomes', 'gabrielg@email.com', '123456', 'n', 'aluno', TRUE),
(3, 'Maria Eduarda Sousa Andrade', 'mariaeduarda.mtec3@email.com', '123456', 'n', 'aluno', TRUE),
(3, 'Klaus', 'klaus@email.com', '123456', 'n', 'aluno', TRUE),
(3, 'Marina Rita Alves Guimarães', 'marina@email.com', '123456', 'n', 'aluno', TRUE),
(3, 'Evelaine Cecilia Santos Serafim', 'evelaine@email.com', '123456', 'n', 'aluno', TRUE),
(3, 'Julia Margarida', 'juliam@email.com', '123456', 'n', 'aluno', TRUE),
(3, 'Isabela', 'isabela2.mtec3@email.com', '123456', 'n', 'aluno', TRUE),
(3, 'Mariane Sofia', 'mariane@email.com', '123456', 'n', 'aluno', TRUE),
(3, 'Júlia Carvalho Almeida', 'juliac@email.com', '123456', 'n', 'aluno', TRUE),
(3, 'Beatriz Gaefke Santiago', 'beatrizg@email.com', '123456', 'n', 'aluno', TRUE),
(3, 'Laís Gomes', 'lais@email.com', '123456', 'n', 'aluno', TRUE),
(3, 'João Adelson Vasconcelos', 'joaoadelson@email.com', '123456', 'n', 'aluno', TRUE),
(3, 'Kaio Ricardo Chaves de Jesus', 'kaio@email.com', '123456', 'n', 'aluno', TRUE),
(3, 'Vitor', 'vitor.mtec3@email.com', '123456', 'n', 'aluno', TRUE),
(3, 'Pedro Santana Sobrinho', 'pedros.mtec3@email.com', '123456', 'n', 'aluno', TRUE),
(3, 'Melissa Cerqueira dos Santos', 'melissa.mtec3@email.com', '123456', 'n', 'aluno', TRUE),
(3, 'Jhennifer Bispo', 'jhennifer@email.com', '123456', 'n', 'aluno', TRUE),
(3, 'Yasmin', 'yasmin2.mtec3@email.com', '123456', 'n', 'aluno', TRUE),
-- PI 1 (ID 7)
(7, 'Victor Hugo Souza Silva', 'victor@email.com', '123456', 'n', 'aluno', TRUE),
(7, 'Rogério Caluanã Reis Ramalho', 'rogerio@email.com', '123456', 'n', 'aluno', TRUE),
(7, 'Mateus Espairani da Silva', 'mateus.pi1@email.com', '123456', 'n', 'aluno', TRUE),
(7, 'Benjamin', 'benjamin@email.com', '123456', 'n', 'aluno', TRUE),
(7, 'Sabrina P Lima', 'sabrina@email.com', '123456', 'n', 'aluno', TRUE),
(7, 'Adrian Novais Acioli', 'adrian@email.com', '123456', 'n', 'aluno', TRUE),
(7, 'Nycolas', 'nycolas@email.com', '123456', 'n', 'aluno', TRUE),
(7, 'Caroline', 'caroline@email.com', '123456', 'n', 'aluno', TRUE),
(7, 'Miguel Alves', 'miguel.pi1@email.com', '123456', 'n', 'aluno', TRUE),
(7, 'Vitória dos Santos', 'vitoria@email.com', '123456', 'n', 'aluno', TRUE),
(7, 'Luciana Souza Lima', 'luciana@email.com', '123456', 'n', 'aluno', TRUE),
(7, 'Mirella Lauretto', 'mirella@email.com', '123456', 'n', 'aluno', TRUE),
(7, 'Yasmin Santos', 'yasmin.pi1@email.com', '123456', 'n', 'aluno', TRUE),
(7, 'Cauanny', 'cauanny@email.com', '123456', 'n', 'aluno', TRUE),
(7, 'Mariana', 'mariana.pi1@email.com', '123456', 'n', 'aluno', TRUE),
(7, 'Nathalia', 'nathalia@email.com', '123456', 'n', 'aluno', TRUE),
(7, 'Arthur', 'arthur.pi1@email.com', '123456', 'n', 'aluno', TRUE),
(7, 'Gabrielly', 'gabrielly@email.com', '123456', 'n', 'aluno', TRUE),
(7, 'Felipe Rodrigues Santos', 'felipe.pi1@email.com', '123456', 'n', 'aluno', TRUE),
(7, 'Beatriz Paz dos Santos Ferreira', 'beatriz.pi1@email.com', '123456', 'n', 'aluno', TRUE),
(7, 'Luiz Henryque Alves Ladislau', 'luiz.pi1@email.com', '123456', 'n', 'aluno', TRUE),
(7, 'Felipe de Oliveira Blanco', 'felipeb@email.com', '123456', 'n', 'aluno', TRUE),
(7, 'Rafael', 'rafael.pi1@email.com', '123456', 'n', 'aluno', TRUE),
(7, 'Daniel Pereira de Alcântara', 'daniel.pi1@email.com', '123456', 'n', 'aluno', TRUE),
(7, 'Gabriella', 'gabriella.pi1@email.com', '123456', 'n', 'aluno', TRUE),
(7, 'Leopoldina Francisca Fernanda', 'leopoldina@email.com', '123456', 'n', 'aluno', TRUE),

-- PI 2 (ID 8)
(8, 'Davi Victor', 'davi.pi2@email.com', '123456', 'n', 'aluno', TRUE),
(8, 'Mel Soares', 'mel@email.com', '123456', 'n', 'aluno', TRUE),
(8, 'Mary', 'mary@email.com', '123456', 'n', 'aluno', TRUE),
(8, 'Kathleen', 'kathleen@email.com', '123456', 'n', 'aluno', TRUE),
(8, 'Arthur', 'arthur2.pi2@email.com', '123456', 'n', 'aluno', TRUE),
(8, 'Bianca Bonfim', 'bianca@email.com', '123456', 'n', 'aluno', TRUE),
(8, 'Otávio Carvalho Oliveira', 'otavio@email.com', '123456', 'n', 'aluno', TRUE),
(8, 'Nicolly', 'nicolly.pi2@email.com', '123456', 'n', 'aluno', TRUE),
(8, 'Marcos', 'marcos.pi2@email.com', '123456', 'n', 'aluno', TRUE),
(8, 'Thomas', 'thomas@email.com', '123456', 'n', 'aluno', TRUE),
(8, 'Raquel Lopes Sousa', 'raquel.pi2@email.com', '123456', 'n', 'aluno', TRUE),
(8, 'Anna Beatriz', 'anna.pi2@email.com', '123456', 'n', 'aluno', TRUE),
(8, 'Ana Carolina de Souza Amurim', 'ana.carol.pi2@email.com', '123456', 'n', 'aluno', TRUE),
(8, 'Mellyssa', 'mellyssa@email.com', '123456', 'n', 'aluno', TRUE),
(8, 'Lucas Silvério de Matos', 'lucas.pi2@email.com', '123456', 'n', 'aluno', TRUE),
(8, 'Beatriz Cristina', 'beatrizc.pi2@email.com', '123456', 'n', 'aluno', TRUE),
(8, 'João', 'joao.pi2@email.com', '123456', 'n', 'aluno', TRUE),
(8, 'Sophia Alexandre Duarte Silva', 'sophia.pi2@email.com', '123456', 'n', 'aluno', TRUE),
(8, 'Isabelle', 'isabelle.pi2@email.com', '123456', 'n', 'aluno', TRUE),
(8, 'Lara Santos Amaro', 'lara.pi2@email.com', '123456', 'n', 'aluno', TRUE),
(8, 'Carolina Giovana', 'carolina.pi2@email.com', '123456', 'n', 'aluno', TRUE),
(8, 'Luigi Henrique', 'luigi.pi2@email.com', '123456', 'n', 'aluno', TRUE),
(8, 'Daniel Martins Pinheiro', 'danielp.pi2@email.com', '123456', 'n', 'aluno', TRUE),
(8, 'Miguel Willian', 'miguelw.pi2@email.com', '123456', 'n', 'aluno', TRUE),
(8, 'Cauã de Brito Fontes Benedito', 'caua@email.com', '123456', 'n', 'aluno', TRUE),
(8, 'Lucas Karube de Oliveira', 'karube@email.com', '123456', 'n', 'aluno', TRUE),

-- PI 3 (ID 9)
(9, 'Gabriel de Paula', 'gabriel.pi3@email.com', '123456', 'n', 'aluno', TRUE),
(9, 'Jacqueline Santos', 'jacqueline@email.com', '123456', 'n', 'aluno', TRUE),
(9, 'César Mica Cordiolli Nascimento', 'cesar@email.com', '123456', 'n', 'aluno', TRUE),
(9, 'Carolline de Castro Silva Souza', 'carolline.pi3@email.com', '123456', 'n', 'aluno', TRUE),
(9, 'Luan Henrique dos Santos Gonçalves', 'luan.pi3@email.com', '123456', 'n', 'aluno', TRUE),
(9, 'Cassia', 'cassia@email.com', '123456', 'n', 'aluno', TRUE),
(9, 'Adriane', 'adriane@email.com', '123456', 'n', 'aluno', TRUE),
(9, 'Giovanna Drovette', 'giovanna.pi3@email.com', '123456', 'n', 'aluno', TRUE),
(9, 'Yasmin Alves', 'yasmina.pi3@email.com', '123456', 'n', 'aluno', TRUE),
(9, 'Lucas Dias dos Santos', 'lucasd.pi3@email.com', '123456', 'n', 'aluno', TRUE),
(9, 'Natalia Cristina Santana de Azevedo', 'natalia@email.com', '123456', 'n', 'aluno', TRUE),
(9, 'Layssa Rodrigues', 'layssa@email.com', '123456', 'n', 'aluno', TRUE),
(9, 'Thomax', 'thomax@email.com', '123456', 'n', 'aluno', TRUE),
(9, 'Sofia Pereira e Silva', 'sofia.pi3@email.com', '123456', 'n', 'aluno', TRUE),
(9, 'Isabella Silva Delgado', 'isabella.pi3@email.com', '123456', 'n', 'aluno', TRUE),
(9, 'Alexsandro Simpliciano Marques', 'alexsandro@email.com', '123456', 'n', 'aluno', TRUE),
(9, 'Heloísa', 'heloisa.pi3@email.com', '123456', 'n', 'aluno', TRUE),
(9, 'Carolline da Silva Tinti', 'carol.pi3@email.com', '123456', 'n', 'aluno', TRUE),
(9, 'Renan Oliveira Peres', 'renan@email.com', '123456', 'n', 'aluno', TRUE),
(9, 'Isabelly Brito de Jesus', 'isabelly.pi3@email.com', '123456', 'n', 'aluno', TRUE);
