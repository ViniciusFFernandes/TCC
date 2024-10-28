<?php
    require_once("../_BD/conecta_login.php");
    require_once("tabelas.class.php");
    //
    $chavePgtoSistema = $parametros->buscaValor("sistema: chave de pagamento");
    $chavePgtoSac = $usuarios->busca_chave_pagto(true);
    //
    if($chavePgtoSistema != $chavePgtoSac){
        $btnAtualizar = '<button class="btn btn-success" onclick="atualizarChaveSistema()"> Atualizar Chave</button>';
    }else{
        $btnAtualizar = "Seu sistema já está com a chave de acesso atualizada!";
    }
    unset($_SESSION['mensagem'], $_SESSION['tipoMsg']);
    //
    //Abre o arquivo html e Inclui mensagens e trechos php
    $html = $html->buscaHtml(true);
    $html = str_replace("##Mensagem##", $msg, $html);
    $html = str_replace("##chavePgtoSistema##", $chavePgtoSistema, $html);
    $html = str_replace("##chavePgtoSac##", $chavePgtoSac, $html);
    $html = str_replace("##btnAtualizar##", $btnAtualizar, $html);
    echo $html;
    exit;
?>