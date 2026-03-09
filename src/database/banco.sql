drop database if exists soee;
create database if not exists soee;
use soee;

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
    rm_usuario varchar(10) not null,
    ra_usuario varchar(15),
    cpf_usuario varchar(14) not null unique,
    genero_usuario char(1) not null,
    data_nasc_usuario date not null,
    tipo_usuario enum('aluno','adm_sala','adm_geral') not null default 'aluno',
    foto_perfil_usuario varchar(255),
    ativo_usuario tinyint(1) default 1,
    foreign key (turma_id_turma) references turma(id_turma) on delete set null on update cascade
);

create table modalidade (
    id_modalidade int auto_increment primary key,
    nome_modalidade varchar(60) not null unique,
    descricao_modalidade text,
    tipo_modalidade enum('quadra','mesa','campo','outro') not null,
    formato_modalidade enum('mata_mata','grupos','grupos_mata_mata','todos_contra_todos') not null,
    tipo_participacao enum('solo','dupla','trio','time') not null,
    qtd_min_jogadores int not null,
    qtd_max_jogadores int not null,
    ativo_modalidade tinyint(1) default 1
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
    foreign key (usuario_id_usuario)references usuario(id_usuario)on delete cascade on update cascade,
    foreign key (edicao_modalidade_id)references edicao_modalidade(id_edicao_modalidade) on delete cascade on update cascade
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

-- valores teste
insert into curso (nome_curso, sigla_curso)
values ('técnico em informática', 'mtec');

insert into turma(curso_id_curso,nome_turma,ano_serie_turma,ano_letivo_turma,periodo_turma) 
values (1,'3 mtec',3,2026,'manha');

insert into usuario(turma_id_turma,nome_usuario,email_usuario,senha_usuario,rm_usuario,ra_usuario,cpf_usuario,genero_usuario,data_nasc_usuario,tipo_usuario,ativo_usuario) 
values (1,'henrique','henrique@soee.com','12345hb','00001','00001','000.000.000-00','m','2009-03-13','adm_geral',1);