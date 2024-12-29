<?php
require_once("../_BD/conecta_login.php");
require_once("autoComplete.class.php");

    // Gerar autocomplete de produtos
    $autoComplete = new autoComplete();
    $codigo_js_produtos = $autoComplete->gerar("prod_nome", "idprodutos", "produtos", "prod_nome", "idprodutos", "", "WHERE UPPER(prod_nome) LIKE UPPER('##valor##%')", 10);
    $codigo_campo_produtos = $autoComplete->criaCampos("prod_nome", "idprodutos", "Produto");

    $html = $html->buscaHtml(true);
    $html = str_replace("##autoComplete_Produtos##", $codigo_js_produtos, $html);
    $html = str_replace("##autoComplete_CampoProdutos##", $codigo_campo_produtos, $html);
    $html = str_replace("##prod_nome##", '', $html);
    $html = str_replace("##idprodutos##", '', $html);
    echo $html; 
    exit;
?>