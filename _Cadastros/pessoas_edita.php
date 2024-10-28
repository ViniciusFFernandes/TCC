<?php
  require_once("../_BD/conecta_login.php");
  require_once("autoComplete.class.php");
  //
  //Operações do banco de dados
  if(!empty($_REQUEST['id_cadastro'])){
    $sql = "SELECT * 
            FROM pessoas 
              LEFT JOIN cidades ON (pess_idcidades = idcidades) 
              LEFT JOIN estados ON (cid_idestados = idestados) 
            WHERE idpessoas = {$_REQUEST['id_cadastro']}";
    $reg = $db->retornaUmReg($sql);
  }
  //
  //Gera o autoComplete 
  $autoComplete = new autoComplete();
  $codigo_js = $autoComplete->gerar("pess_cidades", "pess_idcidades", "cidades JOIN estados ON (cid_idestados = idestados)", "CONCAT(cid_nome, ' - ', est_uf)", "idcidades", "", "WHERE UPPER(cid_nome) LIKE UPPER('##valor##%')");
  $codigo_campo = $autoComplete->criaCampos("pess_cidades", "pess_idcidades", "Cidade");
  //
  //Monta variaveis de exibição
  $escondeDivTelefone = "style='display: none;'";
  //
  $sql = "SELECT * FROM setores";
  $comboBoxSetores = $html->criaSelectSql("set_nome", "idsetores", "pess_idsetores", $reg['pess_idsetores'], $sql, "form-control", "", true, "Selecione um Setor");
  //
  $sql = "SELECT * FROM funcoes";
  $comboBoxFuncoes = $html->criaSelectSql("func_nome", "idfuncoes", "pess_idfuncoes", $reg['pess_idfuncoes'], $sql, "form-control", "", true, "Selecione uma Função");
  //
  $sql = "SELECT * FROM empresas";
  $comboBoxEmpresas = $html->criaSelectSql("emp_nome", "idempresas", "pess_idempresas", $reg['pess_idempresas'], $sql, "form-control", "", true, "Selecione a Empresa");
  //
  if(SISTEMA_SAC == "SIM"){
    $divCodCliente = '<div class="col-md-4 col-sm-4 col-12 pb-3">';
      $divCodCliente .= '<input type="text" class="form-control" id="pess_cod_cliente" name="pess_cod_cliente" value="##pess_cod_cliente##" placeholder="Codigo Cliente" >';
    $divCodCliente .= '</div>';
    //
    $checkVip = '&nbsp;&nbsp;&nbsp;<span style= "white-space: nowrap;"><input type="checkbox" id="pess_vip" name="pess_vip" ##CheckVip## value="SIM">&nbsp;<label for="pess_vip" style="font-weight: normal !important;"> Vip </label></span>';
    $checkVip = str_replace("##CheckVip##", $html->defineChecked($reg['pess_vip']), $checkVip);
    //
    if(!empty($reg['idpessoas']) && !empty($reg['pess_cod_cliente'])){ 
      $btnGerarChave = '<button type="button" class="btn btn-primary mb-1" data-target="#modelosChave"  data-toggle="modal">Chave de Acesso</button>';
    }

  }
  //
  if(!empty($reg['idpessoas'])){ 
    $editaLogin = '<span align="right" data-toggle="modal" title="Cria/Edita login" data-target="#criaEditaLogin" style="cursor: pointer;color: #54565d;">';
    $editaLogin .=  '&nbsp;<i class="fas fa-user-lock"></i>';
    $editaLogin .= '</span>';
    //
    $btnExcluir = '<button type="button" onclick="excluiCadastro()" class="btn btn-danger mb-1">Excluir</button>';
    $btnImprimir = '<button type="button" class="btn btn-info mb-1" data-target="#modelosImprimir"  data-toggle="modal">Imprimir</button>';
    //
    $checkCliente = $html->defineChecked($reg['pess_cliente']);
    $checkFornecedor = $html->defineChecked($reg['pess_fornecedor']);
    $checkFuncionario = $html->defineChecked($reg['pess_funcionario']);
    $checkAssociado = $html->defineChecked($reg['pess_associado']);
    //
    if(!empty($reg['cid_nome'])){
      $cidade = $reg['cid_nome'] . " - " . $reg['est_uf'];
    }
    //
    $imgCarregandoTelefone = '<center><img src="../icones/carregando.gif" width="25px"></center>';
    $escondeDivTelefone = "";
    //
    $sql = "SELECT * FROM grupos_acessos WHERE IFNULL(grac_inativo, 0) <> 1";
    $comboGruposAcessos = $html->criaSelectSql("grac_nome", "idgrupos_acessos", "pess_idgrupos_acessos", $reg['pess_idgrupos_acessos'], $sql, "form-control");
    //
    if($reg['pess_inativo'] <> 'S'){
      $btnAtivarInativar = "<button type='button' class='btn btn-warning mb-1' onclick=\"ativoInativo('S')\">Inativar</button>";
    }else{
      $btnAtivarInativar = "<button type='button' class='btn btn-default mb-1' onclick=\"ativoInativo('N')\">Ativar</button>";
    }
    //
    $pess_chave_pagto = $reg['pess_chave_pagto'];
    if(empty($pess_chave_pagto)){
      $pess_chave_pagto = '<span class="Obs_claro">*Em Branco*</span>';
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
  $html = str_replace("##autoComplete_Cidades##", $codigo_js, $html);
  $html = str_replace("##autoComplete_CampoCidades##", $codigo_campo, $html);
  $html = str_replace("##grupos_acessos##", $comboGruposAcessos, $html);
  $html = str_replace("##caregando_telefone##", $imgCarregandoTelefone, $html);
  $html = str_replace("##esconde_div_telefone##", $escondeDivTelefone, $html);
  $html = str_replace("##EditaLogin##", $editaLogin, $html);
  $html = str_replace("##CheckCliente##", $checkCliente, $html);
  $html = str_replace("##CheckFornecedor##", $checkFornecedor, $html);
  $html = str_replace("##CheckFuncionario##", $checkFuncionario, $html);
  $html = str_replace("##CheckAssociado##", $checkAssociado, $html);
  $html = str_replace("##CheckVip##", $checkVip, $html);
  $html = str_replace("##id_cadastro##", $reg['idpessoas'], $html);
  $html = str_replace("##pess_nome##", $reg['pess_nome'], $html);
  $html = str_replace("##pess_cpf##", $reg['pess_cpf'], $html);
  $html = str_replace("##pess_cnpj##", $reg['pess_cnpj'], $html);
  $html = str_replace("##pess_rg##", $reg['pess_rg'], $html);
  $html = str_replace("##comboBoxSetores##", "$comboBoxSetores", $html);
  $html = str_replace("##comboBoxFuncoes##", $comboBoxFuncoes, $html);
  $html = str_replace("##comboBoxEmpresas##", $comboBoxEmpresas, $html);
  $html = str_replace("##pess_endereco##", $reg['pess_endereco'], $html);
  $html = str_replace("##pess_endereco_numero##", $reg['pess_endereco_numero'], $html);
  $html = str_replace("##pess_cidades##", $cidade, $html);
  $html = str_replace("##pess_idcidades##", $reg['idcidades'], $html);
  $html = str_replace("##pess_bairro##", $reg['pess_bairro'], $html);
  $html = str_replace("##pess_cep##", $reg['pess_cep'], $html);
  $html = str_replace("##pess_usuario##", $reg['pess_usuario'], $html);
  $html = str_replace("##pess_chave_pagto##", $pess_chave_pagto, $html);
  $html = str_replace("##divCodCliente##", $divCodCliente, $html);
  $html = str_replace("##pess_cod_cliente##", $reg['pess_cod_cliente'], $html);
  $html = str_replace("##btnExcluir##", $btnExcluir, $html);
  $html = str_replace("##btnImprimir##", $btnImprimir, $html);
  $html = str_replace("##btnGerarChave##", $btnGerarChave, $html);
  $html = str_replace("##btnAtivarInativar##", $btnAtivarInativar, $html);
  echo $html;
  exit;
?>