﻿<?php
include_once("../_BD/conecta_login.php");
include_once("../Class/Tabelas.class.php");
// print_r($_POST);
// exit;
  if ($_POST['operacao'] == "buscaMeioPagto") {
    $sql = "SELECT * FROM meio_pagto";
    //
    if ($_POST['pesquisa'] != "") {
      $sql .= " WHERE idmeio_pagto LIKE " . $util->sgr("%" . $_POST['pesquisa'] ."%") . "
                  OR mpag_nome LIKE " . $util->sgr("%" . $_POST['pesquisa'] ."%");
    }
    //
    $res = $db->consultar($sql);
    $tabelas = new Tabelas();
    //
    unset($dados);
    $dados['idmeio_pagto'] = "width='6%'";
    $dados['mpag_nome'] = "";
    //
    $tabelas->geraTabelaBusca($res, $db, $dados, "abreMeioPagto");
    exit;
  }

  if ($_POST['operacao'] == 'novoCadastro'){
    header('location:../_Cadastros/meio_pagto_edita.php');
    exit;
    }

  if ($_POST['operacao'] == 'gravar'){
  	$db->setTabela("meio_pagto", "idmeio_pagto");

    unset($dados);
    $dados['id']            = $_POST['idmeio_pagto'];
  	$dados['mpag_nome'] 	  = $util->sgr($_POST['mpag_nome']);
    $db->gravarInserir($dados, true);

  	if ($_POST['idmeio_pagto'] > 0) {
  		$id = $_POST['idmeio_pagto'];
    }else{
  		$id = $db->getUltimoID();
  }
  header('location:../_Cadastros/meio_pagto_edita.php?idmeio_pagto=' . $id);
  exit;
}

if ($_POST['operacao'] == "excluiCad") {
    $db->setTabela("meio_pagto", "idmeio_pagto");
    $db->excluir($_POST['idmeio_pagto'], "Excluir");
    if($db->erro()){
        $util->mostraErro("Erro ao excluir meio de pagamento<br>Operação cancelada!");
        exit;
    }
    header('location:../_Cadastros/meio_pagto_edita.php');
    exit;
  }

 ?>
