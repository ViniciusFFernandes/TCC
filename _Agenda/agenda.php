<?php
  require_once("../_BD/conecta_login.php");
  //
  //
  //Rotinas para agenda
  //
  //Abre o arquivo html e Inclui mensagens e trechos php
  $html = $html->buscaHtml(true);
  $html = str_replace("##Mensagem##", $msg, $html);
  $html = str_replace("##mesAtual##", $util->mesExtenso(date("m")), $html);
  $html = str_replace("##scriptGraficoContasPagas##", $scriptGraficoContasPagas, $html);
  $html = str_replace("##scriptGraficoContasRecebidas##", $scriptGraficoContasRecebidas, $html);
  $html = str_replace("##scriptGraficoContasTotais##", $scriptGraficoContasTotais, $html);

  echo $html;
  exit;
  
  ?>
