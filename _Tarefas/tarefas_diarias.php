<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
date_default_timezone_set('America/Sao_Paulo');
if(!realpath("../privado/constantes.vf")){
	exit;
}
require_once("../set_path.php");
require_once("DB.class.php");
require_once("util.class.php");
require_once("parametros.class.php");
require_once("atualizacao.class.php");
require_once("tarefas_diarias.class.php");
require_once("email.class.php");
require_once("usuarios.class.php");
require_once("constantes.vf");
//
//inicia as classes nescessarias
$util = new Util();
$db = new Db($SERVIDOR, $PORTA, $USUARIO, $SENHA, $DB_NAME);
$parametros = new Parametros($db);
$atualizacao = new Atualizacao($db);
$email = new Email($db);
$usuarios = new Usuarios($db);
//
//
//Conecta com o banco de dados
$db->conectar();
//
//inicio das operações
//
$tarefasDiarias = new Tarefas_Diarias($parametros, $db, $util, $atualizacao, $email, $usuarios);
$tarefasDiarias->executa_tarefas();
//
echo "Tarefas diarias executadas com sucesso!";
exit;
?>
