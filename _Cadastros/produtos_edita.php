<?php
  require_once("../_BD/conecta_login.php");
  require_once("produtos.class.php");
  require_once("tabelas.class.php");
  require_once("autoComplete.class.php");
  //
  //Inicia classes nescessarias
  // $html = new html($db, $util);
  $produtos = new produtos($db);
  //
  //Gera o autoComplete 
  $autoComplete = new autoComplete();
  $codigo_js = $autoComplete->gerar("produtos", "pfor_idprodutos", "produtos LEFT JOIN unidades ON (prod_idunidades = idunidades)", "prod_nome", "idprodutos", "", "WHERE prod_tipo_produto = 'Materia Prima' AND UPPER(prod_nome) LIKE UPPER('##valor##%')");
  //
  //Operações do banco de dados
  if(!empty($_REQUEST['id_cadastro'])){
    $sql = "SELECT * 
            FROM produtos 
              LEFT JOIN unidades ON (prod_idunidades = idunidades) 
              LEFT JOIN grupos ON (prod_idgrupos = idgrupos) 
              LEFT JOIN subgrupos ON (prod_idsubgrupos = idsubgrupos) 
            WHERE idprodutos = {$_REQUEST['id_cadastro']}";
    $reg = $db->retornaUmReg($sql);
  }
  //
  //
  //Monta variaveis de exibição
  $sql = "SELECT * FROM unidades";
  $comboBoxUnidades = $html->criaSelectSql("uni_sigla", "idunidades", "prod_idunidades", $reg['prod_idunidades'], $sql, "form-control", "", true, "Selecione a Unidade");
  //
  $sql = "SELECT * FROM grupos";
  $comboBoxGrupos = $html->criaSelectSql("grup_nome", "idgrupos", "prod_idgrupos", $reg['prod_idgrupos'], $sql, "form-control", "", true, "Selecione o Grupo");
  //
  $sql = "SELECT * FROM subgrupos";
  $comboBoxSubGrupos = $html->criaSelectSql("subg_nome", "idsubgrupos", "prod_idsubgrupos", $reg['prod_idsubgrupos'], $sql, "form-control", "", true, "Selecione o Sub Grupo");
  //
  $comboBoxTipo     = $html->defineSelected("", $reg['prod_tipo_produto']);
  //
  if(!empty($reg['idprodutos'])){ 
    //
    $btnExcluir = '<button type="button" onclick="excluiCadastro()" class="btn btn-danger">Excluir</button>';
    //
    $comboBoxTipoPR   = $html->defineSelected("Produto Revenda", $reg['prod_tipo_produto']);
    $comboBoxTipoMP   = $html->defineSelected("Materia Prima", $reg['prod_tipo_produto']);
    $comboBoxTipoPC   = $html->defineSelected("Produto para Consumo", $reg['prod_tipo_produto']);
    $comboBoxTipoPP   = $html->defineSelected("Producao Propria", $reg['prod_tipo_produto']);
    $comboBoxTipoPV   = $html->defineSelected("Producao para Venda", $reg['prod_tipo_produto']);
    $comboBoxTipoV    = $html->defineSelected("Veiculo", $reg['prod_tipo_produto']);
    $comboBoxTipoBT   = $html->defineSelected("Brindes de Terceiros", $reg['prod_tipo_produto']);
    $comboBoxTipoBP   = $html->defineSelected("Brindes Próprios", $reg['prod_tipo_produto']);
    $comboBoxTipoE    = $html->defineSelected("Embalagem", $reg['prod_tipo_produto']);
    $comboBoxTipoIP   = $html->defineSelected("Imobilizado Proprio", $reg['prod_tipo_produto']);
    $comboBoxTipoPAT  = $html->defineSelected("Terceiros", $reg['prod_tipo_produto']);
    $comboBoxTipoO    = $html->defineSelected("Outros", $reg['prod_tipo_produto']);
    //
    //Carrega Tabs
    $tabs = '<ul class="nav nav-tabs">
              <li class="nav-item" ><a class="nav-link active" data-toggle="tab" href="#divVazia">-</a></li>';
    if($reg['prod_tipo_produto'] == "Producao Propria"){          
        $tabs .= '<li class="nav-item" ><a class="nav-link" data-toggle="tab" href="#formula">Formula</a></li>';
            }
    $tabs .=  '</ul>';
    $tabs .= '<div class="tab-content">
                <div id="divVazia" class="tab-pane fade"></div>';
    if($reg['prod_tipo_produto'] == "Producao Propria"){
       $tabs .= $produtos->getItensFormulaEdita($reg['idprodutos']);
    }
    $tabs .= '</div>';
    //
   
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
  $html = str_replace("##id_cadastro##", $reg['idprodutos'], $html);
  $html = str_replace("##comboBoxTipo##", $comboBoxTipo, $html);
  $html = str_replace("##comboBoxTipoPR##", $comboBoxTipoPR, $html);
  $html = str_replace("##comboBoxTipoMP##", $comboBoxTipoMP, $html);
  $html = str_replace("##comboBoxTipoPC##", $comboBoxTipoPC, $html);
  $html = str_replace("##comboBoxTipoPP##", $comboBoxTipoPP, $html);
  $html = str_replace("##comboBoxTipoPV##", $comboBoxTipoPV, $html);
  $html = str_replace("##comboBoxTipoV##", $comboBoxTipoV, $html);
  $html = str_replace("##comboBoxTipoBT##", $comboBoxTipoBT, $html);
  $html = str_replace("##comboBoxTipoBP##", $comboBoxTipoBP, $html);
  $html = str_replace("##comboBoxTipoE##", $comboBoxTipoE, $html);
  $html = str_replace("##comboBoxTipoIP##", $comboBoxTipoIP, $html);
  $html = str_replace("##comboBoxTipoPAT##", $comboBoxTipoPAT, $html);
  $html = str_replace("##comboBoxTipoO##", $comboBoxTipoO, $html);
  $html = str_replace("##prod_nome##", $reg['prod_nome'], $html);
  $html = str_replace("##prod_qte_estoque##", $reg['prod_qte_estoque'], $html);
  $html = str_replace("##prod_preco_tabela##", $reg['prod_preco_tabela'], $html);
  $html = str_replace("##comboBoxGrupos##", $comboBoxGrupos, $html);
  $html = str_replace("##comboBoxSubGrupos##", $comboBoxSubGrupos, $html);
  $html = str_replace("##comboBoxUnidades##", $comboBoxUnidades, $html);
  $html = str_replace("##autoComplete_Produtos##", $codigo_js, $html);
  $html = str_replace("##tabsProdutos##", $tabs, $html);
  $html = str_replace("##btnExcluir##", $btnExcluir, $html);
  echo $html;
  exit;
?>