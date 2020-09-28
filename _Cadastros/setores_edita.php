<?php
  require_once("../_BD/conecta_login.php");
  //
  //Operações do banco de dados
  if(!empty($_REQUEST['id_cadastro'])){
    $sql = "SELECT * 
            FROM setores 
            WHERE idsetores = {$_REQUEST['id_cadastro']}";
    $reg = $db->retornaUmReg($sql);
  }
  //
  //Monta variaveis de exibição
  if(!empty($reg['idsetores'])){ 
    $btnExcluir = '<button type="button" onclick="excluiCadastro()" class="btn btn-danger">Excluir</button>';
  }
  //
  if (isset($_SESSION['mensagem'])) {
    $msg = $html->mostraMensagem($_SESSION['tipoMsg'], $_SESSION['mensagem']);
    unset($_SESSION['mensagem'], $_SESSION['tipoMsg']);
  }
  //
  //Abre o arquivo html e Inclui mensagens e trechos php
  $html = $html->buscaHtml("cadastros", $parametros);
  $html = str_replace("##Mensagem##", $msg, $html);
  $html = str_replace("##id_cadastro##", $reg['idsetores'], $html);
  $html = str_replace("##set_nome##", $reg['set_nome'], $html);
  $html = str_replace("##btnExcluir##", $btnExcluir, $html);
  echo $html;
  exit;
?>