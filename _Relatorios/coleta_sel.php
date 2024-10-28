<?php
  require_once("../_BD/conecta_login.php");
  require_once("tabelas.class.php");
  require_once("autoComplete.class.php");
  //
  //Gera o autoComplete 
  $autoComplete = new autoComplete();
  $codigo_js = $autoComplete->gerar("produtos", "idprodutos", "produtos", "prod_nome", "idprodutos", "", "WHERE UPPER(prod_nome) LIKE UPPER('##valor##%')");
  $codigo_campo = $autoComplete->criaCampos("produtos", "idprodutos", "Filtrar Produto");
  //
  //
   if (isset($_SESSION['mensagem'])) {
    $msg = $html->mostraMensagem($_SESSION['tipoMsg'], $_SESSION['mensagem']);
    unset($_SESSION['mensagem'], $_SESSION['tipoMsg']);
  }
  if (isset($_SESSION['mensagemChavePagto'])) {
    $msg .= $html->mostraMensagem("warning", $_SESSION['mensagemChavePagto']);
  }
  //
  //
  //Monta variaveis de exibição
  $sql = "SELECT * FROM empresas";
  $comboBoxEmpresas = $html->criaSelectSql("emp_nome", "idempresas", "idempresas", '', $sql, "form-control", "", true, "Selecione a Empresa");
  //
  //Abre o arquivo html e Inclui mensagens e trechos php
  $html = $html->buscaHtml(true);
  $html = str_replace("##Mensagem##", $msg, $html);
  $html = str_replace("##autoComplete_Produto##", $codigo_js, $html);
  $html = str_replace("##autoComplete_CampoProduto##", $codigo_campo, $html);
  $html = str_replace("##comboBoxEmpresas##", $comboBoxEmpresas, $html);
  $html = str_replace("##produtos##", "", $html);
  $html = str_replace("##idprodutos##", "", $html);
  echo $html;
  exit;
?>