<?php 
  //Inicia Sessão
  session_start(); 
  //
  //Desativa os erros e permite apenas avisos
  error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
  //
  //Verifica se já está logado
  if($_SESSION['logado']){
    header('Location: _Inicio/inicio.php');
    exit;
  }
  //
  if(!realpath("privado/constantes.vf")){
    header("Location: privado/_Constante/criaConstante_login.html");
    exit;
  }
  //
  require_once("set_path.php");
  //
  require_once("privado/constantes.vf");
  //
  //Inclui classes
  require_once("util.class.php");
  require_once("html.class.php");
  $util = new Util();
  $html = new html('');
  //
  //Monta variaveis de exibição
   if (isset($_SESSION['mensagem'])) {
    $msg = $html->mostraMensagem($_SESSION['tipoMsg'], $_SESSION['mensagem']);
    unset($_SESSION['mensagem'], $_SESSION['tipoMsg']);
  }
  if (isset($_SESSION['mensagemChavePagto'])) {
    $msg .= $html->mostraMensagem("warning", $_SESSION['mensagemChavePagto']);
  }
  //
  //Abre o arquivo html e Inclui mensagens e trechos php
  $html = file_get_contents('login.html');
  $html = str_replace("##Mensagem##", $msg, $html);
  $html = str_replace("##nomeSistema##", NOME_SISTEMA, $html);
  echo $html;
  exit;
?>
