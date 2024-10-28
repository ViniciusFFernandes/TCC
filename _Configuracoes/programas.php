<?php
  require_once("../_BD/conecta_login.php");
  //
  //Operações do banco de dados
  if(!empty($_REQUEST['id_cadastro'])){
    $sql = "SELECT * 
            FROM programas 
            WHERE idprogramas = {$_REQUEST['id_cadastro']}";
    $reg = $db->retornaUmReg($sql);
  }
  //
  //Monta variaveis de exibição
  $progModelos = "";
  $btnGravar = "";
  //
  if(!empty($reg['idprogramas'])){ 
    //
    $btnGravar = '<button type="button" onclick="testaDados()" class="btn btn-success">Gravar</button>';
    //
    $progFile = explode(".", $reg["prog_file"]);
    //
    $sql = "SELECT * 
            FROM programas
            WHERE prog_file LIKE " . $util->sgr($progFile[0] . "__mod_%");
    //
    $progModelos = $html->criaSelectSql("prog_nome", "prog_file", "prog_modelo", $reg['prog_modelo'], $sql, "form-control", "", true, "Modelo Padrão", false);
    //
  }
  //
   if (isset($_SESSION['mensagem'])) {
    $msg = $html->mostraMensagem($_SESSION['tipoMsg'], $_SESSION['mensagem']);
    unset($_SESSION['mensagem'], $_SESSION['tipoMsg']);
  }
  if (isset($_SESSION['mensagemChavePagto'])) {
    $msg .= $html->mostraMensagem("warning", $_SESSION['mensagemChavePagto']);
  }
  //
  //Abre o arquivo html e Inclui mensagens e trechos php
  $html = $html->buscaHtml(true);
  $html = str_replace("##Mensagem##", $msg, $html);
  $html = str_replace("##id_cadastro##", $reg['idprogramas'], $html);
  $html = str_replace("##prog_file##", $reg['prog_file'], $html);
  $html = str_replace("##progModelos##", $progModelos, $html);
  $html = str_replace("##btnGravar##", $btnGravar, $html);
  echo $html;
  exit;
?>