<?php
  require_once("../_BD/conecta_login.php");
  //
  //Operações do banco de dados
  if(!empty($_REQUEST['id_cadastro'])){
    $sql = "SELECT * 
            FROM tipo_contas 
            WHERE idtipo_contas = {$_REQUEST['id_cadastro']}";
    $reg = $db->retornaUmReg($sql);
  }
  //
  //Monta variaveis de exibição
  if(!empty($reg['idtipo_contas'])){ 
    $checkTipoVale = $html->defineChecked($reg['tico_tipo_vale']);
    $checkTipoExtra = $html->defineChecked($reg['tico_tipo_extra']);
    $checkTipoSalario = $html->defineChecked($reg['tico_tipo_salario']);
    //
    $btnExcluir = '<button type="button" onclick="excluiCadastro()" class="btn btn-danger">Excluir</button>';
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
  $html = str_replace("##id_cadastro##", $reg['idtipo_contas'], $html);
  $html = str_replace("##tico_nome##", $reg['tico_nome'], $html);
  $html = str_replace("##tico_cor##", $reg['tico_cor'], $html);
  $html = str_replace("##CheckTipoVale##", $checkTipoVale, $html);
  $html = str_replace("##CheckTipoExtra##", $checkTipoExtra, $html);
  $html = str_replace("##CheckTipoSalario##", $checkTipoSalario, $html);
  $html = str_replace("##btnExcluir##", $btnExcluir, $html);
  echo $html;
  exit;
?>