<?php
  require_once("../_BD/conecta_login.php");
  require_once("tabelas.class.php");
  require_once("autoComplete.class.php");
  //
  //Operações do banco de dados
  if(!empty($_REQUEST['id_cadastro'])){
    $sql = "SELECT *
            FROM coleta
              LEFT JOIN produtos ON (idprodutos = cole_idprodutos)
            WHERE idcoleta = {$_REQUEST['id_cadastro']}";
    $reg = $db->retornaUmReg($sql);
  }
  //
  //Gera o autoComplete 
  $autoComplete = new autoComplete();
  $codigo_js = $autoComplete->gerar("cole_produtos", "cole_idprodutos", "produtos", "prod_nome", "idprodutos", "", "WHERE UPPER(prod_nome) LIKE UPPER('##valor##%')");
  //
  //Monta variaveis de exibição
  $btnGravar = '<button type="button" onclick="testaDados(\'gravar\')" class="btn btn-success">Gravar</button>';
  if(!empty($reg['idcoleta'])){ 
    //
    $btnExcluir = '<button type="button" onclick="excluiCadastro()" class="btn btn-danger">Excluir</button>';
    $cole_situacao = "<b>" . $reg['cole_situacao'] . "</b>";
    //
    if($reg['cole_situacao'] == 'Aberta'){
      $btnFecharReabrir = '<button type="button" onclick="testaDados(\'fechar\')" class="btn btn-warning">Fechar</button>';
    }elseif($reg['cole_situacao'] == 'Fechada'){
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
  $html = str_replace("##id_cadastro##", $reg['idcoleta'], $html);
  $html = str_replace("##idcoleta##", $reg['idcoleta'], $html);
  $html = str_replace("##cole_situacao##", $cole_situacao, $html);
  $html = str_replace("##cole_data_entrada##", $reg['cole_data_entrada'], $html);
  $html = str_replace("##cole_produtos##", $reg['prod_nome'], $html);
  $html = str_replace("##cole_idprodutos##", $reg['cole_idprodutos'], $html);
  $html = str_replace("##cole_qte##", $reg['cole_qte'], $html);
  $html = str_replace("##cole_placa_veiculo##", $reg['cole_placa_veiculo'], $html);
  $html = str_replace("##btnGravar##", $btnGravar, $html);
  $html = str_replace("##btnExcluir##", $btnExcluir, $html);
  $html = str_replace("##btnFecharReabrir##", $btnFecharReabrir, $html);
  echo $html;
  exit;
?>