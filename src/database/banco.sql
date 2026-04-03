drop database if exists soee;
create database if not exists soee;
use soee;
--------------------------------
-- --- ESTRUTURA DAS TABELAS ---
--------------------------------
create table curso (
    id_curso int auto_increment primary key,
    nome_curso varchar(60) not null,
    sigla_curso varchar(15) not null unique
);

create table turma (
    id_turma int auto_increment primary key,
    curso_id_curso int not null,
    nome_turma varchar(20) not null,
    ano_serie_turma int not null,
    ano_letivo_turma year not null,
    periodo_turma enum('manha','tarde','noite') not null,
    foreign key (curso_id_curso) references curso(id_curso) on delete restrict on update cascade
);

create table usuario (
    id_usuario int auto_increment primary key,
    turma_id_turma int,
    nome_usuario varchar(100) not null,
    email_usuario varchar(120) not null unique,
    senha_usuario varchar(255) not null,
    genero_usuario char(1) not null,
    tipo_usuario enum('aluno','adm_sala','adm_geral','professor') not null default 'aluno',
    foto_perfil_usuario varchar(255),
    remember_token VARCHAR(255) NULL,
    ativo_usuario tinyint(1) default 1,
    foreign key (turma_id_turma) references turma(id_turma) on delete set null on update cascade
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
    ativo_modalidade TINYINT(1) DEFAULT 1,
    foto_modalidade VARCHAR(255) NULL,
    regulamento_modalidade TEXT NULL,
    tipo_duracao ENUM('minutos','pontos') NULL,
    duracao_minutos VARCHAR(10) NULL,
    duracao_pontos TINYINT UNSIGNED NULL
);

create table edicao (
    id_edicao int auto_increment primary key,
    nome_edicao varchar(80) not null,
    ano_edicao year not null,
    data_inicio_edicao date not null,
    data_fim_edicao date,
    status_edicao enum('planejamento','inscricoes','em_andamento','encerrado') default 'planejamento',
    descricao_edicao text
);

create table edicao_modalidade (
    id_edicao_modalidade int auto_increment primary key,
    edicao_id_edicao int not null,
    modalidade_id_modalidade int not null,
    data_inicio_inscricao date not null,
    data_fim_inscricao date not null,
    status_edicao_modalidade enum('inscricoes','em_andamento','encerrado') default 'inscricoes',
    foreign key (edicao_id_edicao) references edicao(id_edicao) on delete cascade on update cascade,
    foreign key (modalidade_id_modalidade) references modalidade(id_modalidade) on delete restrict on update cascade
);

create table inscricao (
    id_inscricao int auto_increment primary key,
    usuario_id_usuario int not null,
    edicao_modalidade_id int not null,
    numero_camisa_inscricao int,
    posicao_inscricao varchar(40),
    capitao_inscricao tinyint(1) default 0,
    data_inscricao datetime default current_timestamp,
    status_inscricao enum('ativa','cancelada') default 'ativa',
    foreign key (usuario_id_usuario) references usuario(id_usuario) on delete cascade on update cascade,
    foreign key (edicao_modalidade_id) references edicao_modalidade(id_edicao_modalidade) on delete cascade on update cascade
);

create table partida (
    id_partida int auto_increment primary key,
    edicao_modalidade_id int not null,
    turma_id_time_a int not null,
    turma_id_time_b int not null,
    data_partida date not null,
    hora_partida time not null,
    local_partida varchar(100),
    fase_partida enum('grupos','oitavas','quartas','semi','final','terceiro_lugar') not null,
    grupo_partida char(1),
    status_partida enum('agendada','realizada','cancelada','wo') default 'agendada',
    observacoes_partida text,
    foreign key (edicao_modalidade_id) references edicao_modalidade(id_edicao_modalidade) on delete cascade on update cascade,
    foreign key (turma_id_time_a) references turma(id_turma) on delete restrict on update cascade,
    foreign key (turma_id_time_b) references turma(id_turma) on delete restrict on update cascade
);

create table resultado (
    id_resultado int auto_increment primary key,
    partida_id_partida int not null unique,
    placar_time_a int default 0,
    placar_time_b int default 0,
    turma_id_vencedor int,
    observacoes_resultado text,
    foreign key (partida_id_partida) references partida(id_partida) on delete cascade on update cascade,
    foreign key (turma_id_vencedor) references turma(id_turma) on delete set null on update cascade
);

create table sumula (
    id_sumula int auto_increment primary key,
    partida_id_partida int not null,
    usuario_id_enviou int not null,
    nome_arquivo_sumula varchar(200) not null,
    caminho_arquivo_sumula varchar(255) not null,
    tipo_arquivo_sumula varchar(20) not null,
    data_envio_sumula datetime default current_timestamp,
    status_sumula enum('pendente','validada','rejeitada') default 'pendente',
    foreign key (partida_id_partida) references partida(id_partida) on delete cascade on update cascade,
    foreign key (usuario_id_enviou) references usuario(id_usuario) on delete cascade on update cascade
);

create table classificacao (
    id_classificacao int auto_increment primary key,
    edicao_modalidade_id int not null,
    turma_id_turma int not null,
    pontos int default 0,
    vitorias int default 0,
    derrotas int default 0,
    empates int default 0,
    pontos_pro int default 0,
    pontos_contra int default 0,
    saldo int default 0,
    jogos int default 0,
    unique (edicao_modalidade_id, turma_id_turma),
    foreign key (edicao_modalidade_id) references edicao_modalidade(id_edicao_modalidade) on delete cascade on update cascade,
    foreign key (turma_id_turma) references turma(id_turma) on delete cascade on update cascade
);

create table foto_perfil (
    id_foto int auto_increment primary key,
    usuario_id_usuario int not null,
    caminho_foto varchar(255) not null,
    nome_arquivo_foto varchar(200) not null,
    tipo_arquivo_foto varchar(10) not null,
    data_upload_foto datetime default current_timestamp,
    atual_foto tinyint(1) default 1,
    foreign key (usuario_id_usuario) references usuario(id_usuario) on delete cascade on update cascade
);
--------------------------------------
---- Area de Alimentação de Dados ----
--------------------------------------

insert into curso (nome_curso, sigla_curso)
values
	('Ensino medio com administração', 'MTEC'),
	('Ensino medio com itinerario formativo', 'EMIF'),
    ('Ensino medio com administração em periodo integral','MTECPI');

insert into turma 
(curso_id_curso, nome_turma, ano_serie_turma, ano_letivo_turma, periodo_turma) 
values
-- Professores
(1, 'Professores', 4, 2026, 'manha'),

-- MTEC
(1, '1 MTEC', 1, 2026, 'manha'),
(1, '2 MTEC', 2, 2026, 'manha'),
(1, '3 MTEC', 3, 2026, 'manha'),

-- EMIF
(1, '1 EMIF', 1, 2026, 'manha'),
(1, '2 EMIF', 2, 2026, 'manha'),
(1, '3 EMIF', 3, 2026, 'manha'),

-- PI
(1, '1 PI', 1, 2026, 'manha'),
(1, '2 PI', 2, 2026, 'manha'),
(1, '3 PI', 3, 2026, 'manha');  

insert into usuario (turma_id_turma, nome_usuario, email_usuario, senha_usuario, genero_usuario, tipo_usuario, ativo_usuario) 
values 

-- ADM (turma_id_turma é NULL)
(NULL, 'Henrique Batista Orlovas', 'batista.henriqui@gmail.com', '12345hbo', 'm', 'adm_geral', 1),
(NULL, 'Carlos Henrique Valentim', 'rikcar22@gmail.com', '12345chv', 'm', 'adm_geral', 1),
(NULL, 'Miguel Lopes Aquinez da Silva', 'miguelaquinez17@gmail.com', '12345mlas', 'm', 'adm_geral', 1),
(NULL, 'Matheus Ferreira Lopes', 'matheusflopes167@gmail.com', '12345mfl', 'm', 'adm_geral', 1),
(NULL, 'Isabelly Barbosa Santos', 'isabellybarbosantos1357@gmail.com', '12345ibs', 'f', 'adm_geral', 1),


-- Professores --
(10, 'Silmara Beltrame', 'silmara.beltrame@gmail.com', '123456', 'f', 'professor', 1),

-- Adm sala --


-- EMIF 1 (ID 4)
(4, 'Guilherme Luiz', 'guilherme.luiz@gmail.com', '123456', 'n', 'aluno', 1),
(4, 'Gabriel de Carvalho', 'gabriel.carvalho@gmail.com', '123456', 'n', 'aluno', 1),
(4, 'Laura', 'laura.emif1@gmail.com', '123456', 'n', 'aluno', 1),
(4, 'Lara', 'lara.emif1@gmail.com', '123456', 'n', 'aluno', 1),
(4, 'Luisa Souza de Jesus', 'luisa.jesus@gmail.com', '123456', 'n', 'aluno', 1),
(4, 'Carlos Miguel', 'carlos.miguel@gmail.com', '123456', 'n', 'aluno', 1),
(4, 'Pedro', 'pedro.emif1@gmail.com', '123456', 'n', 'aluno', 1),
(4, 'Melissa', 'melissa.emif1@gmail.com', '123456', 'n', 'aluno', 1),
(4, 'Amanda Batista', 'amanda.batista@gmail.com', '123456', 'n', 'aluno', 1),
(4, 'Allana Assis', 'allana.assis@gmail.com', '123456', 'n', 'aluno', 1),
(4, 'Eloise Tavares', 'eloise.tavares@gmail.com', '123456', 'n', 'aluno', 1),
(4, 'Leonardo Sousa', 'leonardo.sousa@gmail.com', '123456', 'n', 'aluno', 1),
(4, 'João Augusto', 'joao.augusto@gmail.com', '123456', 'n', 'aluno', 1),
(4, 'Alexsander Lima', 'alexsander.lima@gmail.com', '123456', 'n', 'aluno', 1),
(4, 'Pietro', 'pietro.emif1@gmail.com', '123456', 'n', 'aluno', 1),
(4, 'Julia Campos de Sousa', 'julia.sousa@gmail.com', '123456', 'n', 'aluno', 1),
(4, 'João Hélio', 'joao.helio@gmail.com', '123456', 'n', 'aluno', 1),
(4, 'Sophia Carvalho', 'sophia.carvalho@gmail.com', '123456', 'n', 'aluno', 1),
(4, 'Miguel Eduardo', 'miguel.eduardo@gmail.com', '123456', 'n', 'aluno', 1),
(4, 'Yuki', 'yuki@gmail.com', '123456', 'n', 'aluno', 1),
(4, 'Enzo Dias Siqueira', 'enzo.siqueira@gmail.com', '123456', 'n', 'aluno', 1),
(4, 'Leonardo Gomes Costa Silva', 'leonardo.silva@gmail.com', '123456', 'n', 'aluno', 1),
(4, 'Rafael Oliveira Sa', 'rafael.sa@gmail.com', '123456', 'n', 'aluno', 1),
(4, 'Iasmin Lucas Silva', 'iasmin.silva@gmail.com', '123456', 'n', 'aluno', 1),
(4, 'Lucas Azevedo', 'lucas.azevedo@gmail.com', '123456', 'n', 'aluno', 1),
(4, 'Belly', 'belly@gmail.com', '123456', 'n', 'aluno', 1),
(4, 'Rebeca', 'rebeca@gmail.com', '123456', 'n', 'aluno', 1),
(4, 'Geovanna Lima Lopes', 'geovanna.lopes@gmail.com', '123456', 'n', 'aluno', 1),
(4, 'Isabela Vasconcelos', 'isabela.vasconcelos@gmail.com', '123456', 'n', 'aluno', 1),
(4, 'Mateus Vieira', 'mateus.vieira@gmail.com', '123456', 'n', 'aluno', 1),

-- EMIF 2 (ID 5)
(5, 'Rafaella Barbosa', 'rafaella.barbosa@gmail.com', '123456', 'n', 'aluno', 1),
(5, 'Davi', 'davi.emif2@gmail.com', '123456', 'n', 'aluno', 1),
(5, 'Giovanna Silva de Oliveira', 'giovanna.oliveira@gmail.com', '123456', 'n', 'aluno', 1),
(5, 'Luiz Henrique Apolinário Silva', 'luiz.henrique@gmail.com', '123456', 'n', 'aluno', 1),
(5, 'Julia Gonçalves Zambotti', 'julia.zambotti@gmail.com', '123456', 'n', 'aluno', 1),
(5, 'Clara Cenni', 'clara.cenni@gmail.com', '123456', 'n', 'aluno', 1),
(5, 'Thales Oliveira Rieger', 'thales.rieger@gmail.com', '123456', 'n', 'aluno', 1),
(5, 'Willian Geraldo Alves Martins', 'willian.martins@gmail.com', '123456', 'n', 'aluno', 1),
(5, 'Flavia Nery', 'flavia.nery@gmail.com', '123456', 'n', 'aluno', 1),
(5, 'Giulia', 'giulia.emif2@gmail.com', '123456', 'n', 'aluno', 1),
(5, 'Arthur Mentros', 'arthur.mentros@gmail.com', '123456', 'n', 'aluno', 1),
(5, 'Giovanna Figueira', 'giovanna.figueira@gmail.com', '123456', 'n', 'aluno', 1),
(5, 'Brenda Rosa', 'brenda.rosa@gmail.com', '123456', 'n', 'aluno', 1),
(5, 'Duda', 'duda.emif2@gmail.com', '123456', 'n', 'aluno', 1),
(5, 'Gabriella Arantes', 'gabriella.arantes@gmail.com', '123456', 'n', 'aluno', 1),
(5, 'Petherson Gabriel Macedo Santos', 'petherson.santos@gmail.com', '123456', 'n', 'aluno', 1),
(5, 'Samuel José Correa de Andrade', 'samuel.andrade@gmail.com', '123456', 'n', 'aluno', 1),
(5, 'Arthur', 'arthur.emif2@gmail.com', '123456', 'n', 'aluno', 1),
(5, 'Gisele Leles de Lima', 'gisele.lima@gmail.com', '123456', 'n', 'aluno', 1),
(5, 'Henrique Soares de Matos Junior', 'henrique.matos@gmail.com', '123456', 'n', 'aluno', 1),
(5, 'Duduq', 'duduq@gmail.com', '123456', 'n', 'aluno', 1),
(5, 'Beatriz Bezerra', 'beatriz.bezerra@gmail.com', '123456', 'n', 'aluno', 1),
(5, 'Arthur Rosa Silva', 'arthur.rosa@gmail.com', '123456', 'n', 'aluno', 1),
(5, 'Eduardo Henrique', 'eduardo.henrique@gmail.com', '123456', 'n', 'aluno', 1),
(5, 'Ana Sardi', 'ana.sardi@gmail.com', '123456', 'n', 'aluno', 1),
(5, 'Gabriel Bitencourt', 'gabriel.bitencourt@gmail.com', '123456', 'n', 'aluno', 1),
(5, 'Sophia Freitas Santos', 'sophia.santos@gmail.com', '123456', 'n', 'aluno', 1),
(5, 'Herick Cilindro', 'herick@gmail.com', '123456', 'n', 'aluno', 1),
(5, 'Arthur Olintras Teixeira', 'arthur.teixeira@gmail.com', '123456', 'n', 'aluno', 1),
(5, 'Pablo Gomes Gonçalves', 'pablo.gomes@gmail.com', '123456', 'n', 'aluno', 1),
(5, 'Triana Macabi', 'triana.macabi@gmail.com', '123456', 'n', 'aluno', 1),
(5, 'Laura de Medeiros', 'laura.medeiros@gmail.com', '123456', 'n', 'aluno', 1),

-- EMIF 3 (ID 6)
(6, 'André', 'andre@gmail.com', '123456', 'n', 'aluno', 1),
(6, 'Julia Santos Costa', 'julia.costa@gmail.com', '123456', 'n', 'aluno', 1),
(6, 'Julia', 'julia.emif3@gmail.com', '123456', 'n', 'aluno', 1),
(6, 'Seiji Seguchi dos Santos', 'seiji.santos@gmail.com', '123456', 'n', 'aluno', 1),
(6, 'Karolyne', 'karolyne@gmail.com', '123456', 'n', 'aluno', 1),
(6, 'Alvaro', 'alvaro@gmail.com', '123456', 'n', 'aluno', 1),
(6, 'Nicolle Cavalcante', 'nicolle.cavalcante@gmail.com', '123456', 'n', 'aluno', 1),
(6, 'Maria Eduarda da Costa', 'maria.costa@gmail.com', '123456', 'n', 'aluno', 1),
(6, 'Rodrigo Vilas Boas', 'rodrigo.boas@gmail.com', '123456', 'n', 'aluno', 1),
(6, 'Nicolas Alves Bezerra', 'nicolas.bezerra@gmail.com', '123456', 'n', 'aluno', 1),
(6, 'Lucas Alves Almeida', 'lucas.almeida@gmail.com', '123456', 'n', 'aluno', 1),
(6, 'Grazielly', 'grazielly@gmail.com', '123456', 'n', 'aluno', 1),
(6, 'Guilherme Bispo', 'guilherme.bispo@gmail.com', '123456', 'n', 'aluno', 1),
(6, 'Mayara Veloso', 'mayara.veloso@gmail.com', '123456', 'n', 'aluno', 1),
(6, 'Maria', 'maria.emif3@gmail.com', '123456', 'n', 'aluno', 1),
(6, 'Laryssa', 'laryssa@gmail.com', '123456', 'n', 'aluno', 1),
(6, 'Clarissa', 'clarissa@gmail.com', '123456', 'n', 'aluno', 1),
(6, 'Alícia Carvalho', 'alicia.carvalho@gmail.com', '123456', 'n', 'aluno', 1),
(6, 'Mariana Oliveira', 'mariana.oliveira@gmail.com', '123456', 'n', 'aluno', 1),
(6, 'Pietra', 'pietra@gmail.com', '123456', 'n', 'aluno', 1),
(6, 'Leandro Carneiro Reis', 'leandro.reis@gmail.com', '123456', 'n', 'aluno', 1),
(6, 'Davi Bispo Bomfim', 'davi.bomfim@gmail.com', '123456', 'n', 'aluno', 1),
(6, 'Kelly', 'kelly@gmail.com', '123456', 'n', 'aluno', 1),
(6, 'Marcos Araújo', 'marcos.araujo@gmail.com', '123456', 'n', 'aluno', 1),
(6, 'Mariana Gonçalves de Lima Santos', 'mariana.lima@gmail.com', '123456', 'n', 'aluno', 1),
(6, 'Kauã Dias', 'kaua.dias@gmail.com', '123456', 'n', 'aluno', 1),
(6, 'Kauã Moreira', 'kaua.moreira@gmail.com', '123456', 'n', 'aluno', 1),
(6, 'Gabs', 'gabs@gmail.com', '123456', 'n', 'aluno', 1),
(6, 'Arthur Mafra', 'arthur.mafra@gmail.com', '123456', 'n', 'aluno', 1),
(6, 'Heloísa de Freitas', 'heloisa.freitas@gmail.com', '123456', 'n', 'aluno', 1),

-- MTEC 1 (ID 1)
(1, 'Ruan', 'ruan@email.com', '123456', 'n', 'aluno', 1),
(1, 'Phietro', 'phietro@email.com', '123456', 'n', 'aluno', 1),
(1, 'Luan Borges Santos', 'luanborges@email.com', '123456', 'n', 'aluno', 1),
(1, 'Pedro Maia de Carvalho Neto', 'pedromaia@email.com', '123456', 'n', 'aluno', 1),
(1, 'Conrado H', 'conrado@email.com', '123456', 'n', 'aluno', 1),
(1, 'Sophia Oliveira Santos', 'sophiaoliveira@email.com', '123456', 'n', 'aluno', 1),
(1, 'Dayane Aparecida', 'dayane@email.com', '123456', 'n', 'aluno', 1),
(1, 'Valeria', 'valeria@email.com', '123456', 'n', 'aluno', 1),
(1, 'Laís Borgeus Nascimento', 'laisborgeus@email.com', '123456', 'n', 'aluno', 1),
(1, 'Izabelle Crispim Faustino da Paixão', 'izabelle@email.com', '123456', 'n', 'aluno', 1),
(1, 'Beatriz Silva Lapazini', 'beatrizlapazini@email.com', '123456', 'n', 'aluno', 1),
(1, 'TIFFANY', 'tiffany@email.com', '123456', 'n', 'aluno', 1),
(1, 'Sofia Costa Andrade', 'sofiacosta@email.com', '123456', 'n', 'aluno', 1),
(1, 'Lorena Tavares Braga de Oliveira', 'lorena@email.com', '123456', 'n', 'aluno', 1),
(1, 'Maria Luiza de Souza Santos', 'marialuiza@email.com', '123456', 'n', 'aluno', 1),
(1, 'Heloiza', 'heloiza@email.com', '123456', 'n', 'aluno', 1),
(1, 'Luana Sabino', 'luana@email.com', '123456', 'n', 'aluno', 1),
(1, 'Aline de Souza Medina', 'aline@email.com', '123456', 'n', 'aluno', 1),
(1, 'João Vitor Pestana da Silva', 'joaovitor@email.com', '123456', 'n', 'aluno', 1),
(1, 'Gabriel de Almeida', 'gabriel@email.com', '123456', 'n', 'aluno', 1),
(1, 'Davi Braga', 'davibraga@email.com', '123456', 'n', 'aluno', 1),
(1, 'Laura Mariah', 'laura.mtec1@email.com', '123456', 'n', 'aluno', 1),
(1, 'Maria Luiza Silva', 'mluiza.mtec1@email.com', '123456', 'n', 'aluno', 1),
(1, 'Julia Oliveira Santos', 'juliaoliveira@email.com', '123456', 'n', 'aluno', 1),
(1, 'Isabelly Pimenta', 'isabelly@email.com', '123456', 'n', 'aluno', 1),

-- MTEC 2 (ID 2)
(2, 'Fernanda Fava', 'fernanda@email.com', '123456', 'n', 'aluno', 1),
(2, 'Mateus Soares', 'mateus.mtec2@email.com', '123456', 'n', 'aluno', 1),
(2, 'Daniel de Araujo Nogueira', 'daniel.mtec2@email.com', '123456', 'n', 'aluno', 1),
(2, 'Fernanda Destra', 'destra@email.com', '123456', 'n', 'aluno', 1),
(2, 'Eloisa Mendes Ferreira', 'eloisa@email.com', '123456', 'n', 'aluno', 1),
(2, 'Raquel Santos', 'raquel.mtec2@email.com', '123456', 'n', 'aluno', 1),
(2, 'Eduardo Romão', 'eduardo@email.com', '123456', 'n', 'aluno', 1),
(2, 'Jonathan Teo Motoki França', 'jonathan@email.com', '123456', 'n', 'aluno', 1),
(2, 'Miguel Oliveira Gama Chaves', 'miguel.mtec2@email.com', '123456', 'n', 'aluno', 1),
(2, 'Gabriell Pereira de Lana', 'gabriell@email.com', '123456', 'n', 'aluno', 1),
(2, 'Luigi Sanches Pereira Monteiro', 'luigi.mtec2@email.com', '123456', 'n', 'aluno', 1),
(2, 'Pedro Nolasco Moraes', 'pedronolasco@email.com', '123456', 'n', 'aluno', 1),
(2, 'Yasmin Azevedo', 'yasmin.mtec2@email.com', '123456', 'n', 'aluno', 1),
(2, 'Gabriela Aparecida', 'gabriela@email.com', '123456', 'n', 'aluno', 1),
(2, 'Kaíke Eduardo da Silva Vigeta dos Santos', 'kaike@email.com', '123456', 'n', 'aluno', 1),
(2, 'Ana Luísa de Sousa Osório', 'analuisa@email.com', '123456', 'n', 'aluno', 1),
(2, 'Luís Fernando Ferreira Barbosa', 'luis.mtec2@email.com', '123456', 'n', 'aluno', 1),
(2, 'Eduardo Pereira de Sousa', 'eduardop@email.com', '123456', 'n', 'aluno', 1),
(2, 'Isabela Reis', 'isabela.mtec2@email.com', '123456', 'n', 'aluno', 1),
(2, 'Nicoly Carvalho de Souza', 'nicoly.mtec2@email.com', '123456', 'n', 'aluno', 1),
(2, 'Ana Giulia Ribeiro dos Santos', 'anagiulia@email.com', '123456', 'n', 'aluno', 1),
(2, 'Kemelly Luara Delmira Guimarães', 'kemelly@email.com', '123456', 'n', 'aluno', 1),
(2, 'Fernanda de Souza', 'fernandas@email.com', '123456', 'n', 'aluno', 1),
(2, 'Davi Silva Alves', 'davis.mtec2@email.com', '123456', 'n', 'aluno', 1),
(2, 'Pedro Tavares', 'pedrot@email.com', '123456', 'n', 'aluno', 1),
(2, 'Guilherme Lopes Dourado', 'guilherme.mtec2@email.com', '123456', 'n', 'aluno', 1),
(2, 'Beatriz da Conceição Constantino', 'beatrizc.mtec2@email.com', '123456', 'n', 'aluno', 1),
(2, 'Rafaella Siervi Cabral', 'rafaella.mtec2@email.com', '123456', 'n', 'aluno', 1),
(2, 'Leonardo Lima de Oliveira', 'leonardo.mtec2@email.com', '123456', 'n', 'aluno', 1),
(2, 'Rafaella Rodrigues de Oliveira', 'rafaellar.mtec2@email.com', '123456', 'n', 'aluno', 1),

-- MTEC 3 (ID 3)
(3, 'Ana Julia Oliveira', 'anajulia@email.com', '123456', 'n', 'aluno', 1),
(3, 'Isabelly Pereira Neves', 'isabellyn.mtec3@email.com', '123456', 'n', 'aluno', 1),
(3, 'Sarah', 'sarah@email.com', '123456', 'n', 'aluno', 1),
(3, 'Igor', 'igor@email.com', '123456', 'n', 'aluno', 1),
(3, 'Maria Isabella Rodrigues Duarte', 'mariaisabella@email.com', '123456', 'n', 'aluno', 1),
(3, 'Isabella Nascimento', 'isabellan.mtec3@email.com', '123456', 'n', 'aluno', 1),
(3, 'Pedro Henrique Vieira Leal', 'pedrohenrique.mtec3@email.com', '123456', 'n', 'aluno', 1),
(3, 'Gustavo Alves da Silva', 'gustavo@email.com', '123456', 'n', 'aluno', 1),
(3, 'Matheus Nascimento Dias', 'matheus.mtec3@email.com', '123456', 'n', 'aluno', 1),
(3, 'Beatriz Teodosio', 'beatrizt@email.com', '123456', 'n', 'aluno', 1),
(3, 'Gabriel dos Santos Gomes', 'gabrielg@email.com', '123456', 'n', 'aluno', 1),
(3, 'Maria Eduarda Sousa Andrade', 'mariaeduarda.mtec3@email.com', '123456', 'n', 'aluno', 1),
(3, 'Klaus', 'klaus@email.com', '123456', 'n', 'aluno', 1),
(3, 'Marina Rita Alves Guimarães', 'marina@email.com', '123456', 'n', 'aluno', 1),
(3, 'Evelaine Cecilia Santos Serafim', 'evelaine@email.com', '123456', 'n', 'aluno', 1),
(3, 'Julia Margarida', 'juliam@email.com', '123456', 'n', 'aluno', 1),
(3, 'Isabela', 'isabela2.mtec3@email.com', '123456', 'n', 'aluno', 1),
(3, 'Mariane Sofia', 'mariane@email.com', '123456', 'n', 'aluno', 1),
(3, 'Júlia Carvalho Almeida', 'juliac@email.com', '123456', 'n', 'aluno', 1),
(3, 'Beatriz Gaefke Santiago', 'beatrizg@email.com', '123456', 'n', 'aluno', 1),
(3, 'Laís Gomes', 'lais@email.com', '123456', 'n', 'aluno', 1),
(3, 'João Adelson Vasconcelos', 'joaoadelson@email.com', '123456', 'n', 'aluno', 1),
(3, 'Kaio Ricardo Chaves de Jesus', 'kaio@email.com', '123456', 'n', 'aluno', 1),
(3, 'Vitor', 'vitor.mtec3@email.com', '123456', 'n', 'aluno', 1),
(3, 'Pedro Santana Sobrinho', 'pedros.mtec3@email.com', '123456', 'n', 'aluno', 1),
(3, 'Melissa Cerqueira dos Santos', 'melissa.mtec3@email.com', '123456', 'n', 'aluno', 1),
(3, 'Jhennifer Bispo', 'jhennifer@email.com', '123456', 'n', 'aluno', 1),
(3, 'Yasmin', 'yasmin2.mtec3@email.com', '123456', 'n', 'aluno', 1),
-- PI 1 (ID 7)
(7, 'Victor Hugo Souza Silva', 'victor@email.com', '123456', 'n', 'aluno', 1),
(7, 'Rogério Caluanã Reis Ramalho', 'rogerio@email.com', '123456', 'n', 'aluno', 1),
(7, 'Mateus Espairani da Silva', 'mateus.pi1@email.com', '123456', 'n', 'aluno', 1),
(7, 'Benjamin', 'benjamin@email.com', '123456', 'n', 'aluno', 1),
(7, 'Sabrina P Lima', 'sabrina@email.com', '123456', 'n', 'aluno', 1),
(7, 'Adrian Novais Acioli', 'adrian@email.com', '123456', 'n', 'aluno', 1),
(7, 'Nycolas', 'nycolas@email.com', '123456', 'n', 'aluno', 1),
(7, 'Caroline', 'caroline@email.com', '123456', 'n', 'aluno', 1),
(7, 'Miguel Alves', 'miguel.pi1@email.com', '123456', 'n', 'aluno', 1),
(7, 'Vitória dos Santos', 'vitoria@email.com', '123456', 'n', 'aluno', 1),
(7, 'Luciana Souza Lima', 'luciana@email.com', '123456', 'n', 'aluno', 1),
(7, 'Mirella Lauretto', 'mirella@email.com', '123456', 'n', 'aluno', 1),
(7, 'Yasmin Santos', 'yasmin.pi1@email.com', '123456', 'n', 'aluno', 1),
(7, 'Cauanny', 'cauanny@email.com', '123456', 'n', 'aluno', 1),
(7, 'Mariana', 'mariana.pi1@email.com', '123456', 'n', 'aluno', 1),
(7, 'Nathalia', 'nathalia@email.com', '123456', 'n', 'aluno', 1),
(7, 'Arthur', 'arthur.pi1@email.com', '123456', 'n', 'aluno', 1),
(7, 'Gabrielly', 'gabrielly@email.com', '123456', 'n', 'aluno', 1),
(7, 'Felipe Rodrigues Santos', 'felipe.pi1@email.com', '123456', 'n', 'aluno', 1),
(7, 'Beatriz Paz dos Santos Ferreira', 'beatriz.pi1@email.com', '123456', 'n', 'aluno', 1),
(7, 'Luiz Henryque Alves Ladislau', 'luiz.pi1@email.com', '123456', 'n', 'aluno', 1),
(7, 'Felipe de Oliveira Blanco', 'felipeb@email.com', '123456', 'n', 'aluno', 1),
(7, 'Rafael', 'rafael.pi1@email.com', '123456', 'n', 'aluno', 1),
(7, 'Daniel Pereira de Alcântara', 'daniel.pi1@email.com', '123456', 'n', 'aluno', 1),
(7, 'Gabriella', 'gabriella.pi1@email.com', '123456', 'n', 'aluno', 1),
(7, 'Leopoldina Francisca Fernanda', 'leopoldina@email.com', '123456', 'n', 'aluno', 1),

-- PI 2 (ID 8)
(8, 'Davi Victor', 'davi.pi2@email.com', '123456', 'n', 'aluno', 1),
(8, 'Mel Soares', 'mel@email.com', '123456', 'n', 'aluno', 1),
(8, 'Mary', 'mary@email.com', '123456', 'n', 'aluno', 1),
(8, 'Kathleen', 'kathleen@email.com', '123456', 'n', 'aluno', 1),
(8, 'Arthur', 'arthur2.pi2@email.com', '123456', 'n', 'aluno', 1),
(8, 'Bianca Bonfim', 'bianca@email.com', '123456', 'n', 'aluno', 1),
(8, 'Otávio Carvalho Oliveira', 'otavio@email.com', '123456', 'n', 'aluno', 1),
(8, 'Nicolly', 'nicolly.pi2@email.com', '123456', 'n', 'aluno', 1),
(8, 'Marcos', 'marcos.pi2@email.com', '123456', 'n', 'aluno', 1),
(8, 'Thomas', 'thomas@email.com', '123456', 'n', 'aluno', 1),
(8, 'Raquel Lopes Sousa', 'raquel.pi2@email.com', '123456', 'n', 'aluno', 1),
(8, 'Anna Beatriz', 'anna.pi2@email.com', '123456', 'n', 'aluno', 1),
(8, 'Ana Carolina de Souza Amurim', 'ana.carol.pi2@email.com', '123456', 'n', 'aluno', 1),
(8, 'Mellyssa', 'mellyssa@email.com', '123456', 'n', 'aluno', 1),
(8, 'Lucas Silvério de Matos', 'lucas.pi2@email.com', '123456', 'n', 'aluno', 1),
(8, 'Beatriz Cristina', 'beatrizc.pi2@email.com', '123456', 'n', 'aluno', 1),
(8, 'João', 'joao.pi2@email.com', '123456', 'n', 'aluno', 1),
(8, 'Sophia Alexandre Duarte Silva', 'sophia.pi2@email.com', '123456', 'n', 'aluno', 1),
(8, 'Isabelle', 'isabelle.pi2@email.com', '123456', 'n', 'aluno', 1),
(8, 'Lara Santos Amaro', 'lara.pi2@email.com', '123456', 'n', 'aluno', 1),
(8, 'Carolina Giovana', 'carolina.pi2@email.com', '123456', 'n', 'aluno', 1),
(8, 'Luigi Henrique', 'luigi.pi2@email.com', '123456', 'n', 'aluno', 1),
(8, 'Daniel Martins Pinheiro', 'danielp.pi2@email.com', '123456', 'n', 'aluno', 1),
(8, 'Miguel Willian', 'miguelw.pi2@email.com', '123456', 'n', 'aluno', 1),
(8, 'Cauã de Brito Fontes Benedito', 'caua@email.com', '123456', 'n', 'aluno', 1),
(8, 'Lucas Karube de Oliveira', 'karube@email.com', '123456', 'n', 'aluno', 1),

-- PI 3 (ID 9)
(9, 'Gabriel de Paula', 'gabriel.pi3@email.com', '123456', 'n', 'aluno', 1),
(9, 'Jacqueline Santos', 'jacqueline@email.com', '123456', 'n', 'aluno', 1),
(9, 'César Mica Cordiolli Nascimento', 'cesar@email.com', '123456', 'n', 'aluno', 1),
(9, 'Carolline de Castro Silva Souza', 'carolline.pi3@email.com', '123456', 'n', 'aluno', 1),
(9, 'Luan Henrique dos Santos Gonçalves', 'luan.pi3@email.com', '123456', 'n', 'aluno', 1),
(9, 'Cassia', 'cassia@email.com', '123456', 'n', 'aluno', 1),
(9, 'Adriane', 'adriane@email.com', '123456', 'n', 'aluno', 1),
(9, 'Giovanna Drovette', 'giovanna.pi3@email.com', '123456', 'n', 'aluno', 1),
(9, 'Yasmin Alves', 'yasmina.pi3@email.com', '123456', 'n', 'aluno', 1),
(9, 'Lucas Dias dos Santos', 'lucasd.pi3@email.com', '123456', 'n', 'aluno', 1),
(9, 'Natalia Cristina Santana de Azevedo', 'natalia@email.com', '123456', 'n', 'aluno', 1),
(9, 'Layssa Rodrigues', 'layssa@email.com', '123456', 'n', 'aluno', 1),
(9, 'Thomax', 'thomax@email.com', '123456', 'n', 'aluno', 1),
(9, 'Sofia Pereira e Silva', 'sofia.pi3@email.com', '123456', 'n', 'aluno', 1),
(9, 'Isabella Silva Delgado', 'isabella.pi3@email.com', '123456', 'n', 'aluno', 1),
(9, 'Alexsandro Simpliciano Marques', 'alexsandro@email.com', '123456', 'n', 'aluno', 1),
(9, 'Heloísa', 'heloisa.pi3@email.com', '123456', 'n', 'aluno', 1),
(9, 'Carolline da Silva Tinti', 'carol.pi3@email.com', '123456', 'n', 'aluno', 1),
(9, 'Renan Oliveira Peres', 'renan@email.com', '123456', 'n', 'aluno', 1),
(9, 'Isabelly Brito de Jesus', 'isabelly.pi3@email.com', '123456', 'n', 'aluno', 1);