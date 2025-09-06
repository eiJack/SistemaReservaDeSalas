Create database banco_servidoresII;
 
use banco_servidoresII;
 
create table tbl_usuario (
id_usuario integer not null auto_increment primary key,
nome varchar(50),
usuario varchar(15),
senha varchar(32),
email varchar(80),
dtcria datetime default now(),
estatus char(01) default ''
);
 
Create table tbl_sala(
codigo integer primary key,
descricao varchar(30) default '',
andar integer,
capacidade integer,
dtcria datetime default now(),
estatus char(1) default ''
);
 
Create table tbl_professor(
codigo integer auto_increment primary key,
nome varchar(30) default '',
cpf varchar(11) default '',
tipo char(1) default 'F',
dtcria datetime default now(),
estatus char(01) default ''
);
 
Create table tbl_turma(
codigo integer auto_increment primary key,
descricao varchar(50),
capacidade integer default 0,
dataInicio date,
dtcria datetime default now(),
estatus char(01) default ''
);
 
Create table tbl_horario(
codigo integer auto_increment primary key,
descricao varchar(50) default '',
hora_ini time,
hora_fim time,
dtcria datetime default now(),
estatus char(01) default ''
);
 
Create table tbl_mapa(
codigo integer auto_increment primary key,
datareserva date,
sala integer default 0,
codigo_horario integer default 0,
codigo_turma integer default 0,
codigo_professor integer default 0,
estatus char(01) default '',
foreign key (sala) references tbl_sala(codigo),
foreign key (codigo_horario) references tbl_horario(codigo),
foreign key (codigo_turma) references tbl_turma (codigo),
foreign key (codigo_professor) references tbl_professor(codigo)
);
