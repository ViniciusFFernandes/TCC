﻿<?php
  require_once("../_BD/conecta_login.php");
  require_once("../Class/Tabelas.class.php");
  require_once("../Class/html.class.php");
  //
  //Inicia classes nescessarias
  $html = new html($db, $util);
  //
  $paginaRetorno = 'grupos_acessos.php';
  //
  if ($_POST['operacao'] == "buscaCadastro") {
    $sql = "SELECT * 
			FROM grupos_acessos";
			
    if ($_POST['pesquisa'] != "") {
        $sql .= " WHERE idgrupos_acessos LIKE " . $util->sgr("%" . $_POST['pesquisa'] ."%") . "
                  OR grac_nome LIKE " . $util->sgr("%" . $_POST['pesquisa'] ."%");
    }
    //
    $res = $db->consultar($sql);
    $tabelas = new Tabelas();
    //
    unset($dados);
    $dados['idgrupos_acessos'] = "width='6%'";
    $dados['grac_nome'] = "";
    //
    $tabelas->geraTabelaBusca($res, $db, $dados, $paginaRetorno);
    exit;
  }

  if ($_POST['operacao'] == "ativarDesativarProgram") {
    //
    // $db->beginTransaction();
    //
    $db->setTabela("grupos_acessos_programas", "idgrupos_acessos_programas");
    //
    unset($dados);
    $dados['id']            = $_POST['idgrupos_acessos_programas'];
  	$dados['gap_executa']   = $util->igr($_POST['gap_executa']);
    $db->gravarInserir($dados, true);
    //
    unset($dados);
    if($db->erro()){
      // $db->rollBack();
      $dados['retorno'] = 'erro';
      $dados['msg'] = $db->getErro();
    }else{
      $dados['retorno'] = 'ok';
      $sql = "SELECT * FROM grupos_acessos_programas JOIN programas ON (gap_idprogramas = idprogramas) WHERE idgrupos_acessos_programas = " . $_POST['idgrupos_acessos_programas'];
      $reg = $db->retornaUmReg($sql);
      if($reg['prog_tipo'] == 'menu'){
        $ret = $html->criaMenu($reg['gap_idgrupos_acessos'], $reg['prog_tipo_menu']);
        if(!$ret){
          // $db->rollBack();
          $dados['retorno'] = 'erro';
          $dados['msg'] = 'Erro ao recriar menu!<br>Operação cancelada!';
        }
      }
    }
    // $db->commit();
    echo json_encode($dados);
    exit;
  }

  if ($_POST['operacao'] == 'novoCadastro'){
    header('location:../_Configuracoes/' . $paginaRetorno);
    exit;
    }

  if ($_POST['operacao'] == 'gravar'){
  	$db->setTabela("grupos_acessos", "idgrupos_acessos");
    //
    unset($dados);
    $dados['id']              = $_POST['id_cadastro'];
  	$dados['grac_nome'] 			= $util->sgr($_POST['grac_nome']);
    $db->gravarInserir($dados, true);
    //
  	if ($_POST['id_cadastro'] > 0) {
  		$id = $_POST['id_cadastro'];
    }else{
      $id = $db->getUltimoID();
      //
      //Insere a permissão dos programas
      inserePermissoes($id);
  }
    header('location: ../_Configuracoes/' . $paginaRetorno . '?id_cadastro=' . $id);
    exit;
}

if ($_POST['operacao'] == "excluiCad") {
    $db->setTabela("grupos_acessos", "idgrupos_acessos");
    $db->excluir($_POST['id_cadastro'], "Excluir");
    if($db->erro()){
        $html->mostraErro("Erro ao excluir grupo<br>Operação cancelada!");
        exit;
    }
    header('location:../_Configuracoes/' . $paginaRetorno);
    exit;
  }

if($_POST['operacao'] == 'ativarDesativarTodos'){
  $sql = "UPDATE grupos_acessos_programas SET gap_executa = {$_POST['gap_executa']} WHERE gap_idgrupos_acessos = " . $_POST['gap_idgrupos_acessos'];
  $db->executaSQL($sql);
  //
  $sql = "SELECT * FROM grupos_acessos_programas WHERE gap_idgrupos_acessos = " . $_POST['gap_idgrupos_acessos'];
  $res = $db->consultar($sql);
  echo json_encode($res);
}

  function inserePermissoes($idGruposAcessos){
    global $db;
    
    $sql = "INSERT INTO grupos_acessos_programas (gap_idgrupos_acessos, gap_idprogramas, gap_executa)
              SELECT {$idGruposAcessos}, idprogramas, 0 FROM programas";
              echo $sql;
    $db->executaSQL($sql);
  }
?>