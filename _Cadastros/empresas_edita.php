<?php
  require_once("../_BD/conecta_login.php");
  require_once("autoComplete.class.php");
  //
  //Operações do banco de dados
  if(!empty($_REQUEST['id_cadastro'])){
    $sql = "SELECT * 
            FROM empresas 
              LEFT JOIN cidades ON (emp_idcidades = idcidades) 
              LEFT JOIN estados ON (cid_idestados = idestados) 
            WHERE idempresas = {$_REQUEST['id_cadastro']}";
    $reg = $db->retornaUmReg($sql);
  }
  //
  //Gera o autoComplete 
  $autoComplete = new autoComplete();
  $codigo_js = $autoComplete->gerar("emp_cidades", "emp_idcidades", "cidades JOIN estados ON (cid_idestados = idestados)", "CONCAT(cid_nome, ' - ', est_uf)", "idcidades", "", "WHERE UPPER(cid_nome) LIKE UPPER('##valor##%')");
  $codigo_campo = $autoComplete->criaCampos("emp_cidades", "emp_idcidades", "Cidade");
  //
  //Monta variaveis de exibição
  if(!empty($reg['idempresas'])){ 
    $btnExcluir = '<button type="button" onclick="excluiCadastro()" class="btn btn-danger">Excluir</button>';
    //
    $checkLogoRelatoio = $html->defineChecked($reg['emp_logo_relatorio']);
    //
    if(!empty($reg['cid_nome'])){
      $cidade = $reg['cid_nome'] . " - " . $reg['est_uf'];
    }
    //
    $btnEnviaImg = ' <div class="row">
                      <div class="col-md-12 col-sm-12 col-12" align="center">
                        <form name="formUpload" id="formUpload" method="post" enctype="multipart/form-data">
                          <input type="hidden" name="operacaoAjax" value="enviarLogo">
                          <input type="hidden" name="id_cadastro" value="' . $reg['idempresas'] . '">
                          <label class="btn btn-light">
                            Selecionar Arquivo <input type="file" name="arquivo" id="inputLogo" hidden onchange="enviaLogo()">
                          </label> 
                          <div id="statusEnvio"></div>
                          <div class="progress" id="divProgress" style="display: none;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" width: 0%;" role="progressbar" id="progress" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">25%</div>
                          </div>
                          <br>
                        </form>
                      </div>
                      <div class="col-md-12 col-sm-12 col-12" align="center" id="imgLogo">';
    if($reg['emp_logo'] != ''){
      $btnEnviaImg .= '<img src="../uploads/' . $reg['emp_logo'] . '" class="img-fluid" />';
    }
    $btnEnviaImg .= ' </div>
                    </div>';
    //
    $btnModalEnviaLogo = '<span align="right" data-toggle="modal" title="Envia Logo" data-target="#enviaLogo" style="cursor: pointer;" onclick="limpaStatus()">
                            &nbsp;<i class="fas fa-camera text-dark"></i>
                          </span>';
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
  $html = str_replace("##id_cadastro##", $reg['idempresas'], $html);
  $html = str_replace("##emp_nome##", $reg['emp_nome'], $html);
  $html = str_replace("##emp_cnpj##", $reg['emp_cnpj'], $html);
  $html = str_replace("##emp_endereco##", $reg['emp_endereco'], $html);
  $html = str_replace("##emp_cep##", $reg['emp_cep'], $html);
  $html = str_replace("##emp_telefone##", $reg['emp_telefone'], $html);
  $html = str_replace("##autoComplete_Cidades##", $codigo_js, $html);
  $html = str_replace("##autoComplete_CampoCidades##", $codigo_campo, $html);
  $html = str_replace("##emp_cidades##", $cidade, $html);
  $html = str_replace("##emp_idcidades##", $reg['idcidades'], $html);
  $html = str_replace("##CheckLogoRelatoio##", $checkLogoRelatoio, $html);
  $html = str_replace("##btnExcluir##", $btnExcluir, $html);
  $html = str_replace("##btnEnviaImg##", $btnEnviaImg, $html);
  $html = str_replace("##btnModalEnviaLogo##", $btnModalEnviaLogo, $html);
  echo $html;
  exit;
?>