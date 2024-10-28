<?php
require_once("../_BD/conecta_login.php");
require_once("tabelas.class.php");
// print_r($_POST);
// exit;
$paginaRetorno = 'pessoas_edita.php';
//
  if ($_POST['operacao'] == "buscaCadastro") {
    $sql = "SELECT * 
            FROM pessoas 
              LEFT JOIN cidades ON (pess_idcidades = idcidades) 
              LEFT JOIN estados ON (cid_idestados = idestados)";
    //
    if ($_POST['pesquisa'] != "") {
      $sql .= " WHERE idpessoas LIKE " . $util->sgr("%" . $_POST['pesquisa'] ."%") . "
                OR pess_nome LIKE " . $util->sgr("%" . $_POST['pesquisa'] ."%") . "
                OR pess_idcidades LIKE " . $util->sgr("%" . $_POST['pesquisa'] ."%");
    }
    $res = $db->consultar($sql);
    $tabelas = new Tabelas();
    echo $tabelas->geraTabelaPes($res, $db);
    exit;
  }

  if ($_POST['operacao'] == "excluiTelefones") {
    $db->setTabela("pessoas_numeros", "idpessoas_numeros");
    $db->excluir($_POST['idtelefone']);
    //
    $ret['retorno'] = "ok";
    echo json_encode($ret);
    exit;
  }

  if ($_POST['operacao'] == "gravaTelefones") {
    $ret['retorno'] = "ok";
    //
    if($db->retornaUmCampoID("pess_inativo", "pessoas", $_POST['id_cadastro']) == 'S'){
      $ret['retorno'] = "erro";
      $ret['msg'] = "Você não pode alterar os dados de uma pessoa invativa\nOperação cancelada!";
      echo json_encode($ret);
      exit;
    }
    //
    $db->setTabela("pessoas_numeros", "idpessoas_numeros");
    $dados['pnum_idpessoas']        = $util->sgr($_POST['idpessoas']);
    $dados['pnum_DDD']              = $util->sgr($_POST['pnum_DDD']);
    $dados['pnum_numero']           = $util->sgr($_POST['pnum_numero']);
    $db->gravarInserir($dados);
    $idFone = $db->getUltimoID();
    //
    $linhaFone = "";
    $linhaFone .= '<tr id="linhaFone_' . $idFone . '">';
    $linhaFone .= '<td width="50px">(' . trim($_POST['pnum_DDD']) . ')</td>';
    $linhaFone .= '<td>' . $_POST['pnum_numero'] . '</td>';
    $linhaFone .= '<td width="40px;" class="pl-0 pr-0" align="center" id="btnExcluiTelefone_' . $idFone . '"><img src="../icones/excluir.png" onclick="excluirTelefone(' . $idFone . ')" style="cursor:pointer;"></td>';
    $linhaFone .= '</tr>';
    //
    $ret["linhaFone"] = $linhaFone;
    //
    echo json_encode($ret);
    exit;
  }

  if ($_POST['operacao'] == "buscaTelefones") {
    $sql = "SELECT * FROM pessoas_numeros WHERE pnum_idpessoas = " . $_POST['idpessoas'];
    $res = $db->consultar($sql);
    //
    $tabelas = new Tabelas();
    $tabelas->geraTabelaTel($res);
    exit;
  }

  if ($_POST['operacao'] == 'novoCadastro'){
    header('location:../_Cadastros/' . $paginaRetorno);
    exit;
    }

  if ($_POST['operacao'] == 'validaUsuario'){
    $db->setTabela("pessoas");
    $sql = "SELECT * FROM pessoas WHERE pess_usuario = " . $util->sgr($_POST['pess_usuario']) . " AND idpessoas <> " . $_POST['idpessoas'];
    $reg = $db->retornaUmReg($sql);
    if($reg['idpessoas'] > 0){
      $dados['existe'] = "true";
    }else{
      $dados['existe'] = "false"; 
    }
    //
    echo json_encode($dados);
    exit;
  }

  if ($_POST['operacao'] == 'validaSenha'){
    $db->setTabela("pessoas");
    $sql = "SELECT * FROM pessoas WHERE idpessoas = " . $_POST['idpessoas'];
    $reg = $db->retornaUmReg($sql);
    if($reg['pess_senha'] != ''){
      $dados['existe'] = "true";
    }else{
      $dados['existe'] = "false"; 
    }
    //
    echo json_encode($dados);
    exit;
  }

  if ($_POST['operacao'] == 'gravaLogin'){
    if($db->retornaUmCampoID("pess_inativo", "pessoas", $_POST['id_cadastro']) == 'S'){
      $dados['retorno'] = "erro";
      $dados['msg'] = "Você não pode alterar os dados de uma pessoa invativa\nOperação cancelada!";
      echo json_encode($dados);
      exit;
    }
    if($_POST['pess_idgrupos_acessos'] == 1){
      unset($dados);
      $dados['retorno'] = "erro";
      $dados['msg'] = "Não é permitido inserir usuarios no grupo Administradores!";
      echo json_encode($dados);
      exit;
    }
    //
    $db->setTabela("pessoas", "idpessoas");
    //
    $dados['id']                      = $_POST['idpessoas'];
    $dados['pess_usuario']            = $util->sgr($_POST['pess_usuario']);
    $dados['pess_idgrupos_acessos']   = $util->sgr($_POST['pess_idgrupos_acessos']);
    if(!empty($_POST['pess_senha'])){
      $dados['pess_senha']            = $util->sgr($_POST['pess_senha']);
    }
    $db->gravarInserir($dados);
    //
    unset($dados);
    if($db->erro()){
      $dados['retorno'] = "erro";
      $dados['msg'] = $db->getErro();
    }else{
      $dados['retorno'] = "ok";
    }
    echo json_encode($dados);
    exit;
    }

  if ($_POST['operacao'] == 'gravar'){
    //
    if($db->retornaUmCampoID("pess_inativo", "pessoas", $_POST['id_cadastro']) == 'S'){
       $html->mostraErro("Você não pode alterar os dados de uma pessoa invativa.<br>Operação cancelada!");
      exit;
    }
    //
    if($_POST['pess_nome'] == '' || $_POST['pess_idcidades'] == ''){
      $html->mostraErro("Você não pode incluir uma pessoa sem informar nome e cidade.<br>Operação cancelada!");
     exit;
    }
    //
  	$db->setTabela("pessoas", "idpessoas");
    //
    unset($dados);
    $dados['id']                     = $_POST['id_cadastro'];
  	$dados['pess_nome'] 			       = $util->sgr($_POST['pess_nome']);
  	$dados['pess_rg'] 				       = $util->sgr($_POST['pess_rg']);
  	$dados['pess_cpf'] 				       = $util->sgr($_POST['pess_cpf']);
  	$dados['pess_cnpj'] 			       = $util->sgr($_POST['pess_cnpj']);
  	$dados['pess_cep'] 				       = $util->sgr($_POST['pess_cep']);
  	$dados['pess_endereco'] 		     = $util->sgr($_POST['pess_endereco']);
  	$dados['pess_endereco_numero'] 	 = $util->sgr($_POST['pess_endereco_numero']);
    $dados['pess_idcidades']         = $util->igr($_POST['pess_idcidades']);
    $dados['pess_idsetores']         = $util->igr($_POST['pess_idsetores']);
    $dados['pess_idfuncoes']         = $util->igr($_POST['pess_idfuncoes']);
  	$dados['pess_bairro'] 			     = $util->sgr($_POST['pess_bairro']);
  	$dados['pess_cliente'] 			     = $util->sgr($_POST['pess_cliente']);
  	$dados['pess_fornecedor'] 		   = $util->sgr($_POST['pess_fornecedor']);
  	$dados['pess_funcionario'] 		   = $util->sgr($_POST['pess_funcionario']);
  	$dados['pess_associado'] 		     = $util->sgr($_POST['pess_associado']);
  	$dados['pess_vip'] 		           = $util->sgr($_POST['pess_vip']);
  	$dados['pess_idempresas'] 		   = $util->sgr($_POST['pess_idempresas']);
  	$dados['pess_cod_cliente'] 		   = $util->sgr($_POST['pess_cod_cliente']);
    //
    $db->gravarInserir($dados, true);
    //
    if($db->erro()){
      $html->mostraErro("Erro ao gravar pessoa<br>Operação cancelada!<br>" . $db->getErro());
      exit;
    }
    //
  	if ($_POST['id_cadastro'] > 0) {
      $id = $_POST['id_cadastro'];
    }else{
  		$id = $db->getUltimoID();
    }
    header('location:../_Cadastros/' . $paginaRetorno . '?id_cadastro=' . $id);
    exit;
  }

  if($_POST['operacao'] == "inativaAtivaPessoa"){
    $db->setTabela("pessoas", "idpessoas");

    $dados['id']                     = $_POST['idpessoas'];
    $dados['pess_inativo'] 		     = $util->sgr($_POST['pess_inativo']);

    $db->gravarInserir($dados, false);
  }

  if ($_POST['operacao'] == "excluiCad") {
    $db->setTabela("pessoas", "idpessoas");
    $db->excluir($_POST['id_cadastro'], "Excluir");
    if($db->erro()){
        $html-> $html->mostraErro("Erro ao excluir cadastro<br>Operação cancelada!");
        exit;
    }
    header('location:../_Cadastros/' . $paginaRetorno);
    exit;
  }

  if ($_POST['operacao'] == "geraChavePagto") {
    //
    $novaChavePagto = $usuarios->gerar_chave_pagto($_POST["pess_cod_cliente"], $_POST["tipo"]);
    //
    $dados['chavePagto'] = $novaChavePagto;
    //
    echo json_encode($dados);
    exit;
  }

?>
