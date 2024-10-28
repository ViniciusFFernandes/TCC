<?php
  require_once("../_BD/conecta_login.php");
  require_once("autoComplete.class.php");
  //
  //Operações do banco de dados
  if(!empty($_REQUEST['id_cadastro'])){
    $sql = "SELECT * 
            FROM forma_pagto 
            WHERE idforma_pagto = {$_REQUEST['id_cadastro']}";
    $reg = $db->retornaUmReg($sql);
  }
  //
  //Monta variaveis de exibição
  $comboBoxTipo     = $html->defineSelected("", $reg['forp_tipo']);
  if(!empty($reg['idforma_pagto'])){ 
    $comboBoxTipoAV   = $html->defineSelected("A Vista", $reg['forp_tipo']);
    $comboBoxTipoMCE  = $html->defineSelected("Mensal com Entrada", $reg['forp_tipo']);
    $comboBoxTipoMSE  = $html->defineSelected("Mensal sem Entrada", $reg['forp_tipo']);
    $comboBoxTipoDU   = $html->defineSelected("Dias Uteis", $reg['forp_tipo']);
    $comboBoxTipoDDL  = $html->defineSelected("DDL", $reg['forp_tipo']);
    $comboBoxTipoDI   = $html->defineSelected("Dias informados", $reg['forp_tipo']);
    $comboBoxTipoPL   = $html->defineSelected("Parcelamento Livre", $reg['forp_tipo']);
    $comboBoxTipoG    = $html->defineSelected("Garantia", $reg['forp_tipo']);
    $comboBoxTipoD    = $html->defineSelected("Devolucao", $reg['forp_tipo']);
    $comboBoxTipoT    = $html->defineSelected("Troca", $reg['forp_tipo']);
    $comboBoxTipoR    = $html->defineSelected("Retorno", $reg['forp_tipo']);
    $comboBoxTipoB    = $html->defineSelected("Bonificacao", $reg['forp_tipo']);
    $comboBoxTipoCD   = $html->defineSelected("Condicional", $reg['forp_tipo']);
    $comboBoxTipoCS   = $html->defineSelected("Consignacao", $reg['forp_tipo']);
    $comboBoxTipoFM   = $html->defineSelected("Faturado", $reg['forp_tipo']);

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
  $html = str_replace("##id_cadastro##", $reg['idforma_pagto'], $html);
  $html = str_replace("##forp_nome##", $reg['forp_nome'], $html);
  $html = str_replace("##comboBoxTipo##", $comboBoxTipo, $html);
  $html = str_replace("##comboBoxTipoAV##", $comboBoxTipoAV, $html);
  $html = str_replace("##comboBoxTipoMCE##", $comboBoxTipoMCE, $html);
  $html = str_replace("##comboBoxTipoMSE##", $comboBoxTipoMSE, $html);
  $html = str_replace("##comboBoxTipoDU##", $comboBoxTipoDU, $html);
  $html = str_replace("##comboBoxTipoDDL##", $comboBoxTipoDDL, $html);
  $html = str_replace("##comboBoxTipoDI##", $comboBoxTipoDI, $html);
  $html = str_replace("##comboBoxTipoPL##", $comboBoxTipoPL, $html);
  $html = str_replace("##comboBoxTipoG##", $comboBoxTipoG, $html);
  $html = str_replace("##comboBoxTipoD##", $comboBoxTipoD, $html);
  $html = str_replace("##comboBoxTipoT##", $comboBoxTipoT, $html);
  $html = str_replace("##comboBoxTipoR##", $comboBoxTipoR, $html);
  $html = str_replace("##comboBoxTipoB##", $comboBoxTipoB, $html);
  $html = str_replace("##comboBoxTipoCD##", $comboBoxTipoCD, $html);
  $html = str_replace("##comboBoxTipoCS##", $comboBoxTipoCS, $html);
  $html = str_replace("##comboBoxTipoFM##", $comboBoxTipoFM, $html);
  $html = str_replace("##btnExcluir##", $btnExcluir, $html);
  echo $html;
  exit;
?>