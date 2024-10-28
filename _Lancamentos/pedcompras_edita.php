<?php
  require_once("../_BD/conecta_login.php");
  require_once("autoComplete.class.php");
  require_once("pedcompras.class.php");
  //
  //Operações do banco de dados
  if(!empty($_REQUEST['id_cadastro'])){
    $sql = "SELECT * 
            FROM pedcompras 
              LEFT JOIN pessoas ON (pcom_idfornecedor = idpessoas)
              LEFT JOIN forma_pagto ON (pcom_idforma_pagto = idforma_pagto)
            WHERE idpedcompras = {$_REQUEST['id_cadastro']}";
    $reg = $db->retornaUmReg($sql);
  }
  //
  //Inicia a Classe
  $pedcompras = New Pedcompras($db);
  //
  //Gera o autoComplete 
  $autoComplete = new autoComplete();
  $codigo_js_pessoas = $autoComplete->gerar("pcom_pessoa", "pcom_idfornecedor", "pessoas LEFT JOIN cidades ON (pess_idcidades = idcidades) LEFT JOIN estados ON (cid_idestados = idestados)", "CONCAT(pess_nome, ', ', cid_nome, ' - ', est_uf)", "idpessoas", "", "WHERE UPPER(pess_nome) LIKE UPPER('##valor##%') AND pess_fornecedor = 'SIM'");
  $codigo_campo_pessoas = $autoComplete->criaCampos("pcom_pessoa", "pcom_idfornecedor", "Cliente");
  //
  $codigo_js_produtos = $autoComplete->gerar("prod_nome", "idprodutos", "produtos", "prod_nome", "idprodutos", "", "WHERE UPPER(prod_nome) LIKE UPPER('##valor##%')", 10, 'buscaDadosProduto()');
  $codigo_campo_produtos = $autoComplete->criaCampos("prod_nome", "idprodutos", "Produto");
  //
  //Monta variaveis de exibição
  //
  $sql = "SELECT * FROM forma_pagto";
  $comboBoxFormaPagto = $html->criaSelectSql("forp_nome", "idforma_pagto", "pcom_idforma_pagto", $reg['pcom_idforma_pagto'], $sql, "form-control", 'onchange="alteraFormaPagto()"', true, "Forma de Pagamento");
  //
  //
  $sql = "SELECT * FROM meio_pagto";
  $comboBoxMeioPagto = $html->criaSelectSql("mpag_nome", "idmeio_pagto", "pcom_idmeio_pagto", $reg['pcom_idmeio_pagto'], $sql, "form-control", "", true, "Meio de Pagamento");
  //
  $sql = "SELECT * FROM bancos";
  $comboBoxBancos = $html->criaSelectSql("banc_nome", "idbancos", "pcom_idbancos", $reg['pcom_idbancos'], $sql, "form-control", 'onchange="carregaComboBoxCC()"', true, "Banco");
  //
  $sql = "SELECT * FROM empresas";
  $comboEmpresas = $html->criaSelectSql("emp_nome", "idempresas", "pcom_idempresas", $reg['pcom_idempresas'], $sql, "form-control", '', true, "Empresa");
  //
  $sql = "SELECT * FROM tipo_contas";
  $comboBoxTipoConta = $html->criaSelectSql("tico_nome", "idtipo_contas", "pcom_idtipo_contas", $reg['pcom_idtipo_contas'], $sql, "form-control", '', true, "Tipo da Conta");
  //
  $comboBoxCC = "<font color='red'>*</font> Selecione o banco";
  //
  $btnGravarReabrir = '<button type="button" onclick="testaDados(\'gravar\')" class="btn btn-success">Gravar</button>';
  //
  $checkComEntrada = $html->defineChecked($reg['pcom_com_entrada']);
  //
  $escondeTab = 'd-none';
  //
  if($reg['idpedcompras'] > 0){ 
    $escondeTab = '';
    //
    if($reg['pcom_idbancos'] > 0){
      $sql = "SELECT * FROM cc WHERE cc_idbancos = " . $reg['pcom_idbancos'];
      $comboBoxCC = $html->criaSelectSql("cc_nome", "idcc", "pcom_idcc", $reg['pcom_idcc'], $sql, "form-control");
    }
    //
    if($reg['pcom_situacao'] == 'Fechado'){
      $pcom_situacao = "<kbd class='bg-success'><b>{$reg['pcom_situacao']}</b></kbd>";
      $btnGravarReabrir = '<button type="button" onclick="chamaGravar(\'reabrir\')" class="btn btn-warning">Reabrir</button>';
      $btnImprimir = '<button type="button" class="btn btn-info" data-target="#modelosImprimir" data-toggle="modal">Imprimir</button>';
    }else{
      $pcom_situacao = "<kbd><b>{$reg['pcom_situacao']}</b></kbd>";
    }
    if($reg['pcom_situacao'] == 'Aberto'){
      $btnFechar = '<button type="button" class="btn btn-warning" onclick="chamaGravar(\'fechar\')" >Fechar</button>';
      $btnExcluir = '<button type="button" onclick="excluiCadastro()" class="btn btn-danger">Excluir</button>';
      $btnParcelar = '<button type="button" onclick="gerarParcelas()" id="btnGerarParcelas" class="btn btn-primary" ><i class="fas fa-percent"></i> Parcelar</button>';
      $btnNovoProduto = '<button type="button" onclick="limpaModalProd()" class="btn btn-success" data-toggle="modal" data-target="#modalProdutos"><i class="fas fa-plus"></i> Produtos</button>';
    }
    //
    $readonly_parcelas = "";
    if($reg['forp_tipo'] == 'A Vista'){
      $readonly_parcelas = "readonly='true'";
    }
    //
    $listaProdutos = $pedcompras->retornaItensPedido($reg['idpedcompras']);
    $listaContas = $pedcompras->retornaContasPedido($reg['idpedcompras']);
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
  $html = str_replace("##autoComplete_Pessoas##", $codigo_js_pessoas, $html);
  $html = str_replace("##autoComplete_CampoPessoas##", $codigo_campo_pessoas, $html);
  $html = str_replace("##autoComplete_Produtos##", $codigo_js_produtos, $html);
  $html = str_replace("##autoComplete_CampoProdutos##", $codigo_campo_produtos, $html);
  $html = str_replace("##prod_nome##", '', $html);
  $html = str_replace("##idprodutos##", '', $html);
  $html = str_replace("##id_cadastro##", $reg['idpedcompras'], $html);
  $html = str_replace("##idpedcompras##", $reg['idpedcompras'], $html);
  $html = str_replace("##pcom_pessoa##", $reg['pess_nome'], $html);
  $html = str_replace("##pcom_idfornecedor##", $reg['idpessoas'], $html);
  $html = str_replace("##pcom_qte_parcelas##", $reg['pcom_qte_parcelas'], $html);
  $html = str_replace("##pcom_situacao##", $pcom_situacao, $html);
  $html = str_replace("##pcom_abertura##", str_replace(" ", "T", $reg['pcom_abertura']), $html);
  $html = str_replace("##pcom_fechamento##", str_replace(" ", "T", $reg['pcom_fechamento']), $html);
  $html = str_replace("##comboBoxFormaPagto##", $comboBoxFormaPagto, $html);
  $html = str_replace("##comboBoxMeioPagto##", $comboBoxMeioPagto, $html);
  $html = str_replace("##comboBoxBancos##", $comboBoxBancos, $html);
  $html = str_replace("##comboEmpresas##", $comboEmpresas, $html);
  $html = str_replace("##comboBoxCC##", $comboBoxCC, $html);
  $html = str_replace("##comboBoxTipoConta##", $comboBoxTipoConta, $html);
  $html = str_replace("##listaProdutos##", $listaProdutos, $html);
  $html = str_replace("##listaContas##", $listaContas, $html);
  $html = str_replace("##escondeTab##", $escondeTab, $html);
  $html = str_replace("##pcom_frete##", $util->formataMoeda($reg['pcom_frete'], 2, true), $html);
  $html = str_replace("##pcom_valor_desconto##", $util->formataMoeda($reg['pcom_valor_desconto'], 2, true), $html);
  $html = str_replace("##pcom_porc_desconto##", $util->formataMoeda($reg['pcom_porc_desconto'], 2, true), $html);
  $html = str_replace("##pcom_total_pedido##", $util->formataMoeda($reg['pcom_total_pedido'], 2, true), $html);
  $html = str_replace("##pcom_com_entrada##", $checkComEntrada, $html);
  $html = str_replace("##readonly_parcelas##", $readonly_parcelas, $html);
  $html = str_replace("##btnExcluir##", $btnExcluir, $html);
  $html = str_replace("##btnFechar##", $btnFechar, $html);
  $html = str_replace("##btnNovoProduto##", $btnNovoProduto, $html);
  $html = str_replace("##btnParcelar##", $btnParcelar, $html);
  $html = str_replace("##btnGravarReabrir##", $btnGravarReabrir, $html);
  $html = str_replace("##btnImprimir##", $btnImprimir, $html);
  echo $html; 
  exit;
?>