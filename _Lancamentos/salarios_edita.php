<?php
  require_once("../_BD/conecta_login.php");
  require_once("salarios.class.php");
  //
  //Operações do banco de dados
  if(!empty($_REQUEST['id_cadastro'])){
    $sql = "SELECT *
            FROM salarios
            WHERE idsalarios = {$_REQUEST['id_cadastro']}";
    $reg = $db->retornaUmReg($sql);
  }
  //
  //Monta variaveis de exibição
  $btnGravar = '<button type="button" onclick="testaDados(\'gravar\')" class="btn btn-success">Gravar</button>';
  $btnImprimir = '';
  //
  $sql = "SELECT * FROM empresas";
  $comboEmpresas = $html->criaSelectSql("emp_nome", "idempresas", "sala_idempresas", $reg['sala_idempresas'], $sql, "form-control", '', true, "Empresa");
  //
  if(!empty($reg['idsalarios'])){ 
    //
    $salarios = new Salarios($db);
    $tabelaFuncionarios = $salarios->getFuncionarios($reg['idsalarios']);
    //
    $btnExcluir = '<button type="button" onclick="excluiCadastro()" class="btn btn-danger">Excluir</button>';
    //
    if($reg['sala_situacao'] == 'Aberto'){
      $btnFecharReabrir = '<button type="button" onclick="testaDados(\'fechar\')" class="btn btn-warning">Fechar</button>';
    }elseif($reg['sala_situacao'] == 'Fechado'){
      $btnFecharReabrir = '<button type="button" onclick="testaDados(\'reabrir\')" class="btn btn-warning">Reabrir</button>';
      $btnImprimir = '<button type="button" class="btn btn-primary" id="btnImprimir" onclick="imprimir(\'contapag_recibo_associado.php\', \'' . $reg['idsalarios'] . '&tipo=completo\')">Imprimir</button>';
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
  $html = str_replace("##id_cadastro##", $reg['idsalarios'], $html);
  $html = str_replace("##sala_data##", $util->convertData($reg['sala_data']), $html);
  $html = str_replace("##sala_data_fechamento##", $util->convertData($reg['sala_data_fechamento']), $html);
  $html = str_replace("##sala_mes##", $reg['sala_mes'], $html);
  $html = str_replace("##sala_ano##", $reg['sala_ano'], $html);
  $html = str_replace("##comboEmpresas##", $comboEmpresas, $html);
  $html = str_replace("##FuncionariosSalarios##", $tabelaFuncionarios, $html);
  $html = str_replace("##btnGravar##", $btnGravar, $html);
  $html = str_replace("##btnExcluir##", $btnExcluir, $html);
  $html = str_replace("##btnImprimir##", $btnImprimir, $html);
  $html = str_replace("##btnFecharReabrir##", $btnFecharReabrir, $html);
  echo $html;
  exit;
?>