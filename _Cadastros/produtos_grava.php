<?php
require_once("../_BD/conecta_login.php");
require_once("tabelas.class.php");
// print_r($_POST);
// exit;
$paginaRetorno = 'produtos_edita.php';
//
if ($_POST['operacao'] == "buscaCadastro") {
    $sql = "SELECT * 
            FROM produtos 
              LEFT JOIN unidades ON (prod_idunidades = idunidades) 
              LEFT JOIN grupos ON (prod_idgrupos = idgrupos)";
    //
    if ($_POST['pesquisa'] != "") {
      $sql .= " WHERE idprodutos LIKE " . $util->sgr("%" . $_POST['pesquisa'] ."%") . "
                OR prod_nome LIKE " . $util->sgr("%" . $_POST['pesquisa'] ."%") . "
                OR grup_nome LIKE " . $util->sgr("%" . $_POST['pesquisa'] ."%");
    }
    //
    $res = $db->consultar($sql);
    $tabelas = new Tabelas();
    //
    unset($dados);
    $dados['idprodutos'] = "width='6%'";
    $dados['prod_nome'] = "";
    $dados['uni_sigla'] = "width='10%'";
    // $dados['grup_nome'] = "width='25%'";
    //
    $cabecalho['Código'] = '';
    $cabecalho['Nome'] = '';
    $cabecalho['Unidade'] = '';
    // $cabecalho['Grupo'] = '';
    //
    echo $tabelas->geraTabelaBusca($res, $db, $dados, $paginaRetorno, $cabecalho);
    exit;
  }

  if ($_POST['operacao'] == 'novoCadastro'){
    header('location:../_Cadastros/' . $paginaRetorno);
    exit;
    }

  if ($_POST['operacao'] == 'gravar'){
    $db->setTabela("produtos", "idprodutos");

    unset($dados);
    $dados['id']                     = $_POST['id_cadastro'];
    $dados['prod_nome']              = $util->sgr($_POST['prod_nome']);
    $dados['prod_idgrupos']          = $util->igr($_POST['prod_idgrupos']);
    $dados['prod_idsubgrupos']       = $util->igr($_POST['prod_idsubgrupos']);
    $dados['prod_idunidades']        = $util->igr($_POST['prod_idunidades']);
    $dados['prod_tipo_produto']      = $util->sgr($_POST['prod_tipo_produto']);
    $dados['prod_preco_tabela']      = $util->vgr($_POST['prod_preco_tabela']);

    $db->gravarInserir($dados, true);
    if($db->erro()){
      $html->mostraErro("Erro ao inserir produto<br>Erro:" . $db->getErro());
      exit;
    }

    if ($_POST['id_cadastro'] > 0) {
      $id = $_POST['id_cadastro'];
    }else{
      $id = $db->getUltimoID();
    }
    header('location:../_Cadastros/'. $paginaRetorno . '?id_cadastro=' . $id);
    exit;
}

if ($_POST['operacao'] == "excluiCad") {
    $db->setTabela("produtos", "idprodutos");
    $db->excluir($_POST['id_cadastro'], "Excluir");
    if($db->erro()){
        $html->mostraErro("Erro ao excluir cadastro<br>Operação cancelada!");
        exit;
    }
    header('location:../_Cadastros/' . $paginaRetorno);
    exit;
  }

  if ($_POST['operacao'] == "excluirItemFormula") {
    $db->setTabela("produtos_formulas", "idprodutos_formulas");
    $db->excluir($_POST['idItemFormula']);
    exit;
  }

  if($_POST['operacao'] == 'gravarItem'){
    $db->setTabela("produtos_formulas", "idprodutos_formulas");
    //
    unset($dados);
    $dados['pfor_idproduto_final']   = $util->igr($_POST['pfor_idproduto_final']);
    $dados['pfor_idprodutos']       = $util->igr($_POST['pfor_idprodutos']);
    $dados['pfor_qte']              = $util->vgr($_POST['pfor_qte']);
    $dados['pfor_porc_perca']       = $util->vgr($_POST['pfor_porc_perca']);
    //
    $db->gravarInserir($dados, false, true);
    $id = $db->getUltimoID();
    //print_r($ret);
    // exit;
    //
    if(!$db->erro()){
        $sql = "SELECT prod_nome FROM produtos WHERE idprodutos = {$_POST['pfor_idprodutos']}";
        $nomeProd = $db->retornaUmCampoSql($sql, "prod_nome");
        ob_clean();
        $retorno = "<tr id='itemFormula_" . $id . "'>";
        $retorno .= "<td>{$_POST['pfor_idprodutos']}</td>";
        $retorno .= "<td>{$nomeProd}</td>";
        $retorno .= "<td>{$_POST['pfor_qte']}</td>";
        $retorno .= "<td>{$_POST['pfor_porc_perca']}%</td>";
        $retorno .= "<td onclick='excluirItemFormula({$db->getUltimoID()})' style='cursor: pointer;'><img src='../icones/excluir.png'></td>";
        $retorno .= "</tr>";
        echo $retorno;
    }
    exit;
}
 ?>
