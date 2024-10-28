<?php
  require_once("../_BD/conecta_login.php");
  //
  //Operações do banco de dados
  if(!empty($_REQUEST['id_cadastro'])){
    $sql = "SELECT * 
            FROM tipo_movto_estoque 
            WHERE idtipo_movto_estoque = {$_REQUEST['id_cadastro']}";
    $reg = $db->retornaUmReg($sql);
  }
  //
  //Monta variaveis de exibição
  if(!empty($reg['idtipo_movto_estoque'])){ 
    $checkTipoEntrada = $html->defineChecked($reg['time_entrada']);
    $checkTipoSaida = $html->defineChecked($reg['time_saida']);
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
  $html = str_replace("##id_cadastro##", $reg['idtipo_movto_estoque'], $html);
  $html = str_replace("##time_nome##", $reg['time_nome'], $html);
  $html = str_replace("##CheckTipoEntrada##", $checkTipoEntrada, $html);
  $html = str_replace("##CheckTipoSaida##", $checkTipoSaida, $html);
  $html = str_replace("##btnExcluir##", $btnExcluir, $html);
  echo $html;
  exit;
?>