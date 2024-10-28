<?php
  require_once("../_BD/conecta_login.php");
  require_once("autoComplete.class.php");
  //
  //Operações do banco de dados
  if(!empty($_REQUEST['id_cadastro'])){
    $sql = "SELECT * 
            FROM cidades 
              LEFT JOIN estados ON (cid_idestados = idestados) 
            WHERE idcidades = {$_REQUEST['id_cadastro']}";
    $reg = $db->retornaUmReg($sql);
  }
  //
  //Gera o autoComplete 
  $autoComplete = new autoComplete();
  $codigo_js = $autoComplete->gerar("cid_estados", "cid_idestados", "estados", "est_nome", "idestados", "", "WHERE UPPER(est_nome) LIKE UPPER('##valor##%')");
 //echo $codigo_js;exit;
  //
  //Monta variaveis de exibição
  if(!empty($reg['idcidades'])){ 
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
  $html = str_replace("##autoComplete_Estados##", $codigo_js, $html);
  $html = str_replace("##id_cadastro##", $reg['idcidades'], $html);
  $html = str_replace("##cid_nome##", $reg['cid_nome'], $html);
  $html = str_replace("##cid_estados##", $reg['est_nome'], $html);
  $html = str_replace("##cid_idestados##", $reg['idestados'], $html);
  $html = str_replace("##btnExcluir##", $btnExcluir, $html);
  echo $html;
  exit;
?>