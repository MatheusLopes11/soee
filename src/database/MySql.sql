CREATE DATABASE IF NOT EXISTS soee;
USE soee;

CREATE TABLE curso (
    id_curso INT AUTO_INCREMENT PRIMARY KEY,
    nome_curso VARCHAR(60) NOT NULL,
    sigla_curso VARCHAR(15) NOT NULL UNIQUE
);

CREATE TABLE turma (
    id_turma INT AUTO_INCREMENT PRIMARY KEY,
    curso_id_curso INT NOT NULL,
    nome_turma VARCHAR(20) NOT NULL,
    ano_serie_turma INT NOT NULL,
    ano_letivo_turma YEAR NOT NULL,
    periodo_turma ENUM('Manha','Tarde','Noite') NOT NULL,
    FOREIGN KEY (curso_id_curso)
        REFERENCES curso(id_curso)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
);

CREATE TABLE usuario (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    turma_id_turma INT,
    nome_usuario VARCHAR(100) NOT NULL,
    email_usuario VARCHAR(120) NOT NULL UNIQUE,
    senha_usuario VARCHAR(255) NOT NULL,
    rm_usuario VARCHAR(10) NOT NULL,
    ra_usuario VARCHAR(15),
    cpf_usuario VARCHAR(14) NOT NULL UNIQUE,
    genero_usuario CHAR(1) NOT NULL,
    data_nasc_usuario DATE NOT NULL,
    tipo_usuario ENUM('aluno','adm_sala','adm_geral') NOT NULL DEFAULT 'aluno',
    foto_perfil_usuario VARCHAR(255),
    ativo_usuario TINYINT(1) DEFAULT 1,
    FOREIGN KEY (turma_id_turma)
        REFERENCES turma(id_turma)
        ON DELETE SET NULL
        ON UPDATE CASCADE
);

CREATE TABLE modalidade (
    id_modalidade INT AUTO_INCREMENT PRIMARY KEY,
    nome_modalidade VARCHAR(60) NOT NULL UNIQUE,
    descricao_modalidade TEXT,
    tipo_modalidade ENUM('quadra','mesa','campo','outro') NOT NULL,
    formato_modalidade ENUM('mata_mata','grupos','grupos_mata_mata','todos_contra_todos') NOT NULL,
    tipo_participacao ENUM('solo','dupla','trio','time') NOT NULL,
    qtd_min_jogadores INT NOT NULL,
    qtd_max_jogadores INT NOT NULL,
    ativo_modalidade TINYINT(1) DEFAULT 1
);

CREATE TABLE edicao (
    id_edicao INT AUTO_INCREMENT PRIMARY KEY,
    nome_edicao VARCHAR(80) NOT NULL,
    ano_edicao YEAR NOT NULL,
    data_inicio_edicao DATE NOT NULL,
    data_fim_edicao DATE,
    status_edicao ENUM('planejamento','inscricoes','em_andamento','encerrado') DEFAULT 'planejamento',
    descricao_edicao TEXT
);

CREATE TABLE edicao_modalidade (
    id_edicao_modalidade INT AUTO_INCREMENT PRIMARY KEY,
    edicao_id_edicao INT NOT NULL,
    modalidade_id_modalidade INT NOT NULL,
    data_inicio_inscricao DATE NOT NULL,
    data_fim_inscricao DATE NOT NULL,
    status_edicao_modalidade ENUM('inscricoes','em_andamento','encerrado') DEFAULT 'inscricoes',
    FOREIGN KEY (edicao_id_edicao)
        REFERENCES edicao(id_edicao)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    FOREIGN KEY (modalidade_id_modalidade)
        REFERENCES modalidade(id_modalidade)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
);

CREATE TABLE inscricao (
    id_inscricao INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id_usuario INT NOT NULL,
    edicao_modalidade_id INT NOT NULL,
    numero_camisa_inscricao INT,
    posicao_inscricao VARCHAR(40),
    capitao_inscricao TINYINT(1) DEFAULT 0,
    data_inscricao DATETIME DEFAULT CURRENT_TIMESTAMP,
    status_inscricao ENUM('ativa','cancelada') DEFAULT 'ativa',
    FOREIGN KEY (usuario_id_usuario)
        REFERENCES usuario(id_usuario)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    FOREIGN KEY (edicao_modalidade_id)
        REFERENCES edicao_modalidade(id_edicao_modalidade)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

CREATE TABLE partida (
    id_partida INT AUTO_INCREMENT PRIMARY KEY,
    edicao_modalidade_id INT NOT NULL,
    turma_id_time_a INT NOT NULL,
    turma_id_time_b INT NOT NULL,
    data_partida DATE NOT NULL,
    hora_partida TIME NOT NULL,
    local_partida VARCHAR(100),
    fase_partida ENUM('grupos','oitavas','quartas','semi','final','terceiro_lugar') NOT NULL,
    grupo_partida CHAR(1),
    status_partida ENUM('agendada','realizada','cancelada','wo') DEFAULT 'agendada',
    observacoes_partida TEXT,
    FOREIGN KEY (edicao_modalidade_id)
        REFERENCES edicao_modalidade(id_edicao_modalidade)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    FOREIGN KEY (turma_id_time_a)
        REFERENCES turma(id_turma)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,
    FOREIGN KEY (turma_id_time_b)
        REFERENCES turma(id_turma)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
);

CREATE TABLE resultado (
    id_resultado INT AUTO_INCREMENT PRIMARY KEY,
    partida_id_partida INT NOT NULL UNIQUE,
    placar_time_a INT DEFAULT 0,
    placar_time_b INT DEFAULT 0,
    turma_id_vencedor INT,
    observacoes_resultado TEXT,
    FOREIGN KEY (partida_id_partida)
        REFERENCES partida(id_partida)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    FOREIGN KEY (turma_id_vencedor)
        REFERENCES turma(id_turma)
        ON DELETE SET NULL
        ON UPDATE CASCADE
);

CREATE TABLE sumula (
    id_sumula INT AUTO_INCREMENT PRIMARY KEY,
    partida_id_partida INT NOT NULL,
    usuario_id_enviou INT NOT NULL,
    nome_arquivo_sumula VARCHAR(200) NOT NULL,
    caminho_arquivo_sumula VARCHAR(255) NOT NULL,
    tipo_arquivo_sumula VARCHAR(20) NOT NULL,
    data_envio_sumula DATETIME DEFAULT CURRENT_TIMESTAMP,
    status_sumula ENUM('pendente','validada','rejeitada') DEFAULT 'pendente',
    FOREIGN KEY (partida_id_partida)
        REFERENCES partida(id_partida)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    FOREIGN KEY (usuario_id_enviou)
        REFERENCES usuario(id_usuario)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

CREATE TABLE classificacao (
    id_classificacao INT AUTO_INCREMENT PRIMARY KEY,
    edicao_modalidade_id INT NOT NULL,
    turma_id_turma INT NOT NULL,
    pontos INT DEFAULT 0,
    vitorias INT DEFAULT 0,
    derrotas INT DEFAULT 0,
    empates INT DEFAULT 0,
    pontos_pro INT DEFAULT 0,
    pontos_contra INT DEFAULT 0,
    saldo INT DEFAULT 0,
    jogos INT DEFAULT 0,
    UNIQUE (edicao_modalidade_id, turma_id_turma),
    FOREIGN KEY (edicao_modalidade_id)
        REFERENCES edicao_modalidade(id_edicao_modalidade)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    FOREIGN KEY (turma_id_turma)
        REFERENCES turma(id_turma)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

CREATE TABLE foto_perfil (
    id_foto INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id_usuario INT NOT NULL,
    caminho_foto VARCHAR(255) NOT NULL,
    nome_arquivo_foto VARCHAR(200) NOT NULL,
    tipo_arquivo_foto VARCHAR(10) NOT NULL,
    data_upload_foto DATETIME DEFAULT CURRENT_TIMESTAMP,
    atual_foto TINYINT(1) DEFAULT 1,
    FOREIGN KEY (usuario_id_usuario)
        REFERENCES usuario(id_usuario)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

-- valores teste
INSERT INTO curso (nome_curso, sigla_curso)
VALUES ('Técnico em Informática', 'MTEC');

INSERT INTO turma (
    curso_id_curso,
    nome_turma,
    ano_serie_turma,
    ano_letivo_turma,
    periodo_turma
) VALUES (
    1,
    '3 MTEC',
    3,
    2026,
    'Manha'
);

INSERT INTO usuario (
    turma_id_turma,
    nome_usuario,
    email_usuario,
    senha_usuario,
    rm_usuario,
    ra_usuario,
    cpf_usuario,
    genero_usuario,
    data_nasc_usuario,
    tipo_usuario,
    ativo_usuario
) VALUES (
    1,
    'Henrique',
    'henrique@soee.com',
    '12345hb',
    '00001',
    '00001',
    '000.000.000-00',
    'M',
    '2009-03-13',
    'adm_geral',
    1
);