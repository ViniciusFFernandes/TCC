<?php
  require_once("../_BD/conecta_login.php");
  require_once("tabelas.class.php");
  require_once("producao.class.php");
  require_once("autoComplete.class.php");
  //
  //Operações do banco de dados
  if(!empty($_REQUEST['id_cadastro'])){
    $sql = "SELECT *
            FROM producao
              LEFT JOIN produtos ON (idprodutos = pdc_idprodutos)
            WHERE idproducao = {$_REQUEST['id_cadastro']}";
    $reg = $db->retornaUmReg($sql);
  }
  //
  //Gera o autoComplete 
  $autoComplete = new autoComplete();
  $codigo_js = $autoComplete->gerar("pdc_produtos", "pdc_idprodutos", "produtos", "prod_nome", "idprodutos", "", "WHERE UPPER(prod_nome) LIKE UPPER('##valor##%') AND prod_tipo_produto = 'Producao Propria'");
  $codigo_campo = $autoComplete->criaCampos("pdc_produtos", "pdc_idprodutos", "Filtrar Produto");
  //
  //Monta variaveis de exibição
  $btnGravar = '<button type="button" onclick="testaDados(\'gravar\')" class="btn btn-success">Gravar</button>';
  if(!empty($reg['idproducao'])){ 
    //
    $producao = new producao($db, $util);
    $itensFormula = $producao->getItensProducao($reg['idproducao']);
    //
    $btnExcluir = '<button type="button" onclick="excluiCadastro()" class="btn btn-danger">Excluir</button>';
    $pdc_situacao = "Situação: " . $reg['pdc_situacao'];
    //
    if($reg['pdc_situacao'] == 'Aberta'){
      $btnFecharReabrir = '<button type="button" onclick="testaDados(\'fechar\')" class="btn btn-warning">Fechar</button>';
    }elseif($reg['pdc_situacao'] == 'Fechada'){
      $btnFecharReabrir = '<button type="button" onclick="testaDados(\'reabrir\')" class="btn btn-warning">Reabrir</button>';
      $btnGravar = '';
      $btnExcluir = '';
    }
  }

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
  $html = str_replace("##autoComplete_Produto##", $codigo_js, $html);
  $html = str_replace("##autoComplete_CampoProduto##", $codigo_campo, $html);
  $html = str_replace("##id_cadastro##", $reg['idproducao'], $html);
  $html = str_replace("##pdc_situacao##", $pdc_situacao, $html);
  $html = str_replace("##pdc_data_abertura##", $util->convertData($reg['pdc_data_abertura']), $html);
  $html = str_replace("##pdc_data_fechamento##", $util->convertData($reg['pdc_data_fechamento']), $html);
  $html = str_replace("##pdc_produtos##", $reg['prod_nome'], $html);
  $html = str_replace("##pdc_idprodutos##", $reg['pdc_idprodutos'], $html);
  $html = str_replace("##pdc_qte_produzida##", $reg['pdc_qte_produzida'], $html);
  $html = str_replace("##pdc_calcula_automatico##", $pdc_calcula_automatico, $html);
  $html = str_replace("##ItensProducao##", $itensFormula, $html);
  $html = str_replace("##btnGravar##", $btnGravar, $html);
  $html = str_replace("##btnExcluir##", $btnExcluir, $html);
  $html = str_replace("##btnFecharReabrir##", $btnFecharReabrir, $html);
  echo $html;
  exit;
?>