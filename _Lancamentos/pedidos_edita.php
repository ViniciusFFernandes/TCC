<?php
  require_once("../_BD/conecta_login.php");
  require_once("autoComplete.class.php");
  require_once("pedidos.class.php");
  //
  //Operações do banco de dados
  if(!empty($_REQUEST['id_cadastro'])){
    $sql = "SELECT * 
            FROM pedidos 
              LEFT JOIN pessoas ON (ped_idcliente = idpessoas)
              LEFT JOIN forma_pagto ON (ped_idforma_pagto = idforma_pagto)
            WHERE idpedidos = {$_REQUEST['id_cadastro']}";
    $reg = $db->retornaUmReg($sql);
  }
  //
  //Inicia a Classe
  $pedidos = New Pedidos($db);
  //
  //Gera o autoComplete 
  $autoComplete = new autoComplete();
  $codigo_js_pessoas = $autoComplete->gerar("ped_pessoa", "ped_idcliente", "pessoas LEFT JOIN cidades ON (pess_idcidades = idcidades) LEFT JOIN estados ON (cid_idestados = idestados)", "CONCAT(pess_nome, ', ', cid_nome, ' - ', est_uf)", "idpessoas", "", "WHERE UPPER(pess_nome) LIKE UPPER('##valor##%') AND (pess_cliente = 'SIM' OR pess_funcionario = 'SIM' OR pess_associado = 'SIM')");
  $codigo_campo_pessoas = $autoComplete->criaCampos("ped_pessoa", "ped_idcliente", "Cliente");
  //
  $codigo_js_produtos = $autoComplete->gerar("prod_nome", "idprodutos", "produtos", "prod_nome", "idprodutos", "", "WHERE UPPER(prod_nome) LIKE UPPER('##valor##%')", 10, 'buscaDadosProduto()');
  $codigo_campo_produtos = $autoComplete->criaCampos("prod_nome", "idprodutos", "Produto");
  //
  //Monta variaveis de exibição
  //
  $sql = "SELECT * FROM forma_pagto";
  $comboBoxFormaPagto = $html->criaSelectSql("forp_nome", "idforma_pagto", "ped_idforma_pagto", $reg['ped_idforma_pagto'], $sql, "form-control", 'onchange="alteraFormaPagto()"', true, "Forma de Pagamento");
  //
  //
  $sql = "SELECT * FROM meio_pagto";
  $comboBoxMeioPagto = $html->criaSelectSql("mpag_nome", "idmeio_pagto", "ped_idmeio_pagto", $reg['ped_idmeio_pagto'], $sql, "form-control", "", true, "Meio de Pagamento");
  //
  $sql = "SELECT * FROM bancos";
  $comboBoxBancos = $html->criaSelectSql("banc_nome", "idbancos", "ped_idbancos", $reg['ped_idbancos'], $sql, "form-control", 'onchange="carregaComboBoxCC()"', true, "Banco");
  //
  $sql = "SELECT * FROM empresas";
  $comboEmpresas = $html->criaSelectSql("emp_nome", "idempresas", "ped_idempresas", $reg['ped_idempresas'], $sql, "form-control", '', true, "Empresa");
  //
  $sql = "SELECT * FROM tipo_contas";
  $comboBoxTipoConta = $html->criaSelectSql("tico_nome", "idtipo_contas", "ped_idtipo_contas", $reg['ped_idtipo_contas'], $sql, "form-control", '', true, "Tipo da Conta");
  //
  $comboBoxCC = "<font color='red'>*</font> Selecione o banco";
  //
  $btnGravarReabrir = '<button type="button" onclick="testaDados(\'gravar\')" class="btn btn-success">Gravar</button>';
  //
  $checkComEntrada = $html->defineChecked($reg['ped_com_entrada']);
  //
  $escondeTab = 'd-none';
  //
  if($reg['idpedidos'] > 0){ 
    $escondeTab = '';
    //
    if($reg['ped_idbancos'] > 0){
      $sql = "SELECT * FROM cc WHERE cc_idbancos = " . $reg['ped_idbancos'];
      $comboBoxCC = $html->criaSelectSql("cc_nome", "idcc", "ped_idcc", $reg['ped_idcc'], $sql, "form-control");
    }
    //
    if($reg['ped_situacao'] == 'Fechado'){
      $ped_situacao = "<kbd class='bg-success'><b>{$reg['ped_situacao']}</b></kbd>";
      $btnGravarReabrir = '<button type="button" onclick="chamaGravar(\'reabrir\')" class="btn btn-warning">Reabrir</button>';
      $btnImprimir = '<button type="button" class="btn btn-info" data-target="#modelosImprimir" data-toggle="modal">Imprimir</button>';
    }else{
      $ped_situacao = "<kbd><b>{$reg['ped_situacao']}</b></kbd>";
    }
    if($reg['ped_situacao'] == 'Aberto'){
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
    $listaProdutos = $pedidos->retornaItensPedido($reg['idpedidos']);
    $listaContas = $pedidos->retornaContasPedido($reg['idpedidos']);
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
  $html = str_replace("##id_cadastro##", $reg['idpedidos'], $html);
  $html = str_replace("##idpedidos##", $reg['idpedidos'], $html);
  $html = str_replace("##ped_pessoa##", $reg['pess_nome'], $html);
  $html = str_replace("##ped_idcliente##", $reg['idpessoas'], $html);
  $html = str_replace("##ped_qte_parcelas##", $reg['ped_qte_parcelas'], $html);
  $html = str_replace("##ped_situacao##", $ped_situacao, $html);
  $html = str_replace("##ped_abertura##", str_replace(" ", "T", $reg['ped_abertura']), $html);
  $html = str_replace("##ped_fechamento##", str_replace(" ", "T", $reg['ped_fechamento']), $html);
  $html = str_replace("##comboBoxFormaPagto##", $comboBoxFormaPagto, $html);
  $html = str_replace("##comboBoxMeioPagto##", $comboBoxMeioPagto, $html);
  $html = str_replace("##comboBoxBancos##", $comboBoxBancos, $html);
  $html = str_replace("##comboEmpresas##", $comboEmpresas, $html);
  $html = str_replace("##comboBoxCC##", $comboBoxCC, $html);
  $html = str_replace("##comboBoxTipoConta##", $comboBoxTipoConta, $html);
  $html = str_replace("##listaProdutos##", $listaProdutos, $html);
  $html = str_replace("##listaContas##", $listaContas, $html);
  $html = str_replace("##escondeTab##", $escondeTab, $html);
  $html = str_replace("##ped_frete##", $util->formataMoeda($reg['ped_frete'], 2, true), $html);
  $html = str_replace("##ped_valor_desconto##", $util->formataMoeda($reg['ped_valor_desconto'], 2, true), $html);
  $html = str_replace("##ped_porc_desconto##", $util->formataMoeda($reg['ped_porc_desconto'], 2, true), $html);
  $html = str_replace("##ped_total_pedido##", $util->formataMoeda($reg['ped_total_pedido'], 2, true), $html);
  $html = str_replace("##ped_com_entrada##", $checkComEntrada, $html);
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