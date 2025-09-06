<?php
defined('BASEPATH') or exit('No direct script access allowed');

$active_group = 'default';
$query_builder = TRUE;

$db['default'] = array(
	'dsn'	   => 'BD_Mapa', //Nome da conexão
	'hostname' => 'localhost:3306',  //Servidor onde está o banco de dados
	'username' => 'root', //Usuário do banco de dados
	'password' => '', //Caso possua, a senha do banco de dados
	'database' => 'banco_servidoresII', //Nome do banco de dados criado
	'dbdriver' => 'mysqli', //Driver do banco de dados, iremos utilizar 
	                        //esse por estarmos trabalhando com o Banco MySQL
	'dbprefix' => '',
	'pconnect' => FALSE,
	'db_debug' => (ENVIRONMENT !== 'production'),
	'cache_on' => FALSE,
	'cachedir' => '',
	'char_set' => 'utf8',
	'dbcollat' => 'utf8_general_ci',
	'swap_pre' => '',
	'encrypt' => FALSE,
	'compress' => FALSE,
	'stricton' => FALSE,
	'failover' => array(),
	'save_queries' => TRUE
);