<?php
  require_once("../_BD/conecta_login.php");
  require_once("autoComplete.class.php");
  //
  //Operações do banco de dados
  if(!empty($_REQUEST['id_cadastro'])){
    $sql = "SELECT * 
            FROM contarec 
              LEFT JOIN pessoas ON (ctrc_idcliente = idpessoas)
            WHERE idcontarec = {$_REQUEST['id_cadastro']}";
    $reg = $db->retornaUmReg($sql);
  }
  //
  //Gera o autoComplete 
  $autoComplete = new autoComplete();
  $codigo_js = $autoComplete->gerar("ctrc_pessoa", "ctrc_idcliente", "pessoas LEFT JOIN cidades ON (pess_idcidades = idcidades) LEFT JOIN estados ON (cid_idestados = idestados)", "CONCAT(pess_nome, ', ', cid_nome, ' - ', est_uf)", "idpessoas", "", "WHERE UPPER(pess_nome) LIKE UPPER('##valor##%') AND (pess_cliente = 'SIM' OR pess_funcionario = 'SIM' OR pess_associado = 'SIM')");
  $codigo_campo = $autoComplete->criaCampos("ctrc_pessoa", "ctrc_idcliente", "Cliente");
  //
  //Monta variaveis de exibição
  //
  $sql = "SELECT * FROM tipo_contas";
  $comboBoxTipoConta = $html->criaSelectSql("tico_nome", "idtipo_contas", "ctrc_idtipo_contas", $reg['ctrc_idtipo_contas'], $sql, "form-control", "", true, "Tipo");
  //
  $sql = "SELECT * FROM meio_pagto";
  $comboBoxMeioPagto = $html->criaSelectSql("mpag_nome", "idmeio_pagto", "ctrc_idmeio_pagto", $reg['ctrc_idmeio_pagto'], $sql, "form-control", "", true, "Meio de Pagamento");
  $comboBoxMeioPagtoModal = $html->criaSelectSql("mpag_nome", "idmeio_pagto", "ctrc_idmeio_pagtoModal", '', $sql, "form-control", "", true, "Meio de Pagamento");
  //
  $sql = "SELECT * FROM bancos";
  $comboBoxBancos = $html->criaSelectSql("banc_nome", "idbancos", "ctrc_idbancos", $reg['ctrc_idbancos'], $sql, "form-control", 'onchange="carregaComboBoxCC()"', true, "Banco");
  $comboBoxBancosModal = $html->criaSelectSql("banc_nome", "idbancos", "ctrc_idbancosModal", '', $sql, "form-control", 'onchange="carregaComboBoxCC(\'Modal\')"', true, "Banco");
  //
  $sql = "SELECT * FROM empresas";
  $comboEmpresas = $html->criaSelectSql("emp_nome", "idempresas", "ctrc_idempresas", $reg['ctrc_idempresas'], $sql, "form-control", '', true, "Empresa");
  //
  $comboBoxCC = "<font color='red'>*</font> Selecione o banco";
  //
  $btnGravarReabrir = '<button type="button" onclick="testaDados(\'gravar\')" class="btn btn-success">Gravar</button>';
  //
  if(!empty($reg['idcontarec'])){ 
    if($reg['ctrc_idbancos'] > 0){
      $sql = "SELECT * FROM cc WHERE cc_idbancos = " . $reg['ctrc_idbancos'];
      $comboBoxCC = $html->criaSelectSql("cc_nome", "idcc", "ctrc_idcc", $reg['ctrc_idcc'], $sql, "form-control");
    }
    //
    $checkAVista = $html->defineChecked($reg['ctrc_a_vista']);
    //
    if($reg['ctrc_situacao'] == 'Quitada' || $reg['ctrc_situacao'] == "QParcial"){
      $ctrc_situacao = "<kbd class='bg-success'><b>{$reg['ctrc_situacao']}</b></kbd>";
      $btnGravarReabrir = '<button type="button" onclick="chamaGravar(\'reabrir\')" class="btn btn-warning">Reabrir</button>';
      $btnImprimir = '<button type="button" class="btn btn-info" data-target="#modelosImprimir"  data-toggle="modal">Imprimir</button>';
    }else{
      $ctrc_situacao = "<kbd><b>{$reg['ctrc_situacao']}</b></kbd>";
      $btnExcluir = '<button type="button" onclick="excluiCadastro()" class="btn btn-danger">Excluir</button>';
    }
    if($reg['ctrc_situacao'] == 'Pendente' || $reg['ctrc_situacao'] == "QParcial"){
      $btnPagar = '<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#pagarConta">Pagar</button>';
    }
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
  $html = str_replace("##autoComplete_Pessoas##", $codigo_js, $html);
  $html = str_replace("##autoComplete_CampoPessoas##", $codigo_campo, $html);
  $html = str_replace("##id_cadastro##", $reg['idcontarec'], $html);
  $html = str_replace("##idcontarec##", $reg['idcontarec'], $html);
  $html = str_replace("##ctrc_pessoa##", $reg['pess_nome'], $html);
  $html = str_replace("##ctrc_idcliente##", $reg['idpessoas'], $html);
  $html = str_replace("##ctrc_situacao##", $ctrc_situacao, $html);
  $html = str_replace("##ctrc_inclusao##", $reg['ctrc_inclusao'], $html);
  $html = str_replace("##ctrc_parcela##", $reg['ctrc_parcela'], $html);
  $html = str_replace("##ctrc_vencimento##", $reg['ctrc_vencimento'], $html);
  $html = str_replace("##ctrc_vlr_bruto##", $util->formataMoeda($reg['ctrc_vlr_bruto'], 2, true), $html);
  $html = str_replace("##ctrc_vlr_juros##", $util->formataMoeda($reg['ctrc_vlr_juros'], 2, true), $html);
  $html = str_replace("##ctrc_porc_juros##", $util->formataMoeda($reg['ctrc_porc_juros'], 2, true), $html);
  $html = str_replace("##ctrc_vlr_desconto##", $util->formataMoeda($reg['ctrc_vlr_desconto'], 2, true), $html);
  $html = str_replace("##ctrc_porc_desconto##", $util->formataMoeda($reg['ctrc_porc_desconto'], 2, true), $html);
  $html = str_replace("##ctrc_vlr_liquido##", $util->formataMoeda($reg['ctrc_vlr_liquido'], 2, true), $html);
  $html = str_replace("##ctrc_vlr_pago##", $util->formataMoeda($reg['ctrc_vlr_pago'], 2, true), $html);
  $html = str_replace("##ctrc_vlr_devedor##", $util->formataMoeda($reg['ctrc_vlr_devedor'], 2, true), $html);
  $html = str_replace("##vlr_pagamento##", $util->formataMoeda($reg['ctrc_vlr_devedor'], 2, true), $html);
  $html = str_replace("##data_pagto##", date("Y-m-d"), $html);
  $html = str_replace("##comboBoxTipoConta##", $comboBoxTipoConta, $html);
  $html = str_replace("##comboBoxMeioPagto##", $comboBoxMeioPagto, $html);
  $html = str_replace("##comboBoxMeioPagtoModal##", $comboBoxMeioPagtoModal, $html);
  $html = str_replace("##comboBoxBancos##", $comboBoxBancos, $html);
  $html = str_replace("##comboBoxBancosModal##", $comboBoxBancosModal, $html);
  $html = str_replace("##comboEmpresas##", $comboEmpresas, $html);
  $html = str_replace("##checkAVista##", $checkAVista, $html);
  $html = str_replace("##comboBoxCC##", $comboBoxCC, $html);
  $html = str_replace("##btnExcluir##", $btnExcluir, $html);
  $html = str_replace("##btnPagar##", $btnPagar, $html);
  $html = str_replace("##btnGravarReabrir##", $btnGravarReabrir, $html);
  $html = str_replace("##btnImprimir##", $btnImprimir, $html);
  echo $html; 
  exit;
?>